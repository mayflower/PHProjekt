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
 * Tests for Index Controller
 */
abstract class FrontInit extends DatabaseTest
{
    public $request      = null;
    public $response     = null;
    public $front        = null;
    public $config       = null;
    public $content      = null;
    public $error        = null;
    public $errormessage = null;

    /**
     * Init the front for test it
     */
    public function __construct()
    {
        parent::__construct();
        $this->request  = new Zend_Controller_Request_Http();
        $this->response = new Zend_Controller_Response_Http();
        $this->config   = Phprojekt::getInstance()->getConfig();

        $this->config->language = "en";

        $this->request->setModuleName('Default');
        $this->request->setActionName('index');

        // Languages Set
        Zend_Loader::loadClass('Phprojekt_Language', PHPR_LIBRARY_PATH);

        $cache = Phprojekt::getInstance()->getCache();
        if (!($translate = $cache->load('Phprojekt_getTranslate_en'))) {
            $translate = new Phprojekt_Language(array('locale' => 'en'));
            $cache->save($translate, 'Phprojekt_getTranslate_en', array('Language'));
        }
        Zend_Registry::set('translate', $translate);

        $view = new Zend_View();
        $view->addScriptPath(PHPR_CORE_PATH . '/Default/Views/dojo/');

        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer($view);
        $viewRenderer->setViewBasePathSpec(':moduleDir/Views');
        $viewRenderer->setViewScriptPathSpec(':action.:suffix');

        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);

        /* Front controller stuff */
        $this->front = Zend_Controller_Front::getInstance();
        $this->front->setDispatcher(new Phprojekt_Dispatcher());

        $this->front->registerPlugin(new Zend_Controller_Plugin_ErrorHandler());
        $this->front->setDefaultModule('Default');

        $moduleDirectories = array();

        // System modules
        foreach (scandir(PHPR_CORE_PATH) as $module) {
            $dir = PHPR_CORE_PATH . DIRECTORY_SEPARATOR . $module;

            if (is_dir(!$dir)) {
                continue;
            }

            $helperPath = $dir . DIRECTORY_SEPARATOR . 'Helpers';

            if (is_dir($helperPath)) {
                $view->addHelperPath($helperPath, $module . '_' . 'Helpers');
                Zend_Controller_Action_HelperBroker::addPath($helperPath);
            }

            $dir = PHPR_CORE_PATH . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR . 'SubModules';
            if (is_dir($dir)) {
                if ($module != 'Core') {
                    $moduleDirectories[] = $dir;
                } else {
                    $coreModules = scandir($dir);
                    foreach ($coreModules as $coreModule) {
                        $coreDir = $dir . DIRECTORY_SEPARATOR . $coreModule;
                        if ($coreModule != '.'  && $coreModule != '..' && is_dir($coreDir)) {
                            $moduleDirectories[] = $coreDir;
                        }
                    }
                }
            }
        }

        // User modules
        foreach (scandir(PHPR_USER_CORE_PATH) as $module) {
            $dir = PHPR_USER_CORE_PATH . $module;

            if (is_dir(!$dir)) {
                continue;
            }

            $helperPath = $dir . DIRECTORY_SEPARATOR . 'Helpers';

            if (is_dir($helperPath)) {
                $view->addHelperPath($helperPath, $module . '_' . 'Helpers');
                Zend_Controller_Action_HelperBroker::addPath($helperPath);
            }

            $dir = PHPR_USER_CORE_PATH . $module . DIRECTORY_SEPARATOR . 'SubModules';
            if (is_dir($dir)) {
                $moduleDirectories[] = $dir;
            }
        }

        Zend_Registry::set('view', $view);

        $this->front->setModuleControllerDirectoryName('Controllers');
        $this->front->addModuleDirectory(PHPR_CORE_PATH);
        $this->front->addModuleDirectory(PHPR_USER_CORE_PATH);

        foreach ($moduleDirectories as $moduleDirectory) {
            $this->front->addModuleDirectory($moduleDirectory);
        }

        $this->front->setParam('useDefaultControllerAlways', true);

        $this->front->throwExceptions(true);
    }

    /**
     * Set the Url
     */
    public function setRequestUrl($url)
    {
        $this->request->setBaseUrl('index.php/'. $url);
        $this->request->setPathInfo('/' . $url);
        $this->request->setRequestUri('/' . $url);

        $this->request->setParam('csrfToken', Phprojekt::createCsrfToken());
    }

    /**
     * Get the responde and delte all the params later
     */
    public function getResponse()
    {
        $this->request->setDispatched(false);
        ob_start();
        $this->error = false;
        try {
            $this->front->dispatch($this->request, $this->response);
            $this->content = ob_get_contents();
            ob_end_clean();
        } catch (Exception $e) {
            /* make sure we end the output buffering in case of an exception */
            ob_end_clean();
            throw $e;
        }

        $this->request->setParams(array());

        return $this->content;
    }

    /**
     * Helper function to parse returned json.
     *
     * @param  string $json The json returned by getResponse()
     * @return array A php array containing the data as strings. (No type conversions)
     */
    public static function phprJsonToArray($json)
    {
        $json = substr($json, 5, -1);
        return Zend_Json::decode($json);
    }

    /**
     * Reset the request and the response to allow another request to be done in this test
     */
    protected function _reset()
    {
        $this->request  = new Zend_Controller_Request_Http();
        $this->response = new Zend_Controller_Response_Http();

        $this->request->setModuleName('Default');
        $this->request->setActionName('index');
    }
}
