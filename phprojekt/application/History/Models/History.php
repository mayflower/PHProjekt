<?php
/**
 * History model class
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
 * History model class
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class History_Models_History extends Phprojekt_Item_Abstract
{
    /**
     * Get all the projects for list from the db
     * The return array have all the rows that are ActiveRecords itself
     * and the number of rows without the limits
     *
     * The pagination is stored in the session name "0 + Module".
     *
     * @return array Array with the rows for render and the total rows number
     */
    public function getListData()
    {
        $listData  = array();
        $where     = null;
        $projectId = 0;

        /* Limit the query for paging */
        $session = new Zend_Session_Namespace($projectId . $this->_name);
        if (true === isset($session->actualPage)) {
            $actualPage = $session->actualPage;
        } else {
            $actualPage = 0;
        }

        $config = Zend_Registry::get('config');
        $count  = $config->itemsPerPage;

        $order = 'datetime DESC';

        $datetime     = null;
        $numberOfRows = 0;
        $howManyRows  = 0;
        $index        = 0;
        /* Get only the different datetime */
        foreach ($this->fetchAll($where, $order) as $row) {
            if ($datetime != $row->datetime) {
                $datetime = $row->datetime;
                /* Ommit the items before the actual page */
                if ($index < $actualPage) {
                    $howManyRows++;
                } else {
                    if (($numberOfRows < $count)) {
                        $numberOfRows++;
                        $listData[] = $row;
                    } else {
                        $howManyRows++;
                    }
                }
                $index++;
            }
        }

        /* Add items found to the total items */
        $howManyRows += $numberOfRows;

        return array($listData, $howManyRows);
    }

    /**
     * Get the form fields
     *
     * @return array Array with the fields for render
     */
    public function getFormData()
    {
        $formData = $this->getFieldsForForm('todo');

        return $formData;
    }

    /**
     * Return wich submodules use this module
     *
     * @return array
     */
    public function getSubModules()
    {
        return array();
    }
}