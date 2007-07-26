<?php
/**
 * Project model class
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
 * Project model class
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Project_Models_Project extends Phprojekt_Item_Abstract
{
    /**
     * Get all the projects from the db
     *
     * @return array Array with the rows for render
     */
    public function getListData()
    {
        $listFields = $this->getFieldsForList('project');
        $listData   = array('0' => $listFields);

        foreach ($this->fetchAll() as $row) {
            foreach ($listFields as $fieldName) {
                if (!isset($listData[$row->id])) {
                    $listData[$row->id] = array();
                }
                if (in_array($fieldName, $listFields)) {
                    $listData[$row->id][$fieldName] = $row->$fieldName;
                }
            }
        }

        return $listData;
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
        $formData = $this->getFieldsForForm('project');

        if ($id > 0) {
            $this->find($id);
            foreach ($formData as $fieldName => $fieldData) {
                $tmpData[$fieldName]          = $fieldData;
                $tmpData[$fieldName]['value'] = $this->$fieldName;
            }
            $formData = $tmpData;
        }

        return $formData;
    }

    /**
     * Save the data into the db
     *
     * @param array $request $_POST array
     *
     * @return void
     */
    public function saveData($request)
    {
        if (isset($request['id'])) {
            $id = (int) $request['id'];
            $this->find($id);
        }
        foreach ($request as $k => $v) {
            if ($this->keyExists($k)) {
                $this->$k = $v;
            }
        }

        $this->save();
    }

    /**
     * Delete a row
     *
     * @param array $request $_POST array
     *
     * @return void
     */
    public function deleteData($request)
    {
        if (isset($request['id'])) {
            $id = intval($request['id']);
            $this->find($id);
            $this->delete();
        }
    }
}