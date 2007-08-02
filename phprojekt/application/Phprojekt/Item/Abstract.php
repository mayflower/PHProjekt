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
     * Initialize new object
     *
     * @param array $config Configuration for Zend_Db_Table
     */
    public function __construct($config)
    {
        parent::__construct($config);

        $this->_dbManager = new Phprojekt_DatabaseManager($config);
        $this->_oError    = new Phprojekt_Error();
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
     * Assign a value to a var using some validations
     *
     * @param string $varname Name of the var to assign
     * @param mixed  $value   Value for assign to the var
     *
     * @return void
     */
    public function __set($varname, $value)
    {
        /* First look if exists a validateField function */
        $validatter = 'validate' . ucfirst($varname);
        if (in_array($validatter, get_class_methods(get_class($this)))) {
            $value = call_user_method($validatter, $this, $value);
        } else {
            /* Validate with the database_manager stuff */
            $fields = $this->_dbManager->getFieldsForForm($this->_name);
            if (isset($fields[$varname])) {
                $validations = $fields[$varname];

                if ($validations['isInteger']) {
                    $value = intval($value);
                }

                if ($validations['isRequired']) {
                    if (empty($value)) {
                        $this->_oError->addError(array(
                            'field'   => $varname,
                            'message' => 'Is a required field')
                            );
                    }
                }
            }
        }
        parent::__set($varname, $value);
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
        $var = parent::__get($varname);
        return $var;
    }

    /**
     * Return the error data
     *
     * @return array
     */
    public function getError()
    {
        return $this->_oError->getError();
    }
}