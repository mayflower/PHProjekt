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
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Default Controller for PHProjekt 6
 *
 * The controller will get all the actions
 * and run the nessesary stuff for each one
 *
 * The class contain the model var for get the module model object
 * that return all the data for process
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
    const ADD_TRUE_TEXT           = "The Item was added correctly";
    const EDIT_TRUE_TEXT          = "The Item was edited correctly";
    const EDIT_MULTIPLE_TRUE_TEXT = "The Items were edited correctly";
    const DELETE_FALSE_TEXT       = "The Item can't be deleted";
    const DELETE_TRUE_TEXT        = "The Item was deleted correctly";
    const NOT_FOUND               = "The Item was not found";
    const ID_REQUIRED_TEXT        = "ID parameter required";
    const INVISIBLE_ROOT          = 1;

    /**
     * Init function
     *
     * First check if it is a logged user, if not he is redirected to the login form.
     *
     * The function initializes all the Helpers,
     * collects the data from the Model Object for list and form
     * and initializes the Project Tree view
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
     * Use the list action
     *
     * List Action
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
     * Returns a tree for a model in JSON.
     *
     * For further information see the chapter json exchange
     * in the internals documentantion
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
     * Returns the list for a model in JSON.
     *
     * For further information see the chapter json exchange
     * in the internals documentantion
     *
     * @requestparam integer count ...
     * @requestparam integer start ...
     *
     * @return void
     */
    public function jsonListAction()
    {
        // Every dojox.data.QueryReadStore has to (and does) return "start" and "count" for paging,
        // so lets apply this to the query set. This is also used for loading a
        // grid on demand (initially only a part is shown, scrolling down loads what is needed).
        $count     = (int) $this->getRequest()->getParam('count', null);
        $offset    = (int) $this->getRequest()->getParam('start', null);
        $projectId = (int) $this->getRequest()->getParam('nodeId', null);
        $itemId    = (int) $this->getRequest()->getParam('id', null);

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
     * Returns the detail for a model in JSON.
     *
     * For further information see the chapter json exchange
     * in the internals documentantion
     *
     * @requestparam integer id ...
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
     * Saves the current item
     * Save if you add or edit one.
     * Use the model module to get the data
     *
     * If there is an error, the save will return a Phprojekt_PublishedException
     * If not, it returns a string with the same format than the Phprojekt_PublishedException
     * but with success type
     *
     * @requestparam integer id ...
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
     * Save some fields for many items
     * Only edit existing items
     * Use the model module to get the data
     *
     * If there is an error, the saving will return a Phprojekt_PublishedException
     * If not, it returns is a string with the same format than the Phprojekt_PublishedException
     * but with success type
     *
     * @requestparam string data Array with fields and values
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
                $showId  = Array($id);
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
     * If the item are already deleted or do not exist
     * return a Phprojekt_PublishedException
     * If the item is deleted, the return is a string with the same format than the Phprojekt_PublishedException
     * but with success type
     *
     * @requestparam integer id ...
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
                $message = Phprojekt::getInstance()->translate(self::DELETE_FALSE_TEXT);
            } else {
                $message = Phprojekt::getInstance()->translate(self::DELETE_TRUE_TEXT);
            }
            $return = array('type'    => 'success',
                            'message' => $message,
                            'code'    => 0,
                            'id'      => $id);

            Phprojekt_Converter_Json::echoConvert($return);
        } else {
            throw new Phprojekt_PublishedException(self::NOT_FOUND);
        }
    }

    /**
     * Get the model object, or the default if none exists.
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
     * Get a list of permissions for each module
     * for the users who requested the list.
     * It checks the user permission on the projectId
     *
     * @requestparam integer projectId
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
     * Return a list of all the projects
     *
     * @return void
     */
    public function jsonGetProjectsAction()
    {
        $object  = Phprojekt_Loader::getModel('Project', 'Project');
        $records = $object->fetchAll();

        Phprojekt_Converter_Json::echoConvert($records, Phprojekt_ModelInformation_Default::ORDERING_LIST);
    }

    /**
     * Returns the list for a model in CSV format.
     *
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

        $records = $this->getModelObject()->fetchAll($where, null, 0, 0);

        Phprojekt_Converter_Csv::echoConvert($records, Phprojekt_ModelInformation_Default::ORDERING_LIST);
    }

    /**
     * Return all the words translated in each modules for the $language
     *
     * @requestparam string $language The current language
     *
     * @return void
     */
    public function getTranslatedStringsAction()
    {
        $language  = Cleaner::sanitize('alpha', $this->getRequest()->getParam('language', 'en'));
        $translate = Phprojekt::getInstance()->getTranslate();

        Phprojekt_Converter_Json::echoConvert($translate->getTranslatedStrings($language));
    }

    /**
     * Return the front configurations from the configuration.ini
     * And some others Core Settings
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
     * Shows the upload template page form
     *
     * @return void
     */
    public function fileFormAction()
    {
        $this->getResponse()->clearHeaders();
        $this->getResponse()->clearBody();

        $linkBegin = Phprojekt::getInstance()->getConfig()->webpath . 'index.php/'
            . $this->getRequest()->getModuleName();
        $value  = (string) $this->getRequest()->getParam('value', null);
        $itemId = (int) $this->getRequest()->getParam('id', null);
        $field  = Cleaner::sanitize('alnum', $this->getRequest()->getParam('field', null));

        $_SESSION['uploadedFiles_' . $field] = $value;

        $this->_fileRenderView($linkBegin, $itemId, $field, $value, false);
    }

    /**
     * Handle the files from upload form
     *
     * @return void
     */
    public function fileUploadAction()
    {
        $field      = Cleaner::sanitize('alnum', $this->getRequest()->getParam('field', null));
        $value      = $_SESSION['uploadedFiles_' . $field];
        $maxSize    = (int) $this->getRequest()->getParam('MAX_FILE_SIZE', null);
        $itemId     = (int) $this->getRequest()->getParam('itemId', null);
        $addedValue = '';

        $this->_fileCheckWritePermission($itemId);

        // Fix name for save it as md5
        if (is_array($_FILES) && !empty($_FILES) && isset($_FILES['uploadedFile'])) {
            $md5name                        = md5(uniqid(rand(), 1));
            $addedValue                     = $md5name . '|' . $_FILES['uploadedFile']['name'];
            $_FILES['uploadedFile']['name'] = $md5name;
        }

        $adapter = new Zend_File_Transfer_Adapter_Http();
        $adapter->setDestination(Phprojekt::getInstance()->getConfig()->uploadpath);

        $this->getResponse()->clearHeaders();
        $this->getResponse()->clearBody();

        if (!$adapter->receive()) {
            $messages = $adapter->getMessages();
            foreach ($messages as $index => $message) {
                $messages[$index] = Phprojekt::getInstance()->translate($message);
                if ($index == 'fileUploadErrorFormSize') {
                    $maxSize           = (int) ($maxSize / 1024);
                    $messages[$index] .= ': ' . $maxSize . ' Kb.';
                }
            }
            $this->view->errorMessage = implode("\n", $messages);

        } else {
            if (!empty($value)) {
                $value .= '||';
            }
            $value .= $addedValue;
        }
        $_SESSION['uploadedFiles_' . $field] = $value;

        $linkBegin = Phprojekt::getInstance()->getConfig()->webpath . 'index.php/'
            . $this->getRequest()->getModuleName();

        $this->_fileRenderView($linkBegin, $itemId, $field, $value, true);
    }

    /**
     * Retrieve the file from upload folder
     *
     * @return void
     */
    public function fileDownloadAction()
    {
        $itemId = (int) $this->getRequest()->getParam('itemId', null);
        $field  = Cleaner::sanitize('alnum', $this->getRequest()->getParam('field', null));
        $order  = (int) $this->getRequest()->getParam('order', null);

        $this->_fileCheckParamField($field);

        if ($itemId > 0) {
            $model = $this->getModelObject();
            $model->find($itemId);
            // The user has download permission?
            $rights = $model->getRights();
            if (!$rights['currentUser']['download']) {
                $error = Phprojekt::getInstance()->translate("You don't have permission for downloading on this item.");
                die($error);
            }
            $files = $model->$field;
        } else {
            $files = $_SESSION['uploadedFiles_' . $field];
        }
        $files = explode('||', $files);

        $this->_fileCheckParamOrder($order, count($files));

        list($md5Name, $fileName) = explode("|", $files[$order - 1]);

        if (!empty($fileName)) {
            $md5Name = Phprojekt::getInstance()->getConfig()->uploadpath . $md5Name;
            if (file_exists($md5Name)) {
                header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
                header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
                header("Cache-Control: no-store, no-cache, must-revalidate");
                header("Cache-Control: post-check=0, pre-check=0", false);
                header("Pragma: no-cache");
                header('Content-Length: ' . filesize($md5Name));
                header("Content-Disposition: attachment; filename=\"" . (string) $fileName . "\"");
                header('Content-Type: download');
                $fh = fopen($md5Name, 'r');
                fpassthru($fh);
            }
        }
    }

    /**
     * Delete a file from the Upload field
     *
     * @return void
     */
    public function fileDeleteAction()
    {
        $this->getResponse()->clearHeaders();
        $this->getResponse()->clearBody();

        $linkBegin = Phprojekt::getInstance()->getConfig()->webpath . 'index.php/'
            . $this->getRequest()->getModuleName();
        $field     = Cleaner::sanitize('alnum', $this->getRequest()->getParam('field', null));
        $itemId    = (int) $this->getRequest()->getParam('id', null);
        $order     = (int) $this->getRequest()->getParam('order', 0);

        $this->_fileCheckParamField($field);
        $this->_fileCheckWritePermission($itemId);

        if ($itemId > 0) {
            $model = $this->getModelObject();
            $model->find($itemId);
            $files = $model->$field;
        } else {
            $files = $_SESSION['uploadedFiles_' . $field];
        }

        $filesIn = explode('||', $files);

        $this->_fileCheckParamOrder($order, count($filesIn));

        // Delete the file name and md5 from the string
        $filesOut = '';
        $i        = 1;
        foreach ($filesIn as $file) {
            if ($i != $order) {
                if ($filesOut != '') {
                    $filesOut .= '||';
                }
                $filesOut .= $file;
            } else {
                // Delete the file from the server
                $md5Name          = substr($file, 0, strpos($file, '|'));
                $fileAbsolutePath = Phprojekt::getInstance()->getConfig()->uploadpath . $md5Name;
                if (file_exists($fileAbsolutePath)) {
                    unlink($fileAbsolutePath);
                }
            }
            $i++;
        }

        $_SESSION['uploadedFiles_' . $field] = $filesOut;

        $this->_fileRenderView($linkBegin, $itemId, $field, $filesOut, true);
    }

    /**
     * Set some values deppend on the params
     * Each module can implement this function to change their values
     *
     * @return array
     */
    public function setParams()
    {
        $args = func_get_args();

        if (1 > count($args)) {
            throw new Exception('Missing argument');
        }

        return $args[0];
    }

    /**
     * Renders the upload file field with the received data
     *
     * @return void
     */
    private function _fileRenderView($linkBegin, $itemId, $field, $value, $filesChanged)
    {
        $this->view->webpath        = Phprojekt::getInstance()->getConfig()->webpath;
        $this->view->compressedDojo = (bool) Phprojekt::getInstance()->getConfig()->compressedDojo;
        $this->view->formPath       = $linkBegin . '/index/fileUpload/';
        $this->view->downloadLink   = '';
        $this->view->fileName       = null;
        $this->view->itemId         = $itemId;
        $this->view->field          = $field;
        $this->view->value          = $value;
        $this->view->filesChanged   = $filesChanged;

        $filesForView = array();

        // Is there any file?
        if (!empty($value)) {
            $files = explode('||', $value);
            $model = $this->getModelObject();
            $model->find($itemId);
            $rights = $model->getRights();
            $i      = 0;
            foreach ($files as $file) {
                $fileName = strstr($file, '|');
                $fileData = 'itemId/' . $itemId . '/field/' . $field . '/order/' . (string) ($i + 1);

                $filesForView[$i] = array('fileName' => substr($fileName, 1));
                if ($rights['currentUser']['download']) {
                    $filesForView[$i]['downloadLink'] = $linkBegin . '/index/fileDownload/' . $fileData;
                }
                if ($rights['currentUser']['write']) {
                    $filesForView[$i]['deleteLink'] = $linkBegin . '/index/fileDelete/' . $fileData;
                }
                $i++;
            }

        }
        if (isset($this->view->errorMessage) && !empty($this->view->errorMessage)) {
            $filesForView[] = array();
        }

        $this->view->files = $filesForView;
        $this->render('upload');
    }

    /**
     * Checks that the 'field' parameter for download and delete file actions is valid. If not, terminates script
     * execution.
     *
     * @return void
     */
    private function _fileCheckParamField($field)
    {
        $model     = $this->getModelObject();
        $dbManager = $model->getInformation();
        $dbField   = $dbManager->find($field);
        $valid     = false;

        if (!empty($dbField)) {
            $fieldType = $dbManager->getType($field);
            if ($fieldType == 'upload') {
                $valid = true;
            }
        }
        if (!$valid) {
            $error  = Phprojekt::getInstance()->translate("Error in received parameter, consult the admin. Parameter:");
            $error .= " field";

            // Log error
            Phprojekt::getInstance()->getLog()->err("Error: wrong 'field' parameter trying to Download or Delete a file"
                . ". User Id: " . Phprojekt_Auth::getUserId() . " - Module: " . $this->getRequest()->getModuleName());
            Phprojekt::getInstance()->getLog()->err($error);
            // Show error to user and stop script execution
            die($error);
        }
    }


    /**
     * Checks that the 'order' parameter for download and delete file actions is valid. If not, terminates script
     * execution printing an error.
     *
     * @return void
     */
    private function _fileCheckParamOrder($order, $filesAmount)
    {
        if ($order < 1 || $order > $filesAmount) {
            $error  = Phprojekt::getInstance()->translate("Error in received parameter, consult the admin. Parameter:");
            $error .= " order";

            // Log error
            Phprojekt::getInstance()->getLog()->err("Error: wrong 'order' parameter trying to Download or Delete a file"
                . ". User Id: " . Phprojekt_Auth::getUserId() . " - Module: " . $this->getRequest()->getModuleName());
            Phprojekt::getInstance()->getLog()->err($error);
            // Show error to user and stop script execution
            die($error);
        }
    }

    /**
     * Checks that the user has permission for modifying the item, in this case for uploading or deleting files.
     * If not, prints an error, terminating script execution.
     *
     * @return void
     */
    private function _fileCheckWritePermission($itemId) {
        $model = $this->getModelObject();
        $model->find($itemId);
        $rights = $model->getRights();
        if (!$rights['currentUser']['write']) {
            $error = Phprojekt::getInstance()->translate("You don't have permission for modifying this item.");

            // Log error
            Phprojekt::getInstance()->getLog()->err("Error: trying to Delete or Upload a file without write access. "
                . "User Id: " . Phprojekt_Auth::getUserId() . " - Module: " . $this->getRequest()->getModuleName());
            Phprojekt::getInstance()->getLog()->err($error);
            // Show error to user and stop script execution
            die($error);
        }
    }
}