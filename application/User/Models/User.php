<?php
/**
 * User class for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://www.phprojekt.com/license PHProjekt6 License
 * @version    CVS: $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Phprojekt_User for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://www.phprojekt.com/license PHProjekt6 License
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */
class User_Models_User extends Phprojekt_ActiveRecord_Abstract implements Phprojekt_Model_Interface
{
    /**
     * Has many declrations
     *
     * @var array
     */
    public $hasMany = array('settings' => array('module' => 'User',
    'model'  => 'UserModuleSetting'));

    /**
     * Has many and belongs to many declrations
     *
     * @var array
     */
    public $hasManyAndBelongsToMany = array('groups' => array('classname' => 'Groups_Models_Groups',
    'module'    => 'Groups',
    'model'     => 'Groups'));

    /**
     * The standard information manager with hardcoded
     * field definitions
     *
     * @var Phprojekt_ModelInformation_Interface
     */
    protected $_informationManager;

    /**
     * Validate object
     *
     * @var Phprojekt_Model_Validate
     */
    protected $_validate = null;

    /**
     * Initialize new user
     * If is seted the user id in the session,
     * the class will get all the values of these user
     *
     * @param array $db Configuration for Zend_Db_Table
     *
     * @return void
     */
    public function __construct($db = null)
    {
        if (null === $db) {
            $db = Zend_Registry::get('db');
        }
        parent::__construct($db);

        //$authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');
        //if (isset($authNamespace->userId)) {
        //    if ($authNamespace->userId > 0) {
        //        $this->find($authNamespace->userId);
        //    }
        //}
        $this->_validate           = new Phprojekt_Model_Validate();
        $this->_informationManager = new User_Models_Information();
    }

    /**
     * Checks if user is active
     *
     * @return boolean id user is active or not
     */
    public function isActive()
    {
        if (strtoupper($this->status) != 'A') {
            return false;
        }
        return true;
    }

    /**
     * Searchs an user Id based on the username
     *
     * @param string $username username necessary to find the userId
     *
     * @return integer with the user id value. If the user is not found then
     * function will return false
     */
    public function findIdByUsername($username)
    {
        $db = Zend_Registry::get('db');
        /* @var $db Zend_Db_Adapter_Abstract */

        try {
            $users  = $this->fetchAll($db->quoteInto("username = ?", $username), null, 1);

            if (!isset($users[0]) || !isset($users[0]->id)) {
                return false;
            }

            return $users[0]->id;
        }
        catch (Phprojekt_ActiveRecord_Exception $are) {
            $this->_log->warn($are->getMessage());
        }
        catch (Exception $e) {
            $this->_log->warn($e->getMessage());
        }

        return false;
    }

    /**
     * Found and user using the id and return this class for the new user
     * If the id is wrong, return the actual user
     *
     * @param int $id The user id
     *
     * @return User_Models_User
     */
    public function findUserById($id)
    {
        if ($id > 0) {
            $clone = clone($this);
            $clone->find($id);
            return $clone;
        } else {
            return $this;
        }
    }

    /**
     * Extencion of the ActiveRecord save adding default permissions
     *
     * @return boolean true if saved correctly
     */
    public function save()
    {
        if (parent::save()) {
            // adding default values
            $rights = new Phprojekt_Item_Rights();

            $rights->saveDefaultRights($this->id);

            return true;
        }

        return false;
    }

    /**
     * Extencion of the ACtive Record deletion adding deleteion of user tags
     *
     * @return void
     */
    public function delete()
    {
        $tags = Phprojekt_Tags_Default::getInstance();
        $tags->deleteTagsByUser($this->id);
        unset($tags);
        parent::delete();
    }

    /**
     * Get the information manager
     *
     * @see Phprojekt_Model_Interface::getInformation()
     *
     * @return Phprojekt_ModelInformation_Interface
     */
    public function getInformation()
    {
        return $this->_informationManager;
    }

    /**
     * Get the rigths for other users
     *
     * @return array
     */
    public function getRights()
    {
        return array();
    }

    /**
     * Save the rigths
     *
     * @return void
     */
    public function saveRights()
    {
    }

    /**
     * Validate the current record
     *
     * @return boolean
     */
    public function recordValidate()
    {
        $data      = $this->_data;
        $fields    = $this->_informationManager->getFieldDefinition(Phprojekt_ModelInformation_Default::ORDERING_FORM);

        $this->_validate = new Phprojekt_Model_Validate();
        return $this->_validate->recordValidate($this, $data, $fields);
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
}