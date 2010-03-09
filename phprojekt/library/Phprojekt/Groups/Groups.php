<?php
/**
 * Group class for PHProjekt 6.0
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @author     Eduardo Polidor <polidor@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Phprojekt_Group for PHProjekt 6.0
 *
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @author     Eduardo Polidor <polidor@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */
class Phprojekt_Groups_Groups extends Phprojekt_ActiveRecord_Abstract implements Phprojekt_Model_Interface
{
    /**
     * Has many and belongs to many declrations
     *
     * @var array
     */
    public $hasManyAndBelongsToMany = array('users' =>  array('classname' => 'Phprojekt_User_User',
                                                              'module'    => 'User',
                                                              'model'     => 'User'));

    /**
     * user
     * @var integer $_user
     */
    private $_userId = null;

    /**
     * The standard information manager with hardcoded
     * field definitions
     *
     * @var Phprojekt_ModelInformation_Interface
     */
    protected $_informationManager;

    /**
     * Constructor for Groups
     */
    public function __construct()
    {
        parent::__construct();

        $this->_userId             = Phprojekt_Auth::getUserId();
        $this->_informationManager = Phprojekt_Loader::getLibraryClass('Phprojekt_Groups_Information');
    }

    /**
     * Define the clone function for prevent the same point to same object.
     *
     * @return void
     */
    public function __clone()
    {
        parent::__clone();
        $this->_informationManager = Phprojekt_Loader::getLibraryClass('Phprojekt_Groups_Information');
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
        $sessionName    = 'Phprojekt_Groups_Groups-isUserInGroup-' . $this->_userId . '-' . $group;
        $groupNamespace = new Zend_Session_Namespace($sessionName);
        if (!isset($groupNamespace->isInGroup)) {
            $currentGroup = $this->find($group);
            if (count($currentGroup->users->find($this->getUserId())) > 0) {
                $groupNamespace->isInGroup = true;
            } else {
                $groupNamespace->isInGroup = false;
            }
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
     * Validate the current record
     *
     * @return boolean
     */
    public function recordValidate()
    {
        return true;
    }
}
