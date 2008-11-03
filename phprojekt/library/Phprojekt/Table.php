<?php
/**
 * Table udpater for setup and database manager
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @author     Eduardo Polidor <polidor@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * The class provide the functions for create and alter tables on database
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @subpackage Core
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */
class Phprojekt_Table {
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
     * @param array                   $db    Db configurations
     */
    public function __construct($db = null)
    {
        $this->_db = $db;
        $this->_dbType = get_class($db);

        $this->_dbType = strtolower(substr($this->_dbType, strpos($this->_dbType, "Pdo") + 4));
    }

    /**
     * Creates a table
     *
     * @param $tableName String table name
     * @param $fields array with fieldnames as key
     *                Options: 'type', 'length', 'null', 'default')
     * @param $keys array with primary keys
     * 
     * @return boolean
     */
    public function createTable($tableName, $fields, $keys = array())
    {
        $sqlString = "CREATE TABLE ".(string)$tableName." (";

        if(is_array($fields) && !empty($fields)) {
            foreach ($fields as $fieldName => $fieldDefinition) {

                $fieldDefinition['length']  = (empty($fieldDefinition['length']))? "" : $fieldDefinition['length'];
                $fieldDefinition['null']    = $fieldDefinition['null'];
                $fieldDefinition['default'] = (empty($fieldDefinition['default']))? "" : $fieldDefinition['default'];

                $sqlString .= $fieldName;
                $sqlString .= $this->_getTypeDefinition($fieldDefinition['type'], $fieldDefinition['length'],
                                                        $fieldDefinition['null'], $fieldDefinition['default']) . ", ";
            }
        } else {
            return false;
        }

        if (isset($keys)) {

            $sqlString .= "PRIMARY KEY (";

            foreach ($keys as $oneKey) {

                $sqlString .= $oneKey . ", ";
            }
            $sqlString = substr($sqlString, 0, -2);

            $sqlString .= ")";
        } else {
            $sqlString = substr($sqlString, 0, -2);
        }

        $sqlString .= ")";

        return $this->_db->getConnection()->exec($sqlString);
    }

    /**
     * Add a field on a table
     *
     * @param $tableName String table name
     * @param $fieldDefinition array with field definition
     *                         Options: 'name', 'type', 'length', 'null', 'default')
     * @param $position after position
     * 
     * @return boolean
     */
    public function addField($tableName, $fieldDefinition, $position = null)
    {
        $sqlString = "ALTER TABLE ".(string)$tableName." ADD ";

        if(is_array($fieldDefinition) && !empty($fieldDefinition)) {
            $fieldDefinition['length']  = (empty($fieldDefinition['length']))?"":$fieldDefinition['length'];
            $fieldDefinition['null']    = $fieldDefinition['null'];
            $fieldDefinition['default'] = (empty($fieldDefinition['default']))?"":$fieldDefinition['default'];

            $sqlString .= $fieldDefinition['name'];
            $sqlString .= $this->_getTypeDefinition($fieldDefinition['type'], $fieldDefinition['length'],
                                                    $fieldDefinition['null'], $fieldDefinition['default']);
        } else {
            return false;
        }

        if (isset($position)) {

            $sqlString .= " AFTER " . (string)$position;
        }

        Zend_Registry::get('log')->debug($sqlString);
        return $this->_db->getConnection()->exec($sqlString);
    }

    /**
     * Deletes a field on a table
     *
     * @param $tableName String table name
     * @param $fieldDefinition array with field definition
     *                         Options: 'name', 'type', 'length', 'null', 'default')
     * 
     * @return boolean
     */
    public function deleteField($tableName, $fieldDefinition)
    {
        $sqlString = "ALTER TABLE ".(string)$tableName." DROP ";

        if(is_array($fieldDefinition) && !empty($fieldDefinition)) {

            $sqlString .= $fieldDefinition['name'];

        } else {
            return false;
        }
        Zend_Registry::get('log')->debug($sqlString);
        return $this->_db->getConnection()->exec($sqlString);
    }

    /**
     * Return an string with the field definition for each table type.
     *
     * @param string $fieldType regular field type names
     * @param int $fieldLength field length 
     * @param boolean $allowNull 
     * @param string $default default value
     *
     * @return string
     */
    private function _getTypeDefinition($fieldType, $fieldLength = null, $allowNull = true, $default = null)
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
            if (($fieldType == 'int') ||
                ($fieldType == 'varchar')) {
                $fieldDefinition .= "(" . (int)$fieldLength . ") ";
            }
        }

        if (!$allowNull) {
            $fieldDefinition .= " NOT NULL ";
        }

        if (!empty($default)) {
            $fieldDefinition .= " DEFAULT '" . (string)$default ."'";
        } else {
            $fieldDefinition .= " DEFAULT NULL";
        }

        return $fieldDefinition;
    }

    /**
     * Synchronizes a table with the provided definition
     *
     * @param $tableName String table name
     * @param $fields array with fieldnames as key
     *                Options: 'type', 'length', 'null', 'default')
     * @param $keys array with primary keys
     * 
     * @return boolean
     */
    public function syncTable($tableName, $fields, $keys = array())
    {
        try {
            $tableFields = $this->_db->describeTable($tableName);
        } catch (Exception $e) {
            return $this->createTable($tableName, $fields, $keys);

        }

        if(is_array($fields) && !empty($fields)) {
            foreach ($fields as $fieldName => $fieldDefinition) {
                if (array_key_exists($fieldName, $tableFields)) {
                    $fieldDefinition['name'] = $fieldName;
                    $this->modifyField($tableName, $fieldDefinition);
                    unset($tableFields[$fieldName]);
                } else {
                    $fieldDefinition['name'] = $fieldName;
                    $this->addField($tableName, $fieldDefinition);
                }
            }
        } else {
            return false;
        }

        if (is_array($tableFields) && !empty($tableFields)) {
            foreach ($tableFields as $fieldName => $fieldDefinition) {
                if (!in_array($fieldName, $this->_excludeFields)) {
                    $fieldDefinition['name'] = $fieldName;
                    $this->deleteField($tableName, $fieldDefinition);
                }
            }
        }

        return true;
    }

    /**
     * Modifies a field on a table
     *
     * @param $tableName String table name
     * @param $fieldDefinition array with field definition
     *                         Options: 'name', 'type', 'length', 'null', 'default')
     * 
     * @return boolean
     */
    public function modifyField($tableName, $fieldDefinition, $position = null)
    {
        $sqlString = "ALTER TABLE ".(string)$tableName." MODIFY ";

        if(is_array($fieldDefinition) && !empty($fieldDefinition)) {
            $fieldDefinition['length']  = (empty($fieldDefinition['length']))  ? "" : $fieldDefinition['length'];
            $fieldDefinition['null']    = $fieldDefinition['null'];
            $fieldDefinition['default'] = (empty($fieldDefinition['default'])) ? "" : $fieldDefinition['default'];

            $sqlString .= $fieldDefinition['name'];
            $sqlString .= $this->_getTypeDefinition($fieldDefinition['type'], $fieldDefinition['length'],
                                                    $fieldDefinition['null'], $fieldDefinition['default']);
        } else {
            return false;
        }

        Zend_Registry::get('log')->debug($sqlString);
        return $this->_db->getConnection()->exec($sqlString);
    }
}
