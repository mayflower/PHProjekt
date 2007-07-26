<?php
/**
 * Database manager interfase
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * The class provide the stuff from the database_manager
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class Phprojekt_DatabaseManager extends Phprojekt_ActiveRecord_Abstract
{
    /**
     * Array with the data of each fields
     *
     * @var array
     */
    protected $_dbFields = array();

    /**
     * Initialize new object
     *
     * @param array $config Configuration for Zend_Db_Table
     */
    public function __construct($config)
    {
        parent::__construct($config);
    }

    /**
     * Get the sorted list of field for list
     *
     * @param string $table The table name of the module
     *
     * @return array        Array with the data of the list fields
     */
    public function getFieldsForList($table)
    {
        $this->getFields($table);
        return $this->sortFieldsBy('listPosition', $this->_dbFields);
    }

    /**
     * Get the sorted form fields for the form
     *
     * @param string $table The table name of the module
     *
     * @return array        Array with the data of the form field
     */
    public function getFieldsForForm($table)
    {
        $this->getFields($table);
        return $this->sortFieldsBy('formPosition', $this->_dbFields);
    }

    /**
     * Get all the fields from the database_manager
     * And collect all the values
     *
     * @param string $table The table name of the module
     *
     * @return array        Array with the data of all the fields
     */
    public function getFields($table)
    {
        if (empty($this->_dbFields)) {
            $fields['id'] = array();

            $where = $this->getAdapter()->quoteInto('tableName = ?', $table);

            $fieldsRow = $this->fetchAll($where);

            foreach ($fieldsRow as $fieldData) {
                $tmp = array();
                foreach ($fieldData->_data as $fieldName => $fieldValue) {
                    $tmp[$fieldName] = $fieldValue;
                }
                $fields[$fieldData->tableField] = $tmp;
            }

            $this->_dbFields = $fields;
        }
    }

    /**
     * Sort the array by the field $field
     * minimal first
     *
     * @param string $field  Field for sort the array
     * @param array  $result Array to be sroted
     *
     * @return array Sorted array
     */
    public function sortFieldsBy($field,$result)
    {
        $change = 0;
        $tmp    = $result;
        $result = array();
        for ($count = 0; $count < count($tmp)+1 ; $count++) {
            $firstField   = array_shift($tmp);
            $secondField  = array_shift($tmp);
            if (!isset($secondField[$field])) {
                $result = array_merge($result,
                          array($firstField['tableField'] => $firstField));
            } else if (!isset($firstField[$field])) {
                $result = array_merge($result,
                          array($secondField['tableField'] => $secondField));
            } else if ($firstField[$field] > $secondField[$field]) {
                $result = array_merge($result,
                          array($secondField['tableField'] => $secondField));
                $result = array_merge($result,
                           array($firstField['tableField'] => $firstField));
                $change = 1;
            } else {
                $result = array_merge($result,
                          array($firstField['tableField'] => $firstField));
                $result = array_merge($result,
                          array($secondField['tableField'] => $secondField));
            }
        }
        if ($change) {
            $result = $this->sortFieldsBy($field,$result);
        }

        return $result;
    }
}