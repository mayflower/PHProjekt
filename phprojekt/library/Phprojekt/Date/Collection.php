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
 * Class to hold a collection of dates.
 * These dates can also be automatically calculated from a iCal-rrule.
 */
class Phprojekt_Date_Collection
{
    /**
     * Array holding the elements of the collection.
     * Each Element is a timestamp.
     *
     * @var array
     */
    private $_elements = array();

    /**
     * The highest value that should be allowed.
     * If a higher value is added it will be dropped.
     *
     * @var int
     */
    private $_maxDate = null;

    /**
     * The lowest value that should be allowed.
     * If a lower value is added it will be dropped.
     *
     * @var int
     */
    private $_minDate = null;

    /**
     * Zend_Date class.
     *
     * @var Zend_Date
     */
    private $_date = null;

    /**
     * Create a new collection of dates.
     *
     * @param string $minDate The lowsest allowed value.
     *
     * @return void
     */
    public function __construct($minDate)
    {
        $this->_date    = new Zend_Date();
        $this->_minDate = $this->_getDate(strtotime($minDate));
    }

    /**
     * Adds a date to the Collection.
     *
     * @param integer $element A timestamp date.
     *
     * @return void
     */
    public function add($element)
    {
        if (!isset($this->_elements[$element])) {
            $this->_elements[$element] = $element;
        }
    }

    /**
     * Adds a date to the Collection.
     * If the date is higher/lower than maxDate/minDate it will not be added.
     *
     * @param array $elements An array of timestamp strings.
     *
     * @return void
     */
    public function addArray(array $elements)
    {
        foreach ($elements as $e) {
            $this->add($e);
        }
    }

    /**
     * Fill the collection with all dates that can be calculated from rrule starting with minDate.
     * If there are already elements in the collection they will be dropped.
     *
     * @param string $rrule The rrule that should be parsed.
     *
     * @return boolean TRUE if parsing was successful, FALSE otherwise.
     */
    public function applyRrule($rrule)
    {
        // Clear collection
        $this->_elements = array();
        // Parse RRule
        $rules = $this->_parseRrule($rrule);
        // Detect method to use for increment
        switch ($rules['FREQ']) {
            case 'YEARLY':
                $method = 'addYear';
                break;
            case 'MONTHLY':
                $method = 'addMonth';
                break;
            case 'WEEKLY':
                $method = 'addWeek';
                break;
            case 'DAILY':
                $method = 'addDay';
                break;
            default:
                // Frequence is not supported
                $method = null;
                return false;
        }

        $datePointer = $this->_minDate;
        $datesToAdd  = $this->_rruleByXXX($rules, $datePointer);
        $this->addArray($datesToAdd);

        while ($datePointer < $this->_maxDate) {
            $datePointer = $this->_applyMethod($method, $rules['INTERVAL'], $datePointer);
            $datesToAdd  = $this->_rruleByXXX($rules, $datePointer);
            foreach ($datesToAdd as $dateToAdd) {
                if ($dateToAdd < $this->_maxDate) {
                    $this->add($dateToAdd);
                }
            }
        }

        // Clean extra dates
        foreach ($this->_elements as $date) {
            if ($date < $this->_minDate || $date > $this->_maxDate) {
                unset($this->_elements[$date]);
            }
        }

        return true;
    }

    /**
     * Parse the RRULE of an iCal-file.
     *
     * @param string $rrule RRULE to parse.
     *
     * @return array Array containing the parsed rule.
     */
    private function _parseRrule($rrule)
    {
        $rrule = explode(';', $rrule);
        $rules = array();

        // Needed to translate the Weekdays to a format compatible with Zend_Date
        $translateByDay = array(
            'MO' => 1,
            'TU' => 2,
            'WE' => 3,
            'TH' => 4,
            'FR' => 5,
            'SA' => 6,
            'SU' => 7
        );

        foreach ($rrule as $rule) {
            list($name, $value) = explode('=', $rule, 2);
            if ($value == '') {
                continue;
            }
            switch ($name) {
                case 'UNTIL':
                    $value = strtotime($value);
                    break;
                case 'BYDAY':
                    $value = explode(',', $value);
                    for ($i = 0; $i < count($value); $i++) {
                        $value[$i] = $translateByDay[$value[$i]];
                    }
                    break;
                case 'BYMONTH':
                case 'BYHOUR':
                case 'BYMINUTE':
                    $value = explode(',', $value);
                    break;
                case 'INTERVAL':
                    $value = (int) $value;
                    break;
            }
            $rules[$name] = $value;
        }

        if (isset($rules['UNTIL'])) {
            $this->_maxDate = $this->_getDate($rules['UNTIL']);
        } else {
            $rules['UNTIL'] = $this->_minDate;
            $this->_maxDate = $this->_minDate;
        }

        return $rules;
    }

    /**
     * Calculate all Dates generated by a 'BYXXX' rule.
     *
     * @param array   $rules Rrule as generated by _parseRrule.
     * @param integer $date  Timestamp value of the date.
     *
     * @return array Array with all the timestamps.
     */
    private function _rruleByXXX($rules, $date)
    {
        $bys = array(
            'BYMONTH'    => 'setMonth',
            'BYWEEKNO'   => 'setWeek',
            'BYYEARDAY'  => 'setDayOfYear',
            'BYMONTHDAY' => 'setDay',
            'BYDAY'      => 'setWeekday',
            'BYHOUR'     => 'setHour',
            'BYMINUTE'   => 'setMinute',
            'BYSECOND'   => 'setSecond'
        );

        $dates = array($this->_getDate($date));

        foreach ($bys as $byName => $setter) {
            if (isset($rules[$byName])) {
                $newDates = array();
                foreach ($rules[$byName] as $value) {
                    foreach ($dates as $date) {
                        $newDates[] = $this->_applyMethod($setter, $value, $date);
                    }
                }
                $dates = $newDates;
            }
        }

        return $dates;
    }

    /**
     * Get the elements of the collection.
     *
     * @return array All the timestamp in an array.
     */
    public function getValues()
    {
        ksort($this->_elements);

        return $this->_elements;
    }

    /**
     * Removes a series of dates from the collection.
     *
     * @param array $exclude Array with Unix Timestamps that should be removed from the collection.
     *
     * @return void
     */
    public function filter($excludeDates)
    {
        foreach ($excludeDates as $excludeDate) {
            if (in_array($excludeDate, $this->_elements)) {
                unset($this->_elements[$excludeDate]);
                continue;
            }
        }
    }

    /**
     * Set the date in the Zend_Date and return it in date format.
     *
     * @param integer $date Timestamp of the date.
     *
     * @return integer The new timestamp.
     */
    private function _getDate($date)
    {
        $this->_date->set($date);

        return $this->_date->get();
    }

    /**
     * Apply the rule method to one date.
     *
     * @param string  $method The method name.
     * @param mix     $value  Parameter for the method.
     * @param integer $date   Timestamp of the date.
     *
     * @return integer The new timestamp.
     */
    private function _applyMethod($method, $value, $date)
    {
        $this->_date->set($date);
        $this->_date->$method($value);

        return $this->_date->get();
    }
}
