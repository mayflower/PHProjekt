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
 * Role-Module Relation.
 * Manage the relation between the Roles and Modules.
 */
class Phprojekt_Role_RoleModulePermissions extends Phprojekt_ActiveRecord_Abstract
{
    /**
     * Return all the modules in an array and the access if exists.
     *
     * @param integer $roleId The role ID.
     *
     * @return array Array with 'id', 'name', 'label' and the access.
     */
    public function getRoleModulePermissionsById($roleId)
    {
        $modules = array();

        $model = new Phprojekt_Module_Module();
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
     * Add a new relation module-role without delete any entry.
     * Used for add modules to the role 1.
     *
     * @param integer $moduleId The Module ID to add.
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
     * Delete a role-module relation.
     *
     * @param integer $moduleId The Module ID to delete.
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
