<?php
/**
 * Bootstrap file.
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
 * @category   Htdocs
 * @package    Htdocs
 * @copyright  2007 Mayflower GmbH
 * @version    CVS: $Id$
 * @license    LGPL 2.1 (See LICENSE file)
 * @author     David Soria Parra <david.soria_parra@mayflower.de>
 */
define('PHPR_CONFIG_SECTION', 'production');

define('PHPR_ROOT_PATH', realpath(dirname(__FILE__) . '/../'));
define('PHPR_CORE_PATH', PHPR_ROOT_PATH . DIRECTORY_SEPARATOR . 'application');
define('PHPR_LIBRARY_PATH', PHPR_ROOT_PATH . DIRECTORY_SEPARATOR . 'library');
define('PHPR_CONFIG_FILE', PHPR_ROOT_PATH . DIRECTORY_SEPARATOR . 'configuration.ini');
define('PHPR_TEMP_PATH', PHPR_ROOT_PATH . DIRECTORY_SEPARATOR . 'tmp/');

set_include_path('.' . PATH_SEPARATOR
               . PHPR_LIBRARY_PATH . PATH_SEPARATOR
               . PHPR_CORE_PATH . PATH_SEPARATOR
               . get_include_path());

require_once 'Zend/Loader.php';
require_once 'Phprojekt/Loader.php';

Zend_Loader::registerAutoload('Phprojekt_Loader');

/* Start zend session to handle all session stuff */
Zend_Session::start();

/* Read the config file, but only the production setting */
$config = new Zend_Config_Ini(PHPR_CONFIG_FILE, PHPR_CONFIG_SECTION, true);
Zend_Registry::set('config', $config);

if (substr($config->webpath, -1) != '/') {
    $config->webpath.= '/';
}

define('PHPR_ROOT_WEB_PATH', $config->webpath . 'index.php/');

/* Make the connection to the DB*/
// require_once 'Zend/Db.php';
$db = Zend_Db::factory($config->database->type, array(
    'host'     => $config->database->host,
    'username' => $config->database->username,
    'password' => $config->database->password,
    'dbname'   => $config->database->name,
));
Zend_Registry::set('db', $db);

/**
 * Initialize Debug Log
 *
 * use $log->priority($txt);
 * Where priority can be emerg,alert,crit,err,warn,notice,info,debug
 */
Zend_Loader::loadClass('Phprojekt_Log', PHPR_LIBRARY_PATH);
$log = new Phprojekt_Log($config);
Zend_Registry::set('log', $log);

$view = new Zend_View();
$view->addScriptPath(PHPR_CORE_PATH . '/Default/Views/dojo/');

$viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer($view);
$viewRenderer->setViewBasePathSpec(':moduleDir/Views');
$viewRenderer->setViewScriptPathSpec(':action.:suffix');

Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);

/* Languages Set */
Zend_Loader::loadClass('Phprojekt_Language', PHPR_LIBRARY_PATH);
$translate = new Phprojekt_Language(Phprojekt_User_User::getSetting("language",$config->language));

/* Front controller stuff */
$front = Zend_Controller_Front::getInstance();
$front->setDispatcher(new Phprojekt_Dispatcher());

$plugin = new Zend_Controller_Plugin_ErrorHandler();
$plugin->setErrorHandlerModule('Default');
$plugin->setErrorHandlerController('Error');
$plugin->setErrorHandlerAction('error');

$front->registerPlugin($plugin);

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
Zend_Registry::set('translate', $translate);

$front->setModuleControllerDirectoryName('Controllers');
$front->addModuleDirectory(PHPR_CORE_PATH);

$front->setParam('useDefaultControllerAlways', true);

$front->dispatch();
