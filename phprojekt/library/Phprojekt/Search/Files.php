<?php
/**
 * Class for get the words from a file.
 *
 * The class provide the functions for read different type of files
 * and get all the words for save.
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
 * @subpackage Search
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
 * Class for get the words from a file.
 *
 * The class provide the functions for read different type of files
 * and get all the words for save.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Search
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_Search_Files
{
    /**
     * Get all the words from a file.
     *
     * @param string $file     The name of the file.
     * @param string $fileType The filetype.
     *
     * @return string All the words found.
     */
    public function getWordsFromFile($file)
    {
        $fileType = $this->_getFileType($file);
        $string   = '';

        $file = PHPR_CORE_PATH . DIRECTORY_SEPARATOR
                . 'Phprojekt' . DIRECTORY_SEPARATOR
                . $file;

        if (is_readable($file)) {
            switch ($fileType) {
                default:
                    $string = implode(' ', file($file));
                    break;
            }
        }

        return $string;
    }

    /**
     * Get the FileType by its extension.
     *
     * @param string $filename The name of the file.
     *
     * @return string Extension.
     */
    private function _getFileType($filename)
    {
        $type = explode(".", $filename);

        return (strtoupper(array_pop($type)));
    }
}
