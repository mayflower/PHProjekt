<?php
/**
 * Group class for PHProjekt 6.0
 *
 * @copyright 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license   http://www.phprojekt.com/license PHProjekt6 License
 * @version   CVS: $Id: User.php,v 1.6 2007/08/30 18:02:36 gustavo Exp $
 * @author    Eduardo Polidor <polidor@mayflower.de>
 * @package   PHProjekt
 * @subpackage Core
 * @link      http://www.phprojekt.com
 * @since     File available since Release 1.0
 */

/**
 * Phprojekt_Group for PHProjekt 6.0
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
class Groups_Models_Groups extends Phprojekt_ActiveRecord_Abstract
{
    /**
     * Has many and belongs to many declrations
     *
     * @var array
     */
    public $hasManyAndBelongsToMany = array('users' =>  array('classname' => 'Users_Models_User',
                                                              'module'    => 'Users',
                                                              'model'     => 'User'));

    /**
     * user
     * @var int $_user
     */
    private $_user = null;

    /**
     * Constructor for Groups
     *
     * @param Zend_Db $db     database
     * @param int     $userId Id of user
     */
    public function __construct($db = null, $userId=null)
    {
        parent::__construct($db);
        $this->_setUser($userId);
    }

    /**
     * setter for user
     *
     * @param int $user Id of user
     */
    private function _setUser($user){
        if ($user != 0) {
            $this->_user= $user;
        } else {
            $authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');
            $this->_user = $authNamespace->userId;
        }
    }

    /**
     * getter for current user
     *
     * @return int $_user Id of user
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * checks whether user is in Group
     *
     * @param int $group Id of group
     *
     * @return boolean
     */
    public function isUserInGroup($group)
    {
        // Keep the user-group relation in the session for optimize the query
        $groupNamespace = new Zend_Session_Namespace('UserInGroup_'.$this->getUser().'_'.$group);
        if (isset($groupNamespace->isInGroup)) {
            $isInGroup = $groupNamespace->isInGroup;
        } else {
            $currentGroup = $this->find($group);
            $where = $currentGroup->getAdapter()->quoteInto('userId = ?', $this->getUser());
            $users = $currentGroup->_hasManyAndBelongsToMany('users');
            $tmp = current((array)$users->_fetchHasManyAndBelongsToMany($where));
            if ($tmp['userId'] == $this->getUser()) {
                $isInGroup = $groupNamespace->isInGroup = true;
            } else {
                $isInGroup = $groupNamespace->isInGroup = false;
            }
            return $groupNamespace->isInGroup;
        }
        return $isInGroup;
    }

    /**
     * returns all groups user belongs to
     *
     * @return array $group Id of group;
     */
    public function getUserGroups()
    {
        // Keep the user-group relation in the session for optimize the query
        $groupNamespace = new Zend_Session_Namespace('UserGroups_'.$this->getUser());
        if (isset($groupNamespace->groups)) {
            $groups = $groupNamespace->groups;
        } else {
            $groups = array();
            $where = $this->getAdapter()->quoteInto('userId = ?', $this->getUser());
            $users = $this->_hasManyAndBelongsToMany('users');
            $tmp   = $users->_fetchHasManyAndBelongsToMany($where);
            foreach ($tmp as $row) {
                $groups[] = $row['groupsId'];
            }
            $groupNamespace->groups = $groups;
        }
        return $groups;
    }
}