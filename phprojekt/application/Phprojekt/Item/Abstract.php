<?php
/**
 * An item, with database manager support
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @link       http://www.phprojekt.com
 * @author     Gustavao Solt <solt@mayflower.de>
 * @since      File available since Release 1.0
 */

/**
 * An item, with database manager support
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavao Solt <solt@mayflower.de>
 */
abstract class Phprojekt_Item_Abstract extends Phprojekt_ActiveRecord_Abstract implements Phprojekt_Model_Interface
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
     * Full text Search object
     *
     * @var Phprojekt_SearchWords
     */
    protected $_search = null;

    /**
     * Rights class object
     *
     * @var array
     */
    protected $_rights = null;

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
    public function __construct($db = null)
    {
        parent::__construct($db);

        $this->_dbManager = new Phprojekt_DatabaseManager($this, $db);
        $this->_error     = new Phprojekt_Error();
        $this->_history   = new Phprojekt_History($db);
        $this->_search    = new Phprojekt_SearchWords($db);
        $this->_config    = Zend_Registry::get('config');
        $this->_rights    = new Phprojekt_Item_Rights($db);
    }

    /**
     * Returns the database manager instance used by this phprojekt item
     *
     * @return Phprojekt_DatabaseManager
     */
    public function getInformation()
    {
        return $this->_dbManager;
    }

    /**
     * Enter description here...
     *
     * @return Phprojekt_DatabaseManager_Field
     */
    public function current()
    {
        return new Phprojekt_DatabaseManager_Field($this->getInformation(),
        $this->key(),
        parent::current());
        // return parent::current();
    }

    /**
     * Assign a value to a var using some validations from the table data
     *
     * @param string $varname Name of the var to assign
     * @param mixed  $value   Value for assign to the var
     *
     * @throws InvalidArgumentException
     *
     * @return void
     */
    public function __set($varname, $value)
    {
        $info = $this->info();

        if (isset($info['metadata'][$varname])) {

            $type = $info['metadata'][$varname]['DATA_TYPE'];

            switch ($type) {
                case 'int':
                    $value = Inspector::sanitize('integer', $value, $messages, false);
                    break;
                case 'float':
                    $value = Inspector::sanitize('float', $value, $messages, false);
                    if ($value !== false) {
                        $value = Zend_Locale_Format::getFloat($value, array('precision' => 2));
                    } else {
                        $value = 0;
                    }
                    break;
                case 'date':
                    $value = Inspector::sanitize('date', $value, $messages, false);
                    break;
                case 'time':
                    $value = Inspector::sanitize('time', $value, $messages, false);

                    // moving the value to UTC
                    $timeZomeComplement = (int)$this->_config->timeZone * -1;
                    $u = strtotime($value);

                    $value = mktime(date("H",$u) + $timeZomeComplement, date("i",$u), date("s",$u), date("m"), date("d"), date("Y"));
                    $value = date("H:i:m",$value);

                    // running again the sanitizer to normalize the format
                    $value = Inspector::sanitize('timestamp', $value, $messages, false);

                    break;
                case 'datetime':
                case 'timestamp':
                    $value = Inspector::sanitize('timestamp', $value, $messages, false);

                    // moving the value to UTC
                    $timeZomeComplement = (int)$this->_config->timeZone * -1;
                    $u = strtotime($value);

                    $value = mktime(date("H",$u) + $timeZomeComplement, date("i",$u), date("s",$u), date("m",$u), date("d",$u), date("Y",$u));

                    // running again the sanitizer to normalize the format
                    $value = Inspector::sanitize('timestamp', $value, $messages, false);

                    break;
                default:
                    $value = Inspector::sanitize('string', $value, $messages, false);
                    break;
            }
        } else {
            $value = Inspector::sanitize('string', $value, $messages, false);
        }

        if ($value === false) {
            throw new InvalidArgumentException('Type doesnot match it\'s definition: ' . $varname . ' expected to be ' . $type .'.');
        }

        parent::__set($varname, $value);
    }

    /**
     * Return if the values are valid or not
     *
     * @return boolean
     */
    public function recordValidate()
    {
        $validated = true;
        $data      = $this->_data;
        $fields    = $this->_dbManager->getFieldDefinition(Phprojekt_ModelInformation_Default::ORDERING_FORM);

        foreach ($data as $varname => $value) {
            if (isset($this->$varname)) {
                /* Validate with the database_manager stuff */
                foreach ($fields as $field) {
                    if ($field['key'] == $varname) {
                        $validations = $field;

                        if (true === $validations['required']) {
                            $error = $this->validateIsRequired($value);
                            if (null != $error) {
                                $validated = false;
                                $this->_error->addError(array(
                                'field'   => $varname,
                                'message' => $error));
                                break;
                            }
                        }

                        $error = $this->validateValue($varname, $value);
                        if (false === $error) {
                            $validated = false;
                            $this->_error->addError(array(
                            'field'   => $varname,
                            'message' => "Invalid Format"));
                        }
                        break;
                    }
                }

                /* Validate an special fieldName */
                $validater  = 'validate' . ucfirst($varname);
                if ($validater != 'validateIsRequired') {
                    if (in_array($validater, get_class_methods($this))) {
                        $error = call_user_method($validater, $this, $value);
                        if (null != $error) {
                            $validated = false;
                            $this->_error->addError(array(
                            'field'   => $varname,
                            'message' => $error));
                        }
                    }
                }
            }
        }
        return $validated;
    }

    /**
     * Validate a value use the database type of the field
     *
     * @param string $varname Name of the field
     * @param mix    $value   Value to validate
     *
     * @return string Error message or null if is valid
     */
    public function validateValue($varname, $value)
    {
        $info  = $this->info();
        $valid = true;
        if (isset($info['metadata'][$varname]) && !empty($value)) {

            $type = $info['metadata'][$varname]['DATA_TYPE'];

            switch ($type) {
                case 'int':
                    $valid = Inspector::validate('integer', $value, $messages, false);
                    break;
                case 'float':
                    $valid = Inspector::validate('float', $value, $messages, false);
                    break;
                case 'date':
                    $valid = Inspector::validate('date', $value, $messages, false);
                    break;
                case 'time':
                    $valid = Inspector::validate('time', $value, $messages, false);
                    break;
                case 'timestamp':
                    $valid = Inspector::validate('timestamp', $value, $messages, false);
                    break;
                default:
                    $valid = Inspector::validate('string', $value, $messages, false);
                    break;
            }
        }

        return $valid !== false;
    }

    /**
     * Validate required fields
     * return the msg error if exists
     *
     * @param mix $value The value to check
     *
     * @return string Error string or null
     */
    public function validateIsRequired($value)
    {
        $error = null;
        if (empty($value)) {
            $error = 'Is a required field';
        }
        return $error;
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
            switch ($type) {
                case 'float':
                    $value = Zend_Locale_Format::toFloat($value, array('precision' => 2));
                    break;
                case 'time':

                    $timeZone = (int)$this->_config->timeZone;
                    $u = strtotime($value);

                    $value = mktime(date("H",$u) + $timeZone, date("i",$u), date("s",$u), date("m"), date("d"), date("Y"));
                    $value = date("H:i:m",$value);

                    break;
                case 'datetime':
                case 'timestamp':

                    $timeZone = (int)$this->_config->timeZone * -1;
                    $u = strtotime($value);

                    $value = mktime(date("H",$u) + $timeZone, date("i",$u), date("s",$u), date("m",$u), date("d",$u), date("Y",$u));
                    break;
            }
        }

        if (null != $value && is_string($value)) {
            $value = stripslashes($value);
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
        $result = true;
        if ($this->id > 0) {
            $this->_saveRights();
            $this->_history->saveFields($this, 'edit');
            $result = parent::save();
        } else {
            $result = parent::save();
            $this->_saveRights();
            $this->_history->saveFields($this, 'add');
        }

        $this->_search->indexObjectItem($this);

        return $result;
    }

    /**
     * Extencion of the Abstarct Record for save the history
     *
     * @return void
     */
    public function delete()
    {
        $this->_history->saveFields($this, 'delete');
        $this->_search->deleteObjectItem($this);
        parent::delete();
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

    /**
     * Return the fields that can be filtered
     *
     * This function must be here for be overwrited by the default module
     *
     * @return array
     */
    public function getFieldsForFilter()
    {
        return $this->getInformation()->getInfo(Phprojekt_ModelInformation_Default::ORDERING_LIST,
        Phprojekt_DatabaseManager::COLUMN_NAME);
    }


    /**
     * Rewrites parent fetchAll, so that only records with read access are shown
     *
     * @param string|array $where  Where clause
     * @param string|array $order  Order by
     * @param string|array $count  Limit query
     * @param string|array $offset Query offset
     *
     * @return Zend_Db_Table_Rowset
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null, $join = null)
    {
        // only fetch records with read access
        $authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');

        $join .= sprintf(' INNER JOIN ItemRights ON (ItemRights.itemId = %s
                         AND ItemRights.module = "%s" AND ItemRights.userId = %d) ',
        		         $this->getAdapter()->quoteIdentifier($this->getTableName().'.id'),
        	             $this->getTableName(),
                         $authNamespace->userId);

        // Set where
        if (null !== $where) {
            $where .= ' AND ';
        }
        $where .= ' (' . sprintf('(%s.ownerId = %d OR %s.ownerId is NULL)', $this->getTableName(), $authNamespace->userId, $this->getTableName());
        $where .= ' OR (ItemRights.adminAccess = 1 OR ItemRights.writeAccess = 1 OR ItemRights.readAccess = 1))';

        return parent::fetchAll($where, $order, $count, $offset, $join);
    }

    /**
     * Returns the right the user has on a Phprojekt item
     *
     * @todo Make sure that this doesnot need any database query
     *
     * @return integer $right bitmap
     *                        0 => no permission
     *                        1 => access
     *                        2 => read
     *                        4 => write
     *                        8 => administration
     */
    public function getRights($userId)
    {
        $itemRight = '';
        // TODO: class name and table name can differ
        $class     = $this->getTableName();

        if ($this->id > 0) {
            $groups = Phprojekt_Loader::getModel('Groups', 'Groups', $userId);
            if ($this->read && $groups->isUserInGroup($this->read)) {
                $itemRight = 'read';
            }
            if ($this->write && $groups->isUserInGroup($this->write)) {
                $itemRight = 'write';
            }
            if ($this->admin && $groups->isUserInGroup($this->admin)) {
                $itemRight = 'admin';
            }
            if ($this->ownerId == $groups->getUserId()) {
                $itemRight = 'admin';
            }

            $relationField = $this->projectId;

        } else {
            $itemRight     = 'write';
            $session       = new Zend_Session_Namespace();
            $relationField = (int) $session->currentProjectId;
        }

        $roleRights      = new Phprojekt_RoleRights($relationField, $class, $this->id);
        $roleRightRead   = $roleRights->hasRight('read');
        $roleRightWrite  = $roleRights->hasRight('write');
        
        switch ($itemRight) {
            case'read':
            if ($roleRightRead || $roleRightWrite) {
                $right = 2; // 'read'
            }
            break;
            case'write':
            if ($roleRightRead) {
                $right = 2; // 'read'
            }
            if ($roleRightWrite) {
                $right = 4; // 'write'
            }
            break;
            case'admin':
            if ($roleRightRead) {
                $right = 2; // 'read'
            }
            if ($roleRightWrite) {
                $right = 8; // 'admin'
            }
            break;
            default:
                $right = 0; // 'nothing'
                break;
        }
                
        return (int)$right;
    }

    /**
     * Save the rigths for the current item
     * The users are a POST array with userIds
     * There is one save for each user.
     *
     * @return void
     */
    private function _saveRights()
    {
        $writeUsers = array(1);
        $readUsers  = array(1);
        $adminUsers = array(1);

        $this->_rights->_save($this->getTableName(), $this->id, $adminUsers, $writeUsers, $readUsers);
    }
}