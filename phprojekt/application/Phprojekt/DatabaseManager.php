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
        return $this->_getFields($table, 'listPosition');
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
        return $this->_getFields($table, 'formPosition');
    }

    /**
     * Get all the fields from the database_manager
     * And collect all the values
     *
     * @param string $table The table name of the module
     * @param string $order Sort string
     *
     * @return array        Array with the data of all the fields
     */
    protected function _getFields($table, $order)
    {
        $found = 0;
        foreach ($this->_data as $key => $value) {
            if ($key == $order) {
                $found = 1;
            }
        }
        if (!$found) {
            $order = 'id';
        }

        if (empty($this->_dbFields[$order])) {
            $where = $this->getAdapter()->quoteInto('tableName = ?', $table);

            $fieldsRow = $this->fetchAll($where, $order);

            $fields = array();
            foreach ($fieldsRow as $fieldData) {
                $tmp = array();
                foreach ($fieldData->_data as $fieldName => $fieldValue) {
                    $tmp[$fieldName] = $fieldValue;
                }
                $fields[$fieldData->tableField] = $tmp;
            }

            $this->_dbFields[$order] = $fields;
        }

        return $this->_dbFields[$order];
    }
}