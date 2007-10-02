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
class Phprojekt_DatabaseManager extends Phprojekt_ActiveRecord_Abstract
{
    /**
     * Cache
     *
     * @var array
     */
    protected $_dbFields = array();

    protected $_model;

    const FORM_ORDER = 'formPosition';
    const LIST_ORDER = 'listPosition';

    const COLUMN_NAME = 'tableField';
    const COLUMN_TITLE = 'formLabel';

    /**
     * Initialize a new Database Manager and configurate it with a model
     *
     * @param Phprojekt_Item_Abstract $model
     */
    public function __construct(Phprojekt_Item_Abstract $model, $db = null)
    {
        parent::__construct($db);

        $this->_model = $model;
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
     * @param string $table The table name of the module
     * @param string $order Sort string
     *
     * @return array        Array with the data of all the fields
     */
    protected function _getFields($order)
    {
        $table = $this->_model->getTableName();

        if (empty($this->_dbFields[$order])) {
            $where = $this->getAdapter()->quoteInto('tableName = ?', $table);

            $this->_dbFields[$order] = $this->fetchAll($where, $order);
        }

        return $this->_dbFields[$order];
    }

    /**
     * Get the field data sorted with the lisPosition value
     *
     * The fields with listPosition below 0 are not return.
     *
     * @param string $table The table name of the module
     *
     * @return array        Array with the data of the list fields
     */
    public function getFieldsForList()
    {
        $return = array();
        return $this->_getFields(self::LIST_ORDER);
    }

    /**
     * Get the field data sorted with the formPosition value
     *
     * The fields with formPosition below 0 are not return.
     *
     * @param string $table The table name of the module
     *
     * @return array        Array with the data of the form field
     */
    public function getFieldsForForm()
    {
        $return = array();
        return $this->_getFields(self::FORM_ORDER);
    }

    /**
     * Create a primitive mapping to an array. This is not pretty nice, but
     * for this version a reasonable solution
     *
     * @todo Maybe we have to refactor this. Doesnt look pretty for me. (dsp)
     *
     * @param Phprojekt_Item_Abstract $model
     * @param string                  $order
     * @param string                  $column
     *
     * @return array
     */
    public function getInfo($order, $column)
    {
        switch ($order) {
            case self::LIST_ORDER:
                $fields = $this->getFieldsForList();
                break;
            case self::FORM_ORDER:
                $fields = $this->getFieldsForForm();
                break;
            default:
                throw new Exception('No valid $order parameter give');
        }

        $result = array();
        foreach ($fields as $field) {
            if (array_key_exists($column, $field)) {
                $result[] = $field[$column];
            }
        }

        return $result;
    }
}