<?php
/**
 * Project-Role-User Relation
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
 * Manage the relation between the Projects, the roles and the users
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
class Project_Models_ProjectRoleUserPermissions extends Phprojekt_ActiveRecord_Abstract
{
    /**
     * Return all the roles in an array and their relations if exists
     *
     * @param int $projectId The Project id
     *
     * @return array
     */
    function getProjectRoleUserPermissions($projectId)
    {
        $roles         = array();
        $model         = Phprojekt_Loader::getModel('Role', 'Role');
        $authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');

        foreach ($model->fetchAll(null, ' name ASC ') as $role) {
            $roles['data'][$role->id] = array();
            $roles['data'][$role->id]['id']    = $role->id;
            $roles['data'][$role->id]['name']  = $role->name;
            $roles['data'][$role->id]['users'] = array();
        }
        $where  = ' ProjectRoleUserPermissions.projectId = ' . $projectId;
        $where .= ' AND User.id != '. (int) $authNamespace->userId;
        $order  = ' ProjectRoleUserPermissions.userId ASC';
        $select = ' User.username ';
        $join   = ' LEFT JOIN User ON User.id = ProjectRoleUserPermissions.userId ';
        foreach ($this->fetchAll($where, $order, null, null, $select, $join) as $right) {
            $roles['data'][$right->roleId]['users'][] = array('id'   => $right->userId,
                                                              'name' => $right->username);
        }
        return $roles;
    }

    /**
     * Save the roles-user relation for one projectId
     *
     * @param array $roles     Array with the roles Id
     * @param array users      Array with the users Id
     * @param int   $projectId The project Id
     *
     * @return void
     */
    public function saveRelation($roles, $users, $projectId)
    {
        $where   = ' projectId = ' . $projectId;
        foreach ($this->fetchAll($where) as $relation) {
            $relation->delete();
        }
        foreach ($users as $userId) {
            $clone = clone $this;
            $clone->roleId    = $roles[$userId];
            $clone->userId    = $userId;
            $clone->projectId = $projectId;
            $clone->save();

            // Reset cache
            $roleNamespace = new Zend_Session_Namespace('ProjectRoleUserPermissions_'.$projectId.'_'.$userId);
            if (isset($roleNamespace->role) && !empty($roleNamespace->role)) {
                $roleNamespace->role = 0;
            }
        }
    }

    /**
     * Returns the UserRole in the project
     *
     * @param int $userId    The user Id
     * @param int $projectId The project Id
     *
     * @return string $_role current role
     */
    public function fetchUserRole($userId, $projectId)
    {
        $role = 1;
        // Keep the roles in the session for optimize the query
        if (isset($userId) && isset($projectId)) {
            $roleNamespace = new Zend_Session_Namespace('ProjectRoleUserPermissions_'.$projectId.'_'.$userId);
            if (isset($roleNamespace->role) && $roleNamespace->role > 0) {
                $role = $roleNamespace->role;
            } else {
                $where = ' projectId = '. (int) $projectId;
                $where .= ' AND userId = '. (int) $userId;
                $row = $this->fetchall($where);
                if (!empty($row)) {
                    $role = $row[0]->roleId;
                    $roleNamespace->role = $row[0]->roleId;
                } else {
                    // Fix Root Project
                    if ($projectId > 1) {
                        $project = Phprojekt_Loader::getModel('Project', 'Project');
                        $parent  = $project->find($projectId);
                        if (!is_null($parent) && $parent->projectId > 0) {
                            $roleParentNamespace = new Zend_Session_Namespace(
                                                     'ProjectRoleUserPermissions_'.$parent->projectId.'_'.$userId);
                            if (isset($roleParentNamespace->role) && $roleParentNamespace->role > 0) {
                                $role = $roleParentNamespace->role;
                            } else {
                                $role = $this->fetchUserRole($userId, $parent->projectId);
                            }
                            $roleNamespace->role = $role;
                        }
                    }
                }
            }
        }
        return $role;
    }
}