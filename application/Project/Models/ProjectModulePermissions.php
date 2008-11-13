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
        $modules = array();
        $model   = new Phprojekt_Module_Module();
        foreach ($model->fetchAll(' active = 1 AND (saveType = 0 OR saveType = 2) ', ' name ASC ') as $module) {
            $modules['data'][$module->id] = array();
            $modules['data'][$module->id]['id']        = $module->id;
            $modules['data'][$module->id]['name']      = $module->name;
            $modules['data'][$module->id]['label']     = Zend_Registry::get("translate")->translate($module->label);
            $modules['data'][$module->id]['inProject'] = false;
        }
        $where  = ' ProjectModulePermissions.projectId = ' . $projectId;
        $where .= ' AND Module.active = 1 ';
        $order  = ' Module.name ASC';
        $select = ' Module.id as moduleId ';
        $join   = ' RIGHT JOIN Module ON ( Module.id = ProjectModulePermissions.moduleId ';
        $join  .= ' AND (Module.saveType = 0 OR Module.saveType = 2) )';
        foreach ($this->fetchAll($where, $order, null, null, $select, $join) as $right) {
            $modules['data'][$right->moduleId]['inProject'] = true;
        }
        return $modules;
    }
}