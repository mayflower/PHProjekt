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
 * Project-Role-User Relation.
 * Manage the relation between the Projects, the roles and the users.
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
        $model = new Phprojekt_Role_Role();

        foreach ($model->fetchAll(null, 'name ASC') as $role) {
            $roles['data'][$role->id] = array();
            $roles['data'][$role->id]['id']    = (int) $role->id;
            $roles['data'][$role->id]['name']  = $role->name;
            $roles['data'][$role->id]['users'] = array();
        }
        $select  = Phprojekt::getInstance()->getDb()->select();
        $select->from(
            array('prup' => 'project_role_user_permissions'),
            array('roleId' => 'role_id', 'userId' => 'user_id')
        )
            ->where('prup.project_id = ?', (int) $projectId)
            ->joinLeft(array('u' => 'user'), 'u.id = prup.user_id', array('username', 'firstname', 'lastname'))
            ->order('prup.user_id ASC');
        $display = Phprojekt_User_User::getDisplay();
        foreach ($select->query()->fetchAll(Zend_Db::FETCH_OBJ) as $right) {
            $userDisplay = Phprojekt_User_User::applyDisplay($display, $right);

            $roles['data'][$right->roleId]['users'][] = array('id'      => (int) $right->userId,
                                                              'display' => $userDisplay);
        }
        return $roles;
    }

    /**
     * Save the roles-user relation for one projectId.
     *
     * @param array   $roles     Array with the roles ID.
     * @param array   users      Array with the users ID.
     * @param integer $projectId The project ID.
     *
     * @return void
     */
    public function saveRelation($roles, $users, $projectId)
    {
        $where = sprintf('project_id = %d', (int) $projectId);
        foreach ($this->fetchAll($where) as $relation) {
            $relation->delete();
        }

        // Save roles only for allowed users
        $activeRecord = new Phprojekt_User_User();
        $result       = $activeRecord->getAllowedUsers();
        foreach ($result as $user) {
            $userId = $user['id'];
            if (in_array($userId, $users)) {
                $clone = clone $this;
                $clone->roleId    = $roles[$userId];
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
                        $project = new Project_Models_Project();
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
