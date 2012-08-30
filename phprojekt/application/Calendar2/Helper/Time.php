<?php
/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details
 *
 * @copyright  Copyright (c) 2011 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * Calendar2 Time Helper
 *
 * Utility class that provides functions to handle Datetime and related objects.
 */
class Calendar2_Helper_Time
{
    /**
     * Take an array of Datetime pairs representing periods and eliminate
     * overlapping parts by joining the overlapping periods.
     *
     * Events where the start date is the same as the end will be preserved,
     * events with start dates after end dates will be removed.
     *
     * @param array of array('start' => Datetime, 'end' => Datetime) $periods
     *          The periods to compact.
     *
     * @return array of array('start' => Datetime, 'end' => Datetime
     */
    public static function compactPeriods(array $periods)
    {
        $periods = array_filter($periods, array( __CLASS__ , 'isValidPeriod'));
        if (empty($periods)) {
            return array();
        }

        usort($periods, array( __CLASS__ , 'comparePeriods'));

        $return  = array();
        $current = array_shift($periods);

        foreach ($periods as $next) {
            $union = self::unionAdd(
                $current['start'],
                $current['end'],
                $next['start'],
                $next['end']
            );

            if (is_null($union)) {
                // The next period doesn't overlap with $current.
                $return[] = $current;
                $current = $next;
            } else {
                //$current and $next overlap.
                $current = $union;
            }
        }

        $return[] = $current;
        return $return;
    }

    protected static function comparePeriods(array $a, array $b)
    {
        $diff = $a['start']->getTimestamp() - $b['start']->getTimestamp();

        if ($diff != 0) {
            return $diff;
        } else {
            // The start is identical, compare end dates.
            return $a['end']->getTimestamp() - $b['end']->getTimestamp();
        }
    }

    protected static function isValidPeriod(array $p)
    {
        return $p['start'] <= $p['end'];
    }

    /**
     * Add two periods of time if they overlap. If they don't overlap, return
     * null.
     *
     * @param Datetime $aStart The start of the first period.
     * @param Datetime $aEnd The end of the first period.
     * @param Datetime $bStart The start of the first period.
     * @param Datetime $bEnd The end of the first period.
     *
     * @return array('start' => Datetime, 'end' => Datetime)
     *          If the periods overlap, the union. If they don't, null.
     */
    private static function unionAdd(Datetime $aStart, Datetime $aEnd,
                                     Datetime $bStart, Datetime $bEnd)
    {
        $aStartTs = $aStart->getTimestamp();
        $aEndTs   = $aEnd->getTimestamp();
        $bStartTs = $bStart->getTimestamp();
        $bEndTs   = $bEnd->getTimestamp();

        if ($bStartTs > $aEndTs || $bEndTs < $aStartTs) {
            return null;
        }

        $return = array();
        $return['start'] = $aStartTs < $bStartTs
                             ? clone $aStart
                             : clone $bStart;
        $return['end'] = $aEndTs > $bEndTs
                             ? clone $aEnd
                             : clone $bEnd;
        return $return;
    }
}
