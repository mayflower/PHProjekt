<?php
/**
 * Convert a value for save in the table, and get it for the api
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
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Convert a value for save in the table, and get it for the api
 *
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */
class Phprojekt_Converter_Value
{
    /**
     * Return a value for set, using some validations from the table data
     *
     * @param string $type  Type of field
     * @param mixed  $value Value to transform
     *
     * @return mixed
     */
    public static function set($type, $value)
    {
        switch ($type) {
            case 'int':
                $value = Cleaner::sanitize('integer', $value, 0);
                break;
            case 'float':
                $value = Cleaner::sanitize('float', $value, 0);
                if ($value !== false) {
                    $value = Zend_Locale_Format::getFloat($value, array('precision' => 2));
                } else {
                    $value = 0;
                }
                break;
            case 'date':
                $value = Cleaner::sanitize('date', $value);
                break;
            case 'time':
                $value = Cleaner::sanitize('time', $value);
                $value = date("H:i:s", Phprojekt_Converter_Time::userToUtc($value));
                break;
            case 'datetime':
            case 'timestamp':
                $value = Cleaner::sanitize('timestamp', $value);
                $value = date("Y-m-d H:i:s", Phprojekt_Converter_Time::userToUtc($value));
                break;
            case 'text':
                if (is_array($value)) {
                    // if given value for a text field is an array, it's from a MultiSelect field
                    $value = implode(',', $value);
                }
                // Run html sanitize only if the text contain some html code
                if (preg_match("/([\<])([^\>]{1,})*([\>])/i", $value)) {
                    $value = Cleaner::sanitize('html', $value);
                } else {
                    $value = Cleaner::sanitize('string', $value);
                }
                break;
            default:
                $value = Cleaner::sanitize('string', $value);
                break;
        }

        return $value;
    }

    /**
     * Return a value for get, using some validations from the table data
     *
     * @param string $type  Type of field
     * @param mixed  $value Value to transform
     *
     * @return mixed
     */
    public static function get($type, $value)
    {
        switch ($type) {
            case 'float':
                $value = Zend_Locale_Format::toFloat($value, array('precision' => 2));
                break;
            case 'time':
                if (!empty($value)) {
                    $value = date("H:i:s", Phprojekt_Converter_Time::utcToUser($value));
                }
                break;
            case 'datetime':
            case 'timestamp':
                if (!empty($value)) {
                    $value = date("Y-m-d H:i:s", Phprojekt_Converter_Time::utcToUser($value));
                }
                break;
            case 'text':
                // Get html only if the text contain some html code
                if (preg_match("/([\<])([^\>]{1,})*([\>])/i", $value)) {
                    $value = stripslashes($value);
                }
                break;
        }

        return $value;
    }
}
