<?php
/**
 * Helper to delete tree nodes and models
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
 * @package    PHProjekt
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @link       http://www.phprojekt.com
 * @author     Gustavo Solt <solt@mayflower.de>
 * @since      File available since Release 6.0
 */

/**
 * Helper to delete tree nodes and models
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
final class Default_Helpers_Delete
{
    /**
     * Delete a tree and all the sub-itemes
     *
     * @param Phprojekt_Model_Interface $model
     *
     * @throws Exception If validation fails
     *
     * @return boolean
     */
    protected static function _deleteTree(Phprojekt_Model_Interface $model)
    {
        $id = $model->id;
        // Checks
        if ($id == 1) {
            throw new Phprojekt_PublishedException('You can not delete the root project');
        } else if (!self::_checkItemRights($model, 'Project')) {
            throw new Phprojekt_PublishedException('You do not have access for do this action');
        } else {
            $relations = Phprojekt_Loader::getModel('Project', 'ProjectModulePermissions');

            // Delete related items
            $modules = $relations->getProjectModulePermissionsById($id);
            foreach ($modules['data'] as $moduleData) {
                if ($moduleData['inProject']) {
                    $module  = Phprojekt_Loader::getModel($moduleData['name'], $moduleData['name']);
                    $records = $module->fetchAll('project_id = ' . $id);
                    if (is_array($records)) {
                        foreach ($records as $record) {
                            $record->delete();
                        }
                    }
                }
            }

            // Delete module-project relaton
            $records = $relations->fetchAll('project_id = ' . $id);
            if (is_array($records)) {
                foreach ($records as $record) {
                    $record->delete();
                }
            }

            // Delete user-role-projetc relation
            $relations = Phprojekt_Loader::getModel('Project', 'ProjectRoleUserPermissions');
            $records   = $relations->fetchAll('project_id = ' . $id);
            if (is_array($records)) {
                foreach ($records as $record) {
                    $record->delete();
                }
            }

            // Delete the project itself
            return $model->delete();
        }
    }

    /**
     * Help to delete a model
     *
     * @param Phprojekt_Model_Interface $model  The model
     *
     * @throws Exception If validation fails
     *
     * @return boolean
     */
    protected static function _deleteModel(Phprojekt_Model_Interface $model)
    {
        // Checks
        $moduleName = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
        if (!self::_checkItemRights($model, $moduleName)) {
            throw new Phprojekt_PublishedException('You do not have access for do this action');
        } else {
            return $model->delete();
        }
    }

    /**
     * Overwrite call
     *
     * @param string $name
     * @throws Exception If validation of parameters fails
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

        if ($model instanceof Phprojekt_Model_Interface) {
            if ($model->getModelName() == 'Project') {
                return self::_deleteTree($model);
            } else {
                return self::_deleteModel($model);
            }
        }

        return true;
    }

    /**
     * Check if the user has delete access to the item if is not a global module
     *
     * @param Phprojekt_Model_Interface $model      The model to save
     * @param string                    $moduleName The current module
     *
     * @return boolean
     */
    private function _checkItemRights($model, $moduleName)
    {
        $canDelete = false;

        if ($moduleName == 'Core') {
            return Phprojekt_Auth::isAdminUser();
        } else if (Phprojekt_Module::getSaveType(Phprojekt_Module::getId($moduleName)) == 0) {
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
