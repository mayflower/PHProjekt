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
 * Project-Module Relation Model.
 * Manage the relation between the Projects and the active modules.
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

        $select = Phprojekt::getInstance()->getDb()->select();
        $select->from(array('pmp' => 'project_module_permissions'), 'module_id')
            ->joinRight(
                array('m' => 'module'),
                'm.id = pmp.module_id AND (m.save_type = 0 OR m.save_type = 2)',
                array()
            )
            ->where('pmp.project_id = ?', (int) $projectId)
            ->where('m.active = 1');

        foreach ($select->query()->fetchAll(Zend_Db::FETCH_COLUMN) as $moduleId) {
            $modules['data'][$moduleId]['inProject'] = true;
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
