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
    public $hasManyAndBelongsToMany = array('users' =>  array('classname' => 'User_Models_User',
                                                              'module'    => 'User',
                                                              'model'     => 'User'));

    /**
     * user
     * @var integer $_user
     */
    private $_userId = null;

    /**
     * Constructor for Groups
     */
    public function __construct()
    {
        parent::__construct();

        $authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');
        $this->_userId = $authNamespace->userId;
    }

    /**
     * Returns the user id thats checked
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->_userId;
    }

    /**
     * Checks whether user is in Group
     *
     * @param integer $group Id of group
     *
     * @return boolean
     */
    public function isUserInGroup($group)
    {
        // Keep the user-group relation in the session for optimize the query
        $groupNamespace = new Zend_Session_Namespace('UserInGroup_'.$this->_userId.'_'.$group);
        if (isset($groupNamespace->isInGroup)) {
            return $groupNamespace->isInGroup;
        }

        $currentGroup = $this->find($group);

        $where = $currentGroup->getAdapter()->quoteInto('userId = ?', $this->_userId);
        $users = $currentGroup->_hasManyAndBelongsToMany('users');

        /* @todo don't use internal functions */
        $tmp = current((array)$users->_fetchHasManyAndBelongsToMany($where));
        if ($tmp['userId'] == $this->getUserId()) {
             $groupNamespace->isInGroup = true;
        } else {
             $groupNamespace->isInGroup = false;
        }
        return $groupNamespace->isInGroup;
    }

    /**
     * Returns all groups user belongs to
     *
     * @return array $group Id of group;
     */
    public function getUserGroups()
    {
        // Keep the user-group relation in the session for optimize the query
        $groupNamespace = new Zend_Session_Namespace('UserGroups_'.$this->_userId);
        if (isset($groupNamespace->groups)) {
            $groups = $groupNamespace->groups;
        } else {
            $groups = array();
            $where  = $this->getAdapter()->quoteInto('userId = ?', $this->_userId);
            $users  = $this->_hasManyAndBelongsToMany('users');
            /* @todo don't use internal functions */
            $tmp    = $users->_fetchHasManyAndBelongsToMany($where);
            foreach ($tmp as $row) {
                $groups[] = $row['groupsId'];
            }
            $groupNamespace->groups = $groups;
        }
        return $groups;
    }
}