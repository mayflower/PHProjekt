<?php
/**
 * Project-Role-User Relation.
 * Manage the relation between the Projects, the roles and the users.
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
 * Project-Role-User Relation.
 * Manage the relation between the Projects, the roles and the users.
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
class Project_Models_ProjectRoleUserPermissions extends Phprojekt_ActiveRecord_Abstract
{
    /**
     * Return all the roles in an array and their relations if exists.
     *
     * @param integer $projectId The Project ID.
     *
     * @return array Array with 'id', 'name' and 'users'.
     */
    function getProjectRoleUserPermissions($projectId)
    {
        $roles = array();
        $model = Phprojekt_Loader::getLibraryClass('Phprojekt_Role_Role');

        foreach ($model->fetchAll(null, 'name ASC') as $role) {
            $roles['data'][$role->id] = array();
            $roles['data'][$role->id]['id']    = (int) $role->id;
            $roles['data'][$role->id]['name']  = $role->name;
            $roles['data'][$role->id]['users'] = array();
        }
        $where   = sprintf('project_role_user_permissions.project_id = %d', (int) $projectId);
        $order   = 'project_role_user_permissions.user_id ASC';
        $select  = ' user.username, user.firstname, user.lastname ';
        $join    = ' LEFT JOIN user ON user.id = project_role_user_permissions.user_id ';
        $display = Phprojekt_User_User::getDisplay();
        foreach ($this->fetchAll($where, $order, null, null, $select, $join) as $right) {
            $userDisplay = Phprojekt_User_User::applyDisplay($display, $right);

            $roles['data'][$right->roleId]['users'][] = array('id'      => (int) $right->userId,
                                                              'display' => $userDisplay);
        }
        return $roles;
    }

    /**
     * Save the roles-user relation for one projectId.
     *
     * @param array   $userRoleRelation Array with the userId as key and the roleId as value.
     * @param integer $projectId        The project ID.
     *
     * @return void
     */
    public function saveRelation($userRoleRelation, $projectId)
    {
        $where = sprintf('project_id = %d', (int) $projectId);
        foreach ($this->fetchAll($where) as $relation) {
            $relation->delete();
        }

        // Save roles only for allowed users
        $activeRecord = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
        $result       = $activeRecord->getAllowedUsers();
        foreach ($result as $user) {
            $userId = $user['id'];
            if (in_array($userId, array_keys($userRoleRelation))) {
                $clone            = clone $this;
                $clone->roleId    = $userRoleRelation[$userId];
                $clone->userId    = $userId;
                $clone->projectId = $projectId;
                $clone->save();

                // Reset cache
                $sessionName = 'Project_Models_ProjectRoleUserPermissions-fetchUserRole-' . $projectId . '-'
                    . $userId;
                $roleNamespace = new Zend_Session_Namespace($sessionName);
                $roleNamespace->unsetAll();
            }
        }
    }

    /**
     * Returns the UserRole in the project.
     *
     * @param integer $userId    The user ID.
     * @param integer $projectId The project ID.
     *
     * @return integer $_role Role ID.
     */
    public function fetchUserRole($userId, $projectId)
    {
        $role = 1;

        // Keep the roles in the session for optimize the query
        if (isset($userId) && isset($projectId)) {
            $sessionName   = 'Project_Models_ProjectRoleUserPermissions-fetchUserRole-' . $projectId . '-' . $userId;
            $roleNamespace = new Zend_Session_Namespace($sessionName);

            if (isset($roleNamespace->role)) {
                $role = $roleNamespace->role;
            } else {
                $where = sprintf('project_id = %d AND user_id = %d', (int) $projectId, (int) $userId);
                $row   = $this->fetchall($where);

                if (!empty($row)) {
                    $role                = $row[0]->roleId;
                    $roleNamespace->role = $row[0]->roleId;
                } else {
                    // Fix Root Project
                    if ($projectId > 1) {
                        $project = Phprojekt_Loader::getModel('Project', 'Project');
                        $parent  = $project->find($projectId);
                        if (!is_null($parent) && !empty($parent) && $parent->projectId > 0) {
                            $sessionName = 'Project_Models_ProjectRoleUserPermissions-fetchUserRole-'
                                . $parent->projectId . '-' . $userId;
                            $roleParentNamespace = new Zend_Session_Namespace($sessionName);
                            if (isset($roleParentNamespace->role)) {
                                $role = $roleParentNamespace->role;
                            } else {
                                $role = $this->fetchUserRole($userId, $parent->projectId);
                            }
                            $roleNamespace->role = $role;
                        }
                    } else {
                        // Default Role
                        $role                = 1;
                        $roleNamespace->role = 1;
                    }
                }
            }
        }

        return $role;
    }
}
