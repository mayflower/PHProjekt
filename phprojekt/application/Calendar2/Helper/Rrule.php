<?php
/**
 * Calendar2 recurrence rule helper.
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Calendar2
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: @package_version@
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 */

/**
 * Calendar2 recurrence rule Helper.
 *
 * This class is used to create a set of dates from a start date and an
 * recurrence rule.
 *
 * The rrule format is defined in RFC 5545.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Calendar2
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: @package_version@
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
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
     * 'FREQ'     => DateInterval between occurences
     * 'INTERVAL' => int
     * 'UNTIL'    => DateTime (exclusive, meant for DatePeriod)
     * 'COUNT'    => int (The number of _RE_occurences, i.e. excluding the first event)
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
     * Constructor.
     *
     * @param Datetime $first   The first occurence of the event.
     * @param String   $rrule   The recurrence rule.
     * @param Array of Datetime Exceptions from the recurrence.
     */
    public function __construct(Datetime $first, $rrule, Array $exceptions = array())
    {
        $this->_first       = $first;
        $this->_rrule       = $this->_parseRrule($rrule);
        $this->_rruleString = $rrule;
        $this->_exceptions  = $exceptions;
    }

    /**
     * Retrieves all the single events in the given period.
     *
     * @param Datetime $start The start of the period.
     * @param datetime $end   The end of the period.
     *
     * @return Array of Datetime The single events.
     */
    public function getDatesInPeriod(Datetime $start, Datetime $end)
    {
        $firstTs = $this->_first->getTimestamp();
        $startTs = $start->getTimestamp();
        $endTs   = $end->getTimestamp();

        if (is_null($this->_rrule['FREQ'])) {
            // There is no frequency in the rrule, so there's actually no recurrence
            if ($firstTs >= $startTs && $firstTs <= $endTs) {
                return array($this->_first);
            } else {
                return array();
            }
        }

        if (!is_null($this->_rrule['COUNT'])) {
            $period = new DatePeriod(
                $this->_first,
                $this->_rrule['FREQ'],
                $this->_rrule['COUNT']
            );
        } else if (!is_null($this->_rrule['UNTIL'])) {
            $period = new DatePeriod(
                $this->_first,
                $this->_rrule['FREQ'],
                $this->_rrule['UNTIL']
            );
        } else {
            $period = new DatePeriod($this->_first, $this->_rrule['FREQ'], $end);
        }

        $ret = array();
        foreach ($period as $date) {
            // Work around http://bugs.php.net/bug.php?id=52454
            // 'Relative dates and getTimestamp increments by one day'
            $datestring = $date->format('Y-m-d H:i:s');
            $date       = new Datetime($datestring, new DateTimeZone('UTC'));

            $ts = $date->getTimestamp();
            if ($startTs <= $ts && $ts <= $endTs && !in_array($date, $this->_exceptions)) {
                $ret[] = new Datetime($datestring, new DateTimeZone('UTC'));
            } else if ($ts > $endTs) {
               break;
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

        return (0 < count($dates));
    }

    /**
     * Splits this helper's rrule in 2 parts, one for all events before the
     * split date and one for all other occurences.
     *
     * @param Datetime $splitDate The first occurence of the second part.
     *
     * @return array See description
     */
    public function splitRrule(Datetime $splitDate)
    {
        // This only supports rrules with either until or no ending

        if (!is_null($this->_rrule['COUNT'])) {
            //TODO: Have a close look for other cases where this might break.
            throw new Exception('Rrules with count not fully supported yet');
        }

        if (is_null($this->_rrule['UNTIL'])) {
            // The recurrence never ends, no need to calculate anything
            $old = $this->_rruleString;
        } else {
            $dates = $this->getDatesInPeriod($this->_first, $splitDate);
            $lastBeforeSplit = $dates[count($dates) - 2];

            $old = preg_replace(
                '/UNTIL=[^;]*/',
                "UNTIL={$lastBeforeSplit->format('Ymd\THis\Z')}",
                $this->_rruleString
            );
        }

        return array('old' => $old, 'new' => $this->_rruleString);
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
            return array(
                'FREQ'     => null,
                'INTERVAL' => null,
                'COUNT'    => null,
                'UNTIL'    => null
            );
        }

        $ret = array();
        $ret['INTERVAL']  = self::_parseInterval($rrule);
        $ret['FREQ']      = self::_parseFreq($rrule);
        $ret['UNTIL']     = self::_parseUntil($rrule);
        $ret['COUNT']     = self::_parseCount($rrule);

        if (is_null($ret['UNTIL']) && is_null($ret['COUNT']))
        {
            throw new Exception('Rrule contains neither COUNT nor UNTIL.');
        }

        return $ret;
    }

    private static function _parseInterval($rrule)
    {
        $interval = (int) self::_extractFromRrule($rrule, 'INTERVAL');
        if (is_null($interval)) {
            $interval = 1;
        } else if (0 >= $interval) {
            throw new Exception('Negative or Zero Intervals not permitted.');
        }
        return $interval;
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
                throw new Exception('Cannot handle rrules with timezone information');
            } else if (false === strpos($until, 'Z')) {
                // A floating time, can't handle those either.
                throw new Exception('Cannot handle floating times in rrules.');
            } else if (!preg_match('/\d{8}T\d{6}Z/', $until)) {
                throw new Exception('Malformed until');
            }

            // php doesn't understand ...Z as an alias for UTC
            $return = new Datetime(substr($until, 0, 15), new DateTimeZone('UTC'));
            // php datePeriod also excludes the last occurence. we need it, so
            // we add one second.
            $return->modify('+1 second');
            return $return;
        }
        return null;
    }

    private static function _parseCount($rrule)
    {
        // Parse count
        $count = self::_extractFromRrule($rrule, 'COUNT');
        if (!empty($count)) {
            // iCalendar counts the first occurence, while php does not.
            $count = $count - 1;
        } else if (!is_null($count)) {
            throw new Exception('Count 0 is invalid.');
        }

        return $count;
    }

    /**
     * Helper function for parseRrule
     */
    private static function _extractFromRrule($rrule, $prop)
    {
        //TODO: Maybe optimize this to a single match?
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
