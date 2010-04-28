<?php
/**
 * DbParser Class for process the json db data.
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
 * @subpackage Core
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
 * DbParser Class for process the json db data.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Core
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_DbParser
{
    /**
     * Class for manage the db transactions.
     *
     * @var Phprojekt_Table
     */
    private $_tableManager = null;

    /**
     * Use the extra data content or not.
     *
     * @var boolean
     */
    private $_useExtraData = false;

    /**
     * Keep relations data for process it at the end.
     *
     * @var array
     */
    private $_relations = array();

    /**
     * Current db connection.
     *
     * @var Zend_Db
     */
    private $_db = null;

    /**
     * Use log file.
     *
     * @var boolean
     */
    protected $_log = false;

    /**
     * Keep the messages for return.
     *
     * @var array
     */
    private $_messages = array();

    /**
     * Constructor.
     *
     * @param array $options Array with options.
     * @param array $db      Configuration for Zend_Db_Table.
     *
     * @return void
     */
    public function __construct($options = array(), $db = null)
    {
        if (null === $db) {
            $this->_db = Phprojekt::getInstance()->getDb();
        } else {
            $this->_db = $db;
        }

        $this->_tableManager = new Phprojekt_Table($this->_db);

        if (isset($options['useExtraData'])) {
            $this->_useExtraData = (boolean) $options['useExtraData'];
        }
    }

    /**
     * Activate the debug.
     *
     * @return void
     */
    public function activeDebugLog()
    {
        $this->_log = true;
    }

    /**
     * Read all the data from the /$module/Sql/Db.json files,
     * decode the json data and process it.
     *
     * The function will call the parse process by each module.
     *
     * @return void
     */
    public function parseData($coreDirectory = null)
    {
        if (null === $coreDirectory) {
            $coreDirectory = PHPR_CORE_PATH;
        }

        // Load the Code file and process it
        $json         = file_get_contents($coreDirectory . '/Core/Sql/Db.json');
        $dataToParser = Zend_Json::decode($json);
        if (is_dir($coreDirectory . '/Core/SubModules/')) {
            $files = scandir($coreDirectory . '/Core/SubModules/');
            foreach ($files as $file) {
                if ($file != '.'  && $file != '..') {
                    $subFiles = scandir($coreDirectory . '/Core/SubModules/' . $file);
                    foreach ($subFiles as $subFile) {
                        if ($subFile != '.'  && $subFile != '..') {
                            $subPath = $coreDirectory . '/Core/SubModules/' . $file . '/' . $subFile . '/Sql/Db.json';
                            if (file_exists($subPath)) {
                                $json         = file_get_contents($subPath);
                                $data         = Zend_Json::decode($json);
                                $dataToParser = array_merge_recursive($dataToParser, $data);
                            }
                        }
                    }
                }
            }
        }
        if (!empty($dataToParser)) {
            $this->_parseData($dataToParser, 'Core');
        }

        // Per module, load the file and process it
        $files = scandir($coreDirectory);
        foreach ($files as $file) {
            if ($file != '.'  && $file != '..' && $file != 'Core') {
                $dataToParser = array();
                if (file_exists($coreDirectory . '/' . $file . '/Sql/Db.json')) {
                    $json         = file_get_contents($coreDirectory . '/' . $file . '/Sql/Db.json');
                    $data         = Zend_Json::decode($json);
                    $dataToParser = $data;
                }
                if (is_dir($coreDirectory . '/' . $file . '/SubModules/')) {
                    $subFiles = scandir($coreDirectory . '/' . $file . '/SubModules/');
                    foreach ($subFiles as $subFile) {
                        if ($subFile != '.'  && $subFile != '..') {
                            $subPath = $coreDirectory . '/' . $file . '/SubModules/' . $subFile . '/Sql/Db.json';
                            if (file_exists($subPath)) {
                                $json         = file_get_contents($subPath);
                                $data         = Zend_Json::decode($json);
                                $dataToParser = array_merge_recursive($dataToParser, $data);
                            }
                        }
                    }
                }
                if (!empty($dataToParser)) {
                    $this->_parseData($dataToParser, $file);
                }
            }
        }

        // Process relations
        $relations = array();
        foreach ($this->_relations as $relation) {
            $newId = $relation['newId'];
            $data  = $relation['content'];

            $relations = array_merge_recursive($relations, $this->_convertSpecialValues($data, $newId));
        }

        // Remove duplicate entries
        foreach ($relations as $tableName => $content) {
            foreach ($content as $action => $dataContent) {
                foreach ($dataContent as $index => $values) {
                    foreach ($relations[$tableName][$action] as $checkIndex => $checkValues) {
                        if ($index != $checkIndex) {
                            $diff = array_diff_assoc($values, $checkValues);
                            if (empty($diff)) {
                                unset($relations[$tableName][$action][$index]);
                            }
                        }
                    }
                }
            }
        }

        $this->_processData($relations);
    }

    /**
     * Parse the data content.
     * Use only the correct version data.
     *
     * Update the module with the new version.
     *
     * @param array  $data   Array with all the version and data for parse.
     * @param string $module Current module of the data.
     *
     * @return void
     */
    private function _parseData($data, $module)
    {
        $data          = $this->_getVersionsForProcess($module, $this->_sortData($data));
        $moduleVersion = $this->_getModuleVersion($module);
        foreach ($data as $version => $content) {
            if (!isset($this->_messages[$module])) {
                $this->_messages[$module] = array();
            }
            $this->_messages[$module]['version'] = $version;
            // Only process the initialData if the module version is lower than the data version
            if (Phprojekt::compareVersion($moduleVersion, $version) < 0) {
                if (!isset($this->_messages[$module]['process'])) {
                    $this->_messages[$module]['process'] = array();
                }
                if (isset($content['structure'])) {
                    $this->_messages[$module]['process']['structure'] = true;
                    $this->_processStructure($content['structure']);
                }

                if (isset($content['initialData'])) {
                    $this->_messages[$module]['process']['initalData'] = true;
                    $this->_processData($this->_convertSpecialValues($content['initialData'], 0));
                }

                if (isset($content['extraData']) && $this->_useExtraData) {
                    $this->_messages[$module]['process']['extraData'] = true;
                    $this->_processData($this->_convertSpecialValues($content['extraData'], 0));
                }
                $this->_messages[$module]['finish'] = 'Done';
            } else {
                $this->_messages[$module]['finish'] = 'Already installed';
            }
            $this->_setModuleVersion($module, $version);
        }
    }

    /**
     * Return the version of the module.
     *
     * @param string $module The name of the module.
     *
     * @return string Version string.
     */
    private function _getModuleVersion($module)
    {
        // Use Project version for all the core modules
        if ($module == 'Core') {
            $module = 'Project';
        }

        try {
            $version = $this->_moduleRow($module, 'version');
        } catch (Zend_Db_Statement_Exception $error) {
            // The module table don't exists yet
            $version = "0.0.0";
        }

        // New module => set version lower
        if (null === $version) {
            $version = "0.0.0";
        }

        return $version;
    }

    /**
     * Save the version for the module.
     *
     * @param string $module  The name of the module.
     * @param string $version The current version for save.
     *
     * @return void
     */
    private function _setModuleVersion($module, $version)
    {
        // Use Project for all the core modules
        if ($module == 'Core') {
            $module = 'Project';
        }

        $data  = array('version' => $version);
        $where = sprintf('id = %d', (int) $this->_getModuleId($module));
        $this->_tableManager->updateRows('module', $data, $where);
    }

    /**
     * Sort the array using the version as key.
     *
     * @param array $data Array with all the version and data for parse.
     *
     * @return array Sorted array.
     */
    private function _sortData($data)
    {
        uksort($data, array("Phprojekt", "compareVersion"));

        return $data;
    }

    /**
     * Delete all the version higher than the current one
     * and the version lower than the current module version.
     *
     * @param string $module Current module of the data.
     * @param array  $data   Array with all the version and data for parse.
     *
     * @return array Array with only the correct versions.
     */
    private function _getVersionsForProcess($module, $data)
    {
        $current       = Phprojekt::getVersion();
        $moduleVersion = $this->_getModuleVersion($module);

        foreach (array_keys($data) as $version) {
            if (Phprojekt::compareVersion($moduleVersion, $version) > 0 ||
                Phprojekt::compareVersion($current, $version) < 0) {
                unset($data[$version]);
            }
        }

        return $data;
    }

    /**
     * Parse and process the structure content.
     *
     * create => create the table.
     *
     * add    => add a new field.
     * update => make some changes into one field.
     * delete => delete a field.
     *
     * drop   => drop the table.
     *
     * @param array $array Array from the json data with the table data.
     *
     * @return void
     */
    private function _processStructure($array)
    {
        foreach ($array as $tableName => $content) {
            foreach ($content as $action => $fields) {
                switch ($action) {
                    case 'create':
                        if (!$this->_tableManager->tableExists($tableName)) {
                            $keys   = $this->_getKeys($fields);
                            $fields = $this->_convertFieldsData($fields);
                            $this->_tableManager->createTable($tableName, $fields, $keys);
                        }
                        break;
                    case 'drop':
                        $this->_tableManager->dropTable($tableName);
                        break;
                    case 'add':
                        $fields = $this->_convertFieldsData($fields);
                        foreach ($fields as $key => $field) {
                            $this->_tableManager->addField($tableName, $field);
                        }
                        break;
                    case 'update':
                        $fields = $this->_convertFieldsData($fields);
                        foreach ($fields as $key => $field) {
                            if (!isset($field['newName'])) {
                                $this->_tableManager->modifyField($tableName, $field);
                            } else {
                                $this->_tableManager->changeField($tableName, $field);
                            }
                        }
                        break;
                    case 'delete':
                        $fields = $this->_convertFieldsData($fields);
                        foreach ($fields as $key => $field) {
                            $this->_tableManager->deleteField($tableName, $field);
                        }
                        break;
                }
            }
        }
    }

    /**
     * Convert the values ##xxx_moduleId## with the moduleId value.
     *
     * @param array $data Array from the json data with the table data.
     *
     * @return array The array with the values replaced.
     */
    private function _convertModulesId($data)
    {
        foreach ($data as $key => $value) {
            if (preg_match("/^##([A-Za-z]+)_moduleId##$/", $value, $matches)) {
                $data[$key] = $this->_getModuleId($matches[1]);
            }
        }

        return $data;
    }

    /**
     * Parse and process the data content.
     *
     * insert => insert rows.
     * update => make some changes into the rows.
     * delete => delete rows.
     *
     * The values ##Module_id## are reemplaces with the moduleId value
     *
     * @param array $array Array from the json data with the changes.
     *
     * @return void
     */
    private function _processData($array)
    {
        foreach ($array as $tableName => $content) {
            foreach ($content as $action => $rows) {
                switch ($action) {
                    case 'insert':
                        foreach ($rows as $data) {
                            $relations = array();
                            if (isset($data['_relations'])) {
                                $relations = $data['_relations'];
                                unset($data['_relations']);
                            }
                            $data  = $this->_convertModulesId($data);
                            $newId = $this->_tableManager->insertRow($tableName, $data);
                            if (!empty($relations)) {
                                $this->_relations[] = array('newId'   => $newId,
                                                            'content' => $relations);
                            }
                        }
                        break;
                    case 'update':
                        foreach ($rows as $data) {
                            if (empty($data['_sqlWhere'])) {
                                $where = null;
                            } else {
                                $where = $data['_sqlWhere'];
                            }
                            unset($data['_sqlWhere']);
                            $data = $this->_convertModulesId($data);
                            $this->_tableManager->updateRows($tableName, $data, $where);
                        }
                        break;
                    case 'delete':
                        foreach ($rows as $code => $where) {
                            if (empty($code)) {
                                $where = null;
                            }
                            $data = $this->_convertModulesId($data);
                            $this->_tableManager->deleteRows($tableName, $where);
                        }
                        break;
                }
            }
        }
    }

    /**
     * Convert some ##values## into the real one.
     *
     * @param array   $array Array with all the data.
     * @param integer $newId New id generated.
     *
     * @return array Array with the converted values.
     */
    private function _convertSpecialValues($array, $newId)
    {
        // Convert the "all" and "1,2,3,etc" values in new entries
        foreach ($array as $tableName => $content) {
            foreach ($content as $action => $data) {
                foreach ($data as $index => $values) {
                    foreach ($values as $key => $value) {
                        $matches   = array();
                        $tmpValues = array();
                        if (!is_array($value)) {
                            if ($value == "all" && preg_match("/^([a-z]+)_id$/", $key, $matches)) {
                                $tmpValues = $this->_getAllRows($matches[1]);
                            } else if (strstr($value, ",") && preg_match("/^([a-z]+)_id$/", $key, $matches)) {
                                $tmpValues = explode(",", $value);
                            }
                        }
                        if (!empty($tmpValues)) {
                            $array[$tableName][$action][$index][$key] = array_shift($tmpValues);
                            foreach ($tmpValues as $id) {
                                $tmp       = $array[$tableName][$action][$index];
                                $tmp[$key] = $id;

                                $array[$tableName][$action][] = $tmp;
                            }
                        }
                    }
                }
            }
        }

        // Convert ##id##, ##ModuleName_moduleId## and NULL
        foreach ($array as $tableName => $content) {
            foreach ($content as $action => $data) {
                foreach ($data as $index => $values) {
                    foreach ($values as $key => $value) {
                        $matches = array();
                        if (!is_array($value)) {
                            if ($value == '##id##') {
                                $value = $newId;
                            } else if (preg_match("/^##([A-Za-z]+)_moduleId##$/", $value, $matches)) {
                                $value = $this->_getModuleId($matches[1]);
                            } else if ($value == 'NULL') {
                                $value = null;
                            }
                        }
                        $array[$tableName][$action][$index][$key] = $value;
                    }
                }
            }
        }

        return $array;
    }

    /**
     * Return all the IDs of one module.
     *
     * @param string $module The module name.
     *
     * @return array Array with IDs.
     */
    private function _getAllRows($module)
    {
        $rows   = array();
        $select = $this->_db->select()
                            ->from($module);

        switch ($module) {
            case 'module':
                $select->where('save_type = 0');
                break;
            case 'user':
                $select->where('status = ?', 'A');
                break;
        }

        $results = $this->_db->query($select)->fetchAll();
        foreach ($results as $result) {
            if (isset($result['id'])) {
                array_push($rows, $result['id']);
            }
        }

        return $rows;
    }

    /**
     * Return the keys of the table.
     * (id by default and all the "primary" fields).
     *
     * @param array $fields Array with all the fields data.
     *
     * @return array Array with keys (primary key, and unique).
     */
    private function _getKeys($fields)
    {
        $keys = array();

        foreach ($fields as $key => $content) {
            if ($key == 'id' && $content == 'default') {
                $keys['primary key'][] = 'id';
            } else {
                if (isset($content['primary'])) {
                   $keys['primary key'][] = $key;
                }
                if (isset($content['unique'])) {
                   $keys['unique'][] = $key;
                }
            }
        }

        return $keys;
    }

    /**
     * Convert the json data into Phprojekt_Table data for fields.
     *
     * @param array $fields Array with all the fields data.
     *
     * @return array Array with data for use with Phprojekt_Table.
     */
    private function _convertFieldsData($fields)
    {
        $data = array();

        foreach ($fields as $key => $content) {
            if ($key == 'id' && $content == 'default') {
                $data['id'] = array('type' => 'auto_increment', 'length' => 11);
            } else {
                if (isset($content['type'])) {
                    $data[$key]['type'] = $content['type'];
                }

                if (isset($content['length'])) {
                    $data[$key]['length'] = (int) $content['length'];
                } else {
                    if (isset($content['type'])) {
                        switch ($content['type']) {
                            case 'varchar':
                                $data[$key]['length'] = 255;
                                break;
                            case 'int':
                                $data[$key]['length'] = 11;
                                break;
                        }
                    }
                }

                if (isset($content['notNull'])) {
                    $data[$key]['null'] = false;
                }

                if (isset($content['default'])) {
                    $data[$key]['default'] = $content['default'];
                }

                if (isset($content['noQuoteDefaultValue'])) {
                    $data[$key]['default_no_quote'] = true;
                }

                if (isset($content['unsigned'])) {
                    $data[$key]['unsigned'] = true;
                }

                if (isset($content['newName'])) {
                    $data[$key]['newName'] = $content['newName'];
                    $data[$key]['name']    = $content['newName'];
                    $data[$key]['oldName'] = $key;
                } else {
                    $data[$key]['name']    = $key;
                    $data[$key]['oldName'] = $key;
                }
            }
        }

        return $data;
    }

    /**
     * Return the ID of the module in the module table.
     *
     * @param string $module Name of the module.
     *
     * @return integer The module ID.
     */
    private function _getModuleId($module)
    {
        $moduleId = $this->_moduleRow($module, 'id');
        if ($moduleId == 0) {
            $moduleId = $this->_db->lastInsertId($module, 'id');
        }

        return $moduleId;
    }

    /**
     * Make a query into the module table.
     * The function make the query directly for avoid caches.
     *
     * @param string $module Name of the module.
     * @param string $field  Name of the field for get.
     *
     * @return mix
     */
    private function _moduleRow($module, $field = 'id')
    {
        $select = $this->_db->select()
                            ->from('module')
                            ->where('name = ?', $module);

        $stmt = $this->_db->query($select);
        $rows = $stmt->fetchAll();

        switch ($field) {
            case 'id':
                $default = 0;
                break;
            case 'version':
            default:
                $default = null;
                break;
        }

        if (isset($rows[0])) {
            return $rows[0][$field];
        } else {
            return $default;
        }
    }

    /**
     * Return the messages generated by the class.
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }
}
