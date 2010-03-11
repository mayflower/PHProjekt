<?php
/**
 * Clean an string for index it.
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
 * @package    Phprojekt
 * @subpackage Converter
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
 * Clean an string for index it.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Converter
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_Converter_String
{
    /**
     * Clean Up a string for index.
     *
     * @param string $string The string for cleanup.
     *
     * @return string Cleaned string.
     */
    public static function cleanupString($string)
    {
        // Clean up HTML
        $string = strip_tags($string);
        $string = mb_strtolower($string, 'UTF-8');
        $string = preg_replace('#\P{L}+#u', ' ', $string);

        return $string;
    }

    /**
     * Remove the short or long words from the index.
     *
     * @param array $string String to check.
     *
     * @return boolean True for words between 3 and 256.
     */
    public static function stripLengthWords($string)
    {
        $len = mb_strlen($string, 'UTF-8');

        return ($len > 2 && $len < 256);
    }
}
