<?php
/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * Role Rights class.
 */
class Phprojekt_RoleRights
{
    /**
     * ID of the item,
     *
     * @var integer
     */
    protected $_id = 0;

    /**
     * ID of the current module.
     *
     * @var integer
     */
    protected $_moduleId = 1;

    /**
     * ID of the user.
     *
     * @var integer
     */
    protected $_user = 0;

    /**
     * ID of the project that the item belongs to.
     *
     * @var integer
     */
    protected $_project = 0;

    /**
     * Role ID of user for current item.
     *
     * @var string
     */
    protected $_role ='';

    /**
     * Zend acls
     *
     * @var Phprojekt_Acl
     */
    protected $_acl = array();

    /**
     * Constructor.
     *
     * @param integer $project  Project ID.
     * @param string  $moduleId Current module Id.
     * @param integer $id       Current item ID.
     * @param integer $user     Current user ID.
     *
     * @return void
     */
    public function __construct($project, $moduleId = 1, $id = 0, $user = 0)
    {
        $this->_setId($id);
        $this->_setModule($moduleId);
        $this->_setUser($user);
        $this->_setProject($project);
        $this->_setAcl();
        $this->_setUserRole();
    }

    /**
     * Checks whether user has a certain right on an item.
     *
     * @param string $right    Name of right.
     * @param string $moduleId Module ID.
     *
     * @return boolean True if he has.
     */
    public function hasRight($right, $moduleId = null)
    {
        if (null != $moduleId) {
            $this->_setModule($moduleId);
        }

        $role = $this->getUserRole();
        $acl  = $this->getAcl();

        if (null === $moduleId) {
            $moduleId = $this->getModule();
        }

        try {
            if ($acl->has($moduleId)) {
                return $acl->isAllowed($role, $moduleId, $right);
            } else {
                return false;
            }
        } catch(Zend_Acl_Exception $error) {
            $logger = Phprojekt::getInstance()->getLog();
            $logger->debug((string) $error);
            return false;
        }
    }

    /**
     * Setter for item ID.
     *
     * @param integer $id Current ID.
     *
     * @return void
     */
    private function _setId($id)
    {
        $this->_id = $id;
    }

    /**
     * Getter for item ID.
     *
     * @return integer $_id Current ID.
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Sets the project that the item belongs to
     * (if item itsel is a project this is the id of item itself).
     *
     * @param integer $project Current project ID.
     *
     * @return void
     */
    private function _setProject($project)
    {
        if ($this->getId() > 0 && $this->getmodule() == 'Project') {
            $this->_project = $this->getId();
        } else {
            $this->_project = $project;
        }
    }

    /**
     * Returns the project that the item belongs to.
     *
     * @return integer $_project Current project.
     */
    public function getProject()
    {
        return $this->_project;
    }

    /**
     * Sets the module Id.
     *
     * @param integer $moduleId Current module ID.
     *
     * @return void
     */
    private function _setModule($moduleId)
    {
        $this->_module = $moduleId;
    }

    /**
     * Returns the module Id.
     *
     * @return integer $module Current module ID.
     */
    public function getModule()
    {
        return $this->_module;
    }

    /**
     * Setter for User ID.
     *
     * @param integer $user Current user ID.
     *
     * @return void
     */
    private function _setUser($user)
    {
        if ($user != 0) {
            $this->_user = $user;
        } else {
            $this->_user = Phprojekt_Auth::getUserId();
        }
    }

    /**
     * Getter for User ID.
     *
     * @return integer $_user Current user ID.
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * Setter for acl
     *
     * @return void
     */
    private function _setAcl()
    {
        $this->_acl = Phprojekt_Acl::getInstance();
    }

    /**
     * Getter for acl
     *
     * @return Phprojekt_Acl $_acl Acls from session.
     */
    public function getAcl()
    {
        return $this->_acl;
    }

    /**
     * Setter for UserRole,
     * the Role of ther user is fetched from the db.
     *
     * @return void
     */
    private function _setUserRole()
    {
        $project = new Project_Models_ProjectRoleUserPermissions();
        $this->_role = $project->fetchUserRole($this->getUser(), $this->getProject());
    }

    /**
     * Getter for UserRole.
     *
     * @return string $_role Current role Id.
     */
    public function getUserRole()
    {
        return $this->_role;
    }
}
