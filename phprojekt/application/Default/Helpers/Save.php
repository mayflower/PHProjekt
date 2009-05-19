<?php
/**
 * Helper to save tree nodes and models
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
 * @author     David Soria Parra <soria_parra@mayflower.de>
 * @since      File available since Release 6.0
 */

/**
 * Helper to save tree nodes and models
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
final class Default_Helpers_Save
{
    /**
     * Save a tree
     *
     * @todo optimize and use native queries to do
     * @param Phprojekt_Tree_Node_Database $node
     * @param array $params
     *
     * @throws Exception If validation of parameters fails
     *
     * @return boolean
     */
    protected static function _saveTree(Phprojekt_Tree_Node_Database $node, array $params, $parentId = null)
    {
        $node->setup();

        if (null === $parentId) {
            $parentId = $node->getParentNode()->id;
        }

        $parentNode = new Phprojekt_Tree_Node_Database($node->getActiveRecord(), $parentId);
        $parentNode->setup();

        // Assign the values
        foreach ($params as $k => $v) {
            if (isset($node->$k)) {
                $node->$k = $v;
            }
        }

        if (empty($node->getActiveRecord()->id)) {
            $newItem = true;
        } else {
            $newItem = false;
        }

        // Set the owner
        if ($newItem && isset($node->ownerId)) {
            $node->ownerId = Phprojekt_Auth::getUserId();
        }

        // Parent Project
        if (!isset($node->projectId) || null === $node->projectId) {
            $node->projectId = 1;
        }
        $projectId = $node->projectId;

        // Checks
        if (!$node->getActiveRecord()->recordValidate()) {
            $error = array_pop($node->getActiveRecord()->getError());
            throw new Phprojekt_PublishedException($error['label'] . ': ' . $error['message']);
        } else if (!self::_checkModule(1, $projectId)) {
            throw new Phprojekt_PublishedException('You do not have access to add projects on the parent project');
        } else if (!self::_checkItemRights($node->getActiveRecord(), 'Project')) {
            throw new Phprojekt_PublishedException('You do not have access for do this action');
        } else {
            if (null === $node->id || $node->id == 0) {
                $parentNode->appendNode($node);
            } else {
                $node->projectId = 0;
                $node->setParentNode($parentNode);
            }

            $rights = Default_Helpers_Right::getRights($params, $newItem);

            if (count($rights) > 0) {
                $node->getActiveRecord()->saveRights($rights);
            }

            // Save the module-project relation
            if (isset($params['moduleRelation'])) {
                if (!isset($params['checkModuleRelation'])) {
                    $params['checkModuleRelation'] = array();
                }
                $node->getActiveRecord()->saveModules(array_keys($params['checkModuleRelation']));
            }

            // Save the role-user-project relation
            if (isset($params['userRelation'])) {
                $model = Phprojekt_Loader::getModel('Project', 'ProjectRoleUserPermissions');
                $model->saveRelation($params['roleRelation'],
                                     array_keys($params['userRelation']),
                                     $node->getActiveRecord()->id);
            }

            return $node->getActiveRecord();
        }
    }

    /**
     * Help to save a model by setting the models properties.
     * Validation is based on the ModelInformation implementation
     *
     * @param Phprojekt_Model_Interface $model  The model
     * @param array                     $params The parameters used to feed the model
     *
     * @throws Exception
     *
     * @return boolean
     */
    protected static function _saveModel(Phprojekt_Model_Interface $model, array $params)
    {
        foreach ($params as $k => $v) {
            if (isset($model->$k)) {
                // Don't allow to set the id on save, since it is done by the ActiveRecord
                if (!in_array($k, array('id'))) {
                    $model->$k = $v;
                }
            }
        }

        if (empty($model->id)) {
            $newItem = true;
        } else {
            $newItem = false;
        }

        // Set the owner
        if ($newItem && isset($model->ownerId)) {
            $model->ownerId = Phprojekt_Auth::getUserId();
        }

        // Parent Project
        if (isset($model->projectId)) {
            $projectId = $model->projectId;
        } else {
            $projectId = 0;
        }

        // Checks
        $moduleName = Phprojekt_Loader::getModuleFromObject($model);
        if (!$model->recordValidate()) {
            $error = array_pop($model->getError());
            throw new Phprojekt_PublishedException($error['label'] . ': ' . $error['message']);
        } else if (!self::_checkModule(Phprojekt_Module::getId($moduleName), $projectId)) {
            throw new Phprojekt_PublishedException('The parent project do not have enabled this module');
        } else if (!self::_checkItemRights($model, $moduleName)) {
            throw new Phprojekt_PublishedException('You do not have access for do this action');
        } else {
            $model->save();

            $rights = Default_Helpers_Right::getRights($params, $newItem);

            if (count($rights) > 0) {
                $model->saveRights($rights);
            }

            return $model;
        }
    }

    /**
     * Overwrite call to support multiple save routines
     *
     * @param string $name
     * @param array  $arguments
     * @throws Exception If validation of parameters fails
     *
     * @return void
     */
    public static function save()
    {
        $arguments = func_get_args();
        $model     = $arguments[0];
        $params    = $arguments[1];

        if (func_num_args() < 2) {
            throw new Phprojekt_PublishedException('Two arguments expected');
        }

        if (!is_array($params)) {
            throw new Phprojekt_PublishedException('Second parameter needs to be an array');
        }

        if ($model instanceof Phprojekt_Tree_Node_Database) {
            if (func_num_args() == 3) {
                $parentId = $arguments[2];
            } else if (array_key_exists('projectId', $params)) {
                $parentId = $params['projectId'];
            } else {
                throw new Phprojekt_PublishedException('No parent id found in parameters or passed');
            }

            $return = self::_saveTree($model, $params, $parentId);

            // Send mail notification?
            if (array_key_exists('sendNotification', $params)) {
                if ($params['sendNotification'] == 'on') {
                    $mail = new Phprojekt_Mail_Notification('UTF-8');
                    $mail->sendNotificationText($model->getActiveRecord());
                }
            }

            return $return;
        }

        if ($model instanceof Phprojekt_Model_Interface) {
            $return = self::_saveModel($model, $params);

            // Send mail notification?
            if (array_key_exists('sendNotification', $params)) {
                if ($params['sendNotification'] == 'on') {
                    $mail = new Phprojekt_Mail_Notification('UTF-8');
                    $mail->sendNotificationText($model);
                }
            }

            return $return;

        }

        return true;
    }

    /**
     * Check if the parent project has this module enabled
     *
     * @param integer $projectId The project Id to check
     *
     * @return boolean
     */
    private function _checkModule($moduleId, $projectId)
    {
        $boolean = false;
        if ($projectId > 0) {
            if ($projectId == 1 && Phprojekt_Module::getSaveType($moduleId) > 0) {
                $boolean = true;
            } else {
                if (Phprojekt_Module::getSaveType($moduleId) > 0) {
                    $boolean = true;
                } else {
                    $relation = Phprojekt_Loader::getModel('Project', 'ProjectModulePermissions');
                    $modules  = $relation->getProjectModulePermissionsById($projectId);
                    if ($modules['data'][$moduleId]['inProject']) {
                        $boolean = true;
                    } else {
                        $boolean = false;
                    }
                }
            }
        } else {
            $boolean = true;
        }
        return $boolean;
    }

    /**
     * Check if the user has write access to the item if is not a global module
     *     *
     * @param Phprojekt_Model_Interface $model      The model to save
     * @param string                    $moduleName The current module
     *
     * @return boolean
     */
    private function _checkItemRights($model, $moduleName)
    {
        $canWrite = false;

        if ($moduleName == 'Core') {
            return Phprojekt_Auth::isAdminUser();
        } else if (Phprojekt_Module::getSaveType(Phprojekt_Module::getId($moduleName)) == 0) {
            $itemRights = $model->getRights();

            if (isset($itemRights['currentUser'])) {
                if (!$itemRights['currentUser']['write']  &&
                    !$itemRights['currentUser']['create'] &&
                    !$itemRights['currentUser']['copy']   &&
                    !$itemRights['currentUser']['admin']) {
                    $canWrite = false;
                } else {
                    $canWrite = true;
                }
            }
        } else {
            $canWrite = true;
        }

        return $canWrite;
    }
}
