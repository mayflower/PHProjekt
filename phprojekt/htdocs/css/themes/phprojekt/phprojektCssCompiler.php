<?php
/**
 * Phprojekt CSS Compiler.
 *
 * Collect all the css files from the current theme directory
 * Include all the css files in the folder and sub-folders
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
 * @category  PHProjekt
 * @package   Htdocs
 * @copyright Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license   LGPL v3 (See LICENSE file)
 * @link      http://www.phprojekt.com
 * @since     File available since Release 6.0
 * @version   Release: @package_version@
 * @author    Mariano La Penna <mariano.lapenna@mayflower.de>
 */

header("Content-type: text/css");

$allCss = "";
$allCss .= file_get_contents("../../../dojo/dojo/resources/dojo.css");

//For every item in the current directory
foreach (scandir(".") as $item) {
    if (is_dir($item)) {
        //Is it a subdirectory
        if ($item != '.' && $item != '..') {
            //It is a CSS folder
            foreach (scandir($item) as $subItem) { //Iterate on every file
                $subItemPath = $item . DIRECTORY_SEPARATOR . $subItem;
                if (!is_dir($subItemPath) && substr($subItem, -4) == '.css') {
                    $allCss .= file_get_contents($subItemPath);
                }
            }
        }
    } else {
        //It is a file
        if (substr($item, -4) == '.css') {
            $allCss .= file_get_contents($item);
        }
    }
}

$allCss  = str_replace("../images", "images", $allCss);
$allCss .= file_get_contents("../../../dojo/dijit/themes/dijit.css");
$allCss .= file_get_contents("../../../dojo/dijit/themes/dijit_rtl.css");

echo $allCss;
