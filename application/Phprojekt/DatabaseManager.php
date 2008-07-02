<?php
/**
 * Database manager interface
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * The class provide the functions for get data drom the databaseManager
 *
 * You can get all the fields from an specific module
 * sorted by list or form order
 *
 * For each field we have all the data of the them needed for the project like:
 *
 * tableName     = Name of the module and the table of the module
 * tablefield    = Name of the field in the table
 * formTab       = Number of the tab for show it in various tabs
 * formLabel     = Text for display in the form (english text that is translated later)
 * formTooltip   = Text for display in the title of the field in the form
 * formType      = Type of the field
 * formPosition  = Position of the field in the form
 * formColumns   = Number of columns that use the field
 * formRegexp    = Regular Expresion for check the field
 * formRange     = Mix value for make the data of the fields, like for select types
 * defaultValue  = Default falue
 * listPosition  = Position of the field in the list
 * listAlign     = Aligment of the field in the list
 * listUseFilter = Use the field in the filter list or not
 * altPosition   = Position of the field in the alt view
 * status        = Active or Inactive field
 * isInteger     = Int field or not
 * isRequired    = If is a required field or not
 * isUnique      = If is a uniq value that can not be repeat or not
 *
 * The class itself is an ActiveRecord, so:
 * @see Phprojekt_ActiveRecord_Abstract
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @subpackage Core
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
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

    const COLUMN_NAME  = 'tableField';
    const COLUMN_TITLE = 'formLabel';

    /**
     * We have to do the mapping, cause the constants that are passed
     * are just integers.
     *
     * @var array
     */
    private $_mapping = array (Phprojekt_ModelInformation_Default::ORDERING_FORM   => 'formPosition',
                               Phprojekt_ModelInformation_Default::ORDERING_LIST   => 'listPosition',
                               Phprojekt_ModelInformation_Default::ORDERING_FILTER => 'listUseFilter');

    /**
     * Initialize a new Database Manager and configurate it with a model
     *
     * @param Phprojekt_Item_Abstract $model Phprojekt_Item_Abstract
     * @param array                   $db    Db configurations
     */
    public function __construct(Phprojekt_Item_Abstract $model, $db = null)
    {
        parent::__construct($db);
        $this->_model = $model;
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
     * The firs call, the function save the fields order into and array,
     * The second call, if is the same order, the function return the saved data,
     * if not, get the new order fields and save it too.
     * This is for make the querry to the database only one time and not for each request.
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
            $table = $this->_model->getTableName();

            if (in_array($order, $this->_mapping)) {
                $where = $this->getAdapter()->quoteInto('tableName = ? AND '.$order.' > 0', $table);
                $result = $this->fetchAll($where, $order);
                $this->_dbFields[$order] = $result;
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
        $table     = $this->_model->getTableName();
        return parent::fetchRow($this->_db->quoteInto('tableName = ?', $table)
                                . ' AND '
                                . $this->_db->quoteInto('tableField = ?', $fieldname));
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
        /* the db manager handles field different than the encoder/output layer expect */
        foreach ($fields as $field) {
            switch ($field->formType) {
                case 'selectValues':
                    $converted[] = $this->_convertSelect($field);
                    break;
                case 'multipleSelectValues':
                    $entry = $this->_convertSelect($field);
                    $entry['type'] = 'multipleselectbox';
                    $converted[] = $entry;
                    break;
                case 'textarea':
                case 'textfield':
                case 'checkbox':
                case 'date':
                case 'upload':
                case 'time':
                case 'datetime':
                case 'timestamp':
                    $converted[] = $this->_convertStandard($field);
                    break;
                case 'multipleselect':
                case 'select_multiple':
                    $converted[]   = $this->_convertStandard($field);
                    $entry['type'] = 'multipleselect';
                    $converted[]   = $entry;
                case 'text':
                    $entry         = $this->_convertStandard($field);
                    $entry['type'] = 'textfield';
                    $converted[]   = $entry;
                    break;
                case 'display':
                    $entry             = $this->_convertStandard($field);
                    $entry['type']     = 'label';
                    $entry['readOnly'] = true;
                    $converted[]       = $entry;
                    break;
                case 'tree':
                    $entry             = $this->_convertTree($field);
                    $entry['type']     = 'selectbox';
                    $converted[]       = $entry;
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
                $converted['range'][] = array('id'   => $key,
                                              'name' => $value);
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
        $translate = Zend_Registry::get('translate');

        $converted['key']      = $field->tableField;
        $converted['label']    = $translate->translate($field->formLabel);
        $converted['type']     = $field->formType;
        $converted['hint']     = $field->formTooltip;
        $converted['order']    = 0;
        $converted['position'] = (int) $field->formPosition;
        $converted['fieldset'] = '';
        $converted['range']    = array('id'   => $field->formRange,
                                       'name' => $field->formRange);
        $converted['required'] = (boolean) $field->isRequired;
        $converted['readOnly'] = false;

        return $converted;
    }

    /**
     * Convert to a selectbox using tree values
     *
     * @param array $field
     *
     * @return array
     */
    public function _convertTree(Phprojekt_ModelInformation_Interface $field)
    {
        $converted          = $this->_convertStandard($field);
        $converted['range'] = array();
        $converted['type']  = 'selectbox';

        $activeRecord = Phprojekt_Loader::getModel($field->formRange, $field->formRange);
        $tree = new Phprojekt_Tree_Node_Database($activeRecord,1);
        $tree->setup();
        foreach ($tree as $node) {
            $key   = $node->id;
            $value = str_repeat('....', $node->getDepth()) . $node->title;
            $converted['range'][] = array('id'   => $key,
                                          'name' => $value);
        }

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
     * Return an array with titles to simplify things
     *
     * @param integer $ordering An ordering constant (MODELINFO_ORD_FORM, etc)
     *
     * @return array
     */
    public function getTitles($ordering = Phprojekt_ModelInformation_Default::ORDERING_DEFAULT)
    {
        $result = array();
        foreach ($this->_getFields($this->_mapping[$ordering]) as $field) {
            $result[] = $field->formLabel;
        }
        return $result;
    }
    
    /**
     * Gets the data range for a select using a model
     *
     * @param Phprojekt_ModelInformation_Interface $field the field description
     * @return an array with key and value to be used as datarange
     */
    public function getRangeFromModel(Phprojekt_ModelInformation_Interface $field) 
    {
        $options = array();
        
        $activeRecord = Phprojekt_Loader::getModel($field->formRange, $field->formRange);
        
        switch ($field->formRange) {
            case 'User':
                $result = $activeRecord->fetchAll("status = 'A'");
                foreach ($result as $oneUser) {
                    $options[] = array('id'   => $oneUser->id,
                                          'name' => $oneUser->username);
                }
                break;
        }
        
        return $options;
            
    }
}