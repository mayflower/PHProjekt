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
     * Validate object
     *
     * @var Phprojekt_Model_Validate
     */
    protected $_validate = null;

    /**
     * History object
     *
     * @var Phprojekt_History
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
     * @var Phprojekt_Search_Words
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
     * Field for display in the search results
     *
     * @var string
     */
    public $searchFirstDisplayField = 'title';

    /**
     * Field for display in the search results
     *
     * @var string
     */
    public $searchSecondDisplayField = 'notes';
    
    /**
     * Field for user timezone to be used on time conversion
     *
     * @var string
     */
    private $_timezone = null;

    /**
     * Initialize new object
     *
     * @param array $db Configuration for Zend_Db_Table
     */
    public function __construct($db = null)
    {
        parent::__construct($db);

        $this->_dbManager = new Phprojekt_DatabaseManager($this, $db);
        $this->_validate  = new Phprojekt_Model_Validate();
        $this->_history   = new Phprojekt_History($db);
        $this->_search    = new Phprojekt_Search_Default();
        $this->_config    = Zend_Registry::get('config');
        $this->_rights    = new Phprojekt_Item_Rights();
        $this->_timezone  = (int)Phprojekt_User_User::getSetting("timeZone", $this->_config->timeZone);
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
        $messages = null;
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
                    $timeZomeComplement = (int)$this->_timezone * -1;
                    $u = strtotime($value);

                    $value = mktime(date("H", $u) + $timeZomeComplement, date("i", $u),
                             date("s", $u), date("m"), date("d"), date("Y"));

                    $value = date("H:i:s", $value);

                    // running again the sanitizer to normalize the format
                    $value = Inspector::sanitize('time', $value, $messages, false);


                    break;
                case 'datetime':
                case 'timestamp':
                    $value = Inspector::sanitize('timestamp', $value, $messages, false);

                    // moving the value to UTC
                    $timeZomeComplement = (int)$this->_timezone * -1;
                    $u = strtotime($value);

                    $value = mktime(date("H", $u) + $timeZomeComplement, date("i", $u),
                             date("s", $u), date("m", $u), date("d", $u), date("Y", $u));

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
            throw new InvalidArgumentException('Type doesnot match it\'s definition: ' . $varname .
                                               ' expected to be ' . $type .'.');
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
        $data      = $this->_data;
        $fields    = $this->_dbManager->getFieldDefinition(Phprojekt_ModelInformation_Default::ORDERING_FORM);

        return $this->_validate->recordValidate($this, $data, $fields);
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

                    $timeZone = (int)$this->_timezone;
                    $u = strtotime($value);

                    $value = mktime(date("H", $u) + $timeZone, date("i", $u), date("s", $u),
                             date("m"), date("d"), date("Y"));

                    $value = date("H:i:s", $value);

                    break;
                case 'datetime':
                case 'timestamp':

                    $timeZone = (int)$this->_timezone * -1;
                    $u = strtotime($value);

                    $value = mktime(date("H", $u) + $timeZone, date("i", $u), date("s", $u),
                             date("m", $u), date("d", $u), date("Y", $u));
                    break;
            }
        }

        if (null !== $value && is_string($value)) {
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
        return (array) $this->_validate->error->getError();
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
            $this->_history->saveFields($this, 'edit');
            $result = parent::save();
        } else {
            $result = parent::save();
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
        $moduleId = (!empty($this->moduleId))? $this->moduleId: 1;

        $this->_history->saveFields($this, 'delete');
        $this->_search->deleteObjectItem($this);
        $this->_rights->_save($moduleId, $this->id, array());
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
     * @param string       $select The comma-separated columns of the joined columns
     * @param string       $join   The join statements
     *
     * @return Zend_Db_Table_Rowset
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null, $select = null, $join = null)
    {
        // only fetch records with read access
        $authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');

        $join .= sprintf(' INNER JOIN ItemRights ON (ItemRights.itemId = %s
                         AND ItemRights.moduleId = %d AND ItemRights.userId = %d) ',
                         $this->getAdapter()->quoteIdentifier($this->getTableName().'.id'),
                         Phprojekt_Module::getId($this->getTableName()),
                         $authNamespace->userId);

        // Set where
        if (null !== $where) {
            $where .= ' AND ';
        }
        $where .= ' (' . sprintf('(%s.ownerId = %d OR %s.ownerId is NULL)', $this->getTableName(),
                  $authNamespace->userId, $this->getTableName());

        $where .= ' OR (ItemRights.access > 0)) ';

        return parent::fetchAll($where, $order, $count, $offset, $select, $join);
    }

    /**
     * Returns the right for each user has on a Phprojekt item
     *
     * @return array
     */
    public function getRights()
    {
        $rights   = $this->_rights->getRights(Phprojekt_Module::getId($this->getTableName()), $this->id);
        $moduleId = Phprojekt_Module::getId($this->getTableName());

        $roleRights      = new Phprojekt_RoleRights($this->projectId, $moduleId, $this->id);
        $roleRightRead   = $roleRights->hasRight('read');
        $roleRightWrite  = $roleRights->hasRight('write');
        $roleRightCreate = $roleRights->hasRight('create');
        $roleRightAdmin  = $roleRights->hasRight('admin');

        // Map roles with item rigths and make one array
        foreach ($rights as $userId => $access) {
            foreach ($access as $name => $value) {
                switch ($name) {
                    case 'admin':
                        $rights[$userId]['admin'] = $roleRightAdmin && $value;
                        break;
                    case 'donwload':
                        $rights[$userId]['donwload'] = ($roleRightRead || $roleRightWrite || $roleRightAdmin) && $value;
                        break;
                    case 'delete':
                        $rights[$userId]['delete'] = ($roleRightWrite || $roleRightAdmin) && $value;
                        break;
                    case 'copy':
                        $rights[$userId]['copy'] = ($roleRightWrite || $roleRightCreate || $roleRightAdmin) && $value;
                        break;
                    case 'create':
                        $rights[$userId]['create'] = ($roleRightWrite || $roleRightCreate || $roleRightAdmin) && $value;
                        break;
                    case 'access':
                        $rights[$userId]['access'] = ($roleRightRead || $roleRightWrite ||
                                                      $roleRightCreate || $roleRightAdmin) && $value;
                        break;
                    case 'write':
                        $rights[$userId]['write'] = ($roleRightWrite || $roleRightCreate || $roleRightAdmin) && $value;
                        break;
                    case 'read':
                        $rights[$userId]['read'] = ($roleRightRead || $roleRightWrite || $roleRightAdmin) && $value;
                        break;
                    case 'none':
                        $rights[$userId]['none'] = $value;
                        break;
                }
            }
        }

        return $rights;
    }

    /**
     * Save the rigths for the current item
     * The users are a POST array with userIds
     *
     * @param array $rights - Array of usersId with the bitmask access
     *
     * @return void
     */
    public function saveRights($rights)
    {
        $this->_rights->_save(Phprojekt_Module::getId($this->getTableName()), $this->id, $rights);
    }
    
}