<?php
/**
 * Database manager interface
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * The class provide the functions for get data drom the databaseManager
 *
 * You can get all the fields from an specific module
 * sorted by list or form order
 *
 * For each field we have all the data of the them needed for the project like:
 *
 * tableName     = Name of the module and the table of the module
 * tablefield    = Name of the field in the table
 * formTab       = Number of the tab for show it in various tabs
 * formLabel     = Text for display in the form (english text that is translated later)
 * formType      = Type of the field
 * formPosition  = Position of the field in the form
 * formColumns   = Number of columns that use the field
 * formRegexp    = Regular Expresion for check the field
 * formRange     = Mix value for make the data of the fields, like for select types
 * defaultValue  = Default falue
 * listPosition  = Position of the field in the list
 * listAlign     = Aligment of the field in the list
 * listUseFilter = Use the field in the filter list or not
 * altPosition   = Position of the field in the alt view
 * status        = Active or Inactive field
 * isInteger     = Int field or not
 * isRequired    = If is a required field or not
 * isUnique      = If is a uniq value that can not be repeat or not
 *
 * The class itself is an ActiveRecord, so:
 * @see Phprojekt_ActiveRecord_Abstract
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @subpackage Core
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class Phprojekt_DatabaseManager extends Phprojekt_ActiveRecord_Abstract implements Phprojekt_ModelInformation_Interface
{
    /**
     * Cache
     *
     * @var array
     */
    protected $_dbFields = array();

    /**
     * The model to fetch the infos from
     *
     * @var Phprojekt_Item_Abstract
     */
    protected $_model;

    /**
     * Cache
     *
     * @var array
     */
    protected $_fieldTypes = array();

    /**
     * Error Class
     *
     * @var Phprojekt_Error
     */
    protected $_error = null;

    const COLUMN_NAME  = 'tableField';
    const COLUMN_TITLE = 'formLabel';

    /**
     * We have to do the mapping, cause the constants that are passed
     * are just integers.
     *
     * @var array
     */
    private $_mapping = array (Phprojekt_ModelInformation_Default::ORDERING_FORM   => 'formPosition',
                               Phprojekt_ModelInformation_Default::ORDERING_LIST   => 'listPosition',
                               Phprojekt_ModelInformation_Default::ORDERING_FILTER => 'listUseFilter');

    /**
     * Initialize a new Database Manager and configurate it with a model
     *
     * @param Phprojekt_Item_Abstract $model Phprojekt_Item_Abstract
     * @param array                   $db    Db configurations
     */
    public function __construct(Phprojekt_Item_Abstract $model = null, $db = null)
    {
        parent::__construct($db);
        $this->_model = $model;
        $this->getTypes();
    }

    /**
     * Return the associated model
     *
     * @return Phprojekt_Item_Abstract
     */
    public function getModel()
    {
        return $this->_model;
    }

    /**
     * Get all the fields from the databaseManager
     * And collect all the values
     *
     * If is defined a correct order, the array will return sorted by the order.
     * If not, the array will return sorted by id.
     *
     * The firs call, the function save the fields order into and array,
     * The second call, if is the same order, the function return the saved data,
     * if not, get the new order fields and save it too.
     * This is for make the querry to the database only one time and not for each request.
     *
     * @param string $order Sort string
     *
     * @return array        Array with the data of all the fields
     */
    protected function _getFields($order)
    {
        $result = array();
        if (!empty($this->_dbFields[$order])) {
            $result = $this->_dbFields[$order];
        } else {
            if (null !== $this->_model) {
                $table = $this->_model->getTableName();

                if (in_array($order, $this->_mapping)) {
                    $where  = $this->getAdapter()->quoteInto('tableName = ? AND '.$order.' > 0', $table);
                    $result = $this->fetchAll($where, $order);
                    $this->_dbFields[$order] = $result;
                }
            } else {
                $this->_dbFields[$order] = array();
            }
        }
        return $result;
    }

    /**
     * Find a special fieldname
     *
     * @return Zend_Db_Rowset
     */
    public function find()
    {
        $fieldname = func_get_arg(0);
        $table     = $this->_model->getTableName();
        return parent::fetchRow($this->_db->quoteInto('tableName = ?', $table)
                                . ' AND '
                                . $this->_db->quoteInto('tableField = ?', $fieldname));
    }

    /**
     * Return an array of field information.
     *
     * @param integer $ordering An ordering constant
     *
     * @return array
     */
    public function getFieldDefinition($ordering = Phprojekt_ModelInformation_Default::ORDERING_DEFAULT)
    {
        $converted = array();
        $fields    = $this->_getFields($this->_mapping[$ordering]);
        /* the db manager handles field different than the encoder/output layer expect */
        foreach ($fields as $field) {
            switch ($field->formType) {
                case 'selectValues':
                    $converted[] = $this->_convertSelect($field);
                    break;
                case 'multipleSelectValues':
                    $entry         = $this->_convertSelect($field);
                    $entry['type'] = 'multipleselectbox';
                    $converted[]   = $entry;
                    break;
                case 'display':
                    // Has it an Id value that should be translated into a descriptive String?
                    if ($field->formRange == '') {
                        // No
                        $entry             = $this->_convertStandard($field);
                        $entry['type']     = 'display';
                        $entry['readOnly'] = true;
                        $converted[]       = $entry;
                        
                    } else {
                        // Yes
                        $entry             = $this->_convertSelect($field);
                        $entry['type']     = 'display';
                        $entry['readOnly'] = true;
                        $converted[]       = $entry;
                    }
                    break;
                case 'upload':
                    $entry         = $this->_convertStandard($field);
                    $entry['type'] = 'upload';
                    $converted[]   = $entry;
                    break;
                default:
                    $converted[] = $this->_convertStandard($field);
                    break;
            }
        }
        return $converted;
    }

    /**
     * Convert to a a selectbox
     *
     * @param array $field
     *
     * @return array
     */
    protected function _convertSelect(Phprojekt_ModelInformation_Interface $field)
    {
        $converted          = $this->_convertStandard($field);
        $converted['range'] = array();
        $converted['type']  = 'selectbox';
        if (strpos($field->formRange, "|") > 0) {
            foreach (explode('|', $field->formRange) as $range) {
                list($key, $value) = explode('#', $range);
                $converted['range'][] = array('id'   => $key,
                                              'name' => Phprojekt::getInstance()->translate($value));
            }
        } else {
            $converted['range'] = $this->getRangeFromModel($field);
        }

        return $converted;
    }

    /**
     * Fields from the database manager have a complete different
     * type than those that should be propagated into the PHProjekt core
     *
     * @param array $field
     *
     * @return array
     */
    protected function _convertStandard(Phprojekt_ModelInformation_Interface $field)
    {
        $converted = array();

        $converted['key']      = $field->tableField;
        $converted['label']    = Phprojekt::getInstance()->translate($field->formLabel);
        $converted['type']     = $field->formType;
        $converted['hint']     = Phprojekt::getInstance()->getTooltip($field->tableField);
        $converted['order']    = 0;
        $converted['position'] = (int) $field->formPosition;
        $converted['fieldset'] = '';
        $converted['range']    = array('id'   => $field->formRange,
                                       'name' => $field->formRange);
        $converted['required'] = (boolean) $field->isRequired;
        $converted['readOnly'] = false;
        $converted['tab']      = $field->formTab;

        return $converted;
    }

    /**
     * Create a primitive mapping to an array. This is not pretty nice, but
     * for this version a reasonable solution
     *
     * @param integer $order  An ordering constant
     * @param string  $column Column
     *
     * @todo Maybe we have to refactor this. Doesnt look pretty for me. (dsp)
     *
     * @return array
     */
    public function getInfo($order, $column)
    {
        $fields = $this->_getFields($this->_mapping[$order]);
        $result = array();
        foreach ($fields as $field) {
            if (isset($field->$column)) {
                $result[] = $field->$column;
            }
        }
        return $result;
    }

    /**
     * Return an array with titles to simplify things
     *
     * @param integer $ordering An ordering constant (MODELINFO_ORD_FORM, etc)
     *
     * @return array
     */
    public function getTitles($ordering = Phprojekt_ModelInformation_Default::ORDERING_DEFAULT)
    {
        $result = array();
        foreach ($this->_getFields($this->_mapping[$ordering]) as $field) {
            $result[] = $field->formLabel;
        }
        return $result;
    }

    /**
     * Return an array with form types to simplify things
     *
     * @param integer $ordering An ordering constant (MODELINFO_ORD_FORM, etc)
     *
     * @return array
     */
    public function getTypes($ordering = Phprojekt_ModelInformation_Default::ORDERING_DEFAULT)
    {
        foreach ($this->_getFields($this->_mapping[$ordering]) as $field) {
            $this->_fieldTypes[$field->tableField] = $field->formType;
        }
    }

    /**
     * Return the type of one field
     *
     * @param string $fieldName The name of the field to chekc
     *
     * @return string
     */
    public function getType($fieldName)
    {
        $return = null;
        if (isset($this->_fieldTypes[$fieldName])) {
            $return = $this->_fieldTypes[$fieldName];
        }
        return $return;
    }

    /**
     * Gets the data range for a select using a model
     *
     * @param Phprojekt_ModelInformation_Interface $field the field description
     *
     * @return an array with key and value to be used as datarange
     */
    public function getRangeFromModel(Phprojekt_ModelInformation_Interface $field)
    {
        $options = array();
        list($module, $key, $value) = explode('#', $field->formRange);
        $module = trim($module);
        $key    = trim($key);
        $value  = trim($value);
        switch ($module) {
            case 'Project':
                $activeRecord = Phprojekt_Loader::getModel('Project', 'Project');
                $tree         = new Phprojekt_Tree_Node_Database($activeRecord, 1);
                $tree->setup();
                foreach ($tree as $node) {
                    $showKey   = $node->$key;
                    $showValue = str_repeat('....', $node->getDepth()) . $node->$value;
                    $options[] = array('id'   => $showKey,
                                       'name' => $showValue);
                }
                break;
            case 'User':
                if (!$field->isRequired) {
                    $options[] = array('id'   => 0,
                                       'name' => '');
                }
                $activeRecord = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
                $result       = $activeRecord->fetchAll("status = 'A'");
                foreach ($result as $oneUser) {
                    $options[] = array('id'   => $oneUser->$key,
                                       'name' => $oneUser->$value . ", " . $oneUser->firstname);
                }
                break;
            default:
                $activeRecord = Phprojekt_Loader::getModel($module, $module);
                if (in_array('getRangeFromModel', get_class_methods($activeRecord))) {
                    $options = call_user_method('getRangeFromModel', $activeRecord, $field);
                } else {
                    if (!$field->isRequired) {
                        $options[] = array('id'   => 0,
                                           'name' => '');
                    }
                    $result = $activeRecord->fetchAll();
                    foreach ($result as $item) {
                        $options[] = array('id'   => $item->$key,
                                           'name' => $item->$value);
                    }
                }
                break;
        }
        return $options;
    }

    /**
     * Validate the fields definitions per each field
     *
     * @param string $module The module table name
     * @param array  $data   The field definition
     *
     * @return boolean
     */
    public function recordValidate($module, $data)
    {
        $validated    = true;
        $this->_error = new Phprojekt_Error();

        if (empty($data)) {
            $validated = false;
            $this->_error->addError(array(
                'field'   => 'Module Designer',
                'label'   => Phprojekt::getInstance()->translate('Module Designer'),
                'message' => Phprojekt::getInstance()->translate('The Module must contain at least one field')));
        }

        if (empty($data[0]['tableName'])) {
            $validated = false;
            $this->_error->addError(array(
                'field'   => 'Module Designer',
                'label'   => Phprojekt::getInstance()->translate('Module Designer'),
                'message' => Phprojekt::getInstance()->translate('Please enter a name for this module')));
        } else {
            if (!preg_match("/^[a-zA-Z]/", $data[0]['tableName'])) {
                $validated = false;
                $this->_error->addError(array(
                    'field'   => 'Module Designer',
                    'label'   => Phprojekt::getInstance()->translate('Module Designer'),
                    'message' => Phprojekt::getInstance()->translate('The module name must start with a letter')));
            }
        }

        $foundFields    = array();
        $foundProjectId = false;
        foreach ($data as $field) {
            if (empty($field['tableField'])) {
                $validated = false;
                $this->_error->addError(array(
                    'field'   => 'Module Designer',
                    'label'   => Phprojekt::getInstance()->translate('Module Designer'),
                    'message' => Phprojekt::getInstance()->translate('All the fields must have a table name')));
                break;
            } else {
                if (in_array($field['tableField'], $foundFields)) {
                    $validated = false;
                    $this->_error->addError(array(
                        'field'   => 'Module Designer',
                        'label'   => Phprojekt::getInstance()->translate('Module Designer'),
                        'message' => Phprojekt::getInstance()->translate('There are two fields with the same '
                            . 'Field Name')));
                    break;
                } else {
                    $foundFields[] = $field['tableField'];
                }
            }

            if ($field['tableType'] == 'varchar') {
                if ($field['tableLength'] < 1 && $field['tableLength'] > 255) {
                    $validated = false;
                    $this->_error->addError(array(
                        'field'   => 'Module Designer',
                        'label'   => Phprojekt::getInstance()->translate('Module Designer'),
                        'message' => Phprojekt::getInstance()->translate('The length of the varchar fields must be '
                            . 'between 1 and 255')));
                    break;
                }
            }

            if ($field['tableType'] == 'int') {
                if ($field['tableLength'] < 1 && $field['tableLength'] > 11) {
                    $validated = false;
                    $this->_error->addError(array(
                        'field'   => 'Module Designer',
                        'label'   => Phprojekt::getInstance()->translate('Module Designer'),
                        'message' => Phprojekt::getInstance()->translate('The length of the int fields must be between'
                            . ' 1 and 11')));
                    break;
                }
            }

            if ($field['formType'] == 'selectValues') {
                if (!strstr($field['formRange'], '#')) {
                    $validated = false;
                    $this->_error->addError(array(
                        'field'   => 'Module Designer',
                        'label'   => Phprojekt::getInstance()->translate('Module Designer'),
                        'message' => Phprojekt::getInstance()->translate('Invalid form Range for the select field')));
                    break;
                }
                if ($field['tableField'] == 'projectId') {
                    $foundProjectId = true;
                }
            }
        }

        if (!$foundProjectId) {
            $validated = false;
            $this->_error->addError(array(
                'field'   => 'Module Designer',
                'label'   => Phprojekt::getInstance()->translate('Module Designer'),
                'message' => Phprojekt::getInstance()->translate('The module must have a project selector called '
                    . 'projectId')));
        }

        return $validated;
    }

    /**
     * Return an error array if there is any error
     *
     * @return array
     */
    public function getError()
    {
        return array_pop($this->_error->getError());
    }

    /**
     * Delete all entries for the current table and create the new one
     *
     * @param string $table The table name
     * @param array  $data  All the data of each field
     *
     * @return void
     */
    public function saveData($table, $data)
    {
        $where  = $this->getAdapter()->quoteInto('tableName = ?', $table);
        $result = $this->fetchAll($where);
        foreach ($result as $row) {
            $row->delete();
        }
        foreach ($data as $values) {
            $databaseManager = clone($this);
            foreach ($values as $key => $value) {
                if (isset($databaseManager->$key)) {
                    $databaseManager->$key = $value;
                }
            }
            $databaseManager->tableName = $table;
            $databaseManager->save();
        }
    }

    /**
     * Return an array with the definitions of the field
     * from the databasemanager and the module table itself
     *
     * The length don't work from int field types
     *
     * @return array
     */
    public function getDataDefinition()
    {
        $fields = $this->_getFields('formPosition');
        $data   = array();
        $i      = 0;
        if (null !== $this->_model) {
            $info   = $this->_model->info();
            foreach ($fields as $field) {
                $data[$i]['tableName'] = $info['name'];
                while($field->valid()) {
                    $key = $field->key();
                    if ($key != 'tableName') {
                        $data[$i][$key] = $field->$key;
                    }
                    $field->next();
                }
                $data[$i]['tableType']   = $info['metadata'][$field->tableField]['DATA_TYPE'];
                $data[$i]['tableLength'] = $info['metadata'][$field->tableField]['LENGTH'];
                $i++;
            }
        }

        return $data;
    }

    /**
     * Check the current Fields and make the sync in the table of the module
     *
     * @paran array  $newFields Array with all the data per new field
     * @param string $tableName Name of the module Table
     * @param array  $tableData Array with the table data definition per new field
     *
     * @return boolean
     */
    public function syncTable($newFields, $tableName, $tableData)
    {
        $tableManager = new Phprojekt_Table(Phprojekt::getInstance()->getDb());

        // Clean the metadata cache
        Zend_Db_Table_Abstract::getDefaultMetadataCache()->clean();

        $oldFields = $this->getDataDefinition();
        $tableDataForCreate['id'] = array('type'   => 'auto_increment',
                                          'length' => 11);
        $tableDataForCreate['ownerId'] = array('type'   => 'int',
                                               'length' => 11);
        array_merge($tableDataForCreate, $tableData);
        $tableFields = $tableManager->getTableFields($tableName, $tableDataForCreate);

        // Search for Modify and Delete
        $return = true;
        foreach ($oldFields as $oldValues) {
            $found = false;
            foreach ($newFields as $newValues) {
                if ($oldValues['id'] == $newValues['id']) {
                    $fieldDefinition            = $tableData[$newValues['tableField']];
                    $fieldDefinition['name']    = $newValues['tableField'];
                    if ($oldValues['tableField'] == $newValues['tableField']) {
                        if (!$tableManager->modifyField($tableName, $fieldDefinition)) {
                            $return = false;
                        }
                    } else {
                        $fieldDefinition['oldName'] = $oldValues['tableField'];
                        if (!$tableManager->changeField($tableName, $fieldDefinition)) {
                            $return = false;
                        }
                    }
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $fieldDefinition         = array();
                $fieldDefinition['name'] = $oldValues['tableField'];
                if (!$tableManager->deleteField($tableName, $fieldDefinition)) {
                    $return = false;
                }
            }
        }

        // Search for Add
        foreach ($newFields as $newValues) {
            if ($newValues['id'] == 0) {
                $fieldDefinition         = $tableData[$newValues['tableField']];
                $fieldDefinition['name'] = $newValues['tableField'];
                if (!$tableManager->addField($tableName, $fieldDefinition)) {
                    $return = false;
                }
            }
        }

        return $return;
    }

    /**
     * Delete all the entries for the current Module
     * And drop the table
     *
     * @return boolean
     */
    public function deleteModule()
    {
        $table  = $this->_model->getTableName();
        $where  = $this->getAdapter()->quoteInto(' tableName = ? ', $table);
        $result = $this->fetchAll($where);
        foreach ($result as $record) {
            $record->delete();
        }
        $tableManager = new Phprojekt_Table(Phprojekt::getInstance()->getDb());
        return $tableManager->dropTable($table);
    }
}
