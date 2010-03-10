<?php
/**
 * User model
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
 * @author     David Soria Parra <david.soria_parra@mayflower.de>
 */

/**
 * Settings on a per user base
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Role
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     David Soria Parra <david.soria_parra@mayflower.de>
 */
class Phprojekt_Role_RoleModulePermissions extends Phprojekt_ActiveRecord_Abstract
{
    /**
     * Return all the modules in an array and the access if exists
     *
     * @param int $roleId The role id
     *
     * @return array
     */
    public function getRoleModulePermissionsById($roleId)
    {
        $modules = array();

        $model = Phprojekt_Loader::getLibraryClass('Phprojekt_Module_Module');
        foreach ($model->fetchAll('(save_type = 0 OR save_type = 2)', 'name ASC') as $module) {
            $modules['data'][$module->id] = array();
            $modules['data'][$module->id]['id']    = $module->id;
            $modules['data'][$module->id]['name']  = $module->name;
            $modules['data'][$module->id]['label'] = Phprojekt::getInstance()->translate($module->label, null,
                $module->name);
            $modules['data'][$module->id] = array_merge($modules['data'][$module->id],
                Phprojekt_Acl::convertBitmaskToArray(0));
        }

        $where = 'role_module_permissions.role_id = ' . (int) $roleId;
        foreach ($this->fetchAll($where) as $right) {
            if (isset($modules['data'][$right->moduleId])) {
                $modules['data'][$right->moduleId] = array_merge($modules['data'][$right->moduleId],
                    Phprojekt_Acl::convertBitmaskToArray($right->access));
            }
        }

        return $modules;
    }

    /**
     * Add a new relation module-role without delete any entry
     * Used for add modules to the role 1
     *
     * @param int $moduleId  The Module Id to add
     *
     * @return void
     */
    public function addModuleToAdminRole($moduleId)
    {
        $this->roleId   = 1;
        $this->moduleId = $moduleId;
        $this->access   = 139;
        $this->save();
    }

    /**
     * Delete a role-module relation
     *
     * @param int $moduleId  The Module Id to delete
     *
     * @return void
     */
    public function deleteModuleRelation($moduleId)
    {
        $where = sprintf('module_id = %d', (int) $moduleId);
        foreach ($this->fetchAll($where) as $relation) {
            $relation->delete();
        }
    }
}
