<?php
/**
 * A item, with database manager support
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @link       http://www.phprojekt.com
 * @author     David Soria Parra <soria_parra@mayflower.de>
 * @since      File available since Release 1.0
 */

/**
 * A item, with database manager support
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
abstract class Phprojekt_Item_Abstract extends Phprojekt_ActiveRecord_Abstract
{
    /**
     * Represents the database_manager class
     *
     * @var Phprojekt_ActiveRecord_Abstract
     */
    protected $_dbManager = null;

    /**
     * Error object
     *
     * @var Phprojekt_Error
     */
    protected $_oError = null;

    /**
     * History object
     *
     * @var Phprojekt_Histoy
     */
    protected $_oHistory = null;

    /**
     * Config for inicializes children objects
     *
     * @var array
     */
    protected $_config = null;

    /**
     * History data of the fields
     *
     * @var array
     */
    public $history = array();

    /**
     * Initialize new object
     *
     * @param array $db Configuration for Zend_Db_Table
     */
    public function __construct($db)
    {
        parent::__construct($db);

        $this->_dbManager = new Phprojekt_DatabaseManager($db);
        $this->_oError    = new Phprojekt_Error();
        $this->_oHistory  = new Phprojekt_History($db);

        $config           = Zend_Registry::get('config');
        $this->_config    = $config;
    }

    /**
     * Get the field for list view from the databae_manager
     *
     * @return array Array with the data of the fields for make the list
     */
    public function getFieldsForList()
    {
        return $this->_dbManager->getFieldsForList($this->_name);
    }

    /**
     * Get the field for the form view from the databae_manager
     *
     * @return array Array with the data of the fields for make the form
     */
    public function getFieldsForForm()
    {
        return $this->_dbManager->getFieldsForForm($this->_name);
    }

    /**
     * Assign a value to a var using some validations from the table data
     *
     * @param string $varname Name of the var to assign
     * @param mixed  $value   Value for assign to the var
     *
     * @return void
     */
    public function __set($varname, $value)
    {
        $info = $this->info();

        if (true == isset($info['metadata'][$varname])) {

            $type = $info['metadata'][$varname]['DATA_TYPE'];

            if ($type == 'int') {
                $value = (int) $value;
            }

            if ($type == 'float') {
                $value = Zend_Locale_Format::getFloat($value, array('precision' => 2));
            }
        }
        parent::__set($varname, $value);
    }

    /**
     * Return if the values are valid or not
     *
     * @return boolean If are valid or not
     */
    public function recordValidate()
    {
        $validated = true;
        $data = $this->_data;
        foreach ($data as $varname => $value) {
            if ($this->keyExists($varname)) {
                /* Validate with the database_manager stuff */
                $fields = $this->_dbManager->getFieldsForForm($this->_name);
                if (isset($fields[$varname])) {
                    $validations = $fields[$varname];

                    if ($validations['isRequired']) {
                        if (empty($value)) {
                            $validated = false;
                            $this->_oError->addError(array(
                                'field'   => $varname,
                                'message' => 'Is a required field'));
                        }
                    }

                    if (($validations['formType'] == 'date') &&
                        (!empty($value))) {
                        if (!Zend_Date::isDate($value, 'yyyy-MM-dd')) {
                            $validated = false;
                            $this->_oError->addError(array(
                                'field'   => $varname,
                                'message' => 'Invalid format for date'));
                        }
                    }
                }
            }
        }
        return $validated;
    }

    /**
     * Get a value of a var using some validations
     *
     * @param string $varname Name of the var to assign
     *
     * @return mixed
     */
    public function __get($varname)
    {
        /* First look if exists a getField function */
        $get = 'getField' . ucfirst($varname);
        if (in_array($get, get_class_methods(get_class($this)))) {
            $value = call_user_method($get, $this);
        } else {
            $value = parent::__get($varname);
        }
        return $value;
    }

    /**
     * Return the error data
     *
     * @return array
     */
    public function getError()
    {
        return (array) $this->_oError->getError();
    }

    /**
     * Extencion of the Abstarct Record for save the history
     *
     * @throws Exception of there is an error
     *
     * @return void
     */
    public function save()
    {
        if (null !== $this->id) {
            $this->_oHistory->saveFields($this, 'edit');
            parent::save();
        } else {
            parent::save();
            $this->_oHistory->saveFields($this, 'add');
        }
    }

    /**
     * Extencion of the Abstarct Record for save the history
     *
     * @return void
     */
    public function delete()
    {
        $this->_oHistory->saveFields($this, 'delete');
        parent::delete();
    }
}