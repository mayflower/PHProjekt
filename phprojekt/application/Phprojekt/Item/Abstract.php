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
    protected $_error = null;

    /**
     * History object
     *
     * @var Phprojekt_Histoy
     */
    protected $_history = null;

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
        $this->_error     = new Phprojekt_Error();
        $this->_history   = new Phprojekt_History($db);

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
                if (false === empty($value)) {
                    $value = Zend_Locale_Format::getFloat($value, array('precision' => 2));
                } else {
                    $value = 0;
                }
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
                            $this->_error->addError(array(
                                'field'   => $varname,
                                'message' => 'Is a required field'));
                        }
                    }

                    if (($validations['formType'] == 'date') &&
                        (!empty($value))) {
                        if (!Zend_Date::isDate($value, 'yyyy-MM-dd')) {
                            $validated = false;
                            $this->_error->addError(array(
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
     * Get a value of a var.
     * Is the var is a float, return the locale float
     *
     * @param string $varname Name of the var to assign
     *
     * @return mixed
     */
    public function __get($varname)
    {
        $info = $this->info();

        $value = parent::__get($varname);

        if (true == isset($info['metadata'][$varname])) {
            $type = $info['metadata'][$varname]['DATA_TYPE'];
            if ($type == 'float') {
                $value = Zend_Locale_Format::toFloat($value, array('precision' => 2));
            }
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
        return (array) $this->_error->getError();
    }

    /**
     * Extencion of the Abstarct Record for save the history
     *
     * @return void
     */
    public function save()
    {
        if (null !== $this->id) {
            $this->_history->saveFields($this, 'edit');
            parent::save();
        } else {
            parent::save();
            $this->_history->saveFields($this, 'add');
        }
    }

    /**
     * Extencion of the Abstarct Record for save the history
     *
     * @return void
     */
    public function delete()
    {
        $this->_history->saveFields($this, 'delete');
        parent::delete();
    }

    /**
     * Get all the todo for list from the db
     * The return array have all the rows that are ActiveRecords itself
     * and the number of rows without the limits
     *
     * The pagination is stored in the session name "projectID + Module".
     * So each, module have and own session in each project.
     *
     * @return array Array with the rows for render and the total rows number
     */
    public function getListData()
    {
        $listData   = array();

        $projectId   = 0;
        $where       = null;
        $currentPage = 0;

        /* Get the field to filter the current project
           the default is projectId */
        $info = $this->info();
        $parentField = 'projectId';
        if (true === in_array('parent', $info['cols'])) {
            $parentField = 'parent';
        }

        /* Filter the items of the current project */
        $session = new Zend_Session_Namespace();
        if (isset($session->lastProjectId)) {
            $projectId   = $session->lastProjectId;
            $parentField = $this->getAdapter()->quoteIdentifier($parentField);

            $where = sprintf("%s = %d", $parentField, $projectId);
        }

        /* Limit the query for paging */
        $session = new Zend_Session_Namespace($projectId . $this->_name);
        if (true === isset($session->currentPage)) {
            $currentPage = $session->currentPage;
        }

        $config = Zend_Registry::get('config');
        $count  = $config->itemsPerPage;

        $order = 'id';

        foreach ($this->fetchAll($where, $order, $count, $currentPage) as $row) {
            $listData[] = $row;
        }

        $howManyRows = count($this->fetchAll($where));

        return array($listData, $howManyRows);
    }

    /**
     * Get the form fields
     * If the id is defined will make the edit form
     * if not, will make the add form
     *
     * @param integer $id Optional, for edit the row
     *
     * @return array Array with the fields for render
     */
    public function getFormData($id = 0)
    {
        $formData = $this->getFieldsForForm($this->getTableName());

        if ($id > 0) {
            $this->find($id);
            foreach ($formData as $fieldName => $fieldData) {
                $tmpData[$fieldName]          = $fieldData;
                $tmpData[$fieldName]['value'] = $this->$fieldName;
            }
            $formData = $tmpData;
        }

        /* Asign the porject value if exists
           the default field is projectId */
        $info = $this->info();
        $parentField = 'projectId';
        if (true === in_array('parent', $info['cols'])) {
            $parentField = 'parent';
        }
        if (true === in_array($parentField, $info['cols'])) {
            $session = new Zend_Session_Namespace();
            if (isset($session->lastProjectId)) {
                $formData[$parentField]['value'] = $session->lastProjectId;
            }
        }

        return $formData;
    }

    /**
     * Return wich submodules use this module
     *
     * @return array
     */
    public function getSubModules()
    {
        return array();
    }
}