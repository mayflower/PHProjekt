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
 * For each field we have all the data of the item needed for the project like:
 *
 * tableName     = Name of the module and the table of the module
 * tableField    = Name of the field in the table
 * formTab       = Number of the tab to show it in various tabs
 * formLabel     = Text to display in the form (english text that is translated later)
 * formType      = Type of the field
 * formPosition  = Position of the field in the form
 * formColumns   = Number of columns that use the field
 * formRegexp    = Regular Expression to check the field
 * formRange     = Mix value to make the data of the fields, like for select types
 * defaultValue  = Default value
 * listPosition  = Position of the field in the list
 * listAlign     = Aligment of the field in the list
 * listUseFilter = Use the field in the filter list or not
 * altPosition   = Position of the field in the alt view
 * status        = Active or Inactive field
 * isInteger     = Int field or not
 * isRequired    = Is it a required field or not
 * isUnique      = Is it a unique value that can not be repeated or not
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
     * Info of the model fields in the database
     *
     * @var array
     */
    protected $_modelInfo = array();

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

    const COLUMN_NAME  = 'table_field';
    const COLUMN_TITLE = 'form_label';

    /**
     * We have to do the mapping, cause the constants that are passed
     * are just integers.
     *
     * @var array
     */
    private $_mapping = array (Phprojekt_ModelInformation_Default::ORDERING_FORM   => 'form_position',
                               Phprojekt_ModelInformation_Default::ORDERING_LIST   => 'list_position',
                               Phprojekt_ModelInformation_Default::ORDERING_FILTER => 'list_use_filter');

    /**
     * Initialize a new Database Manager and configure it with a model
     *
     * @param Phprojekt_Item_Abstract $model Phprojekt_Item_Abstract
     * @param array                   $db    Db configurations
     */
    public function __construct(Phprojekt_Item_Abstract $model = null, $db = null)
    {
        parent::__construct($db);
        $this->_model = $model;
        if (null !== $this->_model) {
            $this->_modelInfo = $this->_model->info();
        }
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
     * On the first call, the function saves the fields order into an array,
     * on the second call, if is the same order, the function returns the saved data,
     * if not, it gets the new order fields and saves it too.
     * This is for make the query to the database only once and not for each request.
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
                $table = $this->_model->getModelName();

                if (in_array($order, $this->_mapping)) {
                    $where  = $this->getAdapter()->quoteInto('table_name = ? AND ' . $order . ' > 0', $table);
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
        $table     = $this->_model->getModelName();

        return parent::fetchRow($this->_db->quoteInto('table_name = ?', $table)
                                . ' AND '
                                . $this->_db->quoteInto('table_field = ?', $fieldname));
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

        // The db manager handles field different than the encoder/output layer expect
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
                    if (null === $field->formRange) {
                        // No
                        $entry = $this->_convertStandard($field);
                    } else {
                        // Yes
                        $entry = $this->_convertSelect($field);
                    }
                    $entry['type']     = 'display';
                    $entry['readOnly'] = true;
                    $converted[]       = $entry;
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
                if (is_numeric($key)) {
                    $key = (int) $key;
                }
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
        $key       = $index = Phprojekt_ActiveRecord_Abstract::convertVarFromSql($field->tableField);

        $converted['key']      = $key;
        $converted['label']    = Phprojekt::getInstance()->translate($field->formLabel);
        $converted['type']     = $field->formType;
        $converted['hint']     = Phprojekt::getInstance()->getTooltip($key);
        $converted['order']    = 0;
        $converted['position'] = (int) $field->formPosition;
        $converted['fieldset'] = '';
        $converted['range']    = array('id'   => $field->formRange,
                                       'name' => $field->formRange);
        $converted['required'] = (boolean) $field->isRequired;
        $converted['readOnly'] = false;
        $converted['tab']      = $field->formTab;
        $converted['integer']  = (boolean) $field->isInteger;

        $maxLength = isset($this->_modelInfo['metadata'][$field->tableField]['LENGTH']) ?
            (int) $this->_modelInfo['metadata'][$field->tableField]['LENGTH'] : 0;
        $converted['length'] = $maxLength;

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
        $column = Phprojekt_ActiveRecord_Abstract::convertVarFromSql($column);
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
     * Return an array with form types to simplify things
     *
     * @param integer $ordering An ordering constant (MODELINFO_ORD_FORM, etc)
     *
     * @return array
     */
    public function getTypes($ordering = Phprojekt_ModelInformation_Default::ORDERING_FORM)
    {
        foreach ($this->_getFields($this->_mapping[$ordering]) as $field) {
            $index = Phprojekt_ActiveRecord_Abstract::convertVarFromSql($field->tableField);
            $this->_fieldTypes[$index] = $field->formType;
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
        $options                    = array();
        list($module, $key, $value) = explode('#', $field->formRange);
        $module                     = trim($module);
        $key                        = trim($key);
        $value                      = trim($value);

        switch ($module) {
            case 'Project':
                $activeRecord = Phprojekt_Loader::getModel('Project', 'Project');
                $tree         = new Phprojekt_Tree_Node_Database($activeRecord, 1);
                $tree         = $tree->setup();
                foreach ($tree as $node) {
                    $options[] = array('id'   => (int) $node->$key,
                                       'name' => $node->getDepthDisplay($value));
                }
                break;
            case 'User':
                $activeRecord = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
                $where        = sprintf('status = %s', $this->_db->quote('A'));
                $displayName  = $activeRecord->getDisplay();
                $result       = $activeRecord->fetchAll($where, $displayName);
                if (!$field->isRequired) {
                    $options[] = array('id'   => 0,
                                       'name' => '');
                }
                foreach ($result as $node) {
                    $options[] = array('id'   => (int) $node->id,
                                       'name' => $node->applyDisplay($displayName, $node));
                }
                break;
            default:
                $activeRecord = Phprojekt_Loader::getModel($module, $module);
                if (method_exists($activeRecord, 'getRangeFromModel')) {
                    $options = call_user_func(array($activeRecord, 'getRangeFromModel'), $field);
                } else {
                    $result  = $activeRecord->fetchAll();
                    $options = $this->_setRangeValues($field, $result, $key, $value);
                }
                break;
        }

        return $options;
    }

    /**
     * Validate the fields definitions per each field
     *
     * @param array $data The field definition
     *
     * @return boolean
     */
    public function recordValidate($data)
    {
        $valid        = true;
        $this->_error = new Phprojekt_Error();

        if (empty($data)) {
            $valid = false;
            $this->_error->addError(array(
                'field'   => 'Module Designer',
                'label'   => Phprojekt::getInstance()->translate('Module Designer'),
                'message' => Phprojekt::getInstance()->translate('The Module must contain at least one field')));
        }

        if ($valid && empty($data[0]['tableName'])) {
            $valid = false;
            $this->_error->addError(array(
                'field'   => 'Module Designer',
                'label'   => Phprojekt::getInstance()->translate('Module Designer'),
                'message' => Phprojekt::getInstance()->translate('Please enter a name for this module')));
        } else {
            if ($valid && !preg_match("/^[a-zA-Z]/", $data[0]['tableName'])) {
                $valid = false;
                $this->_error->addError(array(
                    'field'   => 'Module Designer',
                    'label'   => Phprojekt::getInstance()->translate('Module Designer'),
                    'message' => Phprojekt::getInstance()->translate('The module name must start with a letter')));
            }
        }

        $foundFields    = array();
        $foundProjectId = false;
        foreach ($data as $field) {
            if ($valid && (!isset($field['tableLength']) || !isset($field['tableField']) ||
                !isset($field['tableType']) || !isset($field['formType']))) {
                $valid = false;
                $this->_error->addError(array(
                    'field'   => 'Module Designer',
                    'label'   => Phprojekt::getInstance()->translate('Module Designer'),
                    'message' => Phprojekt::getInstance()->translate('Invalid parameteres')));
            }

            if ($valid) {
                $field['tableLength'] = intval($field['tableLength']);
            }

            if ($valid && empty($field['tableField'])) {
                $valid = false;
                $this->_error->addError(array(
                    'field'   => 'Module Designer',
                    'label'   => Phprojekt::getInstance()->translate('Module Designer'),
                    'message' => Phprojekt::getInstance()->translate('All the fields must have a table name')));
                break;
            } else {
                if ($valid && in_array($field['tableField'], $foundFields)) {
                    $valid = false;
                    $this->_error->addError(array(
                        'field'   => 'Module Designer',
                        'label'   => Phprojekt::getInstance()->translate('Module Designer'),
                        'message' => Phprojekt::getInstance()->translate('There are two fields with the same '
                            . 'Field Name')));
                    break;
                } else if ($valid) {
                    $foundFields[] = $field['tableField'];
                }
            }

            if ($valid && $field['tableType'] == 'varchar') {
                if ($field['tableLength'] < 1 || $field['tableLength'] > 255) {
                    $valid = false;
                    $this->_error->addError(array(
                        'field'   => 'Module Designer',
                        'label'   => Phprojekt::getInstance()->translate('Module Designer'),
                        'message' => Phprojekt::getInstance()->translate('The length of the varchar fields must be '
                            . 'between 1 and 255')));
                    break;
                }
            }

            if ($valid && $field['tableType'] == 'int') {
                if ($field['tableLength'] < 1 || $field['tableLength'] > 11) {
                    $valid = false;
                    $this->_error->addError(array(
                        'field'   => 'Module Designer',
                        'label'   => Phprojekt::getInstance()->translate('Module Designer'),
                        'message' => Phprojekt::getInstance()->translate('The length of the int fields must be between'
                            . ' 1 and 11')));
                    break;
                }
            }

            if ($valid && $field['formType'] == 'selectValues') {
                if ($valid && !isset($field['formRange'])) {
                    $valid = false;
                    $this->_error->addError(array(
                        'field'   => 'Module Designer',
                        'label'   => Phprojekt::getInstance()->translate('Module Designer'),
                        'message' => Phprojekt::getInstance()->translate('Invalid form Range for the select field')));
                    break;
                } else {
                    $field['formRange'] = trim($field['formRange']);
                }

                if ($valid && !strstr($field['formRange'], '#')) {
                    $valid = false;
                    $this->_error->addError(array(
                        'field'   => 'Module Designer',
                        'label'   => Phprojekt::getInstance()->translate('Module Designer'),
                        'message' => Phprojekt::getInstance()->translate('Invalid form Range for the select field')));
                    break;
                } else {
                    if ($valid && isset($field['selectType'])) {
                        switch ($field['selectType']) {
                            case 'project':
                            case 'user':
                            case 'contact':
                                if ($valid && (count(explode('#', $field['formRange'])) != 3)) {
                                    $valid = false;
                                    $this->_error->addError(array(
                                        'field'   => 'Module Designer',
                                        'label'   => Phprojekt::getInstance()->translate('Module Designer'),
                                        'message' => Phprojekt::getInstance()->translate('Invalid form Range for '
                                            .'the select field')));
                                }
                                break;
                            default:
                                if ($valid && !strstr($field['formRange'], '|')) {
                                    $valid = false;
                                    $this->_error->addError(array(
                                        'field'   => 'Module Designer',
                                        'label'   => Phprojekt::getInstance()->translate('Module Designer'),
                                        'message' => Phprojekt::getInstance()->translate('Invalid form Range for '.
                                            'the select field')));
                                } else {
                                    foreach (explode('|', $field['formRange']) as $range) {
                                        if ($valid && (count(explode('#', trim($range))) != 2)) {
                                            $valid = false;
                                            $this->_error->addError(array(
                                                'field'   => 'Module Designer',
                                                'label'   => Phprojekt::getInstance()->translate('Module Designer'),
                                                'message' => Phprojekt::getInstance()->translate('Invalid form Range '.
                                                    'for the select field')));
                                        }
                                    }
                                }
                                break;
                        }
                    }
                }

                if ($field['tableField'] == 'project_id') {
                    $foundProjectId = true;
                }
            }
        }

        if ($valid && !$foundProjectId) {
            $valid = false;
            $this->_error->addError(array(
                'field'   => 'Module Designer',
                'label'   => Phprojekt::getInstance()->translate('Module Designer'),
                'message' => Phprojekt::getInstance()->translate('The module must have a project selector called '
                    . 'projectId')));
        }

        return $valid;
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
        $where  = $this->getAdapter()->quoteInto('table_name = ?', $table);
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
        $fields = $this->_getFields('form_position');
        $data   = array();
        $i      = 0;
        if (null !== $this->_model) {
            $info   = $this->_model->info();
            foreach ($fields as $field) {
                $data[$i]['tableName'] = ucfirst($info['name']);
                while ($field->valid()) {
                    $key = Phprojekt_ActiveRecord_Abstract::convertVarFromSql($field->key());
                    if ($key != 'tableName') {
                        $value = $field->$key;
                        if (is_numeric($value)) {
                            $data[$i][$key] = (int) $value;
                        } else if (is_scalar($value)) {
                            $data[$i][$key] = $value;
                        } else {
                            if ($field->isInteger) {
                                $data[$i][$key] = (int) $value;
                            } else {
                                $data[$i][$key] = (string) $value;
                            }
                        }
                    }
                    $field->next();
                }
                $index = Phprojekt_ActiveRecord_Abstract::convertVarToSql($field->tableField);
                $data[$i]['tableType']   = $info['metadata'][$index]['DATA_TYPE'];
                if (null === $info['metadata'][$index]['LENGTH']) {
                    switch ($info['metadata'][$index]['DATA_TYPE']) {
                        case 'int':
                            $data[$i]['tableLength'] = 11;
                            break;
                        default:
                            $data[$i]['tableLength'] = 255;
                            break;
                    }
                } else {
                    $data[$i]['tableLength'] = $info['metadata'][$index]['LENGTH'];
                }
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

        // Clean the metadata cache, without Language tags
        Zend_Db_Table_Abstract::getDefaultMetadataCache()->clean(Zend_Cache::CLEANING_MODE_NOT_MATCHING_TAG,
            array('Language'));

        $oldFields = $this->getDataDefinition();
        $tableDataForCreate['id'] = array('type'   => 'auto_increment',
                                          'length' => 11);
        $tableDataForCreate['owner_id'] = array('type'   => 'int',
                                                'length' => 11);
        array_merge($tableDataForCreate, $tableData);
        $tableName   = strtolower(Phprojekt_ActiveRecord_Abstract::convertVarToSql($tableName));
        $tableFields = $tableManager->getTableFields($tableName, $tableDataForCreate);

        // Search for Modify and Delete
        $return = true;
        foreach ($oldFields as $oldValues) {
            $found = false;
            foreach ($newFields as $newValues) {
                if ($oldValues['id'] == $newValues['id']) {
                    $newValues['tableField']    = preg_replace('/[^a-zA-Z0-9_]/i', '', $newValues['tableField']);
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
                $newValues['tableField'] = preg_replace('/[^a-zA-Z0-9_]/i', '', $newValues['tableField']);
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
        $table  = $this->_model->getModelName();
        $where  = $this->getAdapter()->quoteInto('table_name = ?', $table);
        $result = $this->fetchAll($where);

        foreach ($result as $record) {
            $record->delete();
        }

        $tableManager = new Phprojekt_Table(Phprojekt::getInstance()->getDb());
        return $tableManager->dropTable($table);
    }

    /**
     * Process the Range value and return the options as array
     *
     * @param Object $field  Field information
     * @param Object $result Result set of items
     * @param string $key    Field key for the select (id by default)
     * @param string $value  Fields for show in the select
     *
     * @return array
     */
    private function _setRangeValues($field, $result, $key, $value)
    {
        $options = array();

        if (!$field->isRequired) {
            $options[] = array('id'   => 0,
                               'name' => '');
        }

        if (preg_match_all("/([a-zA-z_]+)/", $value, $values)) {
            $values = $values[1];
        } else {
            $values = $value;
        }

        foreach ($result as $item) {
            $showValue = array();
            foreach ($values as $value) {
                if (isset($item->$value)) {
                    $showValue[] = $item->$value;
                }
            }
            $showValue = implode(", ", $showValue);
            $options[] = array('id'   => $item->$key,
                               'name' => $showValue);
        }

        return $options;
    }
}
