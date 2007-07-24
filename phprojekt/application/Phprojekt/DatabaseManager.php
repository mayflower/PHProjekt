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

/* Phprojekt_ActiveRecord_Abstract */
require_once PHPR_CORE_PATH . '/Phprojekt/ActiveRecord/Abstract.php';

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
     * @return array Array with the data of the list fields
     */
    public function getFieldsForList($table)
    {
        $listFields = array('id');

        $where  = "table_name  = '" . $table . "'";
        $order  = "list_position";
        $fields = $this->fetchAll($where, $order);
        foreach ($fields as $fieldData) {
            $listFields[] = $fieldData->table_field;
        }

        return $listFields;
    }

    /**
     * Get the sorted form fields for the form
     *
     * @param string $table The table name of the module
     * 
     * @return array Array with the data of the form field
     */
    public function getFieldsForForm($table)
    {
        $formFields = array();

        $where  = "table_name  = '" . $table . "'";
        $order  = "form_position";
        $fields = $this->fetchAll($where);
        foreach ($fields as $fieldData) {
            $formFields[$fieldData->table_field] = array(
                'type'            => $fieldData->form_type,
                'tab'              => $fieldData->form_tab,
                'label'           => $fieldData->form_label,
                'tooltip'         => $fieldData->form_tooltip,
                'position'       => $fieldData->form_position,
                'columns'       => $fieldData->form_columns,
                'regexp'        => $fieldData->form_regexp,
                'range'           => $fieldData->form_range,
                'value'            => $fieldData->default_value,
                'is_integer'    => $fieldData->is_integer,
                'is_required'  => $fieldData->is_required,
                'is_unique'      => $fieldData->is_unique
            );
        }
        return $formFields;
    }
}