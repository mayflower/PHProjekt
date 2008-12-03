<?php
/**
 * Convert Dates between different formats and timezones
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
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @subpackage Core
 * @version    CVS: $Id:
 * @link       http://www.phprojekt.com
 * @author     Michel Hartmann <michel.hartmann@mayflower.de>
 * @since      File available since Release 1.0
 */

/**
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @subpackage Core
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Michel Hartmann <michel.hartmann@mayflower.de>
 */
class Phprojekt_Date_Converter
{    
    /**
     * Converts a String in one of the following formats:
     *     - yyyyMMdd'T'HHmmss'Z'
     *     - yyyyMMdd'T'HHmmss
     * to an object of type Zend_Date. If a timezone is given the
     * Date is also converted to UTC.
     *
     * @param String $dateTime     Date-time string to parse
     * @param String $timezone     Timeezone of the date-time
     * @return Zend_Date           Object for further transformation
     */
    public static function parseIsoDateTime($dateTime, $timezone = 'UTC')
    {
        $matches = array();
        if (preg_match('/(\d{8})T(\d{6})(Z?)/', $dateTime, $matches)) {
            if ($matches[3] == 'Z') {
                $timezone = 'UTC';
            }
            $date = new Zend_Date();
            $date->setTimezone($timezone);
            $date->setDate($matches[1], 'yyyyMMdd');
            $date->setTime($matches[2], 'HHmmss');
            if ($timezone != 'UTC') {
                $date->setTimezone('UTC');
            }
            return $date;
        }
    }
}
