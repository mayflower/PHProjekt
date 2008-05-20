<?php
/**
 * Default Controller for PHProjekt 6
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @author     David Soria Parra <soria_parra@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
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
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @subpackage Default
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class IndexController extends Zend_Controller_Action
{
    /**
     * Init function
     *
     * First check if is a logued user, if not is redirect to the login form.
     *
     * The function inicialize all the Helpers,
     * collect the data from the Model Object for list and form
     * and inicialited the Project Tree view
     *
     * @return void
     */
    public function init ()
    {
        try {
            Phprojekt_Auth::isLoggedIn();
        } catch (Phprojekt_Auth_UserNotLoggedInException $ae) {
            // User not logged in, display login page
            $logger = Zend_Registry::get('log');
            $logger->debug((string) $ae);
            $this->_redirect(Zend_Registry::get('config')->webpath . 'index.php/Login/index');
            exit;
        }

        // This is a work around as we cannot set this in the front
        $this->_helper->viewRenderer->setNoRender();
    }

    /**
     * Standard action
     * Use the list action
     *
     * List Action
     *
     * @return void
     */
    public function indexAction ()
    {
        $this->view->webpath = Zend_Registry::get('config')->webpath;
        $this->render('index');
    }

    /**
     * Get a list of submodules
     * and check for the users right on them
     * if the nodeId param isset
     *
     * @requestparam integer projectId
     *
     * @return array
     */
    public function jsonGetSubmodulesAction()
    {
        $subModules = Phprojekt_SubModules::getInstance()->getSubModules();
        $projectId  = (int) $this->getRequest()->getParam('nodeId');

        if ($projectId == 0) {
            $data = $subModules;
        } else {
            $allowedSubModules = array();
            $rights = new Phprojekt_RoleRights($projectId);
            foreach ($subModules as $subModuleData) {
                $subModuleId = Phprojekt_Module::getId($subModuleData['name'], $projectId);
                $right = ($rights->hasRight('read', $subModuleId)) ? true : $rights->hasRight('write', $subModuleId);
                if ($right) {
                    $allowedSubModules[] = $subModuleData;
                }
            }
            $data = $allowedSubModules;
        }

        echo Phprojekt_Converter_Json::convert($data);
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
        $tree = new Phprojekt_Tree_Node_Database($this->getModelObject(), 1);
        $tree->setup();

        echo Phprojekt_Converter_Json::convert($tree);
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
        $count     = (int) $this->getRequest()->getParam('count',  null);
        $offset    = (int) $this->getRequest()->getParam('start',  null);
        $projectId = (int) $this->getRequest()->getParam('nodeId', null);
        $itemId    = (int) $this->getRequest()->getParam('id',     null);

        if (!empty($itemId)) {
            $records = $this->getModelObject()->fetchAll('id = ' . $itemId, null, $count, $offset);
        } else if (!empty($projectId)) {
            $records = $this->getModelObject()->fetchAll('projectId = ' . $projectId, null, $count, $offset);
        } else {
            $records = $this->getModelObject()->fetchAll(null, null, $count, $offset);
        }

        echo Phprojekt_Converter_Json::convert($records, Phprojekt_ModelInformation_Default::ORDERING_LIST);
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

        echo Phprojekt_Converter_Json::convert($record, Phprojekt_ModelInformation_Default::ORDERING_FORM);
    }

    /**
     * Saves the current item
     * Save if you are add one or edit one.
     * Use the model module for get the data
     *
     * If there is an error, the save will return a Phprojekt_PublishedException
     * If not, the return is a string with the same format than the Phprojekt_PublishedException
     * but with success type
     *
     * @requestparam integer id ...
     *
     * @return void
     */
    public function jsonSaveAction()
    {
        $translate = Zend_Registry::get('translate');
        $id        = (int) $this->getRequest()->getParam('id');

        if (empty($id)) {
            $model   = $this->getModelObject();
            $message = $translate->translate('The Item was added correctly');
        } else {
            $model   = $this->getModelObject()->find($id);
            $message = $translate->translate('The Item was edited correctly');
        }

        Default_Helpers_Save::save($model, $this->getRequest()->getParams());

        $return    = array('type'    => 'success',
                           'message' => $message,
                           'code'    => 0,
                           'id'      => $model->id);

        echo Phprojekt_Converter_Json::convert($return);
    }

    /**
     * Save some fields for many items
     * Only edit exists items
     * Use the model module for get the data
     *
     * If there is an error, the save will return a Phprojekt_PublishedException
     * If not, the return is a string with the same format than the Phprojekt_PublishedException
     * but with success type
     *
     * @requestparam string data Array with fields and values
     *
     * @return void
     */
    public function jsonSaveMultipleAction()
    {
        $translate = Zend_Registry::get('translate');
        $data      = (array) $this->getRequest()->getParam('data');

        $message = $translate->translate('The Items was edited correctly');
        $showId = array();
        foreach ($data as $id => $fields) {
            $model   = $this->getModelObject()->find($id);
            Default_Helpers_Save::save($model, $fields);
            $showId[] = $id;
        }

        $return = array('type'    => 'success',
                        'message' => $message,
                        'code'    => 0,
                        'id'      => implode(',', $showId));

        echo Phprojekt_Converter_Json::convert($return);
    }

    /**
     * Deletes a certain item
     *
     * If the item are already deleted or don´t exist
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
        $translate = Zend_Registry::get('translate');
        $id        = (int) $this->getRequest()->getParam('id');

        if (empty($id)) {
            throw new Phprojekt_PublishedException('ID parameter required');
        }

        $model = $this->getModelObject()->find($id);

        if ($model instanceof Phprojekt_Model_Interface) {
            $model->delete();
            $message = $translate->translate('The Item was deleted correctly');
            $return  = array('type'    => 'success',
                             'message' => $message,
                             'code'    => 0,
                             'id'      => $id);

            echo Phprojekt_Converter_Json::convert($return);
        } else {
            throw new Phprojekt_PublishedException('Item not found');
        }
    }

    /**
     * Get the model object
     * This function must be redefined in each module
     *
     * @return Phprojekt_Model_Interface
     */
    public function getModelObject()
    {
        static $object = null;
        if (null === $object) {
            $moduleName = $this->getRequest()->getModuleName();
            $object     = Phprojekt_Loader::getModel($moduleName, $moduleName);
            if (null === $object) {
                $object = Phprojekt_Loader::getModel('Default', 'Default');
            }
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
        $subModules = Phprojekt_SubModules::getInstance()->getSubModules();
        $projectId  = (int) $this->getRequest()->getParam('nodeId');

        if ($projectId == 0) {
            $data = array(); // there is no rights on invalid projects
        } else {
            $allowedSubModules = array();
            $rights = new Phprojekt_RoleRights($projectId);
            foreach ($subModules as $subModuleData) {

                $tmpPermission = Phprojekt_Acl::NO_ACCESS;

                if ($rights->hasRight('access', Phprojekt_Module::getId($subModuleData['name'], $projectId))) {
                    $tmpPermission = Phprojekt_Acl::ACCESS;
                }
                if ($rights->hasRight('read', Phprojekt_Module::getId($subModuleData['name'], $projectId))) {
                    $tmpPermission = Phprojekt_Acl::READ;
                }
                if ($rights->hasRight('write', Phprojekt_Module::getId($subModuleData['name'], $projectId))) {
                    $tmpPermission = Phprojekt_Acl::WRITE;
                }
                if ($rights->hasRight('create', Phprojekt_Module::getId($subModuleData['name'], $projectId))) {
                    $tmpPermission = Phprojekt_Acl::ADMIN;
                }

                $subModuleData['permission'] = $tmpPermission;

                $allowedSubModules[] = $subModuleData;

            }
            $data = $allowedSubModules;
        }

        echo Phprojekt_Converter_Json::convert($data);
    }

    /**
     * Return a list of all the projects
     *
     * @return void
     */
    public function jsonGetProjectsAction()
    {
        $object = Phprojekt_Loader::getModel('Project', 'Project');
        $records = $object->fetchAll();
        echo Phprojekt_Converter_Json::convert($records, Phprojekt_ModelInformation_Default::ORDERING_LIST);
    }
}