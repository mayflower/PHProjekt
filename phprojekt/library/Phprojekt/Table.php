<?php
/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * Table udpater for setup and database manager.
 *
 * The class provide the functions for create and alter tables on database.
 */
class Phprojekt_Table
{
    /**
     * Db connection.
     *
     * @var string
     */
    protected $_db = null;

    /**
     * Db type.
     *
     * @var string
     */
    protected $_dbType = null;

    /**
     * Exclude system fields.
     *
     * @var array
     */
    protected $_excludeFields = array('id', 'ownerId');

    /**
     * Use log file.
     *
     * @var boolean
     */
    protected $_log = false;

    /**
     * Initialize a new table admin.
     *
     * @param array $db Configuration for Zend_Db_Table.
     *
     * @return void
     */
    public function __construct($db = null)
    {
        if (null === $db) {
            $db = Phprojekt::getInstance()->getDb();
        }
        $this->_db     = $db;
        $this->_dbType = get_class($db);
        $this->_dbType = strtolower(substr($this->_dbType, strpos($this->_dbType, "Pdo") + 4));
    }

    /**
     * Active the debug log.
     *
     * @return void
     */
    public function activeDebugLog()
    {
        $this->_log = true;
    }

    /**
     * Creates a table.
     *
     * @param $tableName String table name.
     * @param $fields    Array with fieldnames as key
     *                   Options: 'type', 'length', 'null', 'default').
     * @param $keys      Array with keys (each array needs to have the key name
     *                   (primary key, unique, etc) and an array whit the key fields.
     *
     * @return boolean True on a sucessful create.
     */
    public function createTable($tableName, $fields, $keys = array())
    {
        $tableName = strtolower($tableName);

        $definitions = array();
        if (is_array($fields) && !empty($fields)) {
            foreach ($fields as $fieldName => $fieldDefinition) {
                if (!isset($fieldDefinition['length'])) {
                    $fieldDefinition['length'] = "";
                }
                if (!isset($fieldDefinition['null'])) {
                    $fieldDefinition['null'] = true;
                }
                if (!isset($fieldDefinition['default'])) {
                    $fieldDefinition['default'] = "";
                }
                if (!isset($fieldDefinition['default_no_quote'])) {
                    $fieldDefinition['default_no_quote'] = false;
                }
                $definitions[] = $fieldName . $this->_getTypeDefinition($fieldDefinition);
            }
        } else {
            return false;
        }

        if (isset($keys)) {
            foreach ($keys as $keyName => $keyFields) {
                $definitions[] = sprintf('%s (%s) ', $keyName, implode(',', $keyFields));
            }
        }

        $sqlString = sprintf("CREATE TABLE %s (%s) DEFAULT CHARSET=utf8",
            $this->_db->quoteIdentifier((string) $tableName),
            implode(',', $definitions));

        try {
            $this->_db->getConnection()->exec($sqlString);
            // Fix for Zend Framework 1.7.2
            $this->_db->closeConnection();
            return true;
        } catch (Exception $error) {
            if ($this->_log) {
                Phprojekt::getInstance()->getLog()->debug($error->getMessage());
            }
            return false;
        }
    }

    /**
     * Add a field on a table.
     *
     * @param $tableName       String table name.
     * @param $fieldDefinition Array with field definition
     *                         Options: 'name', 'type', 'length', 'null', 'default').
     * @param $position        After position.
     *
     * @return boolean True on a sucessful add.
     */
    public function addField($tableName, $fieldDefinition, $position = null)
    {
        $tableName = strtolower($tableName);
        $sqlString = "ALTER TABLE " . $this->_db->quoteIdentifier((string) $tableName) . " ADD ";

        if (is_array($fieldDefinition) && !empty($fieldDefinition)) {
            if (!isset($fieldDefinition['length'])) {
                $fieldDefinition['length'] = "";
            }
            if (!isset($fieldDefinition['null'])) {
                $fieldDefinition['null'] = true;
            }
            if (!isset($fieldDefinition['default'])) {
                $fieldDefinition['default'] = "";
            }
            if (!isset($fieldDefinition['default_no_quote'])) {
                $fieldDefinition['default_no_quote'] = false;
            }
            $sqlString .= $this->_db->quoteIdentifier((string) $fieldDefinition['name']);
            $sqlString .= $this->_getTypeDefinition($fieldDefinition);
        } else {
            return false;
        }

        if (isset($position)) {
            $sqlString .= " AFTER " . (string) $position;
        }

        try {
            $this->_db->getConnection()->exec($sqlString);
            return true;
        } catch (Exception $error) {
            if ($this->_log) {
                Phprojekt::getInstance()->getLog()->debug($error->getMessage());
            }
            return false;
        }
    }

    /**
     * Change the name and parameteres of a field.
     *
     * @param $tableName       String table name.
     * @param $fieldDefinition Array with field definition
     *                         Options: 'oldName', 'name', 'type', 'length', 'null', 'default').
     *
     * @return boolean True on a sucessful change.
     */
    public function changeField($tableName, $fieldDefinition, $position = null)
    {
        $tableName = strtolower($tableName);
        $sqlString = "ALTER TABLE " . $this->_db->quoteIdentifier((string) $tableName) . " CHANGE ";
        $sqlString .= $this->_db->quoteIdentifier((string) $fieldDefinition['oldName']) . ' ';

        if (is_array($fieldDefinition) && !empty($fieldDefinition)) {
            if (!isset($fieldDefinition['length'])) {
                $fieldDefinition['length'] = "";
            }
            if (!isset($fieldDefinition['null'])) {
                $fieldDefinition['null'] = true;
            }
            if (!isset($fieldDefinition['default'])) {
                $fieldDefinition['default'] = "";
            }
            if (!isset($fieldDefinition['default_no_quote'])) {
                $fieldDefinition['default_no_quote'] = false;
            }

            $sqlString .= $this->_db->quoteIdentifier((string) $fieldDefinition['name']);
            $sqlString .= $this->_getTypeDefinition($fieldDefinition);
        } else {
            return false;
        }

        try {
            $this->_db->getConnection()->exec($sqlString);
            return true;
        } catch (Exception $error) {
            if ($this->_log) {
                Phprojekt::getInstance()->getLog()->debug($error->getMessage());
            }
            return false;
        }
    }

    /**
     * Modifies a field on a table.
     *
     * @param $tableName       String table name.
     * @param $fieldDefinition Array with field definition
     *                         Options: 'oldName', 'name', 'type', 'length', 'null', 'default').
     *
     * @return boolean True on a sucessful modify.
     */
    public function modifyField($tableName, $fieldDefinition, $position = null)
    {
        $tableName = strtolower($tableName);
        $sqlString = "ALTER TABLE " . $this->_db->quoteIdentifier((string) $tableName) . " MODIFY ";

        if (is_array($fieldDefinition) && !empty($fieldDefinition)) {
            if (!isset($fieldDefinition['length'])) {
                $fieldDefinition['length'] = "";
            }
            if (!isset($fieldDefinition['null'])) {
                $fieldDefinition['null'] = true;
            }
            if (!isset($fieldDefinition['default'])) {
                $fieldDefinition['default'] = "";
            }
            if (!isset($fieldDefinition['default_no_quote'])) {
                $fieldDefinition['default_no_quote'] = false;
            }
            if (isset($fieldDefinition['oldName'])) {
                $sqlString .= $this->_db->quoteIdentifier((string) $fieldDefinition['oldName']) . ' ';
            }
            $sqlString .= $this->_db->quoteIdentifier((string) $fieldDefinition['name']);
            $sqlString .= $this->_getTypeDefinition($fieldDefinition);
        } else {
            return false;
        }

        try {
            $this->_db->getConnection()->exec($sqlString);
            return true;
        } catch (Exception $error) {
            if ($this->_log) {
                Phprojekt::getInstance()->getLog()->debug($error->getMessage());
            }
            return false;
        }
    }

    /**
     * Deletes a field on a table.
     *
     * @param $tableName       String table name.
     * @param $fieldDefinition Array with field definition
     *                         Options: 'name', 'type', 'length', 'null', 'default').
     *
     * @return boolean True on a sucessful delete.
     */
    public function deleteField($tableName, $fieldDefinition)
    {
        $tableName = strtolower($tableName);
        $sqlString = "ALTER TABLE " . $this->_db->quoteIdentifier((string) $tableName) . " DROP ";

        if (is_array($fieldDefinition) && !empty($fieldDefinition)) {
            $sqlString .= $this->_db->quoteIdentifier((string) $fieldDefinition['name']);
        } else {
            return false;
        }

        try {
            $this->_db->getConnection()->exec($sqlString);
            return true;
        } catch (Exception $error) {
            if ($this->_log) {
                Phprojekt::getInstance()->getLog()->debug($error->getMessage());
            }
            return false;
        }
    }

    /**
     * Return an string with the field definition for each table type.
     *
     * @param array $fieldDefinition Definitions for the field.
     *
     * @return string Sql clause.
     */
    private function _getTypeDefinition($fieldDefinition)
    {
        if (isset($fieldDefinition['type'])) {
            $fieldType = $fieldDefinition['type'];
        } else {
            $fieldType = null;
        }

        if (isset($fieldDefinition['length'])) {
            $fieldLength = $fieldDefinition['length'];
        } else {
            $fieldLength = null;
        }

        if (isset($fieldDefinition['null'])) {
            $allowNull = $fieldDefinition['null'];
        } else {
            $allowNull = true;
        }

        if (isset($fieldDefinition['default'])) {
            $default = $fieldDefinition['default'];
        } else {
            $default = null;
        }

        if (isset($fieldDefinition['default_no_quote'])) {
            $defaultNoQuotes = $fieldDefinition['default_no_quote'];
        } else {
            $defaultNoQuotes = false;
        }

        switch ($this->_dbType) {
            case 'sqlite':
            case 'sqlite2':
                if ($fieldType == 'auto_increment') {
                    $fieldType = 'integer';
                }
                break;
            case 'pgsql':
                if ($fieldType == 'auto_increment') {
                    $fieldType = 'serial';
                }
                if ($fieldType == 'int') {
                    $fieldType   = 'integer';
                    $fieldLength = null;
                }
                break;
            default:
                if ($fieldType == 'auto_increment') {
                    $fieldType = 'int(11) NOT NULL auto_increment';
                }
                break;
        }

        $sqlString = " " . $fieldType;
        if (!empty($fieldLength)) {
            if (($fieldType == 'int') || ($fieldType == 'varchar')) {
                $sqlString .= "(" . (int) $fieldLength . ") ";
            }
        }

        if (isset($fieldDefinition['unsigned'])) {
            $sqlString .= " UNSIGNED ";
        }

        if (!$allowNull) {
            $sqlString .= " NOT NULL ";
        }

        if (!empty($default) || $default == "0") {
            if (empty($defaultNoQuotes)) {
                $sqlString .= " DEFAULT '" . (string) $default ."'";
            } else {
                $sqlString .= " DEFAULT " . (string) $default;
            }
        } else if ($allowNull) {
            $sqlString .= " DEFAULT NULL";
        }

        return $sqlString;
    }

    /**
     * Check the table and return the field.
     *
     * If the table don`t exist, try to create it.
     *
     * @param string $tableName The name of the table.
     * @param array  $fields    The fields definitions.
     * @param array  $keys      The PRIMARY KEY values.
     *
     * @return array Database definitions.
     */
    public function getTableFields($tableName, $fields = array(), $keys = array('primary key' => array('id')))
    {
        try {
            $tableFields = $this->_db->describeTable($tableName);
            return $tableFields;
        } catch (Exception $error) {
            $error->getMessage();
            $tableName = strtolower($tableName);
            $this->createTable($tableName, $fields, $keys);
            $tableFields = $this->_db->describeTable($tableName);
            return $tableFields;
        }
    }

    /**
     * Delete the table.
     *
     * @param string $tableName The name of the table.
     *
     * @return boolean True on a sucessful drop.
     */
    public function dropTable($tableName)
    {
        $tableName = strtolower($tableName);
        $sqlString = "DROP TABLE " . $this->_db->quoteIdentifier((string) $tableName);

        try {
            $this->_db->getConnection()->exec($sqlString);
            return true;
        } catch (Exception $error) {
            if ($this->_log) {
                Phprojekt::getInstance()->getLog()->debug($error->getMessage());
            }
            return false;
        }
    }

    /**
     * Checks if a table exists.
     *
     * @param string $tableName The name of the table.
     *
     * @return boolean True if exists.
     */
    public function tableExists($tableName)
    {
        $tableName = strtolower($tableName);
        $sqlString = "SELECT COUNT(*) FROM " . $this->_db->quoteIdentifier((string) $tableName);

        try {
            $this->_db->getConnection()->exec($sqlString);
            // Fix for Zend Framework 1.7.2
            $this->_db->closeConnection();
            return true;
        } catch (Exception $error) {
            return false;
        }
    }

    /**
     * Make an insert.
     *
     * @param string $tableName The name of the table.
     * @param array  $data      Array with key => value data.
     *
     * @return integer Last insert ID or 0.
     */
    public function insertRow($tableName, $data)
    {
        try {
            $this->_db->insert($tableName, $data);
            return $this->_db->lastInsertId($tableName, 'id');
        } catch (Exception $error) {
            echo $error . '<br\>';
            return 0;
        }
    }

    /**
     * Make multiple inserts.
     *
     * @param string  $tableName The name of the table.
     * @param array   $fields    Array with keys.
     * @param array   $datas     Array with values.
     * @param boolean $returnId  Return the inserted ids or not.
     *
     * @return array Array with inserted IDs.
     */
    public function insertMultipleRows($tableName, $fields, $datas, $returnId = false)
    {
        $countFields = count($fields);
        if ($countFields < 5) {
            $maxRows = 2000;
        } else if ($countFields < 7) {
            $maxRows = 1500;
        } else if ($countFields < 9) {
            $maxRows = 1000;
        } else {
            $maxRows = 500;
        }

        try {
            if ($returnId) {
                $sqlString = "SELECT MAX(id) as count FROM " . $this->_db->quoteIdentifier((string) $tableName);
                $result    = $this->_db->query($sqlString)->fetchAll();
                $currentId = (int) $result[0]['count'];
            }
            $ids  = array();
            $sql  = 'INSERT INTO ' . $this->_db->quoteIdentifier($tableName) . ' ';
            $sql .= '(' . implode(",", $fields) . ') ';
            $sql .= 'VALUES ';

            $countData  = count($datas);
            $values     = array();
            $current    = 0;
            $sqlValues  = '';
            foreach ($datas as $data) {
                $current++;
                $sqlValues .= '(';
                for ($i = 0; $i < $countFields; $i++) {
                    $sqlValues .= "?";
                    if ($i != $countFields - 1) {
                        $sqlValues .= ",";
                    }
                }
                $sqlValues .= ')';
                if ($countData != $current && ($current % $maxRows != 0)) {
                    $sqlValues .= ', ';
                }
                $values = array_merge($values, $data);
                if ($returnId) {
                    $currentId++;
                    $ids[] = $currentId;
                }
                // If the query have more than $maxRows values, execute it
                if ($current % $maxRows == 0) {
                    $stmt      = $this->_db->query($sql . $sqlValues, $values);
                    $sqlValues = '';
                    $values    = array();
                }
            }
            // Execute the rest of the values
            if (!empty($values)) {
                $this->_db->query($sql . $sqlValues, $values);
            }
            return $ids;
        } catch (Exception $error) {
            echo $error . '<br\>';
            return array();
        }
    }

    /**
     * Make an update.
     *
     * @param string $tableName The name of the table.
     * @param array  $data      Array with key => value data.
     * @param string $where     Sql where.
     *
     * @return boolean True on a sucessful update.
     */
    public function updateRows($tableName, $data, $where)
    {
        try {
            $this->_db->update($tableName, $data, $where);
            return true;
        } catch (Exception $error) {
            echo $error . '<br\>';
            return false;
        }
    }

    /**
     * Make a delete.
     *
     * @param string $tableName The name of the table.
     * @param string $where     Sql where.
     *
     * @return boolean True on a sucessful delete.
     */
    public function deleteRows($tableName, $where)
    {
        try {
            $this->_db->delete($tableName, $where);
            return true;
        } catch (Exception $error) {
            echo $error . '<br\>';
            return false;
        }
    }

    /**
     * Creates an index.
     *
     * @param string $tableName The name of the table.
     * @param array  $columns   The columns contained in the index.
     * @param array  $options   Optional parameters, may contain the keys 'name' (string) and'unique' (boolean).
     * @author Simon Kohlmeyer
     **/
    public function createIndex($tableName, array $columns, array $options = array())
    {
        $defaults = array(
            'name'   => implode('', $columns),
            'unique' => false,
        );
        $options = array_merge($defaults, $options);

        foreach ($columns as $key => $column) {
            $columns[$key] = $this->_db->quoteIdentifier($column);
        }

        $sql = sprintf(
            'CREATE %s INDEX %s ON %s (%s)',
            $options['unique'] ? 'UNIQUE' : '',
            $this->_db->quoteIdentifier($options['name']),
            $this->_db->quoteIdentifier($tableName),
            implode(', ', $columns)
        );

        $this->_db->query($sql);
    }
}
