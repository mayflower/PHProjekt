<?php
/**
 * Role class for PHProjekt 6.0
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
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @version    $Id$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Phprojekt_Role for PHProjekt 6.0
 *
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL v3 (See LICENSE file)
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */
class Phprojekt_Role_Role extends Phprojekt_ActiveRecord_Abstract implements Phprojekt_Model_Interface
{
    public $hasMany = array('modulePermissions' => array('classname' => 'Phprojekt_Role_RoleModulePermissions'));

    /**
     * Id of user
     * @var int $user
     */
    protected $_user = 0;

    /**
     * Keep the found project roles in cache
     *
     * @var array
     */
    private $_projectRoles = array();

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
     * Constructor for Groups
     *
     * @param Zend_Db $db database
     */
    public function __construct($db = null)
    {
        parent::__construct($db);

        $this->_validate           = Phprojekt_Loader::getLibraryClass('Phprojekt_Model_Validate');
        $this->_informationManager = Phprojekt_Loader::getLibraryClass('Phprojekt_Role_Information');
    }

    /**
     * Define the clone function for prevent the same point to same object.
     *
     * @return void
     */
    public function __clone()
    {
        parent::__clone();
        $this->_validate           = Phprojekt_Loader::getLibraryClass('Phprojekt_Model_Validate');
        $this->_informationManager = Phprojekt_Loader::getLibraryClass('Phprojekt_Role_Information');
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
     * Save the rights for each modules
     *
     * @param array $rights Array with the modules and the bitmask access
     *
     * @return void
     */
    public function saveRights($rights)
    {
        // Delete the cache
        $sessionName  = 'Phprojekt_Acl';
        $aclNamespace = new Zend_Session_Namespace($sessionName);
        $aclNamespace->unsetAll();

        foreach ($this->modulePermissions->fetchAll() as $relation) {
            $relation->delete();
        }
        foreach ($rights as $moduleId => $access) {
            $modulePermissions = $this->modulePermissions->create();
            $modulePermissions->moduleId = $moduleId;
            $modulePermissions->roleId   = $this->id;
            $modulePermissions->access   = $access;
            $modulePermissions->save();
        }
    }

    /**
     * Validate the current record
     *
     * @return boolean
     */
    public function recordValidate()
    {
        $data   = $this->_data;
        $fields = $this->_informationManager->getFieldDefinition(Phprojekt_ModelInformation_Default::ORDERING_FORM);

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

    /**
     * Delete a role and all his relations.
     * It prevents deletion of role 1 -admin role-
     *
     * @return void
     */
    public function delete()
    {
        if ($this->id > 1) {
            parent::delete();
        }
    }
}
