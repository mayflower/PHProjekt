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
 * Default Controller.
 *
 * <pre>
 * The controller gets an action and runs the nessesary stuff for it.
 * The actions whose name starts with "json", returns the data in JSON format.
 * The actions whose name starts with csv, returns the data in CSV format.
 * The controller calls the class model of the module, for process all the data.
 * </pre>
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
     * String for use if the nodeId is not in the request parameters.
     */
    const NODEID_REQUIRED_TEXT = "Node Id parameter required";

    /**
     * String for use if the nodeId is not in the request parameters.
     */
    const PROJECTID_REQUIRED_TEXT = "projectId parameter required";

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
     * The action will be executed in the client.
     */
    const MODE_ACTION_CLIENT = 2;

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
     * String to use on success in the action disableFrontendMessages.
     */
    const DISABLE_FRONTEND_MESSAGES_TRUE_TEXT = "All settings were disabled successfully!";

    /**
     * String to use on error in the action disableFrontendMessages.
     */
    const DISABLE_FRONTEND_MESSAGES_FALSE_TEXT = "No settings were disabled!";

    /**
     * Initialize our controller and disable the viewRenderer.
     *
     * Try to detect ajax requests and set the format so we can use json templates.
     *
     * @return void
     */
    public function init()
    {
        $this->_helper->viewRenderer->setNoRender();
        // check for ajax request, if ajax, send json, if not, send html page
        if ($this->getRequest()->getHeader('X-Requested-With') === 'XMLHttpRequest') {
            $this->getRequest()->setParam('format', 'json');
        }
    }

    /**
     * Init function.
     *
     * Checks if it is a logged user, if not,
     * is redirected to the login form or throws an exception.
     *
     * The function sets up the helper and cleans all the variables.
     *
     * @throws Zend_Controller_Action_Exception If the user is not logged in and the request is a POST.
     *
     * @return void
     */
    public function preDispatch()
    {
        $this->checkAuthentication();
    }

    /**
     * Check if the user is logged in and everything is fine.
     *
     * @return void
     */
    public function checkAuthentication()
    {
        $isLoggedIn = Phprojekt_Auth::isLoggedIn();
        if ($isLoggedIn) {
            // Check the CSRF token
            $this->checkCsrfToken();
        } else {
            // User not logged in, display login page
            // If is a GET, show the index page with isLogged false
            // If is a POST, send message in json format
            if ($this->getRequest()->getActionName() == 'index') {
                $isLoggedIn = false;
                if ($this->getRequest()->getModuleName() != 'Default') {
                    $this->_forward('index', 'Default', 'Default', null);
                }
            } else {
                $this->getResponse()->setRawHeader('HTTP/1.1 401 Authorization Required');
                $this->getResponse()->sendHeaders();
                exit;
            }
        }

        $this->view->clearVars();
        $this->view->isLoggedIn = $isLoggedIn;

       // Setting the domain selection
       $authMode = Phprojekt_Auth::getLoginMode();
       if ($authMode == 'ldap') {
           $conf = Phprojekt::getInstance()->getConfig();
           $ldapOptions = isset($conf->authentication->ldap) ? $conf->authentication->ldap->toArray() : array();
           $domains = array();
           foreach ($ldapOptions as $server => $opts) {
               $serverName = isset($opts['accountDomainNameShort']) ? trim($opts['accountDomainNameShort']) :
                               (isset($opts['accountDomainName']) ? trim($opts['accountDomainName']) : $server);
               $domains[$server] = $serverName;
           }
           if (sizeof($domains) > 0) {
               $this->view->domains = $domains;
           }
       }
    }

    /**
     * Check if a token is in the SESSION.
     *
     * @return boolean True for a valid one.
     */
    public function checkCsrfToken()
    {
        $error      = false;
        $controller = $this->getRequest()->getControllerName();
        $action     = $this->getRequest()->getActionName();

        // Skip initial request
        // and jsonGetConfigurations since is the action that returns
        // the valid token for use in the next request
        if ($controller == 'index' || $controller == 'Upgrade') {
            if ($action == 'index' || $action == 'jsonGetConfigurations') {
                return true;
            }
        }

        $sessionName   = 'Phprojekt_CsrfToken';
        $csrfNamespace = new Zend_Session_Namespace($sessionName);

        if (!isset($csrfNamespace->token)) {
            $error = true;
        }

        $token = (string) $this->getRequest()->getParam('csrfToken', null);
        if (null === $token) {
            $error = true;
        }

        if (!$csrfNamespace->token == $token) {
            $error = true;
        }

        if ($error) {
            $this->getResponse()->setRawHeader('HTTP/1.1 403 Forbidden');
        }

        return true;
    }

    /**
     * Standard action.
     *
     * The function sets up the template index.phtml and renders it.
     *
     * @return void
     */
    public function indexAction()
    {
        $language = Phprojekt_Auth::getRealUser()->getSetting(
            "language",
            Phprojekt::getInstance()->getConfig()->language
        );

        $this->view->language       = $language;
        $this->view->compressedDojo = (bool) Phprojekt::getInstance()->getConfig()->compressedDojo;
        $this->view->frontendMsg    = (bool) Phprojekt::getInstance()->getConfig()->frontendMessages;

        // Since the time for re-starting a poll to the server is in milliseconds, a multiple of 1000 is needed here.
        $this->view->pollingLoop = Phprojekt::getInstance()->getConfig()->pollingLoop * 1000;
        if (Phprojekt_Auth::isLoggedIn()) {
            $this->render('index');
        } else {
            $this->render('login');
        }
    }

    /**
     * Return the model name for construct the class.
     *
     * @return string The path to the model in the class format.
     */
    public function getModelName()
    {
        return $this->getRequest()->getModuleName();
    }

    /**
     * Return the module name for construct the class.
     *
     * @return string The module name.
     */
    public function getModuleName()
    {
        return $this->getRequest()->getModuleName();
    }

    /**
     * Gets the class model of the module or the default one.
     *
     * @return Phprojekt_Model_Interface An instance of Phprojekt_Model_Interface.
     */
    public function getModelObject()
    {
        $modelName  = $this->getModelName();
        $moduleName = $this->getModuleName();
        $object     = Phprojekt_Loader::getModel($modelName, $moduleName);
        if (null === $object) {
            throw new Exception('No Model object could be found');
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
     * @throws Zend_Controller_Action_Exception If the arguments are missing.
     *
     * @return array
     */
    public function setParams()
    {
        $args = func_get_args();

        if (1 > count($args)) {
            throw new InvalidArgumentException('Missing arguments in setParams function');
        }

        return $args[0];
    }

    /**
     * Keep in the registry the current project id.
     * Deprecated, do not use.
     *
     * @return void
     */
    public function setCurrentProjectId()
    {
        $projectId = (int) $this->getRequest()->getParam("nodeId");

        if (empty($projectId)) {
            throw new Zend_Controller_Action_Exception(self::NODEID_REQUIRED_TEXT, 400);
        } else {
            Phprojekt::setCurrentProjectId($projectId);
        }
    }

    protected function _saveModel(Phprojekt_Model_Interface $model, $params, $newItem)
    {
        Default_Helpers_Save::save($model, $params);
    }

    /**
     * Keeps the project id in zend registry or reports an error if an empty value is supplied.
     *
     * @param int $projectId The project id to store.
     *
     * @return void
     */
    protected function _storeCurrentProjectId($projectId)
    {
        if (empty($projectId)) {
            throw new Zend_Controller_Action_Exception(self::PROJECTID_REQUIRED_TEXT, 400);
        } else {
            Phprojekt::setCurrentProjectId($projectId);
        }
    }

    /**
     * Add to the internal where, the filters set by the user.
     *
     * @param string $where Internal where clause.
     *
     * @return string Where clause.
     */
    public function getFilterWhere($where = null)
    {
        $filters = $this->getRequest()->getParam('filters', "[]");

        $filters = json_decode($filters);

        if (!empty($filters)) {
            $filterClass = new Phprojekt_Filter($this->getModelObject(), $where);
            foreach ($filters as $filter) {
                list($filterOperator, $filterField, $filterRule, $filterValue) = $filter;
                $filterOperator = Cleaner::sanitize('alpha', $filterOperator, null);
                $filterField    = Cleaner::sanitize('alpha', $filterField, null);
                $filterRule     = Cleaner::sanitize('alpha', $filterRule, null);
                if (isset($filterOperator) && isset($filterField) &&  isset($filterRule) && isset($filterValue)) {
                    $filterClass->addFilter($filterField, $filterRule, $filterValue, $filterOperator);
                }
            }
            $where = $filterClass->getWhere();
        }

        return $where;
    }

    /**
     * Returns the default extra actions to perform for multiple or singles ids.
     * (Delete and Export)
     *
     * Each action defines in the array:
     * <pre>
     *  - action: Name of the action that will process ids.
     *  - label:  Display for the action.
     *  - class:  Name of the class for display the icon.
     * </pre>
     *
     * @return array Array with 'target', 'action', 'label', 'mode' and 'class'.
     */
    public function getDefaultExtraActions()
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

        return array($delete, $export);
    }

    /**
     * Returns the project tree.
     *
     * The return is a tree compatible format, with identifier, label,
     * and the list of items, each one with the name, id, parent, path and children�s id.
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
        $model = new Project_Models_Project();
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
     *  - boolean <b>recursive</b> Include items of subprojects.
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
        $recursive = $this->getRequest()->getParam('recursive', 'false');
        $this->setCurrentProjectId();

        if (!empty($itemId)) {
            $where = sprintf('id = %d', (int) $itemId);
        } else if (!empty($projectId) && $this->getModelObject()->hasField('projectId')) {
            $where  = sprintf('project_id = %d', (int) $projectId);
        } else {
            $where = null;
        }

        /* recursive is only supported if nodeId is specified */
        if (!empty($projectId) && $this->getModelObject()->hasField('projectId')
            && 'true' === $recursive) {
            $tree = new Phprojekt_Tree_Node_Database(
                new Project_Models_Project(),
                $projectId);
            $tree->setup();
            Phprojekt_Converter_Json::echoConvert(
                $tree->getRecordsFor($this->getModelObject(), null, null, $this->getFilterWhere()),
                Phprojekt_ModelInformation_Default::ORDERING_LIST);
        } else  {
            $where   = $this->getFilterWhere($where);
            $records = $this->getModelObject()->fetchAll($where, null, $count, $offset);

            Phprojekt_Converter_Json::echoConvert($records, Phprojekt_ModelInformation_Default::ORDERING_LIST);
        }
    }

    /**
     * Returns the metadata for this Module's default module.
     *
     * Mandatory parameters:
     *  - integer projectId The id of the project that the metadata should be based on.
     */
    public function metadataAction()
    {
        $projectId = $this->getRequest()->getParam('projectId', null);
        $this->_storeCurrentProjectId($projectId);

        $fieldDefinition  = $this->getModelObject()->getInformation()->getFieldDefinition();
        Phprojekt_CompressedSender::send(
            Zend_Json_Encoder::encode($fieldDefinition)
        );
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
        $this->setCurrentProjectId();

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
     * OPTIONAL request parameters:
     * <pre>
     *  - integer <b>id</b>                      id of the item to save.
     *  - mixed   <b>all other module fields</b> All the fields values to save.
     * </pre>
     *
     * If there is an error, the save will return a Zend_Controller_Action_Exception,
     * if not, it returns a string in JSON format with:
     * <pre>
     *  - type    => 'success'.
     *  - message => Success message.
     *  - id      => Id of the item.
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
            $newItem = true;
        } else {
            $model   = $this->getModelObject()->find($id);
            $message = Phprojekt::getInstance()->translate(self::EDIT_TRUE_TEXT);
            $newItem = false;
        }

        if ($model instanceof Phprojekt_Model_Interface) {
            $params = $this->setParams($this->getRequest()->getParams(), $model, $newItem);
            $this->_saveModel($model, $params, $newItem);

            $return = array('type'    => 'success',
                            'message' => $message,
                            'id'      => $model->id);

            Phprojekt_Converter_Json::echoConvert($return);
        } else {
            throw new Zend_Controller_Action_Exception(self::NOT_FOUND, 404);
        }
    }

    /**
     * Save some fields for many items.
     * Only edit existing items.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - array <b>data</b> Array with itemId and field as index, and the value.
     *    ($data[2]['title'] = 'new tittle')
     * </pre>
     *
     * The return is a string in JSON format with:
     * <pre>
     *  - type    => 'success' or 'error'.
     *  - message => Success or error message.
     *  - id      => Comma separated ids of the items.
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
            $model->find((int) $id);
            $params = $this->setParams($fields, $model);
            try {
                $this->_saveModel($model, $params, false);
                $showId[] = $id;
            } catch (Zend_Controller_Action_Exception $error) {
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
                        'id'      => implode(',', $showId));

        Phprojekt_Converter_Json::echoConvert($return);
    }

    /**
     * Deletes a certain item.
     *
     * REQUIRES request parameters:
     * <pre>
     *  - integer <b>id</b> id of the item to delete.
     * </pre>
     *
     * The return is a string in JSON format with:
     * <pre>
     *  - type    => 'success' or 'error'.
     *  - message => Success or error message.
     *  - id      => id of the deleted item.
     * </pre>
     *
     * @throws Zend_Controller_Action_Exception On missing or wrong id, or on error in the action delete.
     *
     * @return void
     */
    public function jsonDeleteAction()
    {
        $id = (int) $this->getRequest()->getParam('id');

        if (empty($id)) {
            throw new Zend_Controller_Action_Exception(self::ID_REQUIRED_TEXT, 400);
        }

        $model = $this->getModelObject()->find($id);
        if (empty($model)) {
            throw new Zend_Controller_Action_Exception(self::NOT_FOUND, 404);
        }
        if ($model->hasField('projectId')) {
            Phprojekt::setCurrentProjectId($model->projectId);
        }

        if ($model instanceof Phprojekt_ActiveRecord_Abstract) {
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
                            'id'      => $id);

            Phprojekt_Converter_Json::echoConvert($return);
        } else {
            throw new Zend_Controller_Action_Exception(self::NOT_FOUND, 404);
        }
    }

    /**
     * Deletes many items together.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - string <b>ids</b> Comma separated ids of the item to delete.
     * </pre>
     *
     * If there is an error, the delete will return a Zend_Controller_Action_Exception,
     * if not, it returns a string in JSON format with:
     * <pre>
     *  - type    => 'success'.
     *  - message => Success message.
     *  - id      => Comma separated ids of the items.
     * </pre>
     *
     * @throws Zend_Controller_Action_Exception On error in the action delete.
     *
     * @return void
     */
    public function jsonDeleteMultipleAction()
    {
        $ids = $this->getRequest()->getParam('ids');
        $this->setCurrentProjectId();

        if (!empty($ids)) {
            $message  = Phprojekt::getInstance()->translate(self::DELETE_MULTIPLE_TRUE_TEXT);
            $showId   = array();
            $model    = $this->getModelObject();
            $idsArray = explode(",", $ids);

            if ($model instanceof Phprojekt_ActiveRecord_Abstract) {
                foreach ($idsArray as $id) {
                    $model->find((int) $id);
                    Default_Helpers_Delete::delete($model);
                    $showId[] = $id;
                }
            }

            $return = array('type'    => 'success',
                            'message' => $message,
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
        $relation  = new Project_Models_ProjectModulePermissions();
        $modules   = $relation->getProjectModulePermissionsById($projectId);

        if ($projectId == 0) {
            $data = array(); // there is no rights or invalid project
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

                    // Return modules with at least one access
                    if ($tmpPermission != Phprojekt_Acl::NONE || Phprojekt_Auth::isAdminUser()) {
                        $module['rights'] = Phprojekt_Acl::convertBitmaskToArray($tmpPermission);
                        $allowedModules[] = $module;
                    }
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
     * Returns the front configurations from the configuration.php (front.xxx),
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

        $user = Phprojekt_Auth_Proxy::getEffectiveUser();
        $settings = $user->settings->fetchAll();

        $tutorialDisplayed = "false";
        foreach ($settings as $setting) {
            if ($setting->keyValue == "tutorialDisplayed") {
                $tutorialDisplayed = $setting->value;
                break;
            }
        }

        // System info
        $data[] = array('name'  => 'phprojektVersion',
                        'value' => Phprojekt::getVersion());
        $data[] = array('name'  => 'currentUserId',
                        'value' => $user->id);
        $data[] = array('name'  => 'currentUserName',
                        'value' => $user->username);
        $data[] = array('name'  => 'csrfToken',
                        'value' => Phprojekt::createCsrfToken());
        $data[] = array('name'  => 'tutorialDisplayed',
                        'value' => $tutorialDisplayed);

        Phprojekt_Converter_Json::echoConvert($data);
    }

    /**
     * Returns the possible extra actions to perform for multiple or singles ids.
     *
     * Each action defines in the array:
     * <pre>
     *  - action: Name of the action that will process ids.
     *  - label:  Display for the action.
     *  - class:  Name of the class for display the icon.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonGetExtraActionsAction()
    {
        $actions = $this->getDefaultExtraActions();

        Phprojekt_Converter_Json::echoConvert($actions);
    }

    /**
     * Returns the frontend (realtime) notification(s) to a user. The return format is JSON.
     *
     * Note:
     * At this point a Zend_Session::writeClose() is needed, to avoid blocking of other requests.
     * See http://www.php.net/manual/en/function.session-write-close.php for more details.
     *
     * @return void
     */
    public function jsonGetFrontendMessageAction()
    {
        try {
            Zend_Session::writeClose(false);
        } catch (Exception $error) {
            Phprojekt::getInstance()->getLog()->debug('Error: ' . $error->getMessage());
        }

        $notification = new Phprojekt_Notification_FrontendMessage();
        $userId       = (int) Phprojekt_Auth::getUserId();
        $data         = $notification->getFrontendMessage($userId);

        $return = array("data" => $data);

        Phprojekt_Converter_Json::echoConvert($return);
    }

    /**
     * Disables all frontend messages.
     *
     * @return void
     */
    public function jsonDisableFrontendMessagesAction()
    {
        $notification = new Phprojekt_Notification();

        try {
            $notification->disableFrontendMessages();
            $message    = Phprojekt::getInstance()->translate(self::DISABLE_FRONTEND_MESSAGES_TRUE_TEXT);
            $resultType = 'success';
        } catch (Exception $error) {
            Phprojekt::getInstance()->getLog()->debug('Error: ' . $error->getMessage());
            $message    = Phprojekt::getInstance()->translate(self::DISABLE_FRONTEND_MESSAGES_FALSE_TEXT);
            $resultType = 'error';
        }

        $return = array('type'    => $resultType,
                        'message' => $message,
                        'id'      => 0);

        Phprojekt_Converter_Json::echoConvert($return);
    }

    /**
     * Returns the ACL rights for all the users of one item.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - integer <b>id</b>     The id of the item to consult.
     *  - integer <b>nodeId</b> The id of the parent project.
     * </pre>
     *
     * The return is an array like ('#userID' => {'admin': true/false, 'read': true/false, etc})
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonGetUsersRightsAction()
    {
        $id        = (int) $this->getRequest()->getParam('id');
        $projectId = (int) $this->getRequest()->getParam('nodeId');

        if (empty($id)) {
            if (empty($projectId)) {
                $record = $this->getModelObject();
            } else {
                $model  = new Project_Models_Project();
                $record = $model->find($projectId);
            }
        } else {
            $record = $this->getModelObject()->find($id);
        }

        if ($record instanceof Phprojekt_Model_Interface) {
            Phprojekt_Converter_Json::echoConvert($record->getUsersRights());
        } else {
            Phprojekt_Converter_Json::echoConvert(array());
        }
    }

    /**
     * Sets the tutorialDisplayed setting of the current user.
     *
     * Sets the tutorialDisplayed setting of the current user, indicating that the tutorial has been displayed.
     * The request parameter "displayed" should either be true or false.
     *
     * @return void
     */
    public function jsonSetTutorialDisplayedAction()
    {
        $displayed = $this->getRequest()->getParam('displayed', "");
        if ($displayed == "true") {
            $displayed = "true";
        } else {
            $displayed = "false";
        }

        $user = Phprojekt_Auth_Proxy::getEffectiveUser();
        $settings = $user->settings->fetchAll();

        $found = false;
        foreach ($settings as $setting) {
            // Update
            if ($setting->keyValue == "tutorialDisplayed") {
                $setting->value = $displayed;
                $setting->save();
                $found = true;
                break;
            }
        }
        if (!$found) {
            // Create
            $record             = $user->settings->create();
            $record->moduleId   = 0;
            $record->keyValue   = "tutorialDisplayed";
            $record->value      = $displayed;
            $record->identifier = 'Core';
            $record->save();
        }

        Phprojekt_Converter_Json::echoConvert(array());
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
        $this->setCurrentProjectId();

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
        $this->setCurrentProjectId();

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

    /**
     * The function init the upload field and call the render for draw it.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - integer <b>id</b>    Id of the current item.
     *  - string  <b>field</b> Name of the field in the module.
     * </pre>
     *
     * @return void
     */
    public function fileFormAction()
    {
        list($model, $field, $itemId) = $this->_getFileParameters();

        $value = Default_Helpers_Upload::initValue($model, $field, $itemId);

        $this->_fileRenderView($itemId, $field, $value);
    }

    /**
     * Upload the file and call the render for draw the upload field.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - integer <b>id</b>    Id of the current item.
     *  - string  <b>field</b> Name of the field in the module.
     * </pre>
     *
     * @return void
     */
    public function fileUploadAction()
    {
        list($model, $field, $itemId) = $this->_getFileParameters();

        try {
            $value = Default_Helpers_Upload::uploadFile($model, $field, $itemId);
        } catch (Exception $error) {
            $this->view->errorMessage = $error->getMessage();
            $value                    = Default_Helpers_Upload::getFiles($model, $field);
        }

        $this->_fileRenderView($itemId, $field, $value);
    }

    /**
     * Retrieves the file from upload folder.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - integer <b>id</b>    Id of the current item.
     *  - string  <b>field</b> Name of the field in the module.
     *  - integer <b>order</b> Position of the file (Can be many uploaded files in the same field).
     * </pre>
     *
     * @return void
     */
    public function fileDownloadAction()
    {
        $hash = $this->getRequest()->getParam('hash', null);

        list($model, $field, $itemId) = $this->_getFileParameters();

        Default_Helpers_Upload::downloadFile($model, $field, $itemId, $hash);
    }

    /**
     * Set the file parameters needed by all the file actions.
     *
     * @return array A list with the file parameters.
     */
    private function _getFileParameters()
    {
        $model  = $this->getModelObject();
        $field  = Cleaner::sanitize('alnum', $this->getRequest()->getParam('field', null));
        $itemId = (int) $this->getRequest()->getParam('id', null);
        $this->setCurrentProjectId();

        return array($model, $field, $itemId);
    }

    /**
     * Renders the upload.phtml template for display an upload field.
     *
     * This function draws the upload field in the form.
     * All the uploaded files are displayed with a cross for delete it and a link for download it.
     *
     * @param integer $itemId Current item id.
     * @param string  $field  Name of the field in the module.
     * @param string  $value  Value of the field.
     *
     * @return void
     */
    private function _fileRenderView($itemId, $field, $files)
    {
        $this->getResponse()->clearHeaders();
        $this->getResponse()->clearBody();

        $sessionName   = 'Phprojekt_CsrfToken';
        $csrfNamespace = new Zend_Session_Namespace($sessionName);
        $config        = Phprojekt::getInstance()->getConfig();
        $linkBegin     = 'index.php/' . $this->getModuleName() . '/index/';
        $fieldId       = $this->getRequest()->getParam('fieldId', '');

        // Add all the extra parameters that have the original URL
        $linkData      = '';
        $removeParams  = array('module', 'controller', 'field', 'id',
                               'csrfToken', 'action', 'MAX_FILE_SIZE', 'order');
        foreach ($this->getRequest()->getParams() as $paramName => $paramValue) {
            if (!in_array($paramName, $removeParams)) {
                $linkData .= $paramName . '/' . $paramValue . '/';
            }
        }

        $this->view->compressedDojo = (bool) $config->compressedDojo;
        $this->view->formPath       = $linkBegin . 'fileUpload/' . $linkData;
        $this->view->downloadLink   = '';
        $this->view->fileName       = null;
        $this->view->itemId         = $itemId;
        $this->view->field          = $field;
        $this->view->fieldId        = $fieldId;
        $this->view->csrfToken      = $csrfNamespace->token;
        $this->view->maxUploadSize  = (isset($config->maxUploadSize)) ? (int) $config->maxUploadSize :
            Phprojekt::DEFAULT_MAX_UPLOAD_SIZE;

        $model = $this->getModelObject();
        $model->find($itemId);

        $filesForView         = array();
        $hasDownloadRight     = $model->hasRight(Phprojekt_Auth_Proxy::getEffectiveUserId(), Phprojekt_Acl::DOWNLOAD);
        $hasWriteRight        = $model->hasRight(Phprojekt_Auth_Proxy::getEffectiveUserId(), Phprojekt_Acl::WRITE);
        $this->view->disabled = !$hasWriteRight;

        // Is there any file?
        if (!empty($files)) {
            $i = 0;

            foreach ($files as $file) {
                $fileName = $file['name'];
                $fileHash = $file['md5'];
                $fileData = 'id/' . $itemId . '/field/' . $field . '/hash/' . $fileHash . '/csrfToken/' . $csrfNamespace->token;

                $filesForView[$i] = array(
                    'fileName' => $fileName,
                    'hash' => $fileHash
                );

                if ($hasDownloadRight) {
                    $filesForView[$i]['downloadLink'] = $linkBegin . 'fileDownload/' . $linkData . $fileData;
                }

                $fileinfo = Default_Helpers_Upload::getInfosFromFile($file);

                $filesForView[$i]['size'] = $fileinfo['size'];
                $filesForView[$i]['ctime'] = $fileinfo['ctime'];

                $i++;
            }
        }
        if (isset($this->view->errorMessage) && !empty($this->view->errorMessage)) {
            $filesForView[] = array();
        }

        $this->view->files = $filesForView;
        $this->render('upload');
    }
}
