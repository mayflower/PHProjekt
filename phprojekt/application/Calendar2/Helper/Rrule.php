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
     * 'FREQ'     => DateInterval
     * 'INTERVAL' => int
     * 'UNTIL'    => DateTime (exclusive, meant for DatePeriod)
     * 'COUNT'    => int (The number of _RE_occurences, i.e. excluding the first event)
     */
    private $_rrule;

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
        $this->_first      = $first;
        $this->_rrule      = $this->_parseRrule($rrule);
        $this->_exceptions = $exceptions;
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

        if (!array_key_exists('FREQ', $this->_rrule)) {
            // There is no frequency in the rrule, so there's actually no recurrence
            if ($firstTs >= $startTs && $firstTs <= $endTs) {
                return array($this->_first);
            } else {
                return array();
            }
        }

        if (array_key_exists('COUNT', $this->_rrule)) {
            $period = new DatePeriod(
                $this->_first,
                $this->_rrule['FREQ'],
                $this->_rrule['COUNT']
            );
        } else if (array_key_exists('UNTIL', $this->_rrule)) {
            $period = new DatePeriod(
                $this->_first,
                $this->_rrule['FREQ'],
                $this->_rrule['UNTIL'] //TODO: Check if this explodes when using timezones.
            );
        } else {
            $period = new DatePeriod($this->_first, $this->_rrule['FREQ'], $end);
        }

        $ret = array();
        foreach ($period as $date) {
            // Work around http://bugs.php.net/bug.php?id=52454
            // 'Relative dates and getTimestamp increments by one day'
            $datestring = $date->format('Y-m-d H:i:s');
            $date = new Datetime($datestring, new DateTimeZone('UTC'));

            $ts = $date->getTimestamp();
            if ($startTs <= $ts && $ts <= $endTs && !in_array($date, $this->_exceptions)) {
                $ret[] = new Datetime($datestring, new DateTimeZone('UTC'));
            }
        }
        return $ret;
    }

    /**
     * Checks whether the given datetime is an occurence of this rrule.
     *
     * @param $time The time to check for.
     *
     * @return bool If the given time is an occurence of this rrule.
     */
    public function containsDate(Datetime $date)
    {
        $dates = $this->getDatesInPeriod($date, $date);

        return !empty($dates);
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

        // Parse interval
        $ret['INTERVAL'] = (int) $this->_parseRruleHelper($rrule, 'INTERVAL');
        if (empty($ret['INTERVAL'])) {
            $ret['INTERVAL'] = 1;
        }

        // Parse frequency
        $freq = $this->_parseRruleHelper($rrule, 'FREQ');
        if (empty($freq)) {
            // Violates RFC 5545
            throw new Exception('Rrule contains no FREQ');
        }
        switch ($freq) {
            case 'DAILY':
                $ret['FREQ'] = new DateInterval("P1D");
                break;
            case 'WEEKLY':
                $ret['FREQ'] = new DateInterval("P1W");
                break;
            case 'MONTHLY':
                $ret['FREQ'] = new DateInterval("P1M");
                break;
            case 'YEARLY':
                $ret['FREQ'] = new DateInterval("P1Y");
                break;
            default:
                // We don't know how to handle anything else.
                throw new Exception("Cannot handle rrule frequency $freq");
        }

        // Parse until
        $until = $this->_parseRruleHelper($rrule, 'UNTIL');
        if (!empty($until)) {
            if (strpos($until, 'TZID')) {
                // We have a time with timezone, can't handle that
                throw new Exception('Cannot handle rrules with timezone information');
            } else if (!strpos($until, 'Z')) {
                // A floating time, can't handle those either.
                throw new Exception('Cannot handle floating times in rrules.');
            }
            // until is yyyymmddThhiissZ. Z represents UTC, but php doesn't understand this.
            $ret['UNTIL'] = new Datetime(substr($until, 0, 15), new DateTimeZone('UTC'));
        }

        // Parse count
        $count = $this->_parseRruleHelper($rrule, 'COUNT');
        if ($count === 0) {
            throw new Exception('Rrule with COUNT=0 means event never occurs');
        } else if (!empty($count)) {
            if (array_key_exists('UNTIL', $ret)) {
                throw new Exception('Both UNTIL and COUNT appear in one rrule (violates rfc5545)');
            }
            // iCalendar counts the first occurence, while php does not.
            $ret['COUNT'] = $count - 1;
        }

        return $ret;
    }

    /**
     * Helper function for parseRrule
     */
    private function _parseRruleHelper($rrule, $prop)
    {
        $matches = array();
        preg_match("/$prop=([^;]+)/", $rrule, $matches);

        if (array_key_exists(1, $matches)) {
            return $matches[1];
        } else {
            return null;
        }
    }

}
