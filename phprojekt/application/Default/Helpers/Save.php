<?php
/**
 * Helper to save tree nodes and models.
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
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */

/**
 * Helper to save tree nodes and models.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Default
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
final class Default_Helpers_Save
{
    /**
     * Maps the parameter array to the appropriate model properties.
     *
     * As we are pretty inconsistent in how we do stuff, the model can either
     * be a Phprojekt_Tree_Node_Database or an Phprojekt_Model_Interface.
     *
     * @param mixed   $model   A model
     * @param array   $params  The mysterious parameters array
     * @param boolean $newItem At least something need a special handling,
     *                         so new items get something special.
     */
    protected static function parameterToModel($model, $params, $newItem = false)
    {
        foreach ($params as $k => $v) {
            if (isset($model->$k)) {
                // Don't allow to set the id on save, since it is done by the ActiveRecord
                if ($k !== 'id') {
                    $model->$k = $v;
                }
            }
        }

        if ($newItem && isset($model->ownerId)) {
            $model->ownerId = Phprojekt_Auth_Proxy::getEffectiveUserId();
        }

        return $model;
    }

    /**
     * Save a tree.
     *
     * @param Phprojekt_Tree_Node_Database $node     The project to save.
     * @param array                        $params   The parameters used to feed the model.
     * @param integer|null                 $parentId The parent ID.
     *
     * @throws Exception If validation of parameters fails.
     *
     * @return boolean True for a sucessful save.
     */
    protected static function _saveTree(Phprojekt_Tree_Node_Database $node, array $params, $parentId = null)
    {
        $node = $node->setup();

        if (null === $parentId) {
            $parentId = $node->getParentNode()->id;
        }

        $parentNode = new Phprojekt_Tree_Node_Database($node->getActiveRecord(), $parentId);
        $parentNode = $parentNode->setup();

        $node = self::parameterToModel($node, $params);

        if (empty($node->getActiveRecord()->id)) {
            $newItem = true;
        } else {
            $newItem = false;
        }

        // Parent Project
        if (!isset($node->projectId) || null === $node->projectId) {
            $node->projectId = 1;
        }

        if (!$node->getActiveRecord()->recordValidate()) {
            $errors = $node->getActiveRecord()->getError();
            $error  = array_pop($errors);
            throw new Phprojekt_PublishedException($error['label'] . ': ' . $error['message']);
        }

        if (!self::_checkModule(1, $node->projectId)) {
            throw new Phprojekt_PublishedException(
                'You do not have access to add projects on the parent project');
        }

        $userId = Phprojekt_Auth_Proxy::getEffectiveUserId();
        $model  = $node->getActiveRecord();
        $rights = Default_Helpers_Right::getRights($params);
        if ($newItem) {
            if (!$parentNode->getActiveRecord()->hasRight($userId, Phprojekt_Acl::CREATE)) {
                throw new Phprojekt_PublishedException(
                    'You do not have the necessary create right');
            }
            $rights[$userId] = Phprojekt_Acl::ALL;
            $parentNode->appendNode($node);
        } else if (!$model->hasRight($userId, Phprojekt_Acl::WRITE, $model->id)) {
            throw new Phprojekt_PublishedException(
                'You do not have the necessary write right');
        }

        if ($newItem || $model->hasRight($userId, Phprojekt_Acl::ADMIN, $model->id)) {
            /* ensure we have at least one right */
            if (count($rights) <= 0) {
                throw new Phprojekt_PublishedException(
                    'At least one person must have access to this item');
            }

            $model->saveRights($rights);
            // Save the module-project relation
            if (isset($params['moduleRelation'])) {
                if (!isset($params['checkModuleRelation'])) {
                    $params['checkModuleRelation'] = array();
                }
                $saveModules = array();
                foreach ($params['checkModuleRelation'] as $checkModule => $checkValue) {
                    if ($checkValue == 1) {
                        $saveModules[] = $checkModule;
                    }
                }
                $model->saveModules($saveModules);
            }

            // Save the role-user-project relation
            if (isset($params['userRelation'])) {
                $pru = new Project_Models_ProjectRoleUserPermissions();
                $pru->saveRelation($params['roleRelation'], array_keys($params['userRelation']),
                    $node->getActiveRecord()->id);
            }
        }

        return $model;
    }

    /**
     * Help to save a model by setting the models properties.
     * Validation is based on the ModelInformation implementation.
     *
     * @param Phprojekt_Model_Interface $model  The model
     * @param array                     $params The parameters used to feed the model.
     *
     * @throws Exception If validation of parameters fails.
     *
     * @return boolean True for a sucessful save.
     */
    protected static function _saveModel(Phprojekt_Model_Interface $model, array $params)
    {
        $newItem    = empty($params['id']);
        $model      = self::parameterToModel($model, $params, $newItem);
        $projectId  = (isset($model->projectId)) ? $model->projectId : 0;
        $userId     = Phprojekt_Auth_Proxy::getEffectiveUserId();
        $moduleName = Phprojekt_Loader::getModuleFromObject($model);
        $moduleId   = Phprojekt_Module::getId($moduleName);

        if (!$model->recordValidate()) {
            $errors = $model->getError();
            $error  = array_pop($errors);
            throw new Phprojekt_PublishedException($error['label'] . ': ' . $error['message']);
        }

        if (!self::_checkModule($moduleId, $projectId)) {
            throw new Phprojekt_PublishedException(
                'The parent project do not have enabled this module');
        }

        $rights = Default_Helpers_Right::getRights($params);

        if ($model instanceof Phprojekt_Item_Abstract) {
            if ($newItem) {
                $project = new Project_Models_Project();
                $project->find($projectId);
                if (!$project->hasRight($userId, Phprojekt_Acl::CREATE)) {
                    throw new Phprojekt_PublishedException(
                        'You do not have the necessary create right');
                }
                $rights[$userId] = Phprojekt_Acl::ALL;
            } else if (!$model->hasRight($userId, Phprojekt_Acl::WRITE)) {
                throw new Phprojekt_PublishedException(
                    'You do not have the necessary wrte right');
            }

            // Set the projectId to 1 for global modules
            // @TODO Remove the Timecard limitation
            if (isset($model->projectId) && Phprojekt_Module::saveTypeIsGlobal($moduleId)
                && Phprojekt_Module::getModuleName($moduleId) != 'Timecard') {
                    $model->projectId = 1;
                }

            $model->save();

            // Save access only if the user have "admin" right
            if ($newItem || $model->hasRight(Phprojekt_Auth_Proxy::getEffectiveUserId(), Phprojekt_Acl::ADMIN)) {
                if (count($rights) <= 0) {
                    throw new Phprojekt_PublishedException(
                        'At least one person must have access to this item');
                }
                $model->saveRights($rights);
            }

        } else {
            $model->save();
            $model->saveRights($rights);
        }

        return $model;
    }

    /**
     * Overwrite call to support multiple save routines.
     *
     * @throws Exception If validation of parameters fails.
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

            $returnModel = self::_saveTree($model, $params, $parentId);
            $notifyModel = $model->getActiveRecord();
        } else if ($model instanceof Phprojekt_Model_Interface) {
            $returnModel = self::_saveModel($model, $params);
            $notifyModel = $model;
        }

        if ($notifyModel instanceof Phprojekt_Item_Abstract) {
            if (isset($params['sendNotification']) && $params['sendNotification']) {
                $notifyModel->getNotification()->send(Phprojekt_Notification::TRANSPORT_MAIL_TEXT);
            }

            if (Phprojekt::getInstance()->getConfig()->frontendMessages) {
                $notifyModel->getNotification()->saveFrontendMessage();
            }
        }

        return $model;
    }

    /**
     * Check if the parent project has this module enabled.
     *
     * @param integer $projectId The project ID to check.
     *
     * @return boolean False if not.
     */
    private static function _checkModule($moduleId, $projectId)
    {
        $boolean = false;
        if ($projectId > 0) {
            if ($projectId == 1 && !Phprojekt_Module::saveTypeIsNormal($moduleId)) {
                $boolean = true;
            } else {
                if (!Phprojekt_Module::saveTypeIsNormal($moduleId)) {
                    $boolean = true;
                } else {
                    $relation = new Project_Models_ProjectModulePermissions();
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
}
