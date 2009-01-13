<?php
/**
 * Phprojekt Class for initialize the Zend Framework
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
 * @version    $Id:$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Phprojekt Class for initialize the Zend Framework
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt {
    /**
     * Singleton instance
     *
     * @var Phprojekt
     */
    protected static $_instance = null;

    /**
     * Config class
     *
     * @var Zend_Config_Ini
     */
	protected $_config;

    /**
     * Db class
     *
     * @var Zend_Db
     */
	protected $_db;

    /**
     * Log class
     *
     * @var Phprojekt_Log
     */
	protected $_log;

    /**
     * Translate class
     *
     * @var Phprojekt_Language
     */
	protected $_translate;

    /**
     * View class
     *
     * @var Zend_View
     */
	protected $_view;

    /**
     * Return this class only one time
     *
     * @return Phprojekt
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
            self::$_instance->_initialize();
        }
        return self::$_instance;
    }

    /**
     * Return the Config class
     *
     * @return Zend_Config_Ini
     */
    public function getConfig()
    {
		return $this->_config;
	}

    /**
     * Return the Db class
     * If don't exists, try to create it
     *
     * @return Zend_Db
     */
    public function getDb()
    {
		if (null === $this->_db) {
			try {
				$this->_db = Zend_Db::factory($this->_config->database);
			} catch (Zend_Db_Adapter_Exception $error) {
				echo $error->getMessage();
				die();
			}
		}
		return $this->_db;
	}

    /**
     * Return the Log class
     * If don't exists, try to create it
     *
     * @return Phprojekt_Log
     */
	public function getLog()
	{
		if (null === $this->_log) {
            try {
                $this->_log = new Phprojekt_Log($this->_config);
			} catch (Zend_Log_Exception $error) {
				echo $error->getMessage();
				die();
			}
		}
		return $this->_log;
	}

    /**
     * Return the Translate class
     * If don't exists, try to create it
     *
     * @return Phprojekt_Language
     */
	public function getTranslate()
	{
		if (null === $this->_translate) {
            $this->_translate = new Phprojekt_Language(Phprojekt_User_User::getSetting("language",
                $this->_config->language));
		}
		return $this->_translate;
	}

    /**
     * Return the View class
     *
     * @return Zend_View
     */
	public function getView()
	{
		return $this->_view;
	}

	/**
	 * Initialize the paths,
	 * the config values and all the render stuff
	 *
	 * @return void
	 */
	public function _initialize()
	{
        define('PHPR_CORE_PATH', PHPR_ROOT_PATH . DIRECTORY_SEPARATOR . 'application');
        define('PHPR_LIBRARY_PATH', PHPR_ROOT_PATH . DIRECTORY_SEPARATOR . 'library');
        if (!defined('PHPR_CONFIG_FILE')) {
            define('PHPR_CONFIG_FILE', PHPR_ROOT_PATH . DIRECTORY_SEPARATOR . 'configuration.ini');
        }
        define('PHPR_TEMP_PATH', PHPR_ROOT_PATH . DIRECTORY_SEPARATOR . 'tmp/');

        set_include_path('.' . PATH_SEPARATOR
            . PHPR_LIBRARY_PATH . PATH_SEPARATOR
            . PHPR_CORE_PATH . PATH_SEPARATOR
            . get_include_path());

        require_once 'Zend/Loader.php';
        require_once 'Phprojekt/Loader.php';

        Zend_Loader::registerAutoload('Phprojekt_Loader');

        /* Read the config file, but only the production setting */
        try {
            $this->_config = new Zend_Config_Ini(PHPR_CONFIG_FILE, PHPR_CONFIG_SECTION, true);
        } catch (Zend_Config_Exception $error) {
            $error->getMessage();
            echo 'You need the file configuration.ini to continue';
            die();
        }

        if (substr($this->_config->webpath, -1) != '/') {
            $this->_config->webpath.= '/';
        }

        define('PHPR_ROOT_WEB_PATH', $this->_config->webpath . 'index.php/');

        /* Start zend session to handle all session stuff */
        Zend_Session::start();

        // Set a metadata cache and clean it
        $frontendOptions = array('automatic_serialization' => true);
        $cache           = Zend_Cache::factory('Core', 'File', $frontendOptions);
        Zend_Db_Table_Abstract::setDefaultMetadataCache($cache);
        Zend_Db_Table_Abstract::getDefaultMetadataCache()->clean();

        $helperPaths  = $this->_getHelperPaths();
        $view         = $this->_setView($helperPaths);

        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer($view);
        $viewRenderer->setViewBasePathSpec(':moduleDir/Views');
        $viewRenderer->setViewScriptPathSpec(':action.:suffix');
        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
        foreach ($helperPaths as $helperPath) {
            Zend_Controller_Action_HelperBroker::addPath($helperPath['path']);
        }

        $plugin = new Zend_Controller_Plugin_ErrorHandler();
        $plugin->setErrorHandlerModule('Default');
        $plugin->setErrorHandlerController('Error');
        $plugin->setErrorHandlerAction('error');

        $front = Zend_Controller_Front::getInstance();
        $front->setDispatcher(new Phprojekt_Dispatcher());
        $front->registerPlugin($plugin);
        $front->setDefaultModule('Default');
        $front->setModuleControllerDirectoryName('Controllers');
        $front->addModuleDirectory(PHPR_CORE_PATH);
        $front->setParam('useDefaultControllerAlways', true);
	}

	/**
	 * Cache the View Class
	 *
	 * @param array $helperPaths Array with all the folders with helpers
	 *
	 * @return Zend_View
	 */
	private function _setView($helperPaths)
	{
	    $viewNamespace = new Zend_Session_Namespace('index_View');
        if (!isset($viewNamespace->view)) {
            $view = new Zend_View();
            $view->addScriptPath(PHPR_CORE_PATH . '/Default/Views/dojo/');
            foreach ($helperPaths as $helperPath) {
                if (is_dir($helperPath['path'])) {
                    $view->addHelperPath($helperPath['path'], $helperPath['module'] . '_' . 'Helpers');
                }
            }
            $viewNamespace->view = $view;
        } else {
            $view = $viewNamespace->view;
        }

        $this->_view = $view;

        return $view;
	}

	/**
	 * Cache the folders with helpers files
	 *
	 * @return array
	 */
	private function _getHelperPaths()
	{
	    $helperPathNamespace = new Zend_Session_Namespace('index_HelperPath');
        if (!isset($helperPathNamespace->helperPaths)) {
            $helperPaths = array();
            foreach (scandir(PHPR_CORE_PATH) as $module) {
                $dir = PHPR_CORE_PATH . DIRECTORY_SEPARATOR . $module;
                if (is_dir(!$dir)) {
                    continue;
                }

                $helperPaths = array('module' => $module,
                                     'path'   => $dir . DIRECTORY_SEPARATOR . 'Helpers');
            }
            $helperPathNamespace->helperPaths = $helperPaths;
        } else {
            $helperPaths = $helperPathNamespace->helperPaths;
        }

        return $helperPaths;
	}

	/**
	 * Run the dispatch
	 *
	 * @return void
	 */
	public function run()
	{
        try {
            Zend_Controller_Front::getInstance()->dispatch();
        } catch (Exception $error) {
            echo "Caught exception: " . $error->getFile() . ':' . $error->getLine() . "\n";
            echo '<br/>' . $error->getMessage();
            echo '<pre>' . $error->getTraceAsString() . '</pre>';
        }
    }
}
