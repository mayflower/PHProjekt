<?php
/**
 * User model
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @author     David Soria Parra <david.soria_parra@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Settings on a per user base
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <david.soria_parra@mayflower.de>
 */
class Role_Models_RoleModulePermissions extends Phprojekt_ActiveRecord_Abstract
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

            $modules['data'][$right->moduleId] = array_merge($modules['data'][$right->moduleId], Phprojekt_Acl::convertBitmaskToArray($right->access));
        }
        if (empty($modules)) {
            $model = Phprojekt_Loader::getModel('Module','Module');
            foreach ($model->fetchAll(null,' name ASC ') as $module) {
                $modules['data'][$module->id] = array();

                $modules['data'][$module->id]['id']   = $module->id;
                $modules['data'][$module->id]['name'] = $module->name;

                $modules['data'][$module->id] = array_merge($modules['data'][$module->id], Phprojekt_Acl::convertBitmaskToArray(0));
            }
        }
        return $modules;
    }

    /**
     * Save the acces per each module for one roleId
     *
     * @param array $rights Array with the modules and the bitmask access
     * @param int   $roleId The role Id
     *
     * @return void
     */
    public function saveRights($rights, $roleId)
    {
        $where   = ' roleId = ' . $roleId;
        foreach ($this->fetchAll($where) as $relation) {
            $relation->delete();
        }
        foreach ($rights as $moduleId => $access) {
            $clone = clone $this;
            $clone->moduleId = $moduleId;
            $clone->roleId   = $roleId;
            $clone->access   = $access;

            $clone->save();
        }
    }
}