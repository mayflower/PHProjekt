<?php
/**
 * List View helper class
 *
 * This class is for help on the draw of the list view
 *
 * LICENSE: Licensed under the terms of the GNU Publice License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @link       http://www.phprojekt.com
 * @author     Gustavo Solt <solt@mayflower.de>
 * @since      File available since Release 1.0
 */

/**
 * List view helper
 *
 * The class process the info for show the list data
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Default_Helpers_ListView
{
    /**
     * Return an array with the fields data
     *
     * @param array $data The array with data of each field
     *
     * @return array The first row of the fields data
     */
    public function getTitles(array $data)
    {
        if (empty($data)) {
            return $data[0] = array();
        }

        return $data[0];
    }

    /**
     * Return an array with all the items
     *
     * @param array $data The array with data of each field
     *
     * @return array An array with all the rows
     */
    public function getItems(array $data)
    {
        foreach ($data as $id => $fieldData) {
            if ($id > 0) {
                $tmp[$id] = $fieldData;
            }
        }

        return $data;
    }
}