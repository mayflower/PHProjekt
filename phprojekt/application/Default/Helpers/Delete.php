<?php
/**
 * Helper to delete tree nodes and models.
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
 * @subpackage Default
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
 * Helper to delete tree nodes and models.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Default
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
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
            throw new Phprojekt_PublishedException('You can not delete the root project');
        } else if (!self::_checkItemRights($model, 'Project')) {
            throw new Phprojekt_PublishedException('You do not have access to do this action');
        } else {
            $relations = Phprojekt_Loader::getModel('Project', 'ProjectModulePermissions');
            $where     = sprintf('project_id = %d', (int) $id);

            // Delete related items
            $modules = $relations->getProjectModulePermissionsById($id);
            $tag     = Phprojekt_Tags::getInstance();
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
            $relations = Phprojekt_Loader::getModel('Project', 'ProjectRoleUserPermissions');
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
            throw new Phprojekt_PublishedException('You do not have access to do this action');
        } else {
            $return = $model->delete();
            if ((isset($return->id) && null === $return->id) || null === $return) {
                 // ActiveRecord delete the model.
                return true;
            } else if (is_bool($return)) {
                // An extention returns true or false.
                return $return;
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
            throw new Phprojekt_PublishedException('The model argument is expected');
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
    private static function _checkItemRights($model, $moduleName)
    {
        $canDelete = false;

        if ($moduleName == 'Core') {
            return Phprojekt_Auth::isAdminUser();
        } else if (Phprojekt_Module::saveTypeIsNormal(Phprojekt_Module::getId($moduleName))) {
            $itemRights = $model->getRights();

            if (isset($itemRights['currentUser'])) {
                if (!$itemRights['currentUser']['delete'] &&
                    !$itemRights['currentUser']['admin']) {
                    $canDelete = false;
                } else {
                    $canDelete = true;
                }
            }
        } else {
            $canDelete = true;
        }

        return $canDelete;
    }
}
