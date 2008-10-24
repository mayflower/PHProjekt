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
                $fieldDefinition['null']    = (empty($fieldDefinition['null'])  )? "" : $fieldDefinition['null'];
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
            $fieldDefinition['length'] = (empty($fieldDefinition['length']))?"":$fieldDefinition['length'];
            $fieldDefinition['null'] = (empty($fieldDefinition['null']))?"":$fieldDefinition['null'];
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
die($sqlString);
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
    private function _getTypeDefinition($fieldType, $fieldLength = null, $allowNull = null, $default = null)
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
                    $fieldType = 'integer';
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
            $fieldDefinition .= "(" . (int)$fieldLength . ")";
        }

        if (!empty($allowNull)) {
            $fieldDefinition .= " NOT NULL";
        }

        if (!empty($default)) {
            $fieldDefinition = "DEFAULT " . (string)$default;
        }

        return $fieldDefinition;
    }
}