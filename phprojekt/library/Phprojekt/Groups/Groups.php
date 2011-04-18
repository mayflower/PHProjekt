<?php
/**
 * Group model class.
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Groups
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */

/**
 * Group model class.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Groups
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */
class Phprojekt_Groups_Groups extends Phprojekt_Item_Abstract
{
    /**
     * Configuration to use or not the history class.
     *
     * @var boolean
     */
    public $useHistory = false;

    /**
     * Configuration to use or not the search class.
     *
     * @var boolean
     */
    public $useSearch = false;

    /**
     * Configuration to use or not the right class.
     *
     * @var boolean
     */
    public $useRights = false;

    /**
     * Has many and belongs to many declrations.
     *
     * @var array
     */
    public $hasManyAndBelongsToMany = array('users' =>  array('classname' => 'Phprojekt_User_User',
                                                              'module'    => 'User',
                                                              'model'     => 'User'));

    /**
     * Current user ID
     *
     * @var integer
     */
    private $_userId = null;

    /**
     * Constructor initializes additional Infomanager.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->_userId = Phprojekt_Auth::getUserId();
    }

    /**
     * Returns the Model information manager.
     *
     * @return Phprojekt_ModelInformation_Interface An instance of a Phprojekt_ModelInformation_Interface.
     */
    public function getInformation()
    {
        if (null == $this->_informationManager) {
            $this->_informationManager = Phprojekt_Loader::getLibraryClass('Phprojekt_Groups_Information');
        }

        return $this->_informationManager;
    }

    /**
     * Returns the user ID thats checked.
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->_userId;
    }

    /**
     * Checks whether user is in Group.
     *
     * @param integer $group ID of group.
     *
     * @return boolean True if the use is in the group.
     */
    public function isUserInGroup($group)
    {
        // Keep the user-group relation in the session for optimize the query
        $sessionName    = 'Phprojekt_Groups_Groups-isUserInGroup-' . $this->_userId . '-' . $group;
        $groupNamespace = new Zend_Session_Namespace($sessionName);
        if (!isset($groupNamespace->isInGroup)) {
            $currentGroup = $this->find($group);
            if ($currentGroup instanceof Phprojekt_Groups_Groups
                && count($currentGroup->users->find($this->getUserId())) > 0) {
                $groupNamespace->isInGroup = true;
            } else {
                $groupNamespace->isInGroup = false;
            }
        }
        return $groupNamespace->isInGroup;
    }

    /**
     * Returns all groups user belongs to.
     *
     * @return array Array with group IDs;
     */
    public function getUserGroups()
    {
        // Keep the user-group relation in the session for optimize the query
        $sessionName    = 'Phprojekt_Groups_Groups-getUserGroups-' . $this->_userId;
        $groupNamespace = new Zend_Session_Namespace($sessionName);
        if (!isset($groupNamespace->groups)) {
            $groups = array();
            $user = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
            $user->find($this->_userId);
            $tmp = $user->groups->fetchAll();
            foreach ($tmp as $row) {
                $groups[] = $row->groupsId;
            }
            $groupNamespace->groups = $groups;
        }
        return $groupNamespace->groups;
    }
}
