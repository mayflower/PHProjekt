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

/* Phprojekt_Item */
require_once (PHPR_CORE_PATH . '/Phprojekt/Item.php');

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
class Project_Models_Project extends Phprojekt_Item
{
    /**
     * Get the field for a list view from the db_manager
     *
     * @todo must be from the db_manager stuff
     *
     *@param void
     * @return array - Array with the list fields
     */
    public function getFieldsForList()
    {
        return array('id','title','notes');
    }

    /**
     * Get the field for a form from the db_manager
     *
     * @todo must be from the db_manager stuff
     *
     *@param void
     * @return array - Array with the form fields
     */
    public function getFieldsForForm() {
        return array('title','notes');
    }

    /**
     * Get all the projects from the db
     *
     *@param void
     * @return array - Array with the rows for render
     */
    public function getListData()
    {
        $listFields = $this->getFieldsForList();
        $listData = array('0' => array());

        $info = $this->info();
        foreach ($info['cols'] as $indexColumn => $nameColumn) {
            if (in_array($nameColumn,$listFields)) {
                array_push($listData[0],$nameColumn);
            }
        }

        foreach ($this->fetchAll() as $row) {
            foreach ($info['cols'] as $fieldIndex => $fieldName) {
                if (!isset($listData[$row->id])) {
                    $listData[$row->id] = array();
                }
                if (in_array($fieldName,$listFields)) {
                    array_push($listData[$row->id],$row->$fieldName);
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
     *@param integer $id - Optional, for edit the row
     * @return array - Array with the fields for render
     */
    public function getFormData($id = 0)
    {
        $formFields = $this->getFieldsForForm();
        $formData = array();
        $info = $this->info();

        if ($id > 0) {
            $this->find($id);
        }

        foreach ($info['cols'] as $indexColumn => $nameColumn) {
            if (in_array($nameColumn,$formFields)) {
                $fieldData = array($nameColumn =>
                    array(
                        'type' => 'text',
                        'showName' => $nameColumn,
                        'value' => $this->$nameColumn
                    )
                );
                if (!isset($formData[$nameColumn])) {
                    $formData[$nameColumn] = array();
                }
                $formData = array_merge($formData,$fieldData);
            }
        }

        return $formData;
    }

    /**
     * Save the data into the db
     *
     *@param array $request - $_POST array
     * @return void
     */
    public function saveData($request) {
        if (isset($request['id'])) {
            $id = intval($request['id']);
            $this->find($id);
        }
        foreach($request as $k => $v) {
          if (array_key_exists($k, $this->_data)) {
              $this->$k = $v;
          }
        }

        $this->save();
    }


    /**
     * Get the action for make the form
     *
     * @param string $action - Define wich action are showing
     * @param integer $id      - The  id of the edited item
     * @return string               - The action url
     */
    public function getActionForm($action, $id = '') {
        switch ($action) {
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
     * @param string $action - Define wich action are showing
     * @param integer $id      - The  id of the edited item
     * @return string               - <a href="">
     */
    public function getButtonsForm($action, $id = '')
    {
        $translate = Zend_Registry::get('translate');

        $add = '<a href="'
                        . PHPR_ROOT_WEB_PATH
                        . 'project/'
                        . 'form'
                        .  '">'. $translate->_("Add") . '</a>';
        $edit = '<a href="'
                      . PHPR_ROOT_WEB_PATH
                      . 'project/'
                      . 'form/'
                      . 'delete/'
                      . 'id/'
                      . $id
                      .  '">'. $translate->_("Delete") . '</a>';

        switch ($action) {
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
     * @param array $request - $_POST array
     * @return void
     */
    public function deleteData($request) {
        if (isset($request['id'])) {
            $id = intval($request['id']);
            $this->find($id);
            $this->delete();
        }
    }
}