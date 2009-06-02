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
 * @version    $Id$
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
class Phprojekt
{
    /**
     * The first part of the version number
     */
    const VERSION_MAJOR = 6;

    /**
     * The second part of the version number
     */
    const VERSION_MINOR = 0;

    /**
     * The third part of the version number
     */
    const VERSION_RELEASE = 0;

    /**
     * The extra part of the version number
     */
    const VERSION_EXTRA = "RC1";

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
     * Returns the current version of PHProjekt
     *
     * @return string the version
     */
    public static function getVersion()
    {
        if (null !== self::VERSION_EXTRA) {
            return sprintf("%d.%d.%d-%s", self::VERSION_MAJOR, self::VERSION_MINOR, self::VERSION_RELEASE,
                self::VERSION_EXTRA);
        } else {
            return sprintf("%d.%d.%d", self::VERSION_MAJOR, self::VERSION_MINOR, self::VERSION_RELEASE);
        }
    }

    /**
     * Compares two PHProjekt version strings. Returns 1 if the first
     * version is higher than the second one, 0 if they are equal and
     * -1 if the second version is higher.
     *
     * @param string $version1 The first string to check
     * @param string $version2 The second string to check
     *
     * @return int
     */
    public static function compareVersion($version1, $version2)
    {
        if (preg_match("@^([0-9])\.([0-9])\.([0-9]+)(-[a-zA-Z0-9]+)?$@i", $version1, $matches)) {
            $v1elements = array_slice($matches, 1);
        } else {
            throw InvalidArgumentException();
        }

        if (preg_match("@^([0-9])\.([0-9])\.([0-9]+)(-[a-zA-Z0-9]+)?$@i", $version2, $matches)) {
            $v2elements = array_slice($matches, 1);
        } else {
            throw InvalidArgumentException();
        }

        for ($i = 0; $i < 3; $i++) {
            if ((int) $v1elements[$i] > (int) $v2elements[$i]) {
                return 1;
            }

            if ((int) $v1elements[$i] < (int) $v2elements[$i]) {
                return -1;
            }
        }

        if (count($v1elements) < count($v2elements)) {
            return 1;
        }

        if (count($v1elements) > count($v2elements)) {
            return -1;
        }

        if (isset($v1elements[3]) && isset($v2elements[3])) {
            return strcmp($v1elements[3], $v2elements[3]);
        } else if (!isset($v1elements[3]) && isset($v2elements[3])) {
            return 1;
        } else if (isset($v1elements[3]) && !isset($v2elements[3])) {
            return -1;
        } else {
            return 0;
        }
    }

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
            $language         = Phprojekt_User_User::getSetting("language", $this->_config->language);
            $this->_translate = new Phprojekt_Language($language);
        }

        return $this->_translate;
    }

    /**
     * Translate a string using the current module
     *
     * @param string             $message Message to translate
     * @param string|Zend_Locale $locale  Locale/Language to set
     *
     * @return string
     */
    public function translate($message, $locale = null)
    {
        $translate  = Phprojekt::getInstance()->getTranslate();
        $moduleName = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();

        if (null === $locale) {
            $locale = Phprojekt_User_User::getSetting("language", $this->_config->language);
        }

        return $this->_translate->translate($message, $moduleName, $locale);
    }

    /**
     * Return the tooltip for a field
     *
     * 1. Look for the tooltip in the current module language file.
     * 2. Look for the tooltip in the Default module language file.
     * 3. Look for the tooltip in the current module english file.
     * 4. Look for the tooltip in the Default module english file.
     * 5. return nothing
     *
     * @param string $field The field for the tooltipo
     *
     * @return string
     */
    public function getTooltip($field)
    {
        $translate  = Phprojekt::getInstance()->getTranslate();
        $moduleName = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();

        $hints = $this->_translate->translate('Tooltip', $moduleName);
        if (!is_array($hints)) {
            $hints = $this->_translate->translate('Tooltip', $moduleName, 'en');
            if (!is_array($hints)) {
                $hints = array();
            }
        }

        return (isset($hints[$field])) ? $hints[$field] : '';
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

        // Read the config file, but only the production setting
        try {
            $this->_config = new Zend_Config_Ini(PHPR_CONFIG_FILE, PHPR_CONFIG_SECTION, true);
        } catch (Zend_Config_Exception $error) {
            $error->getMessage();
            echo 'You need the file configuration.ini to continue.
                  Have you tried the <a href="setup/setup.php">setup</a> routine?';
            die();
        }

        if (substr($this->_config->webpath, -1) != '/') {
            $this->_config->webpath.= '/';
        }

        define('PHPR_ROOT_WEB_PATH', $this->_config->webpath . 'index.php/');

        // Set the timezone to UTC
        date_default_timezone_set('UTC');

        // Start zend session to handle all session stuff
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
                if (is_dir(PHPR_CORE_PATH . '/' . $helperPath['module'] . '/Views/dojo')) {
                    $view->addScriptPath(PHPR_CORE_PATH . '/' . $helperPath['module'] . '/Views/dojo/');
                }
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

                $helperPaths[] = array('module' => $module,
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
