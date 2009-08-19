<?php
/**
 * Project Module Controller for PHProjekt 6
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
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Default Project Module Controller for PHProjekt 6
 *
 * For make a indexController for your module
 * just extend it to the IndexController
 * and redefine the function getModelsObject
 * for return the object model that you want
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Project_IndexController extends IndexController
{
    /**
     * Save Action
     *
     * The save is redefined for use with tree in the project module
     *
     * @return void
     */
    public function jsonSaveAction()
    {
        $id = (int) $this->getRequest()->getParam('id');

        if (empty($id)) {
            $model     = $this->getModelObject();
            $model->id = 0;
            $message   = Phprojekt::getInstance()->translate(self::ADD_TRUE_TEXT);
        } else {
            $model   = $this->getModelObject()->find($id);
            $message = Phprojekt::getInstance()->translate(self::EDIT_TRUE_TEXT);
        }
        if ($model instanceof Phprojekt_Model_Interface) {
            $node    = new Phprojekt_Tree_Node_Database($model, $id);
            $newNode = Default_Helpers_Save::save($node, $this->getRequest()->getParams(),
                (int) $this->getRequest()->getParam('projectId', null));

            // Set the id since the Tree save
            // return differents values from insert and update
            if (empty($id)) {
                $showId = $newNode->id;
            } else {
                $showId = $id;
            }

            $return    = array('type'    => 'success',
                               'message' => $message,
                               'code'    => 0,
                               'id'      => $showId);

            Phprojekt_Converter_Json::echoConvert($return);
        } else {
            throw new Phprojekt_PublishedException(self::NOT_FOUND);
        }
    }

    /**
     * Save Multiple items Action
     *
     * The save is redefined for use with tree in the project module
     *
     * @return void
     */
    public function jsonSaveMultipleAction()
    {
        $data    = (array) $this->getRequest()->getParam('data');
        $showId  = array();
        $model   = $this->getModelObject();
        $success = true;

        foreach ($data as $id => $fields) {
            $model->find($id);
            $node = new Phprojekt_Tree_Node_Database($model, $id);
            try {
                $nodeId   = (int) $this->getRequest()->getParam('nodeId', null);
                $newNode  = Default_Helpers_Save::save($node, $fields, $nodeId);
                $showId[] = $newNode->id;
            } catch (Phprojekt_PublishedException $error) {
                $success = false;
                $showId  = Array($id);
                $message = sprintf("ID %d. %s", $id, $error->getMessage());
                break;
            }
        }

        if ($success) {
            $message    = Phprojekt::getInstance()->translate(self::EDIT_MULTIPLE_TRUE_TEXT);
            $resultType = 'success';
        } else {
            $resultType = 'error';
        }

        $return = array('type'    => $resultType,
                        'message' => $message,
                        'code'    => 0,
                        'id'      => implode(',', $showId));

        Phprojekt_Converter_Json::echoConvert($return);
    }

    /**
     * Get all the modules active and their relation with the projectId
     *
     * @requestparam int $id The project Id
     *
     * @return void
     */
    public function jsonGetModulesProjectRelationAction()
    {
        $projectId = (int) $this->getRequest()->getParam('id');
        $project   = Phprojekt_Loader::getModel('Project', 'ProjectModulePermissions');
        $modules   = $project->getProjectModulePermissionsById($projectId);

        Phprojekt_Converter_Json::echoConvert($modules);
    }

    /**
     * Get all the role-user relation with the projectId
     *
     * @requestparam int $id The project Id
     *
     * @return void
     */
    public function jsonGetProjectRoleUserRelationAction()
    {
        $projectId = (int) $this->getRequest()->getParam('id');
        $project   = Phprojekt_Loader::getModel('Project', 'ProjectRoleUserPermissions');
        $roles     = $project->getProjectRoleUserPermissions($projectId);

        Phprojekt_Converter_Json::echoConvert($roles);
    }
}
