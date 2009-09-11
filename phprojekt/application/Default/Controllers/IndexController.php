<?php
/**
 * Default Controller for PHProjekt 6
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
 * @author     David Soria Parra <soria_parra@mayflower.de>
 * @package    PHProjekt
 * @subpackage Default
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Default Controller for PHProjekt 6
 *
 * <pre>
 * The controller gets an action and runs the nessesary stuff for it.
 * The actions whose name starts with "json", returns the data in JSON format.
 * The actions whose name starts with csv, returns the data in CSV format.
 * The controller calls the class model of the module, for process all the data.
 * </pre>
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @subpackage Default
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class IndexController extends Zend_Controller_Action
{
    /**
     * String to use on success in the action save for new items.
     */
    const ADD_TRUE_TEXT = "The Item was added correctly";

    /**
     * String to use on success in the action save for existing items.
     */
    const EDIT_TRUE_TEXT = "The Item was edited correctly";

    /**
     * String to use on success in the action save for many items together.
     */
    const EDIT_MULTIPLE_TRUE_TEXT = "The Items were edited correctly";

    /**
     * String to use on success in the action delete.
     */
    const DELETE_TRUE_TEXT = "The Item was deleted correctly";

    /**
     * String to use on error in the action delete.
     */
    const DELETE_FALSE_TEXT = "The Item can't be deleted";

    /**
     * String to use on success in the action delete for many items together.
     */
    const DELETE_MULTIPLE_TRUE_TEXT = "The Items were deleted correctly";

    /**
     * String for use if the item don't exists.
     */
    const NOT_FOUND = "The Item was not found";

    /**
     * String for use if the id is not in the request parameters.
     */
    const ID_REQUIRED_TEXT = "ID parameter required";

    /**
     * Internal number for the root project.
     */
    const INVISIBLE_ROOT = 1;

    /**
     * The link will be executed using a normal POST and ajax.
     */
    const MODE_ACTION_XHR = 0;

    /**
     * The link will be executed in a new windows as a normal GET.
     */
    const MODE_ACTION_WINDOW = 1;

    /**
     * The action is for one id,
     * used in the grid for one row.
     */
    const TARGET_ACTION_SINGLE = 0;

    /**
     * The action is for multiple ids,
     * used in the selectbox of the grid for all the checked rows.
     */
    const TARGET_ACTION_MULTIPLE = 1;

    /**
     * Init function
     *
     * Checks if it is a logged user, if not,
     * is redirected to the login form or throws an exception.
     *
     * The function sets up the helper and cleans all the variables.
     *
     * @throws Phprojekt_PublishedException If the user is not logged in and the request is a POST.
     *
     * @return void
     */
    public function init()
    {
        $isLoggedIn = true;
        try {
            Phprojekt_Auth::isLoggedIn();
        } catch (Phprojekt_Auth_UserNotLoggedInException $error) {
            // User not logged in, display login page
            // If is a GET, show the index page with isLogged false
            // If is a POST, send message in json format
            if (!$this->getFrontController()->getRequest()->isGet()) {
                throw new Phprojekt_PublishedException($error->message, 500);
            }
            $isLoggedIn = false;
        }

        // This is a work around as we cannot set this in the front
        $this->_helper->viewRenderer->setNoRender();
        $this->view->clearVars();
        $this->view->isLoggedIn = $isLoggedIn;
    }

    /**
     * Standard action
     *
     * The function sets up the template index.phtml and renders it.
     *
     * @return void
     */
    public function indexAction()
    {
        $language = Phprojekt_User_User::getSetting("language", Phprojekt::getInstance()->getConfig()->language);

        $this->view->webpath        = Phprojekt::getInstance()->getConfig()->webpath;
        $this->view->language       = $language;
        $this->view->compressedDojo = (bool) Phprojekt::getInstance()->getConfig()->compressedDojo;

        $this->render('index');
    }

    /**
     * Gets the class model of the module or the default one.
     *
     * @return Phprojekt_Model_Interface
     */
    public function getModelObject()
    {
        $moduleName = $this->getRequest()->getModuleName();
        $object     = Phprojekt_Loader::getModel($moduleName, $moduleName);
        if (null === $object) {
            $object = Phprojekt_Loader::getModel('Default', 'Default');
        }

        return $object;
    }

    /**
     * Sets some values depending on the parameters.
     * Each module can implement this function to change their values.
     *
     * The function needs at least one parameter
     * (The array of parameters itself for return it).
     *
     * @throws Phprojekt_PublishedException If the arguments are missing.
     *
     * @return array
     */
    public function setParams()
    {
        $args = func_get_args();

        if (1 > count($args)) {
            throw new Phprojekt_PublishedException('Missing arguments in setParams function');
        }

        return $args[0];
    }

    /**
     * Returns the project tree.
     *
     * The return is a tree compatible format, with identifier, label,
     * and the list of items, each one with the name, id, parent, path and children´s id.
     *
     * The tree is stored as a file until a user add, edit or delete a project
     * (tmp/ZendCache/zend_cache---Phprojekt_Tree_Node_Database_setup).
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonTreeAction()
    {
        $model = Phprojekt_Loader::getModel('Project', 'Project');
        $tree  = new Phprojekt_Tree_Node_Database($model, 1);

        Phprojekt_Converter_Json::echoConvert($tree->setup());
    }

    /**
     * Returns the list of items for one model.
     *
     * The return have:
     *  - The metadata of each field.
     *  - The data of all the rows.
     *  - The number of rows.
     *
     * The function use Phprojekt_ModelInformation_Default::ORDERING_LIST for get and sort the fields.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - integer <b>id</b>     List only this id.
     *  - integer <b>nodeId</b> List all the items with projectId == nodeId.
     *  - integer <b>count</b>  Use for SQL LIMIT count.
     *  - integer <b>offset</b> Use for SQL LIMIT offset.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonListAction()
    {
        $itemId    = (int) $this->getRequest()->getParam('id', null);
        $projectId = (int) $this->getRequest()->getParam('nodeId', null);
        $count     = (int) $this->getRequest()->getParam('count', null);
        $offset    = (int) $this->getRequest()->getParam('start', null);

        if (!empty($itemId)) {
            $where = sprintf('id = %d', (int) $itemId);
        } else if (!empty($projectId)) {
            $where = sprintf('project_id = %d', (int) $projectId);
        } else {
            $where = null;
        }
        $records = $this->getModelObject()->fetchAll($where, null, $count, $offset);

        Phprojekt_Converter_Json::echoConvert($records, Phprojekt_ModelInformation_Default::ORDERING_LIST);
    }

    /**
     * Returns the detail (fields and data) of one item from the model.
     *
     * The return have:
     *  - The metadata of each field.
     *  - The data of one item.
     *  - The number of rows.
     *
     * If the request parameter "id" is null or 0, the data will be all values of a "new item",
     * if the "id" is an existing item, the data will be all the values of the item.
     *
     * The function use Phprojekt_ModelInformation_Default::ORDERING_FORM for get and sort the fields.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - integer <b>id</b> id of the item to consult.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonDetailAction()
    {
        $id = (int) $this->getRequest()->getParam('id');

        if (empty($id)) {
            $record = $this->getModelObject();
        } else {
            $record = $this->getModelObject()->find($id);
        }

        Phprojekt_Converter_Json::echoConvert($record, Phprojekt_ModelInformation_Default::ORDERING_FORM);
    }

    /**
     * Saves the current item.
     *
     * If the request parameter "id" is null or 0, the function will add a new item,
     * if the "id" is an existing item, the function will update it.
     *
     * If there is an error, the save will return a Phprojekt_PublishedException,
     * if not, it returns a string in JSON format with:
     * <pre>
     *  - type    => 'success'.
     *  - message => Success message.
     *  - code    => 0 by Default.
     *  - id      => Id of the item.
     * </pre>
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - integer <b>id</b>                      id of the item to save.
     *  - mixed   <b>all other module fields</b> All the fields values to save.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @throws Phprojekt_PublishedException On error in the action save or wrong id.
     *
     * @return void
     */
    public function jsonSaveAction()
    {
        $id = (int) $this->getRequest()->getParam('id');

        if (empty($id)) {
            $model   = $this->getModelObject();
            $message = Phprojekt::getInstance()->translate(self::ADD_TRUE_TEXT);
            $newItem = true;
        } else {
            $model   = $this->getModelObject()->find($id);
            $message = Phprojekt::getInstance()->translate(self::EDIT_TRUE_TEXT);
            $newItem = false;
        }

        if ($model instanceof Phprojekt_Model_Interface) {
            $params = $this->setParams($this->getRequest()->getParams(), $model, $newItem);
            Default_Helpers_Save::save($model, $params);

            $return = array('type'    => 'success',
                            'message' => $message,
                            'code'    => 0,
                            'id'      => $model->id);

            Phprojekt_Converter_Json::echoConvert($return);
        } else {
            throw new Phprojekt_PublishedException(self::NOT_FOUND);
        }
    }

    /**
     * Save some fields for many items.
     * Only edit existing items.
     *
     * The return is a string in JSON format with:
     * <pre>
     *  - type    => 'success' or 'error'.
     *  - message => Success or error message.
     *  - code    => 0 by Default.
     *  - id      => Comma separated ids of the items.
     * </pre>
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - array <b>data</b> Array with itemId and field as index, and the value.
     *    ($data[2]['title'] = 'new tittle')
     * </pre>
     *
     * The return is in JSON format.
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
            $model->find((int) $id);
            $params = $this->setParams($fields, $model);
            try {
                Default_Helpers_Save::save($model, $params);
                $showId[] = $id;
            } catch (Phprojekt_PublishedException $error) {
                $message = sprintf("ID %d. %s", $id, $error->getMessage());
                $success = false;
                $showId  = array($id);
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
     * Deletes a certain item
     *
     * The return is a string in JSON format with:
     * <pre>
     *  - type    => 'success' or 'error'.
     *  - message => Success or error message.
     *  - code    => 0 by Default.
     *  - id      => id of the deleted item.
     * </pre>
     *
     * REQUIRES request parameters:
     * <pre>
     *  - integer <b>id</b> id of the item to delete.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @throws Phprojekt_PublishedException On missing or wrong id, or on error in the action delete.
     *
     * @return void
     */
    public function jsonDeleteAction()
    {
        $id = (int) $this->getRequest()->getParam('id');

        if (empty($id)) {
            throw new Phprojekt_PublishedException(self::ID_REQUIRED_TEXT);
        }

        $model = $this->getModelObject()->find($id);

        if ($model instanceof Phprojekt_Model_Interface) {
            $tmp = Default_Helpers_Delete::delete($model);
            if ($tmp === false) {
                $message    = Phprojekt::getInstance()->translate(self::DELETE_FALSE_TEXT);
                $resultType = 'error';
            } else {
                $message    = Phprojekt::getInstance()->translate(self::DELETE_TRUE_TEXT);
                $resultType = 'success';
            }
            $return = array('type'    => $resultType,
                            'message' => $message,
                            'code'    => 0,
                            'id'      => $id);

            Phprojekt_Converter_Json::echoConvert($return);
        } else {
            throw new Phprojekt_PublishedException(self::NOT_FOUND);
        }
    }

    /**
     * Deletes many items together
     *
     * If there is an error, the delete will return a Phprojekt_PublishedException,
     * if not, it returns a string in JSON format with:
     * <pre>
     *  - type    => 'success'.
     *  - message => Success message.
     *  - code    => 0 by Default.
     *  - id      => Comma separated ids of the items.
     * </pre>
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - string <b>ids</b> Comma separated ids of the item to delete.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @throws Phprojekt_PublishedException On error in the action delete.
     *
     * @return void
     */
    public function jsonDeleteMultipleAction()
    {
        $ids = $this->getRequest()->getParam('ids');

        if (!empty($ids)) {
            $message  = Phprojekt::getInstance()->translate(self::DELETE_MULTIPLE_TRUE_TEXT);
            $showId   = array();
            $model    = $this->getModelObject();
            $idsArray = explode(",", $ids);

            foreach ($idsArray as $id) {
                $model->find((int) $id);
                Default_Helpers_Delete::delete($model);
                $showId[] = $id;
            }

            $return = array('type'    => 'success',
                            'message' => $message,
                            'code'    => 0,
                            'id'      => implode(',', $showId));

            Phprojekt_Converter_Json::echoConvert($return);
        }
    }

    /**
     * Returns project-module && user-role-project permissions.
     *
     * Returns the permissions,
     * ("none", "read", "write", "access", "create", "copy", "delete", "download", "admin")
     * for each module that have the project,
     * for the current logged user,
     * depending on their role and access, in the project.
     *
     * REQUIRES request parameters:
     * <pre>
     *  - integer <b>nodeId</b> The projectId for consult.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonGetModulesPermissionAction()
    {
        $projectId = (int) $this->getRequest()->getParam('nodeId');
        $relation  = Phprojekt_Loader::getModel('Project', 'ProjectModulePermissions');
        $modules   = $relation->getProjectModulePermissionsById($projectId);

        if ($projectId == 0) {
            $data = array(); // there is no rights on invalid projects
        } else {
            $allowedModules = array();
            $rights         = new Phprojekt_RoleRights($projectId);
            foreach ($modules['data'] as $module) {

                if ($module['inProject']) {
                    $tmpPermission = Phprojekt_Acl::NONE;
                    if ($rights->hasRight('admin', $module['id'])) {
                        $tmpPermission = $tmpPermission | Phprojekt_Acl::ADMIN;
                    }
                    if ($rights->hasRight('create', $module['id'])) {
                        $tmpPermission = $tmpPermission | Phprojekt_Acl::CREATE;
                    }
                    if ($rights->hasRight('write', $module['id'])) {
                        $tmpPermission = $tmpPermission | Phprojekt_Acl::WRITE;
                    }
                    if ($rights->hasRight('read', $module['id'])) {
                        $tmpPermission = $tmpPermission | Phprojekt_Acl::READ;
                    }
                    $module['rights'] = Phprojekt_Acl::convertBitmaskToArray($tmpPermission);
                    $allowedModules[] = $module;
                }
            }
            $data = $allowedModules;
        }

        Phprojekt_Converter_Json::echoConvert($data);
    }

    /**
     * Returns all the words translated in each modules for the request language.
     *
     * REQUIRES request parameters:
     * <pre>
     *  - string <b>language</b> The current language for get the translations.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonGetTranslatedStringsAction()
    {
        $language  = Cleaner::sanitize('alpha', $this->getRequest()->getParam('language', 'en'));
        $translate = Phprojekt::getInstance()->getTranslate();

        Phprojekt_Converter_Json::echoConvert($translate->getTranslatedStrings($language));
    }

    /**
     * Returns the front configurations from the configuration.ini (front.xxx),
     * and some others Core Settings.
     *
     * The return is an array like ('name' => varName, 'value' => varValue')
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonGetConfigurationsAction()
    {
        $fronVars = Phprojekt::getInstance()->getConfig()->front;
        $data     = array();
        if (null !== $fronVars) {
            foreach ($fronVars as $key => $value) {
                $data[] = array('name'  => $key,
                                'value' => $value);
            }
        }
        $data[] = array('name'  => 'phprojektVersion',
                        'value' => Phprojekt::getVersion());

        Phprojekt_Converter_Json::echoConvert($data);
    }

    /**
     * Returns the possible extra actions to perform for multiple or singles ids.
     *
     * Each action defines in the array:
     * <pre>
     *  - target: {@link TARGET_ACTION_MULTIPLE} or {@link TARGET_ACTION_SIMPLE}.
     *  - action: Name of the action that will process ids.
     *  - label:  Display for the action.
     *  - mode:   {@link MODE_ACTION_XHR} or {@link MODE_ACTION_WINDOW}.
     *  - class:  Name of the class for display the icon.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonGetExtraActionsAction()
    {
        $delete = array('target' => self::TARGET_ACTION_MULTIPLE,
                        'action' => 'jsonDeleteMultiple',
                        'label'  => Phprojekt::getInstance()->translate('Delete'),
                        'mode'   => self::MODE_ACTION_XHR,
                        'class'  => 'deleteOption');

        $export = array('target' => self::TARGET_ACTION_MULTIPLE,
                        'action' => 'csvExportMultiple',
                        'label'  => Phprojekt::getInstance()->translate('Export'),
                        'mode'   => self::MODE_ACTION_WINDOW,
                        'class'  => 'exportOption');

        $actions = array($delete, $export);

        Phprojekt_Converter_Json::echoConvert($actions);
    }

    /**
     * Returns the list of items for one model.
     *
     * The function use Phprojekt_ModelInformation_Default::ORDERING_LIST for get and sort the fields.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - integer <b>id</b>     List only this id.
     *  - integer <b>nodeId</b> List all the items with projectId == nodeId.
     * </pre>
     *
     * The return is in CSV format.
     *
     * @return void
     */
    public function csvListAction()
    {
        $projectId = (int) $this->getRequest()->getParam('nodeId', null);
        $itemId    = (int) $this->getRequest()->getParam('id', null);

        if (!empty($itemId)) {
            $where = sprintf('id = %d', (int) $itemId);
        } else if (!empty($projectId)) {
            $where = sprintf('project_id = %d', (int) $projectId);
        } else {
            $where = null;
        }

        $records = $this->getModelObject()->fetchAll($where);

        Phprojekt_Converter_Csv::echoConvert($records, Phprojekt_ModelInformation_Default::ORDERING_LIST);
    }

    /**
     * Returns the list of requested ids items.
     *
     * The function use Phprojekt_ModelInformation_Default::ORDERING_LIST for get and sort the fields.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - string <b>ids</b> Comma separated ids of the item to list.
     * </pre>
     *
     * The return is in CSV format.
     *
     * @return void
     */
    public function csvExportMultipleAction()
    {
        $ids = $this->getRequest()->getParam('ids', null);

        if (!empty($ids)) {
            $idsArray = explode(",", $ids);
            $where    = "id IN (";
            $i        = 0;
            foreach ($idsArray as $id) {
                $i++;
                $where .= (int) $id;
                if ($i < count($idsArray)) {
                    $where .= ", ";
                }
            }
            $where .= ")";

            $records = $this->getModelObject()->fetchAll($where, null, 0, 0);
            Phprojekt_Converter_Csv::echoConvert($records, Phprojekt_ModelInformation_Default::ORDERING_LIST);
        }
    }
}
