<?php
/**
 * Project-Module Relation
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Manage the relation between the Projects and the active modules
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
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
        // Cache the query
        $projectModulePermissionsNamespace = new Zend_Session_Namespace('ProjectModulePermissions'.'-'.$projectId);
        if (isset($projectModulePermissionsNamespace->modules) && !empty($projectModulePermissionsNamespace->modules)) {
            $modules = $projectModulePermissionsNamespace->modules;
        } else {
            $modules = array();
            $model = Phprojekt_Loader::getModel('Module','Module');
            foreach ($model->fetchAll(' active = 1 ' ,' name ASC ') as $module) {
                $modules['data'][$module->id] = array();
                $modules['data'][$module->id]['id']        = $module->id;
                $modules['data'][$module->id]['name']      = $module->name;
                $modules['data'][$module->id]['label']     = $module->name;
                $modules['data'][$module->id]['inProject'] = false;
            }
            $where  = ' ProjectModulePermissions.projectId = ' . $projectId;
            $where  .= ' AND Module.active = 1 ';
            $order  = ' Module.name ASC';
            $select = ' Module.id as moduleId ';
            $join   = ' RIGHT JOIN Module ON Module.id = ProjectModulePermissions.moduleId ';
            foreach ($this->fetchAll($where, $order, null, null, $select, $join) as $right) {
                $modules['data'][$right->moduleId]['inProject'] = true;
            }
            $projectModulePermissionsNamespace->modules = $modules;
        }
        return $modules;
    }

    /**
     * Save the allow modules for one projectId
     * First delete all the older relations
     *
     * @param array $rights    Array with the modules to save
     * @param int   $projectId The project Id
     *
     * @return void
     */
    public function saveModules($rights, $projectId)
    {
        $where   = ' projectId = ' . $projectId;
        foreach ($this->fetchAll($where) as $relation) {
            $relation->delete();
        }
        foreach ($rights as $moduleId) {
            $clone = clone $this;
            $clone->moduleId  = $moduleId;
            $clone->projectId = $projectId;
            $clone->save();
        }
        // Destroy cache
        $projectModulePermissionsNamespace = new Zend_Session_Namespace('ProjectModulePermissions'.'-'.$projectId);
        if (isset($projectModulePermissionsNamespace->modules) && !empty($projectModulePermissionsNamespace->modules)) {
            $projectModulePermissionsNamespace->modules = array();
        }
    }

    /**
     * Add a new relation module-project without delete any entry
     * Used for add modules to the root project
     *
     * @param int $moduleId  The Module Id to add
     * @param int $projectId The project Id
     *
     * @return void
     */
    public function addModule($moduleId, $projectId)
    {
        $clone = clone $this;
        $clone->moduleId  = $moduleId;
        $clone->projectId = $projectId;
        $clone->save();
    }
}