<?php
/**
 * User class for PHProjekt 6.0
 *
 * @copyright 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license   http://www.phprojekt.com/license PHProjekt6 License
 * @version   CVS: $Id$
 * @author    Eduardo Polidor <polidor@mayflower.de>
 * @package   PHProjekt
 * @subpackage Core
 * @link      http://www.phprojekt.com
 * @since     File available since Release 1.0
 */

/**
 * Phprojekt_User for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license   http://www.phprojekt.com/license PHProjekt6 License
 * @author     Eduardo Polidor <polidor@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */
class Users_Models_User extends Phprojekt_ActiveRecord_Abstract
{
    /**
     * Has many declrations
     *
     * @var array
     */
    public $hasMany = array('settings' => array('module' => 'Users',
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
     * Initialize new user
     * If is seted the user id in the session,
     * the class will get all the values of these user
     *
     * @param array $db Configuration for Zend_Db_Table
     *
     * @return void
     */
    public function __construct($db)
    {
        parent::__construct($db);

        $authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');
        if (isset($authNamespace->userId)) {
            if ($authNamespace->userId > 0) {
                $this->find($authNamespace->userId);
            }
        }
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
     * @return Users_Models_User
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
}