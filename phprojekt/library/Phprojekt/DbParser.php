<?php
/**
 * DbParser Class for process the json db data
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
 * @subpackage Setup
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * DbParser Class for process the json db data
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @subpackage Setup
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_DbParser
{
    /**
     * Class for manage the db transactions
     *
     * @var Phprojekt_Table
     */
    private $_tableManager = null;

    /**
     * Use the extra data content or not
     *
     * @var boolean
     */
    private $_useExtraData = false;

    /**
     * Keep relations data for process it at the end
     *
     * @var array
     */
    private $_relations = array();

    /**
     * Current db connection
     *
     * @var Zend_Db
     */
    private $_db = null;

    /**
     * Use log file
     *
     * @var boolean
     */
    protected $_log = false;

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

    public function activeDebugLog()
    {
        $this->_log = true;
    }

    /**
     * Read all the data from the /$module/Sql/Db.json files,
     * decode the json data and process it
     *
     * The function will call the parse process by each module
     *
     * return one array
     *
     * @return array
     */
    public function parseData($coreDirectory = null)
    {
        if (null === $coreDirectory) {
            $coreDirectory = PHPR_CORE_PATH;
        }

        // Load the Code file and process it
        $json = file_get_contents($coreDirectory . '/Core/Sql/Db.json');
        $data = Zend_Json::decode($json);
        $this->_parseData($data, 'Core');

        // Per module, load the file and process it
        $files = scandir($coreDirectory);
        foreach ($files as $file) {
            if ($file != '.'  && $file != '..' && $file != '.svn' && $file != 'Core') {
                if (file_exists($coreDirectory . '/' . $file . '/Sql/Db.json')) {
                    $json = file_get_contents($coreDirectory . '/' . $file . '/Sql/Db.json');
                    $data = Zend_Json::decode($json);
                    $this->_parseData($data, $file);
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
     * Parse the data content
     * Use only the correct version data
     *
     * Update the module with the new version
     *
     * @param array  $data   Array with all the version and data for parse
     * @param string $module Current module of the data
     *
     * @return void
     */
    private function _parseData($data, $module)
    {
        $data          = $this->_getVersionsForProcess($module, $this->_sortData($data));
        $moduleVersion = $this->_getModuleVersion($module);
        foreach ($data as $version => $content) {
            echo 'Module: '. $module . ' - ';
            echo 'Version found: ' . $version . ': ';
            // Only process the initialData if the module version is lower than the data version
            if (Phprojekt::compareVersion($moduleVersion, $version) < 0) {
                echo 'Process : ';
                if (isset($content['structure'])) {
                    echo 'Structure | ';
                    $this->_processStructure($content['structure']);
                }

                if (isset($content['initialData'])) {
                    echo 'initalData | ';
                    $this->_processData($this->_convertSpecialValues($content['initialData'], 0));
                }

                if (isset($content['extraData']) && $this->_useExtraData) {
                    echo 'extraData | ';
                    $this->_processData($this->_convertSpecialValues($content['extraData'], 0));
                }
                echo 'Done';
            } else {
                echo 'Already installed';
            }
            echo '<br />';
            $this->_setModuleVersion($module, $version);
        }
    }

    /**
     * Return the version of the module
     *
     * @param string $module The name of the module
     *
     * @return string
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
     * Save the version for the module
     *
     * @param string $module  The name of the module
     * @param string $version The current version for save
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
     * Sort the array using the version as key
     *
     * @return array
     */
    private function _sortData($data)
    {
        uksort($data, array("Phprojekt", "compareVersion"));

        return $data;
    }

    /**
     * Delete all the version higher than the current one
     * and the version lower than the current module version
     *
     * @return array
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
     * Parse and process the structure content
     *
     * create => create the table
     *
     * add    => add a new field
     * update => make some changes into one field
     * delete => delete a field
     *
     * drop   => drop the table
     *
     * @param array $array Array from the json data with the table data
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

    private function _convertModulesId($data) {
        foreach ($data as $key => $value) {
            if (preg_match("/^##([A-Za-z]+)_moduleId##$/", $value, $matches)) {
                $data[$key] = $this->_getModuleId($matches[1]);
            }
        }

        return $data;
    }
    /**
     * Parse and process the data content
     *
     * insert => insert rows
     * update => make some changes into the rows
     * delete => delete rows
     *
     * The values ##Module_id## are reemplaces with the moduleId value
     *
     * @param array $array Array from the json data with the changes
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
     * Convert some ##values## into the real one
     *
     * @param array $array Array with all the data
     * @param int   $newId New id generated
     *
     * @return array
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
                                $tmpValues = split(",", $value);
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
     * Return all the id of one module
     *
     * @param string $module The module name
     *
     * @return array
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
     * Return the keys of the table
     * (id by default and all the "primary" fields)
     *
     * @param array $fields Array with all the fields data
     *
     * @return array
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
     * Convert the json data into Phprojekt_Table data for fields
     *
     * @param array $fields Array with all the fields data
     *
     * @return array
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
     * Return the id of the module in the module table
     *
     * @param string $module Name of the module
     *
     * @return int
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
     * Make a query into the module table
     * The function make the query directly for avoid caches
     *
     * @param string $module Name of the module
     * @param string $field  Name of the field for get
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
}
