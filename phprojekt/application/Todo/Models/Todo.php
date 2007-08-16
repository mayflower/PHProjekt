<?php
/**
 * Todo model class
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
 * Todo model class
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Todo_Models_Todo extends Phprojekt_Item_Abstract
{
    /**
     * Get all the todo for list from the db
     * The return array have all the rows that are ActiveRecords itself
     * and the number of rows without the limits
     *
     * The pagination is stored in the session name "projectID + Module".
     * So each, module have and own session in each project.
     *
     * @return array Array with the rows for render and the total rows number
     */
    public function getListData()
    {
        $listData   = array();

        /* Filter the todos of the current project */
        $session = new Zend_Session_Namespace();
        if (true === isset($session->lastProjectId)) {
            $projectId = $session->lastProjectId;
            $where     = $this->getAdapter()->quoteInto('projectId = ?', $projectId);
        } else {
            $projectId = 0;
            $where     = null;
        }

        /* Limit the query for paging */
        $session = new Zend_Session_Namespace($projectId . $this->_name);
        if (true === isset($session->actualPage)) {
            $actualPage = $session->actualPage;
        } else {
            $actualPage = 0;
        }

        $config = Zend_Registry::get('config');
        $count  = $config->itemsPerPage;

        $order = 'title';

        foreach ($this->fetchAll($where, $order, $count, $actualPage) as $row) {
            $listData[] = $row;
        }

        $howManyRows = count($this->fetchAll($where));

        return array($listData, $howManyRows);
    }

    /**
     * Get the form fields
     * If the id is defined will make the edit form
     * if not, will make the add form
     *
     * @param integer $id Optional, for edit the row
     *
     * @return array      Array with the fields for render
     */
    public function getFormData($id = 0)
    {
        $formData = $this->getFieldsForForm('todo');

        if ($id > 0) {
            $this->find($id);
            foreach ($formData as $fieldName => $fieldData) {
                $tmpData[$fieldName]          = $fieldData;
                $tmpData[$fieldName]['value'] = $this->$fieldName;
            }
            $formData = $tmpData;
        }

        /* Asign the porject value if exists */
        $session = new Zend_Session_Namespace();
        if (true === isset($session->lastProjectId)) {
            $formData['projectId']['value'] = $session->lastProjectId;
        }

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