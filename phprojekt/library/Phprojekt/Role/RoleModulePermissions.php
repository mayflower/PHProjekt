<?php
/**
 * User model
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
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @author     David Soria Parra <david.soria_parra@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Settings on a per user base
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
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
    function getRoleModulePermissionsById($roleId)
    {
        $modules = array();
        $where   = ' RoleModulePermissions.roleId = ' . $roleId .' OR RoleModulePermissions.roleId is null ';
        $order   = ' Module.name ASC';
        $select  = ' Module.id as moduleId ';
        $join    = ' RIGHT JOIN Module ON Module.id = RoleModulePermissions.moduleId ';
        foreach ($this->fetchAll($where, $order, null, null, $select, $join) as $right) {
            $modules['data'][$right->moduleId] = array();

            $modules['data'][$right->moduleId]['id']   = $right->moduleId;
            $modules['data'][$right->moduleId]['name'] = Phprojekt_Module::getModuleName($right->moduleId);
            $label = Phprojekt_Module::getModuleLabel($right->moduleId);
            $modules['data'][$right->moduleId]['label'] = Phprojekt::getInstance()->translate($label); 
            

            $modules['data'][$right->moduleId] = array_merge($modules['data'][$right->moduleId],
                                                             Phprojekt_Acl::convertBitmaskToArray($right->access));
        }
        if (empty($modules)) {
            $model = Phprojekt_Loader::getLibraryClass('Phprojekt_Module_Module');
            foreach ($model->fetchAll(null, ' name ASC ') as $module) {
                $modules['data'][$module->id] = array();

                $modules['data'][$module->id]['id']    = $module->id;
                $modules['data'][$module->id]['name']  = $module->name;
                $modules['data'][$module->id]['label'] = Phprojekt::getInstance()->translate($module->label);

                $modules['data'][$module->id] = array_merge($modules['data'][$module->id],
                                                Phprojekt_Acl::convertBitmaskToArray(0));
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
        $where = $this->getAdapter()->quoteInto(' moduleId = ? ', (int) $moduleId);
        foreach ($this->fetchAll($where) as $relation) {
            $relation->delete();
        }
    }
}
