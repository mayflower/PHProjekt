<?php
/**
 * Class to hold a collection of dates. These dates can also be automatically
 * calculated from a iCal-rrule.
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @subpackage Core
 * @version    $Id$
 * @link       http://www.phprojekt.com
 * @author     Michel Hartmann <michel.hartmann@mayflower.de>
 * @since      File available since Release 6.0
 */

/**
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @subpackage Core
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Michel Hartmann <michel.hartmann@mayflower.de>
 */
class Phprojekt_Date_Collection
{
    /**
     * Array holding the elements of the collection. Each Element
     * is stored as a Zend_Date object.
     *
     * @var array
     */
    private $_elements = array();

    /**
     * The highest value that should be allowed. If a higher value is added
     * it will be dropped.
     *
     * @var Zend_Date
     */

    private $_maxDate = null;

    /**
     * The lowest value that should be allowed. If a lower value is added
     * it will be dropped.
     *
     * @var Zend_Date
     */
    private $minDate = null;

    /**
     * Create a new collection of dates.
     *
     * @param Zend_Date $startDate      The lowsest allowed value
     * @param Zend_Date $endDate        The highest allowed valueed
     */
    public function __construct($minDate, $maxDate = null)
    {
        $this->minDate = new Zend_Date(strtotime($minDate));
        if (null != $maxDate) {
            $this->_maxDate = new Zend_Date(strtotime($maxDate));
        }
    }

    /**
     * Adds a date to the Collection. If the date is higher/lower than
     * maxDate/minDate it will not be added.
     *
     * @param Zend_Date|Array $element      A(n array of) Zend_Date object(s)
     */
    public function add($element)
    {
        if (is_array($element)) {
            foreach ($element as $e) {
                $this->add($e);
            }
        } else {
            if (!isset($this->_elements[$element->get()])) {
                $this->_elements[$element->get()] = $element;
            }
        }
    }

    /**
     * Fill the collection with all dates that can be calculated from rrule
     * starting with minDate.
     * If there already are elements in the collection they will be dropped.
     *
     * @param String $rrule     The rrule that should be parsed
     * @return boolean          TRUE if parsing was successfull,
     *                          FALSE otherwise
     */
    public function applyRrule($rrule)
    {
        // Clear collection
        $this->_elements = array();
        // Parse RRule
        $rules = $this->parseRrule($rrule);
        // Detect mathod to use for increment
        switch ($rules['FREQ']) {
            case 'YEARLY':
               $method = 'addYear';
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
        $date = $this->minDate;

        $dates = $this->rruleByXXX($rules, $date);
        $this->add($dates);
        while ($date < $rules['UNTIL']) {
            $date = $date->$method($rules['INTERVAL']);
            if ($date < $rules['UNTIL']) {
                $dates = $this->rruleByXXX($rules, $date);
            }
            $this->add($dates);
        }

        return true;
    }

    /**
     * Parse the RRULE of an iCal-file
     *
     * @param String $rrule     RRULE to parse
     * @return Array            Array containing the parsed rule
     */
    private function parseRrule($rrule)
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
                    $value = Phprojekt_Date_Converter::parseIsoDateTime($value);
                    $this->_maxDate = $value;
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

        if (!isset($rules['UNTIL'])) {
            $rules['UNTIL'] = $this->minDate;
            $this->_maxDate = $this->minDate;
        }

        return $rules;
    }

    /**
     * Calculate all Dates generated by a 'BYXXX' rule.
     *
     * @param array $rules          rrule as generated by parseRrule
     * @param Zend_Date $date       The date to start from
     * @return array                Array with all generated Zend_Date objects
     */
    private function rruleByXXX($rules, $date)
    {
        $bys = array(
            'BYMONTH' => 'setMonth',
            'BYWEEKNO' => 'setWeek',
            'BYYEARDAY' => 'setDayOfYear',
            'BYMONTHDAY' => 'setDay',
            'BYDAY' => 'setWeekday',
            'BYHOUR' => 'setHour',
            'BYMINUTE' => 'setMinute',
            'BYSECOND' => 'setSecond'
        );

        $dates = array(new Zend_Date($date));

        foreach ($bys as $by => $setter) {
            if (isset($rules[$by])) {
                $res = array();
                foreach ($rules[$by] as $value) {
                    foreach ($dates as $date) {
                        $date->$setter($value);
                        $res[] = new Zend_Date($date);
                    }
                }
                $dates[] = $res;
            }
        }

        return $dates;
    }

    /**
     * Get the elements of the collection
     *
     * @return Array    Returns all dates (Zend_Date) of the collection as an array
     */
    public function getValues()
    {
        ksort($this->_elements);

        return $this->_elements;
    }

    /**
     * Removes a series of dates from the collection
     *
     * @param Array $exclude        Array with Zend_Dates that should be removed from
     *                              the collection
     */
    public function filter($exclude)
    {
        for ($dateIndex = 0; $dateIndex < count($this->_elements); $dateIndex++) {
            foreach ($exclude as $exDate) {
                if ($exDate->compare($this->_elements[$dateIndex]) == 0) {
                    unset($this->_elements[$dateIndex]);
                    continue;
                }
            }
        }
    }

}
