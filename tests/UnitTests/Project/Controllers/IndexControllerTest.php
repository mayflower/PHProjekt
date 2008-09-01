<?php
/**
 * Unit test
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
*/
require_once 'PHPUnit/Framework.php';

/**
 * Tests for Index Controller
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */
class Project_IndexController_Test extends PHPUnit_Framework_TestCase
{

    /**
     * Test of json save Project -in fact, default json save
     */
    public function testJsonSave()
    {
        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();

        $config = Zend_Registry::get('config');
        $config->language = "de";

        $request->setParams(array('action'=>'jsonSave','controller'=>'index','module'=>'Project'));

        $request->setBaseUrl($config->webpath.'index.php/Project/index/jsonSave/id/null/title/test/startDate/2008-08-07/endDate/2020-08-31/priority/2');
        $request->setPathInfo('/Project/index/jsonSave/id/null/title/test/startDate/2008-08-07/endDate/2020-08-31/priority/2');
        $request->setRequestUri('/Project/index/jsonSave/id/null/title/test/startDate/2008-08-07/endDate/2020-08-31/priority/2');

        // getting the view information
        $request->setModuleKey('module');
        $request->setControllerKey('controller');
        $request->setActionKey('action');
        $request->setDispatched(false);

        $view = new Zend_View();
        $view->addScriptPath(PHPR_CORE_PATH . '/Default/Views/dojo/');

        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer($view);
        $viewRenderer->setViewBasePathSpec(':moduleDir/Views');
        $viewRenderer->setViewScriptPathSpec(':action.:suffix');

        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);

        // Languages Set
        Zend_Loader::loadClass('Phprojekt_Language', PHPR_CORE_PATH);
        $translate = new Phprojekt_Language($config->language);

        // Front controller stuff
        $front = Zend_Controller_Front::getInstance();
        $front->setDispatcher(new Phprojekt_Dispatcher());

        $front->registerPlugin(new Zend_Controller_Plugin_ErrorHandler());
        $front->setDefaultModule('Default');

        foreach (scandir(PHPR_CORE_PATH) as $module) {
            $dir = PHPR_CORE_PATH . DIRECTORY_SEPARATOR . $module;

            if (is_dir(!$dir)) {
                continue;
            }

            if (is_dir($dir . DIRECTORY_SEPARATOR . 'Controllers')) {
                $front->addModuleDirectory($dir);
            }

            $helperPath = $dir . DIRECTORY_SEPARATOR . 'Helpers';

            if (is_dir($helperPath)) {
                $view->addHelperPath($helperPath, $module . '_' . 'Helpers');
                Zend_Controller_Action_HelperBroker::addPath($helperPath);
            }
        }

        Zend_Registry::set('view', $view);
        $view->webPath  = $config->webpath;
        Zend_Registry::set('translate', $translate);

        $front->setModuleControllerDirectoryName('Controllers');
        $front->addModuleDirectory(PHPR_CORE_PATH);

        $front->setParam('useDefaultControllerAlways', true);

        $front->throwExceptions(true);

        // Getting the output, otherwise the home page will be displayed
        ob_start();

        $error_produced = false;

        try {
          $front->dispatch($request, $response);

        }
        catch (Phprojekt_PublishedException $e) {
            $error_produced = true;
        }
        $response = ob_get_contents();
        ob_end_clean();

        // checking some parts of the index template
        $this->assertTrue(strpos($response, 'The Item was added correctly') > 0);
    }


    /**
     * Test of json save  multiple Project
     */
    public function testJsonSaveMultiple()
    {
        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();

        $config = Zend_Registry::get('config');
        $config->language = "de";

        $request->setParams(array('action'=>'jsonSave','controller'=>'index','module'=>'Project'));

        $request->setBaseUrl($config->webpath.'index.php/Project/index/jsonSaveMultiple/nodeId/1/data[1][notes]/test');
        $request->setPathInfo('/Project/index/jsonSaveMultiple/nodeId/1/data[1][notes]/test');
        $request->setRequestUri('/Project/index/jsonSaveMultiple/nodeId/1/data[1][notes]/test');

        // getting the view information
        $request->setModuleKey('module');
        $request->setControllerKey('controller');
        $request->setActionKey('action');
        $request->setDispatched(false);

        $view = new Zend_View();
        $view->addScriptPath(PHPR_CORE_PATH . '/Default/Views/dojo/');

        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer($view);
        $viewRenderer->setViewBasePathSpec(':moduleDir/Views');
        $viewRenderer->setViewScriptPathSpec(':action.:suffix');

        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);

        // Languages Set
        Zend_Loader::loadClass('Phprojekt_Language', PHPR_CORE_PATH);
        $translate = new Phprojekt_Language($config->language);

        // Front controller stuff
        $front = Zend_Controller_Front::getInstance();
        $front->setDispatcher(new Phprojekt_Dispatcher());

        $front->registerPlugin(new Zend_Controller_Plugin_ErrorHandler());
        $front->setDefaultModule('Default');

        foreach (scandir(PHPR_CORE_PATH) as $module) {
            $dir = PHPR_CORE_PATH . DIRECTORY_SEPARATOR . $module;

            if (is_dir(!$dir)) {
                continue;
            }

            if (is_dir($dir . DIRECTORY_SEPARATOR . 'Controllers')) {
                $front->addModuleDirectory($dir);
            }

            $helperPath = $dir . DIRECTORY_SEPARATOR . 'Helpers';

            if (is_dir($helperPath)) {
                $view->addHelperPath($helperPath, $module . '_' . 'Helpers');
                Zend_Controller_Action_HelperBroker::addPath($helperPath);
            }
        }

        Zend_Registry::set('view', $view);
        $view->webPath  = $config->webpath;
        Zend_Registry::set('translate', $translate);

        $front->setModuleControllerDirectoryName('Controllers');
        $front->addModuleDirectory(PHPR_CORE_PATH);

        $front->setParam('useDefaultControllerAlways', true);

        $front->throwExceptions(true);

        // Getting the output, otherwise the home page will be displayed
        ob_start();

        $error_produced = false;

        try {
          $front->dispatch($request, $response);

        }
        catch (Phprojekt_PublishedException $e) {
            $error_produced = true;
        }
        $response = ob_get_contents();
        ob_end_clean();

        // checking some parts of the index template
        $this->assertTrue(strpos($response, 'The Items was edited correctly') > 0);
    }


     /**
     * Test the get all the modules active and their relation with the projectId
     */

    public function testJsonGetModulesProjectRelation()
    {
        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();

        $config = Zend_Registry::get('config');

        $request->setParams(array('action'=>'jsonList','controller'=>'index','module'=>'Project'));

        $request->setBaseUrl($config->webpath.'index.php/Project/index/jsonGetModulesProjectRelation/id/1');
        $request->setPathInfo('/Project/index/jsonGetModulesProjectRelation/id/1');
        $request->setRequestUri('/Project/index/jsonGetModulesProjectRelation/id/1');

        // getting the view information
        $request->setModuleKey('module');
        $request->setControllerKey('controller');
        $request->setActionKey('action');
        $request->setDispatched(false);

        $view = new Zend_View();
        $view->addScriptPath(PHPR_CORE_PATH . '/Default/Views/dojo/');

        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer($view);
        $viewRenderer->setViewBasePathSpec(':moduleDir/Views');
        $viewRenderer->setViewScriptPathSpec(':action.:suffix');

        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);

        // Languages Set
        Zend_Loader::loadClass('Phprojekt_Language', PHPR_CORE_PATH);
        $translate = new Phprojekt_Language($config->language);

        // Front controller stuff
        $front = Zend_Controller_Front::getInstance();
        $front->setDispatcher(new Phprojekt_Dispatcher());

        $front->registerPlugin(new Zend_Controller_Plugin_ErrorHandler());
        $front->setDefaultModule('Default');

        foreach (scandir(PHPR_CORE_PATH) as $module) {
            $dir = PHPR_CORE_PATH . DIRECTORY_SEPARATOR . $module;

            if (is_dir(!$dir)) {
                continue;
            }

            if (is_dir($dir . DIRECTORY_SEPARATOR . 'Controllers')) {
                $front->addModuleDirectory($dir);
            }

            $helperPath = $dir . DIRECTORY_SEPARATOR . 'Helpers';

            if (is_dir($helperPath)) {
                $view->addHelperPath($helperPath, $module . '_' . 'Helpers');
                Zend_Controller_Action_HelperBroker::addPath($helperPath);
            }
        }

        Zend_Registry::set('view', $view);
        $view->webPath  = $config->webpath;
        Zend_Registry::set('translate', $translate);

        $front->setModuleControllerDirectoryName('Controllers');
        $front->addModuleDirectory(PHPR_CORE_PATH);

        $front->setParam('useDefaultControllerAlways', true);

        $front->throwExceptions(true);

        // Getting the output, otherwise the home page will be displayed
        ob_start();

        $front->dispatch($request, $response);
        $response = ob_get_contents();

        ob_end_clean();

        // checking some parts of the index template
        $this->assertTrue(strpos($response, '"2":{"id":"2","name":"Todo","label":"Todo","inProject":true}') > 0);
    }

    /**
     * Test the get all the role-user relation with the projectId
     */

    public function testJsonGetProjectRoleUserRelation()
    {
        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();

        $config = Zend_Registry::get('config');

        $request->setParams(array('action'=>'jsonList','controller'=>'index','module'=>'Project'));

        $request->setBaseUrl($config->webpath.'index.php/Project/index/jsonGetProjectRoleUserRelation/id/1');
        $request->setPathInfo('/Project/index/jsonGetProjectRoleUserRelation/id/1');
        $request->setRequestUri('/Project/index/jsonGetProjectRoleUserRelation/id/1');

        // getting the view information
        $request->setModuleKey('module');
        $request->setControllerKey('controller');
        $request->setActionKey('action');
        $request->setDispatched(false);

        $view = new Zend_View();
        $view->addScriptPath(PHPR_CORE_PATH . '/Default/Views/dojo/');

        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer($view);
        $viewRenderer->setViewBasePathSpec(':moduleDir/Views');
        $viewRenderer->setViewScriptPathSpec(':action.:suffix');

        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);

        // Languages Set
        Zend_Loader::loadClass('Phprojekt_Language', PHPR_CORE_PATH);
        $translate = new Phprojekt_Language($config->language);

        // Front controller stuff
        $front = Zend_Controller_Front::getInstance();
        $front->setDispatcher(new Phprojekt_Dispatcher());

        $front->registerPlugin(new Zend_Controller_Plugin_ErrorHandler());
        $front->setDefaultModule('Default');

        foreach (scandir(PHPR_CORE_PATH) as $module) {
            $dir = PHPR_CORE_PATH . DIRECTORY_SEPARATOR . $module;

            if (is_dir(!$dir)) {
                continue;
            }

            if (is_dir($dir . DIRECTORY_SEPARATOR . 'Controllers')) {
                $front->addModuleDirectory($dir);
            }

            $helperPath = $dir . DIRECTORY_SEPARATOR . 'Helpers';

            if (is_dir($helperPath)) {
                $view->addHelperPath($helperPath, $module . '_' . 'Helpers');
                Zend_Controller_Action_HelperBroker::addPath($helperPath);
            }
        }

        Zend_Registry::set('view', $view);
        $view->webPath  = $config->webpath;
        Zend_Registry::set('translate', $translate);

        $front->setModuleControllerDirectoryName('Controllers');
        $front->addModuleDirectory(PHPR_CORE_PATH);

        $front->setParam('useDefaultControllerAlways', true);

        $front->throwExceptions(true);

        // Getting the output, otherwise the home page will be displayed
        ob_start();

        $front->dispatch($request, $response);
        $response = ob_get_contents();

        ob_end_clean();

        $this->assertTrue(strpos($response, '{"1":{"id":"1","name":"admin","users":[]},') > 0);
    }


}