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
 * Convert a time using the timezone.
 */
class Phprojekt_Converter_Time
{
    /**
     * Convert a user time to UTC and return the timestamp.
     *
     * @param string $value Date value to convert.
     *
     * @return integer Unix timestamp value.
     */
    public static function userToUtc($value)
    {
        return self::convert($value, -1);
    }

    /**
     * Convert a UTC time to user and return the timestamp.
     *
     * @param string $value Date value to convert.
     *
     * @return integer Unix timestamp value.
     */
    public static function utcToUser($value)
    {
        return self::convert($value, 1);
    }

    /**
     * Convert a UTC time to user or user to UTC and return the timestamp.
     *
     * @param string  $value Date value to convert.
     * @param integer $side  1 for utc to user, -1 for user to utc.
     *
     * @return integer Unix timestamp value.
     */
    public static function convert($value, $side)
    {
        $timeZone = Phprojekt_Auth_Proxy::getEffectiveUser()->getSetting("timeZone", 'UTC');
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

    /**
     * Convert a number of minutes into HH:mm.
     *
     * @param integer $minutes The number of minutes.
     *
     * @return string Time format.
     */
    public static function convertMinutesToHours($minutes)
    {
        $hoursDiff   = floor($minutes / 60);
        $minutesDiff = $minutes - ($hoursDiff * 60);

        if ($hoursDiff == 0 || $hoursDiff < 10) {
            $hoursDiff = '0' . $hoursDiff;
        }
        if ($minutesDiff == 0 || $minutesDiff < 10) {
            $minutesDiff = '0' . $minutesDiff;
        }

        return $hoursDiff . ':' . $minutesDiff;
    }

    /**
     * Return the timeZones with the P6 values.
     *
     * @return array Array with the P6 timeZones.
     */
    public static function getTimeZones()
    {
        return array(
            "-12" => "(GMT -12:00) International Date Line West",
            "-11" => "(GMT -11:00) Midway Island, Samoa",
            "-10" => "(GMT -10:00) Hawaii",
            "-9" => "(GMT -9:00) Alaska",
            "-8" => "(GMT -8:00) Pacific Time (US & Canada)",
            "-08" => "(GMT -8:00) Tijuana, Baja California",
            "-7" => "(GMT -7:00) Arizona",
            "-07" => "(GMT -7:00) Chihuahua, La Paz, Mazatlan",
            "-007" => "(GMT -7:00) Mountain Time (US & Canada)",
            "-6" => "(GMT -6:00) Central America",
            "-06" => "(GMT -6:00) Central Time (US & Canada)",
            "-006" => "(GMT -6:00) Gudalajara, Mexico City, Monterrey",
            "-0006" => "(GMT -6:00) Saskatchewan",
            "-5" => "(GMT -5:00) Bogota, Lima, Quito",
            "-05" => "(GMT -5:00) Eastern Time (US & Canada)",
            "-005" => "(GMT -5:00) Indiana (East)",
            "-4_-30" => "(GMT -4:30) Caracas",
            "-4" => "(GMT -4:00) Asuncion",
            "-04" => "(GMT -4:00) Atlantic Time (Canada)",
            "-004" => "(GMT -4:00) Manaus",
            "-0004" => "(GMT -4:00) Santiago",
            "-3_-30" => "(GMT -3:30) Newfoundland",
            "-3" => "(GMT -3:00) Brasilia",
            "-03" => "(GMT -3:00) Buenos Aires",
            "-003" => "(GMT -3:00) Cayenne",
            "-0003" => "(GMT -3:00) Greenland",
            "-00003" => "(GMT -3:00) Montevideo",
            "-2" => "(GMT -2:00) Mid-Atlantic",
            "-1" => "(GMT -1:00) Azores",
            "-01" => "(GMT -1:00) Cape Verde Islands",
            "00" => "(GMT) Casablanca",
            "000" => "(GMT) Coordinated Universal Time",
            "0000" => "(GMT) Greenwich Mean Time: Dublin, Edinburgh, Lisbon, London",
            "00000" => "(GMT) Monrovia, Reykjavik",
            "1" => "(GMT +1:00) Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna",
            "01" => "(GMT +1:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague",
            "001" => "(GMT +1:00) Brussels, Copenhagen, Madrid, Paris",
            "0001" => "(GMT +1:00) Sarajevo, Skopje, Warsaw, Zagreb",
            "2" => "(GMT +2:00) West Central Africa",
            "02" => "(GMT +2:00) Amman",
            "002" => "(GMT +2:00) Athens, Bucharest, Istambul",
            "0002" => "(GMT +2:00) Beirut",
            "00002" => "(GMT +2:00) Cairo",
            "000002" => "(GMT +2:00) Harare, Pretonia",
            "0000002" => "(GMT +2:00) Helsinki, Kyiv, Riga, Sofia, Tallinn, Vilnius",
            "00000002" => "(GMT +2:00) Jerusalem",
            "000000002" => "(GMT +2:00) Minsk",
            "0000000002" => "(GMT +2:00) Windhoek",
            "3" => "(GMT +3:00) Baghdad",
            "03" => "(GMT +3:00) Kuwait, Riyadh",
            "003" => "(GMT +3:00) Moscow, St. Petersburg, Volgograd",
            "0003" => "(GMT +3:00) Nairobi",
            "00003" => "(GMT +3:00) Tibilisi",
            "3_30" => "(GMT +3:30) Tehran",
            "4" => "(GMT +4:00) Abu Dhabi, Muscat",
            "04" => "(GMT +4:00) Baku",
            "004" => "(GMT +4:00) Caucasus Standar Time",
            "0004" => "(GMT +4:00) Port Louis",
            "00004" => "(GMT +4:00) Yerevan",
            "4_30" => "(GMT +4:30) Kabul",
            "5" => "(GMT +5:00) Ekaterinburg",
            "05" => "(GMT +5:00) Islamabad, Karachi",
            "005" => "(GMT +5:00) Tashkent",
            "5_30" => "(GMT +5:30) Chennai, Kolkata, Mumbai, New Delhi",
            "5_030" => "(GMT +5:30) Sri Jayawardenepura",
            "5_45" => "(GMT +5:45) Kathmandu",
            "6" => "(GMT +6:00) Almaty, Novosibirsk",
            "06" => "(GMT +6:00) Astana, Dhaka",
            "6_30" => "(GMT +6:30) Yangoon (Rangoon)",
            "7" => "(GMT +7:00) Bangkok, Hanoi, Jakarta",
            "07" => "(GMT +7:00) Krasnoyarsk",
            "8" => "(GMT +8:00) Beijing, Chongging, Hong Kong, Urumqi",
            "08" => "(GMT +8:00) Irkutsk, Ulaan Bataar",
            "008" => "(GMT +8:00) Kuala Lumpur, Singapore",
            "0008" => "(GMT +8:00) Perth",
            "00008" => "(GMT +8:00) Taipei",
            "9" => "(GMT +9:00) Osaka, Sapporo, Tokyo",
            "09" => "(GMT +9:00) Seoul",
            "009" => "(GMT +9:00) Yakutsk",
            "9_30" => "(GMT +9:30) Adelaide",
            "9_030" => "(GMT +9:30) Darwin",
            "10" => "(GMT +10:00) Brisbane",
            "010" => "(GMT +10:00) Canberra, Melbourne, Sydney",
            "0010" => "(GMT +10:00) Guam, Port Moresby",
            "00010" => "(GMT +10:00) Hobart",
            "000010" => "(GMT +10:00) Vladivostok",
            "11" => "(GMT +11:00) Magadan, Solomon Islands, New Caledonia",
            "12" => "(GMT +12:00) Auckland, Wellington",
            "012" => "(GMT +12:00) Fiji, Marshall Island",
            "0012" => "(GMT +12:00) Petropavlovsk-Kamchatsky");
    }
}
