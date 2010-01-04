<?php
/**
 * Convert a time using the timezone
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
 * @copyright  2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Convert a time using the timezone
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */
class Phprojekt_Converter_Time
{
    /**
     * Convert a user time to UTC and return the timestamp
     *
     * @param string $value Date value to convert
     *
     * @return integer
     */
    public static function userToUtc($value)
    {
        return self::convert($value, -1);
    }

    /**
     * Convert a UTC time to user and return the timestamp
     *
     * @param string $value Date value to convert
     *
     * @return integer
     */
    public static function utcToUser($value)
    {
        return self::convert($value, 1);
    }

    /**
     * Convert a UTC time to user or user to UTC and return the timestamp
     *
     * @param string  $value Date value to convert
     * @param integer $side  1 for utc to user, -1 for user to utc
     *
     * @return integer
     */
    public static function convert($value, $side)
    {
        $timeZone = Phprojekt_User_User::getSetting("timeZone", 'UTC');
        if (strstr($timeZone, "_")) {
            list ($hours, $minutes) = explode("_", $timeZone);
        } else {
            $hours   = (int) $timeZone;
            $minutes = 0;
        }
        $hoursComplement   = $hours * $side;
        $minutesComplement = $minutes * $side;
        $u                 = strtotime($value);

        return mktime(date("H", $u) + $hoursComplement, date("i", $u) + $minutesComplement,
            date("s", $u), date("m", $u), date("d", $u), date("Y", $u));
    }
}
