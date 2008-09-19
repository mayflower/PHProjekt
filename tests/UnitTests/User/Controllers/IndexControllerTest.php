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
class User_IndexController_Test extends PHPUnit_Framework_TestCase
{
    /**
     * Test the user list
     */
    public function testGetUsersAction()
    {
        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();

        $config = Zend_Registry::get('config');

        $request->setParams(array('action'=>'jsonList','controller'=>'index','module'=>'user'));

        $request->setBaseUrl($config->webpath.'index.php/Core/user/jsonGetUsers');
        $request->setPathInfo('/Core/user/jsonGetUsers');
        $request->setRequestUri('/Core/user/jsonGetUsers');

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
        $this->assertTrue(strpos(strtolower($response), strtolower('"numRows":1')) > 0);
    }

    /**
     * Test user set setting
     */
    public function testSetSettingAction()
    {
        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();

        $config = Zend_Registry::get('config');

        $request->setParams(array('action'=>'jsonList','controller'=>'index','module'=>'user'));

        $request->setBaseUrl($config->webpath.'index.php/Core/user/jsonSetSetting/name/timezone/value/3');
        $request->setPathInfo('/Core/user/jsonSetSetting/name/timezone/value/3');
        $request->setRequestUri('/Core/user/jsonSetSetting/name/timezone/value/3');

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
        $this->assertTrue(strpos(strtolower($response), strtolower('Value saved successful')) > 0);
    }

    /**
     * Test user get setting
     */
    public function testGetSettingAction()
    {
        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();

        $config = Zend_Registry::get('config');

        $request->setParams(array('action'=>'jsonList','controller'=>'index','module'=>'user'));

        $request->setBaseUrl($config->webpath.'index.php/Core/user/jsonGetSetting/name/timezone');
        $request->setPathInfo('/Core/user/jsonGetSetting/name/timezone');
        $request->setRequestUri('/Core/user/jsonGetSetting/name/timezone');

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
        $this->assertTrue(strpos(strtolower($response), strtolower('"3"')) > 0);
    }

    /**
     * Test user get setting list
     */
    public function testGetSettingListAction()
    {
        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();

        $config = Zend_Registry::get('config');

        $request->setParams(array('action'=>'jsonList','controller'=>'index','module'=>'user'));

        $request->setBaseUrl($config->webpath.'index.php/Core/user/jsonGetSettingList');
        $request->setPathInfo('/Core/user/jsonGetSettingList');
        $request->setRequestUri('/Core/user/jsonGetSettingList');

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
        $this->assertTrue(strpos(strtolower($response), strtolower('{"id":"4","keyValue":"timeZone","value":"3"}')) > 0);
    }

    /**
     * Test user delete setting
     */
    public function testDeleteSetting()
    {
        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();

        $config = Zend_Registry::get('config');

        $request->setParams(array('action'=>'jsonList','controller'=>'index','module'=>'user'));

        $request->setBaseUrl($config->webpath.'index.php/Core/user/jsonDeleteSetting/name/timezone');
        $request->setPathInfo('/Core/user/jsonDeleteSetting/name/timezone');
        $request->setRequestUri('/Core/user/jsonDeleteSetting/name/timezone');

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
        $this->assertTrue(strpos(strtolower($response), strtolower('Value deleted successful')) > 0);
    }

    /**
     * Test of json save multiple User
     */
    public function testJsonSaveMultiple()
    {
        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();

        $config = Zend_Registry::get('config');
        $config->language = "en";

        $request->setParams(array('action'=>'jsonSave','controller'=>'index','module'=>'User'));

        $request->setBaseUrl($config->webpath.'index.php/Core/user/jsonSaveMultiple/data[1][firstname]/John/data[2][lastname]/Doe');
        $request->setPathInfo('/Core/user/jsonSaveMultiple/data[1][firstname]/John/data[2][lastname]/Doe');
        $request->setRequestUri('/Core/user/jsonSaveMultiple/data[1][firstname]/John/data[2][lastname]/Doe');

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
     * Test of json save multiple User
     */
    public function testJsonSaveMultipleSetting()
    {
        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();

        $config = Zend_Registry::get('config');
        $config->language = "en";

        $request->setParams(array('action'=>'jsonSave','controller'=>'index','module'=>'User'));

        $request->setBaseUrl($config->webpath.'index.php/Core/user/jsonSaveMultipleSetting/data[1][value]/-3/data[2][value]/es');
        $request->setPathInfo('/Core/user/jsonSaveMultipleSetting/data[1][value]/-3/data[2][value]/es');
        $request->setRequestUri('/Core/user/jsonSaveMultipleSetting/data[1][value]/-3/data[2][value]/es');

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


}