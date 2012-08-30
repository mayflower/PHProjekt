<?php
/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * Calendar2 recurrence rule Helper.
 *
 * This class is used to create a set of dates from a start date and an
 * recurrence rule.
 *
 * The rrule format is defined in RFC 5545.
 */
class Calendar2_Helper_Rrule
{
    /**
     * @var Datetime The first occurrence of the event.
     */
    private $_first;

    /**
     * @var array of String => mixed The rrule properties.
     *
     * 'FREQ'         => DateInterval between occurences
     * 'INTERVAL'     => int
     * 'FREQINTERVAL' => FREQUENCY, but INTERVAL times
     * 'UNTIL'        => DateTime (inclusive)
     *                   (Note that DatePeriod expects exclusive UNTIL values)
     */
    private $_rrule;

    /**
     * The original rrule string.
     *
     * @var string
     */
    private $_rruleString;

    /**
     * @var array of Datetime Dates to exclude.
     */
    private $_exceptions;

    /**
     * @var int Timestamp delta that marks the duration of the events.
     */
    private $_duration;

    /**
     * @var dateInterval original duration
     */
    private $_durationDT;

    /**
     * Constructor.
     *
     * @param Datetime          $first      The first occurence of the event.
     * @param DateInterval      $duration   The duration of the events.
     * @param String            $rrule      The recurrence rule.
     * @param Array of Datetime $exceptions Exceptions from the recurrence.
     */
    public function __construct(Datetime $first, DateInterval $duration, $rrule, Array $exceptions = array())
    {
        $this->_first       = $first;
        $this->_rrule       = $this->_parseRrule($rrule);
        $this->_rruleString = $rrule;
        $this->_exceptions  = $exceptions;

        $tmp = clone $first;
        $tmp->add($duration);
        $this->_duration    = $tmp->getTimestamp() - $first->getTimestamp();
        $this->_durationDT  = $duration;
    }

    /**
     * Retrieves all the single events in the given period.
     * Both given times are inclusive.
     *
     * @param Datetime $start The start of the period.
     * @param datetime $end   The end of the period.
     *
     * @return Array of Datetime The single events sorted in ascending order.
     */
    public function getDatesInPeriod(Datetime $start, Datetime $end)
    {
        $firstTs = $this->_first->getTimestamp();
        $startTs = $start->getTimestamp();
        $endTs   = $end->getTimestamp();

        if (empty($this->_rrule)) {
            // There is no recurrence
            if ($firstTs + $this->_duration >= $startTs && $firstTs <= $endTs) {
                return array($this->_first);
            } else {
                return array();
            }
        }

        if (!is_null($this->_rrule['UNTIL'])) {
            $until = clone $this->_rrule['UNTIL'];
        } else {
            $until = clone $end;
        }
        // php datePeriod also excludes the last occurence. we need it, so
        // we add one second.
        $until->modify('+1 second');

        $datePeriods = array();
        $dates = array();
        if ($this->_rrule['ORIGINAL_FREQ'] != 'WEEKLY' || empty($this->_rrule['BYDAY'])) {
            $datePeriods[] = new DatePeriod(
                $this->_first,
                $this->_rrule['FREQINTERVAL'],
                $until
            );
        } else {
            if ($firstTs >= $startTs && $firstTs <= $endTs) {
                $firstDay = strtoupper(substr($this->_first->format('D'), 0, 2));
                if (!in_array($firstDay, $this->_rrule['BYDAY'])) {
                    $dates[] = clone $this->_first;
                }
            }
            $first  = clone $this->_first;
            $oneDay = new DateInterval('P1D');
            for ($i = 0; $i < 7; $i++) {
                $day = strtoupper(substr($first->format('D'), 0, 2));
                if (in_array($day, $this->_rrule['BYDAY'])) {
                    $datePeriods[] = new DatePeriod(
                        $first,
                        $this->_rrule['FREQINTERVAL'],
                        $until
                    );
                }
                $first->add($oneDay);
            }
        }

        $dateSeries = array();
        foreach ($datePeriods as $k => $period) {
            $series = $this->_periodToArray($period, $startTs, $endTs);
            if (!empty($series)) {
                $dateSeries[] = $series;
            }
        }

        // Assumptions hold. All periods where created with the same duration. They were in order in $datePeriods, so
        // they are correctly ordered in $dateSeries, too.
        $dates = $this->_mergeSequences($dateSeries);

        if ($this->_rrule['ORIGINAL_FREQ'] == 'DAILY' && !empty($this->_rrule['BYDAY'])) {
            foreach ($dates as $key => $date) {
                $day = strtoupper(substr($date->format('D'), 0, 2));
                if (!in_array($day, $this->_rrule['BYDAY'])) {
                    unset($dates[$key]);
                }
            }
        }
        return $dates;
    }

    /**
     * Retrieves all events from an dateperiod that lie between two points in time.
     *
     * This takes event duration in account.
     */
    private function _periodToArray(DatePeriod $period, $startTs, $endTs)
    {
        $ret = array();
        foreach ($period as $date){
            // Work around http://bugs.php.net/bug.php?id=52454
            // 'Relative dates and getTimestamp increments by one day'
            $datestring = $date->format('Y-m-d H:i:s');
            $date       = new Datetime($datestring, new DateTimeZone('UTC'));

            $ts = $date->getTimestamp();
            if ($startTs <= $ts + $this->_duration && $ts <= $endTs && !in_array($date, $this->_exceptions)) {
                $dt = new Datetime('@' . $ts);
                $dt->setTimezone(new DateTimeZone('utc'));
                $ret[] = $dt;
            } else if ($ts > $endTs) {
               break;
            }
        }

        return $ret;
    }

    /**
     * Merges sorted event sequences.
     *
     * This makes the following assumptions:
     *  1. All arrays in $sequences have the same spacing between events,
     *      i.e. the interval used to create the dateperiods is the same.
     *  2. $sequences is sorted in regard to the first element of each inner array
     *  3. All first elements of the series are before any of the second elements.
     *      (With 1. this holds for all n and n+1)
     */
    private function _mergeSequences(array $sequences)
    {
        $ret = array();

        while (!empty($sequences)) {
            foreach (array_keys($sequences) as $k) {
                $ret[] = array_shift($sequences[$k]);
                if (empty($sequences[$k])) {
                    unset($sequences[$k]);
                }
            }
        }

        return $ret;
    }

    /**
     * Checks whether a datetime is one of the dates described by this rrule.
     *
     * @param Datetime $date The time to check for.
     *
     * @return bool If the given time is an occurence of this rrule.
     */
    public function containsDate(Datetime $date)
    {
        $dates = $this->getDatesInPeriod($date, $date);

        // We have to re-check because getDatesInPeriod also gives us events that start before but end after $date
        foreach ($dates as $d) {
            if ($d == $date) {
                return true;
            }
        }

        return false;
    }

    /**
     * Splits this helper's rrule in 2 parts, one for all events before the
     * split date and one for all other occurences.
     *
     * The returned array will have the form of
     *      {'old' => string, 'new' => string}
     *
     * @param Datetime $splitDate The first occurence of the second part.
     *
     * @return array See description
     */
    public function splitRrule(Datetime $splitDate)
    {
        if (empty($this->_rrule)) {
            return array('old' => '', 'new' => '');
        } elseif (is_null($this->_rrule['UNTIL'])) {
            // The recurrence never ends, no need to calculate anything
            $last  = $this->lastOccurrenceBefore($splitDate);
            $until = "UNTIL={$last->format('Ymd\THis\Z')};";
            $old   = $until . $this->_rruleString;
        } else {
            $last = $this->lastOccurrenceBefore($splitDate);

            $old = preg_replace(
                '/UNTIL=[^;]*/',
                "UNTIL={$last->format('Ymd\THis\Z')}",
                $this->_rruleString
            );
        }

        return array('old' => $old, 'new' => $this->_rruleString);
    }

    /**
     * Checks whether the given Datetime is the last occurrence of this series.
     *
     * @param Datetime $datetime The datetime to check for.
     *
     * @return bool Whether the given datetime is the last occurrence.
     */
    public function isLastOccurrence(Datetime $datetime)
    {
        if (empty($this->_rrule)) {
            return $datetime == $this->_first;
        }

        $until = $this->_rrule['UNTIL'];

        if (is_null($until)) {
            return false;
        } else {
            $events = $this->getDatesInPeriod($this->_first, $until);
            $last   = $events[count($events) - 1];
            return $datetime->getTimestamp() == $last->getTimestamp();
        }
    }

    /**
     * Checks whether the given Datetime is the first occurrence of this series.
     *
     * @param Datetime $datetime The datetime to check for.
     *
     * @return bool Whether the given datetime is the first occurrence.
     */
    public function isFirstOccurrence(Datetime $datetime)
    {
        if ($this->_rrule['ORIGINAL_FREQ'] != 'WEEKLY' || empty($this->_rrule['BYDAY'])) {
            return $this->_first == $datetime;
        } else {
            $first = clone $this->_first;
            $oneDay = new DateInterval('P1D');
            for ($i = 0; $i < 7; $i++) {
                $day = strtoupper(substr($first->format('D'), 0, 2));
                if (in_array($day, $this->_rrule['BYDAY'])) {
                    return $datetime->getTimestamp() == $first->getTimestamp();
                }
            }
            throw new Exception('Could not find first occurrence. Invalid BYDAY?');
        }
    }

    /**
     * Returns the first occurrence after the given datetime
     * This assumes that the given date is a valid occurrence.
     * If this is the last occurrence, null will be returned.
     *
     * @param Datetime $datetime The datetime after which to look.
     *
     * @return Datetime The first occurrence after $datetime or null.
     */
    public function firstOccurrenceAfter(Datetime $datetime)
    {
        if (!$this->containsDate($datetime)) {
            throw new Exception('Invalid Datetime given.');
        }

        if (empty($this->_rrule)) {
            return null;
        }

        $datetime = clone $datetime;

        if ($this->_rrule['ORIGINAL_FREQ'] == 'WEEKLY' && !empty($this->_rrule['BYDAY'])) {
            // Add $interval weeks first.
            $datetime->add(new DateInterval('P' . $this->_rrule['INTERVAL'] . 'W'));
            $oneDay = new DateInterval('P1D');
            for ($i = 0; $i <= 7; $i++) {
                $day = strtoupper(substr($datetime->format('D'), 0, 2));
                if (in_array($day, $this->_rrule['BYDAY'])) {
                    if (in_array($datetime, $this->_exceptions)) {
                        $i = 0; // Give it another full week from here.
                    } else {
                        return $datetime;
                    }
                }
                $datetime->add($oneDay);
                if (!is_null($this->_rrule['UNTIL'])
                        && $datetime->getTimestamp() > $this->_rrule['UNTIL']->getTimestamp()) {
                    // The event doesn't occur any more.
                    return null;
                }
            }
            throw new Exception('Unable to find next occurrence. Invalid BYDAY?');
        } else {
            do {
                $datetime->add($this->_rrule['FREQINTERVAL']);
            } while (in_array($datetime, $this->_exceptions));

            $until = $this->_rrule['UNTIL'];
            if (!is_null($until) && $until->getTimestamp() < $datetime->getTimestamp()) {
                    return null;
            }
            return $datetime;
        }
    }
    /**
     * Returns the last occurrence before the given datetime
     * This assumes that the given date is a valid occurrence.
     * If this is the first occurrence, null will be returned.
     *
     * @param Datetime $datetime The datetime until which to look.
     *
     * @return Datetime The last occurrence before $datetime
     */
    public function lastOccurrenceBefore(Datetime $datetime)
    {
        if (!$this->containsDate($datetime)) {
            throw new Exception("Invalid Datetime {$datetime->format('Y-m-d H:i:s')} given.");
        }

        if ($datetime == $this->_first) {
            return null;
        }

        $datetime = clone $datetime;

        if ($this->_rrule['ORIGINAL_FREQ'] == 'WEEKLY' && !empty($this->_rrule['BYDAY'])) {
            $oneDay = new DateInterval('P1D');
            for ($i = 0; $i < 7; $i++) {
                $datetime->sub($oneDay);
                if ($datetime->getTimestamp < $this->_first->getTimestamp()) {
                    // The event doesn't occur any more.
                    return null;
                }
                $day = strtoupper(substr($datetime->format('D'), 0, 2));
                if (in_array($day, $this->_rrule['BYDAY'])) {
                    if (in_array($datetime, $this->_exceptions)) {
                        $i = 0; // Give it another full week from here.
                    } else {
                        return $datetime;
                    }
                }
            }
            throw new Exception('Unable to find next occurrence. Invalid BYDAY?');
        } else {
            do {
                $datetime->sub($this->_rrule['FREQINTERVAL']);
            } while (in_array($datetime, $this->_exceptions));

            if ($datetime->getTimestamp() < $this->_first->getTimestamp()) {
                return null;
            }
            return $datetime;
        }
    }

    /**
     * Returns a textual representation of the rrule.
     *
     * @return string The recurrence in human-readable form.
     */
    public function getHumanreadableRrule()
    {
        if (empty($this->_rrule)) {
            return '';
        }

        $ret   = 'Every ';
        $interval = $this->_rrule['INTERVAL'];
        switch ($interval) {
            case 1:
                break;
            case 2:
                $ret .= 'other ';
                break;
            default:
                $ret .= $this->_rrule['INTERVAL'] . ' ';
                break;
        }

        if (empty($this->_rrule['BYDAY'])) {
            $wordsFromFreq = array(
                'DAILY'   => 'day',
                'WEEKLY'  => 'week',
                'MONTHLY' => 'month',
                'YEARLY'  => 'year'
            );
            $freq = self::_extractFromRrule($this->_rruleString, 'FREQ');
            if (!array_key_exists($freq, $wordsFromFreq)) {
                // This should be found on __construct
                throw new Exception('No valid FREQ found in rrule');
            }
            $ret .= $wordsFromFreq[$freq];

            if ($interval > 2) {
                $ret .= 's';
            }
        } else {
            $realDays = array(
                'MO' => 'Monday',
                'TU' => 'Tuesday',
                'WE' => 'Wednesday',
                'TH' => 'Thursday',
                'FR' => 'Friday',
                'SA' => 'Saturday',
                'SU' => 'Sunday'
            );
            $days = $this->_rrule['BYDAY'];
            $day  = array_shift($days);
            $ret .= $realDays[$day];
            $count = count($days);
            for ($i = 0; $i < $count - 1; $i++) {
                $ret .= ', ' . $realDays[$days[$i]];
            }
            if ($count > 0) {
                $ret .= ' and ' . $realDays[$days[$count - 1]];
            }
        }

        if (!is_null($this->_rrule['UNTIL'])) {
            $ret .= " until {$this->_rrule['UNTIL']->format('Y-m-d')}";
        }

        return $ret;
    }

    /**
     * Returns a point in time that lies after the last event of this occurrence has finished.
     * The point of this is to get an upper bound for efficiency reasons.
     *
     * @return DateTime Some time after the last event has finished.
     */
    public function getUpperTimeBoundary()
    {
        if (empty($this->_rrule)) {
            // No recurrence at all
            $tmp = clone $this->_first;
            $tmp->add($this->_durationDT);
            return $tmp;
        }
        if (!array_key_exists('UNTIL', $this->_rrule) || empty($this->_rrule['UNTIL'])) {
            // Unlimited recurrence
            return null;
        }

        $until = clone $this->_rrule['UNTIL'];
        $until->add($this->_durationDT);
        return $until;
    }

    /**
     * Parses a rrule string into a dictionary while working around all
     * specialities of iCalendar, so we have values in $this->_rrule that
     * a php programmer would expect. See there for exact documentation.
     *
     * @param string $rrule The rrule.
     *
     * @return array of string => mixed The properties and their values.
     */
    private function _parseRrule($rrule)
    {
        if (empty($rrule)) {
            return array();
        }

        $ret = array();
        $ret['INTERVAL']      = self::_parseInterval($rrule);
        $ret['ORIGINAL_FREQ'] = self::_extractFromRrule($rrule, 'FREQ');
        $ret['FREQ']          = self::_parseFreq($rrule);
        $ret['UNTIL']         = self::_parseUntil($rrule);
        $ret['BYDAY']         = self::_parseByDay($rrule);

        // Apply FREQ INTERVAL times
        $tmp    = new Datetime();
        $frqint = clone $tmp;
        for ($i = 0; $i < $ret['INTERVAL']; $i++) {
            $frqint->add($ret['FREQ']);
        }
        $ret['FREQINTERVAL'] = $tmp->diff($frqint);

        return $ret;
    }

    private static function _parseInterval($rrule)
    {
        $interval = self::_extractFromRrule($rrule, 'INTERVAL');
        if (is_null($interval)) {
            $interval = 1;
        } else if (0 >= $interval) {
            throw new Exception('Negative or Zero Intervals not permitted.');
        }
        return (int) $interval;
    }

    private static function _parseFreq($rrule)
    {
        $freq = self::_extractFromRrule($rrule, 'FREQ');
        if (empty($freq)) {
            // Violates RFC 5545
            throw new Exception('Rrule contains no FREQ');
        }
        switch ($freq) {
            case 'DAILY':
                return new DateInterval("P1D");
                break;
            case 'WEEKLY':
                return new DateInterval("P1W");
                break;
            case 'MONTHLY':
                return new DateInterval("P1M");
                break;
            case 'YEARLY':
                return new DateInterval("P1Y");
                break;
            default:
                // We don't know how to handle anything else.
                throw new Exception("Cannot handle rrule frequency $freq");
        }
    }

    private static function _parseUntil($rrule)
    {
        // Format is yyyymmddThhiissZ.
        $until = self::_extractFromRrule($rrule, 'UNTIL');
        if (!empty($until)) {
            if (false !== strpos($until, 'TZID')) {
                // We have a time with timezone, can't handle that
                throw new Exception(
                    'Cannot handle rrules with timezone information'
                );
            } else if (false === strpos($until, 'Z')) {
                // A floating time, can't handle those either.
                throw new Exception('Cannot handle floating times in rrules.');
            } else if (!preg_match('/\d{8}T\d{6}Z/', $until)) {
                throw new Exception('Malformed until');
            }

            // php doesn't understand ...Z as an alias for UTC
            $return = new Datetime(
                substr($until, 0, 15),
                new DateTimeZone('UTC')
            );
            return $return;
        }
        return null;
    }

    private static function _parseByDay($rrule)
    {
        $byday = self::_extractFromRrule($rrule, 'BYDAY');
        if (!empty($byday)) {
            return explode(',', $byday);
        }
        return null;
    }

    /**
     * Helper function for parseRrule
     */
    private static function _extractFromRrule($rrule, $prop)
    {
        $matches = array();
        $prop = preg_quote($prop, '/');
        preg_match("/$prop=([^;]+)/", $rrule, $matches);

        if (array_key_exists(1, $matches)) {
            return $matches[1];
        } else {
            return null;
        }
    }

}
