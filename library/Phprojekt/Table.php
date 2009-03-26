<?php
/**
 * Table udpater for setup and database manager
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
 * @author     Eduardo Polidor <polidor@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * The class provide the functions for create and alter tables on database
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @subpackage Core
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */
class Phprojekt_Table
{
    /**
     * Db connection
     *
     * @var string
     */
    protected $_db = null;

    /**
     * Db type
     *
     * @var string
     */
    protected $_dbType = null;

    /**
     * Exclude system fields
     *
     * @var array
     */
    protected $_excludeFields = array('id','ownerId');

    /**
     * Initialize a new table admin
     *
     * @param array $db Db configurations
     */
    public function __construct($db = null)
    {
        $this->_db     = $db;
        $this->_dbType = get_class($db);
        $this->_dbType = strtolower(substr($this->_dbType, strpos($this->_dbType, "Pdo") + 4));
    }

    /**
     * Creates a table
     *
     * @param $tableName String table name
     * @param $fields    Array with fieldnames as key
     *                   Options: 'type', 'length', 'null', 'default')
     * @param $keys      Array with keys (each array needs to have the key name (primary key, unique, etc) and an array
     *                   whit the key fields.
     *
     * @return boolean
     */
    public function createTable($tableName, $fields, $keys = array())
    {
        $tableName = ucfirst($tableName);
        $sqlString = "CREATE TABLE " . $this->_db->quoteIdentifier((string) $tableName) . " (";

        if (is_array($fields) && !empty($fields)) {
            foreach ($fields as $fieldName => $fieldDefinition) {
                if (!isset($fieldDefinition['length']) || empty($fieldDefinition['length'])) {
                    $fieldDefinition['length'] = "";
                }
                if (!isset($fieldDefinition['null']) || empty($fieldDefinition['null'])) {
                    $fieldDefinition['null'] = true;
                }
                if (!isset($fieldDefinition['default']) || empty($fieldDefinition['default'])) {
                    $fieldDefinition['default'] = "";
                }
                if (!isset($fieldDefinition['default_no_quote']) || empty($fieldDefinition['default_no_quote'])) {
                    $fieldDefinition['default_no_quote'] = false;
                }
                $sqlString .= $fieldName;
                $sqlString .= $this->_getTypeDefinition($fieldDefinition['type'], $fieldDefinition['length'],
                    $fieldDefinition['null'], $fieldDefinition['default'], $fieldDefinition['default_no_quote']) . ", ";
            }
        } else {
            return false;
        }

        if (isset($keys)) {
            foreach ($keys as $keyName => $keyFields) {
                $sqlString .= $keyName." (";
                foreach ($keyFields as $oneKey) {
                   $sqlString .= $oneKey . ", ";
                }
                $sqlString = substr($sqlString, 0, -2);
                $sqlString .= "),";
            }
            $sqlString = substr($sqlString, 0, -1);
        } else {
            $sqlString = substr($sqlString, 0, -2);
        }
        $sqlString .= ")";

        try {
            $this->_db->getConnection()->exec($sqlString);
            return true;
        } catch (Exception $error) {
            die ($sqlString);
            //Phprojekt::getInstance()->getLog()->debug($error->getMessage());
            return false;
        }
    }

    /**
     * Add a field on a table
     *
     * @param $tableName       String table name
     * @param $fieldDefinition Array with field definition
     *                         Options: 'name', 'type', 'length', 'null', 'default')
     * @param $position        After position
     *
     * @return boolean
     */
    public function addField($tableName, $fieldDefinition, $position = null)
    {
        $tableName = ucfirst($tableName);
        $sqlString = "ALTER TABLE " . $this->_db->quoteIdentifier((string) $tableName) . " ADD ";

        if (is_array($fieldDefinition) && !empty($fieldDefinition)) {
            if (!isset($fieldDefinition['length']) || empty($fieldDefinition['length'])) {
                $fieldDefinition['length'] = "";
            }
            if (!isset($fieldDefinition['null']) || empty($fieldDefinition['null'])) {
                $fieldDefinition['null'] = true;
            }
            if (!isset($fieldDefinition['default']) || empty($fieldDefinition['default'])) {
                $fieldDefinition['default'] = "";
            }
            if (!isset($fieldDefinition['default_no_quote']) || empty($fieldDefinition['default_no_quote'])) {
                $fieldDefinition['default_no_quote'] = false;
            }
            $sqlString .= $this->_db->quoteIdentifier((string) $fieldDefinition['name']);
            $sqlString .= $this->_getTypeDefinition($fieldDefinition['type'], $fieldDefinition['length'],
                $fieldDefinition['null'], $fieldDefinition['default'], $fieldDefinition['default_no_quote']);
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
            Phprojekt::getInstance()->getLog()->debug($error->getMessage());
            return false;
        }
    }

    /**
     * Change the name and parameteres of a field
     *
     * @param $tableName       String table name
     * @param $fieldDefinition Array with field definition
     *                         Options: 'oldName', 'name', 'type', 'length', 'null', 'default')
     *
     * @return boolean
     */
    public function changeField($tableName, $fieldDefinition, $position = null)
    {
        $tableName = ucfirst($tableName);
        $sqlString = "ALTER TABLE " . $this->_db->quoteIdentifier((string) $tableName) . " CHANGE ";
        $sqlString .= $this->_db->quoteIdentifier((string) $fieldDefinition['oldName']) . ' ';

        if (is_array($fieldDefinition) && !empty($fieldDefinition)) {
            if (!isset($fieldDefinition['length']) || empty($fieldDefinition['length'])) {
                $fieldDefinition['length'] = "";
            }
            if (!isset($fieldDefinition['null']) || empty($fieldDefinition['null'])) {
                $fieldDefinition['null'] = true;
            }
            if (!isset($fieldDefinition['default']) || empty($fieldDefinition['default'])) {
                $fieldDefinition['default'] = "";
            }
            if (!isset($fieldDefinition['default_no_quote']) || empty($fieldDefinition['default_no_quote'])) {
                $fieldDefinition['default_no_quote'] = false;
            }

            $sqlString .= $this->_db->quoteIdentifier((string) $fieldDefinition['name']);
            $sqlString .= $this->_getTypeDefinition($fieldDefinition['type'], $fieldDefinition['length'],
                $fieldDefinition['null'], $fieldDefinition['default'], $fieldDefinition['default_no_quote']);
        } else {
            return false;
        }

        try {
            $this->_db->getConnection()->exec($sqlString);
            return true;
        } catch (Exception $error) {
            Phprojekt::getInstance()->getLog()->debug($error->getMessage());
            return false;
        }
    }

    /**
     * Modifies a field on a table
     *
     * @param $tableName       String table name
     * @param $fieldDefinition Array with field definition
     *                         Options: 'oldName', 'name', 'type', 'length', 'null', 'default')
     *
     * @return boolean
     */
    public function modifyField($tableName, $fieldDefinition, $position = null)
    {
        $tableName = ucfirst($tableName);
        $sqlString = "ALTER TABLE " . $this->_db->quoteIdentifier((string) $tableName) . " MODIFY ";

        if (is_array($fieldDefinition) && !empty($fieldDefinition)) {
            if (!isset($fieldDefinition['length']) || empty($fieldDefinition['length'])) {
                $fieldDefinition['length'] = "";
            }
            if (!isset($fieldDefinition['null']) || empty($fieldDefinition['null'])) {
                $fieldDefinition['null'] = true;
            }
            if (!isset($fieldDefinition['default']) || empty($fieldDefinition['default'])) {
                $fieldDefinition['default'] = "";
            }
            if (!isset($fieldDefinition['default_no_quote']) || empty($fieldDefinition['default_no_quote'])) {
                $fieldDefinition['default_no_quote'] = false;
            }
            if (isset($fieldDefinition['oldName'])) {
                $sqlString .= $this->_db->quoteIdentifier((string) $fieldDefinition['oldName']) . ' ';
            }
            $sqlString .= $this->_db->quoteIdentifier((string) $fieldDefinition['name']);
            $sqlString .= $this->_getTypeDefinition($fieldDefinition['type'], $fieldDefinition['length'],
                $fieldDefinition['null'], $fieldDefinition['default'], $fieldDefinition['default_no_quote']);
        } else {
            return false;
        }

        try {
            $this->_db->getConnection()->exec($sqlString);
            return true;
        } catch (Exception $error) {
            Phprojekt::getInstance()->getLog()->debug($error->getMessage());
            return false;
        }
    }

    /**
     * Deletes a field on a table
     *
     * @param $tableName       String table name
     * @param $fieldDefinition Array with field definition
     *                         Options: 'name', 'type', 'length', 'null', 'default')
     *
     * @return boolean
     */
    public function deleteField($tableName, $fieldDefinition)
    {
        $tableName = ucfirst($tableName);
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
            Phprojekt::getInstance()->getLog()->debug($error->getMessage());
            return false;
        }
    }

    /**
     * Return an string with the field definition for each table type.
     *
     * @param string  $fieldType   Regular field type names
     * @param int     $fieldLength Field length
     * @param boolean $allowNull
     * @param string  $default     Default value
     *
     * @return string
     */
    private function _getTypeDefinition($fieldType, $fieldLength = null, $allowNull = true, $default = null, $defaultNoQuotes = false)
    {
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

        $fieldDefinition = " " . $fieldType;
        if (!empty($fieldLength)) {
            if (($fieldType == 'int') || ($fieldType == 'varchar')) {
                $fieldDefinition .= "(" . (int) $fieldLength . ") ";
            }
        }

        if (!$allowNull) {
            $fieldDefinition .= " NOT NULL ";
        }

        if (!empty($default)) {
            if (empty($defaultNoQuotes)) {
                $fieldDefinition .= " DEFAULT '" . (string) $default ."'";
            } else {
                $fieldDefinition .= " DEFAULT " . (string) $default;
            }
        } else if ($allowNull) {
            $fieldDefinition .= " DEFAULT NULL";
        }

        return $fieldDefinition;
    }

    /**
     * Check the table and return the field
     * If the table don`t exist, try to create it
     *
     * @param string $tableName The name of the table
     * @param array  $fields    The fields definitions
     * @param array  $keys      The PRIMARY KEY values
     *
     * @return array
     */
    public function getTableFields($tableName, $fields, $keys = array('id'))
    {
        try {
            $tableFields = $this->_db->describeTable($tableName);
            return $tableFields;
        } catch (Exception $error) {
            $error->getMessage();
            $tableName = ucfirst($tableName);
            $this->createTable($tableName, $fields, $keys);
            $tableFields = $this->_db->describeTable($tableName);
            return $tableFields;
        }
    }

    /**
     * Delete the table
     *
     * @param string $tableName The name of the table
     *
     * @return boolean
     */
    public function dropTable($tableName)
    {
        $tableName = ucfirst($tableName);
        $sqlString = "DROP TABLE " . $this->_db->quoteIdentifier((string) $tableName);

        try {
            $this->_db->getConnection()->exec($sqlString);
            return true;
        } catch (Exception $error) {
            Phprojekt::getInstance()->getLog()->debug($error->getMessage());
            return false;
        }
    }

    /**
     * Checks if a table exists
     *
     * @param string $tableName The name of the table
     *
     * @return boolean
     */
    public function tableExists($tableName)
    {
        $tableName = ucfirst($tableName);
        $sqlString = "SELECT COUNT(*) FROM " . $this->_db->quoteIdentifier((string) $tableName);

        try {
            $this->_db->getConnection()->exec($sqlString);
            return true;
        } catch (Exception $error) {
            return false;
        }
    }
}
