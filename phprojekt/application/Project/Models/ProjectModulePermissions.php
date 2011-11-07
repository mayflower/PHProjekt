<?php
/**
 * Project-Module Relation Model.
 * Manage the relation between the Projects and the active modules.
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
 * @package    Application
 * @subpackage Project
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
 * Project-Module Relation Model.
 * Manage the relation between the Projects and the active modules.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Project
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Project_Models_ProjectModulePermissions extends Phprojekt_ActiveRecord_Abstract
{
    /**
     * Return all the modules in an array and the permission if exists.
     *
     * @param integer $projectId The Project ID.
     *
     * @return array Array with 'id', 'name', 'label' and 'inProject'.
     */
    function getProjectModulePermissionsById($projectId)
    {
        $modules = array();
        $model   = new Phprojekt_Module_Module();
        foreach ($model->fetchAll('active = 1 AND (save_type = 0 OR save_type = 2)', 'name ASC') as $module) {
            $modules['data'][$module->id] = array();
            $modules['data'][$module->id]['id']    = (int) $module->id;
            $modules['data'][$module->id]['name']  = $module->name;
            $modules['data'][$module->id]['label'] = Phprojekt::getInstance()->translate($module->label, null,
                $module->name);
            $modules['data'][$module->id]['inProject'] = false;
        }

        $where  = sprintf('project_module_permissions.project_id = %d AND module.active = 1', (int) $projectId);
        $select = ' module.id AS module_id ';
        $join   = ' RIGHT JOIN module ON ( module.id = project_module_permissions.module_id ';
        $join  .= ' AND (module.save_type = 0 OR module.save_type = 2) )';
        foreach ($this->fetchAll($where, 'module.name ASC', null, null, $select, $join) as $right) {
            $modules['data'][$right->moduleId]['inProject'] = true;
        }

        return $modules;
    }

    /**
     * Delete a project-module relation.
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
