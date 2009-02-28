<?php
/**
 * Project-Module Relation
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
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Manage the relation between the Projects and the active modules
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Project_Models_ProjectModulePermissions extends Phprojekt_ActiveRecord_Abstract
{
    /**
     * Return all the modules in an array and the permission if exists
     *
     * @param int $projectId The Project id
     *
     * @return array
     */
    function getProjectModulePermissionsById($projectId)
    {
        $modules = array();
        $model   = Phprojekt_Loader::getLibraryClass('Phprojekt_Module_Module');
        foreach ($model->fetchAll(" active = 1 AND (" . 
                 $this->_db->quoteIdentifier("saveType")." = 0 OR ".$this->_db->quoteIdentifier("saveType")." = 2) ", 
                 ' name ASC ') as $module) {
            $modules['data'][$module->id] = array();
            $modules['data'][$module->id]['id']        = $module->id;
            $modules['data'][$module->id]['name']      = $module->name;
            $modules['data'][$module->id]['label']     = Phprojekt::getInstance()->translate($module->label);
            $modules['data'][$module->id]['inProject'] = false;
        }
        $where  = " ".$this->_db->quoteIdentifier("ProjectModulePermissions.projectId")." = " . $projectId;
        $where .= " AND ".$this->_db->quoteIdentifier("Module.active")." = 1 ";
        $order  = " Module.name ASC";
        $select = " ".$this->_db->quoteIdentifier("Module").".id as ".$this->_db->quoteIdentifier("ModuleId")." ";
        $join   = " RIGHT JOIN ".$this->_db->quoteIdentifier("Module")." ON ( ".$this->_db->quoteIdentifier("Module.id")." = ".$this->_db->quoteIdentifier("ProjectModulePermissions.moduleId")." ";
        $join  .= " AND (".$this->_db->quoteIdentifier("saveType")." = 0 OR ".$this->_db->quoteIdentifier("saveType")." = 2) )";
        foreach ($this->fetchAll($where, $order, null, null, $select, $join) as $right) {
            $modules['data'][$right->moduleId]['inProject'] = true;
        }
        return $modules;
    }

    /**
     * Delete a project-module relation
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
