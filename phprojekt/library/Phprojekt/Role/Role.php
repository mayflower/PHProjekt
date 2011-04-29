<?php
/**
 * Role class.
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
 * @subpackage Role
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
 * Role class.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Role
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_Role_Role extends Phprojekt_Item_Abstract
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
     * Has many declration.
     *
     * @var array
     */
    public $hasMany = array('modulePermissions' => array('classname' => 'Phprojekt_Role_RoleModulePermissions'));

    /**
     * Returns the Model information manager.
     *
     * @return Phprojekt_ModelInformation_Interface An instance of a Phprojekt_ModelInformation_Interface.
     */
    public function getInformation()
    {
        if (null == $this->_informationManager) {
            $this->_informationManager = Phprojekt_Loader::getLibraryClass('Phprojekt_Role_Information');
        }

        return $this->_informationManager;
    }

    /**
     * Save the rights for each modules.
     *
     * @param array $rights Array with the modules and the bitmask access.
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
     * Delete a role and all his relations.
     * It prevents deletion of role 1 -admin role-.
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
