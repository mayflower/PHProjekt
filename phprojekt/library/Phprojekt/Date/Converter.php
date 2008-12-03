<?php
/**
 * Convert Dates between different formats and timezones
 *
 * @copyright  2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://www.phprojekt.com/license PHProjekt6 License
 * @author     Michel Hartmann <michel.hartmann@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
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