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
                    array_push($listData[$row->id], $row->$fieldName);
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
            $id = intval($request['id']);
            $this->find($id);
        }
        foreach ($request as $k => $v) {
            if (array_key_exists($k, $this->_data)) {
                $this->$k = $v;
            }
        }

        $this->save();
    }

    /**
     * Get the action for make the form
     *
     * @param string  $action Define wich action are showing
     * @param integer $id     The id of the edited item
     *
     * @return string         The action url
     */
    public function getActionForm($action, $id = '')
    {
        switch ($action)
        {
            default:
            case 'display':
                return PHPR_ROOT_WEB_PATH
                . 'project/'
                . 'form/'
                . 'save';
            break;
            case 'edit':
                return PHPR_ROOT_WEB_PATH
                . 'project/'
                . 'form/'
                . 'save/'
                . 'id/'
                . $id;
                break;
        }
    }

    /**
     * Get the buttons deppend on the action
     *
     * @param string  $action Define wich action are showing
     * @param integer $id     The  id of the edited item
     *
     * @return string         <a href="">
     */
    public function getButtonsForm($action, $id = '')
    {
        $translate = Zend_Registry::get('translate');

        $add = '<a href="'
            .  constant("PHPR_ROOT_WEB_PATH")
            . 'project/'
            . 'form'
            .  '">'. $translate->translate("Add") . '</a>';
        $edit = '<a href="'
            .  constant("PHPR_ROOT_WEB_PATH")
            . 'project/'
            . 'form/'
            . 'delete/'
            . 'id/'
            . $id
            .  '">'. $translate->translate("Delete") . '</a>';

        switch ($action)
        {
            default:
            case 'display':
                $buttons = $add;
                break;
            case 'edit':
                $buttons = $add . '&nbsp; ' . $edit;
                break;
        }

        return $buttons;
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