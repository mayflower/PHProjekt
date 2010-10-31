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
 * The rrule format is defined in the iCalendar rfc.
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
    private $first;

    /**
     * @var array of String => String The rrule properties.
     */
    private $rrule;

    /**
     * @var array of Datetime Dates to exclude.
     */
    private $exceptions;

    /**
     * Constructor.
     *
     * @param Datetime $first   The first occurence of the event.
     * @param String   $rrule   The recurrence rule.
     * @param Array of Datetime Exceptions from the recurrence.
     */
    public function __construct(Datetime $first, $rrule, Array $exceptions = array())
    {
        $this->first      = $first;
        $this->rrule      = $this->parseRrule($rrule);
        $this->exceptions = $exceptions;
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
        //TODO: Refactor this in multiple methods.
        $firstTs = $this->first->getTimestamp();
        $startTs = $start->getTimestamp();
        $endTs   = $end->getTimestamp();

        if (!array_key_exists('FREQ', $this->rrule)) {
            // There is no frequency in the rrule, so there's actually no recurrence
            if ($firstTs >= $startTs && $firstTs <= $endTs) {
                return array($this->first);
            } else {
                return array();
            }
        }

        $interval = 1;
        if (array_key_exists('INTERVAL', $this->rrule)) {
            $interval = $this->rrule['INTERVAL'];
        }

        switch ($this->rrule['FREQ']) {
            case 'DAILY':
                $dateInterval = new DateInterval("P{$interval}D");
                break;
            case 'WEEKLY':
                $dateInterval = new DateInterval("P{$interval}W");
                break;
            case 'MONTHLY':
                $dateInterval = new DateInterval("P{$interval}M");
                break;
            case 'YEARLY':
                $dateInterval = new DateInterval("P{$interval}Y");
                break;
            default:
                // We don't know how to handle anything else.
                if ($firstTs >= $startTs && $firstTs <= $endTs) {
                    return array($this->first);
                } else {
                    return array();
                }
        }

        if (array_key_exists('COUNT', $this->rrule)) {
            $period = new DatePeriod(
                $this->first,
                $dateInterval,
                (int) $this->rrule['COUNT']
            );
        } else if (array_key_exists('UNTIL', $this->rrule)) {
            $period = new DatePeriod(
                $this->first,
                $dateInterval,
                new Datetime($this->rrule['UNTIL']) //TODO: Check if this explodes when using timezones.
            );
        } else {
            $period = new DatePeriod($this->first, $dateInterval, $end);
        }

        $ret = array();

        foreach ($period as $date) {
            $ts = $date->getTimestamp();
            if ($startTs <= $ts && $ts <= $endTs && !in_array($date, $this->exceptions)) {
                $ret[] = $date;
            }
        }
        return $ret;
    }

    /**
     * Properties that are extracted from the rrules.
     */
    private $properties = array(
        'FREQ',
        'INTERVAL',
        'COUNT',
        'UNTIL'
    );

    /**
     * Parses a rrule string into a dictionary.
     *
     * @param string $rrule The rrule.
     *
     * @return array of string => string The properties and their values.
     */
    private function parseRrule($rrule)
    {
        if ($rrule === '') {
            return array(
                'FREQ'     => null,
                'INTERVAL' => null,
                'COUNT'    => null,
                'UNTIL'    => null
            );
        }

        $ret = array();

        foreach ($this->properties as $prop) {
            preg_match("/$prop=([^;]+)/", $rrule, $matches);

            if (array_key_exists(1, $matches)) {
                $ret[$prop] = $matches[1];
            }
        }
        return $ret;
    }
}
