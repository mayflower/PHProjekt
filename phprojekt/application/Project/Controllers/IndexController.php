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
 * Project Module Controller.
 */
class Project_IndexController extends IndexController
{
    /**
     * Saves the current project.
     *
     * If the request parameter "id" is null or 0, the function will add a new project,
     * if the "id" is an existing project, the function will update it.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - integer <b>id</b>                      id of the project to save.
     *  - mixed   <b>all other module fields</b> All the fields values to save.
     * </pre>
     *
     * If there is an error, the save will return a Zend_Controller_Action_Exception,
     * if not, it returns a string in JSON format with:
     * <pre>
     *  - type    => 'success'.
     *  - message => Success message.
     *  - id      => Id of the project.
     * </pre>
     *
     * @throws Zend_Controller_Action_Exception On error in the action save or wrong id.
     *
     * @return void
     */
    public function jsonSaveAction()
    {
        $id = (int) $this->getRequest()->getParam('id');
        $this->setCurrentProjectId();

        if (empty($id)) {
            $model   = $this->getModelObject();
            $message = Phprojekt::getInstance()->translate(self::ADD_TRUE_TEXT);
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

            $return = array('type'    => 'success',
                            'message' => $message,
                            'id'      => $showId);

            Phprojekt_Converter_Json::echoConvert($return);
        } else {
            throw new Zend_Controller_Action_Exception(self::NOT_FOUND, 404);
        }
    }

    /**
     * Save some fields for many projects.
     * Only edit existing projects.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - array <b>data</b> Array with projectId and field as index, and the value.
     *    ($data[2]['title'] = 'new tittle')
     * </pre>
     *
     * The return is a string in JSON format with:
     * <pre>
     *  - type    => 'success' or 'error'.
     *  - message => Success or error message.
     *  - id      => Comma separated ids of the projects.
     * </pre>
     *
     * @return void
     */
    public function jsonSaveMultipleAction()
    {
        $data    = (array) $this->getRequest()->getParam('data');
        $showId  = array();
        $model   = $this->getModelObject();
        $success = true;
        $this->setCurrentProjectId();

        foreach ($data as $id => $fields) {
            $model->find($id);
            $node = new Phprojekt_Tree_Node_Database($model, $id);
            try {
                $nodeId   = (int) $this->getRequest()->getParam('nodeId', null);
                $newNode  = Default_Helpers_Save::save($node, $fields, $nodeId);
                $showId[] = $newNode->id;
            } catch (Zend_Controller_Action_Exception $error) {
                $success = false;
                $showId  = array($id);
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
                        'id'      => implode(',', $showId));

        Phprojekt_Converter_Json::echoConvert($return);
    }

    /**
     * Returns all the active modules that have the project.
     *
     * Returns a list of all the modules with:
     * <pre>
     *  - id        => id of the module.
     *  - name      => Name of the module.
     *  - label     => Display for the module.
     *  - inProject => True or false if the project have the module.
     * </pre>
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - integer <b>id</b>     The project id for consult.
     *  - integer <b>nodeId</b> The id of the parent project.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonGetModulesProjectRelationAction()
    {
        $projectId = (int) $this->getRequest()->getParam('id');
        $parentId  = (int) $this->getRequest()->getParam('nodeId');

        // On new entries, get the parent data
        if (empty($projectId)) {
            $projectId = $parentId;
        }

        $project = new Project_Models_ProjectModulePermissions();
        $modules = $project->getProjectModulePermissionsById($projectId);

        Phprojekt_Converter_Json::echoConvert($modules);
    }

    /**
     * Returns all the role-user relation with the project.
     *
     * Returns a list of all the roles related to the users under the project with:
     * <pre>
     *  - id    => id of the role.
     *  - name  => Name of the role.
     *  - users => id and display of the users with the role.
     * </pre>
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - integer <b>id</b>     The project id for consult.
     *  - integer <b>nodeId</b> The id of the parent project.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonGetProjectRoleUserRelationAction()
    {
        $projectId = (int) $this->getRequest()->getParam('id');
        $parentId  = (int) $this->getRequest()->getParam('nodeId');

        // On new entries, get the parent data
        if (empty($projectId)) {
            $projectId = $parentId;
        }

        $project = new Project_Models_ProjectRoleUserPermissions();
        $roles   = $project->getProjectRoleUserPermissions($projectId);

        Phprojekt_Converter_Json::echoConvert($roles);
    }
}
