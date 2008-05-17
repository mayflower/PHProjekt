<?php
/**
 * Class for get the words from a file
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * The class provide the functions for read different type of files
 * and get all the words for save
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @subpackage Core
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_Search_Files
{
    /**
     * Get all the words from a file into an array
     *
     * @param string $file     The name of the file
     * @param string $fileType The filetype
     *
     * @return array
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
     * Get the FileType by its extension
     *
     * @param string $filename The name of the file
     *
     * @return string
     */
    private function _getFileType($filename) {
        return(strtoupper(array_pop(explode(".", $filename))));
    }
}