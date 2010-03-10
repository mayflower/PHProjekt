<?php
/**
 * Clean an string for index it
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
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @version    $Id$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Clean an string for index it
 *
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL v3 (See LICENSE file)
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */
class Phprojekt_Converter_String
{
    /**
     * Clean Up a string for index
     *
     * @param string $string The string for cleanup
     *
     * @return string
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
     * Remove the short or long words from the index
     *
     * @param array $string String to check
     *
     * @return boolean
     */
    public static function stripLengthWords($string)
    {
        $len = mb_strlen($string, 'UTF-8');

        return ($len > 2 && $len < 256);
    }
}
