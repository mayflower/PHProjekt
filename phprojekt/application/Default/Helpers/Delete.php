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
 * Helper to delete tree nodes and models.
 */
final class Default_Helpers_Delete
{
    /**
     * Delete a tree and all the sub-itemes.
     *
     * @param Phprojekt_ActiveRecord_Abstract $model Model to delete.
     *
     * @throws Exception If validation fails.
     *
     * @return boolean True for a sucessful delete.
     */
    protected static function _deleteTree(Phprojekt_ActiveRecord_Abstract $model)
    {
        $id = $model->id;
        // Checks
        if ($id == 1) {
            throw new Zend_Controller_Action_Exception('You can not delete the root project', 422);
        } else if (!self::_checkItemRights($model, 'Project')) {
            throw new Zend_Controller_Action_Exception('You do not have access to do this action', 403);
        } else {
            $relations = new Project_Models_ProjectModulePermissions();
            $where     = sprintf('project_id = %d', (int) $id);

            // Delete related items
            $modules = $relations->getProjectModulePermissionsById($id);
            $tag     = new Phprojekt_Tags();
            foreach ($modules['data'] as $moduleData) {
                if ($moduleData['inProject']) {
                    $module = Phprojekt_Loader::getModel($moduleData['name'], $moduleData['name']);
                    if ($module instanceof Phprojekt_ActiveRecord_Abstract) {
                        $records = $module->fetchAll($where);
                        if (is_array($records)) {
                            foreach ($records as $record) {
                                $tag->deleteTagsByItem($moduleData['id'], $record->id);
                                self::delete($record);
                            }
                        }
                    }
                }
            }

            // Delete module-project relaton
            $records = $relations->fetchAll($where);
            if (is_array($records)) {
                foreach ($records as $record) {
                    $record->delete();
                }
            }

            // Delete user-role-projetc relation
            $relations = new Project_Models_ProjectRoleUserPermissions();
            $records   = $relations->fetchAll($where);
            if (is_array($records)) {
                foreach ($records as $record) {
                    $record->delete();
                }
            }

            // Delete the project itself
            return (null === $model->delete());
        }
    }

    /**
     * Help to delete a model.
     *
     * @param Phprojekt_ActiveRecord_Abstract $model The model to delete.
     *
     * @throws Exception If validation fails.
     *
     * @return boolean True for a sucessful delete.
     */
    protected static function _deleteModel(Phprojekt_ActiveRecord_Abstract $model)
    {
        // Checks
        $moduleName = Phprojekt_Loader::getModuleFromObject($model);
        if (!self::_checkItemRights($model, $moduleName)) {
            throw new Zend_Controller_Action_Exception('You do not have access to do this action', 400);
        } else {
            $return = $model->delete();
            if (is_bool($return)) {
                // An extention returns true or false.
                return $return;
            } else if (is_null($return) || (is_a($return, 'Phprojekt_ActiveRecord_Abstract') && is_null($return->id))) {
                 // ActiveRecord delete the model.
                return true;
            } else {
                // Any other value, is wrong.
                return false;
            }
        }
    }

    /**
     * Overwrite call.
     *
     * @throws Exception If validation of parameters fails.
     *
     * @return void
     */
    public static function delete()
    {
        $arguments = func_get_args();
        $model     = $arguments[0];

        if (func_num_args() < 1) {
            throw new Zend_Controller_Action_Exception('The model argument is expected', 400);
        }

        if ($model instanceof Phprojekt_ActiveRecord_Abstract) {
            if (Phprojekt::getInstance()->getConfig()->frontendMessages) {
                if (method_exists($model, 'getNotification')) {
                    $notificationModel = $model->getNotification();
                    $notificationModel->setControllProcess(Phprojekt_Notification::LAST_ACTION_DELETE);
                    $notificationModel->saveFrontendMessage();
                }
            }

            if ($model->getModelName() == 'Project') {
                return self::_deleteTree($model);
            } else {
                return self::_deleteModel($model);
            }
        }

        return true;
    }

    /**
     * Check if the user has delete access to the item if is not a global module.
     *
     * @param Phprojekt_ActiveRecord_Abstract $model      The model to save.
     * @param string                          $moduleName The current module.
     *
     * @return boolean True for a valid right.
     */
    private static function _checkItemRights(Phprojekt_ActiveRecord_Abstract $model, $moduleName)
    {
        $canDelete = false;

        if ($moduleName == 'Core') {
            return Phprojekt_Auth::isAdminUser();
        } else if (Phprojekt_Module::saveTypeIsNormal(Phprojekt_Module::getId($moduleName))
                && method_exists($model, 'hasRight')) {
            return $model->hasRight(Phprojekt_Auth_Proxy::getEffectiveUserId(), Phprojekt_Acl::DELETE);
        } else {
            return true;
        }
    }
}
