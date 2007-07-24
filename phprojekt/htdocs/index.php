<?php
/**
 * Bootstrap file.
 *
 * @category   Htdocs
 * @package    Htdocs
 * @copyright  2007 Mayflower GmbH
 * @version    CVS: $Id$
 * @license    http://phprojekt.com/license
 * @author     David Soria Parra <david.soria_parra@mayflower.de>
 */
define('PHPR_CONFIG_SECTION', 'production');

define('PHPR_ROOT_PATH', realpath( dirname(__FILE__) . '/../') );
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
$config = new Zend_Config_Ini(PHPR_CONFIG_FILE,PHPR_CONFIG_SECTION);
Zend_Registry::set('config', $config);
define('PHPR_ROOT_WEB_PATH',$config->webpath . 'index.php/');

/* Make the connection to the DB*/
require_once 'Zend/Db.php';
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
Zend_Loader::loadClass('Phprojekt_Log', PHPR_CORE_PATH);
$oLog = new Phprojekt_Log($config);
Zend_Registry::set('log', $oLog);

/**
 * Configure the ViewRenderer Helper
 * to enable the autorendering feature of ZF
 */
Zend_Loader::loadClass('Default_Helpers_Smarty', PHPR_CORE_PATH);
$oView = new Default_Helpers_Smarty(PHPR_TEMP_PATH . DIRECTORY_SEPARATOR . 'templates_c');

$oViewRenderer = new Zend_Controller_Action_Helper_ViewRenderer($oView);
$oViewRenderer->setViewBasePathSpec(':moduleDir/Views')
              ->setViewScriptPathSpec(':action.:suffix')
              ->setViewScriptPathNoControllerSpec(':action.:suffix')
              ->setViewSuffix('tpl');
Zend_Controller_Action_HelperBroker::addHelper($oViewRenderer);
Zend_Registry::set('view', $oView);

/* Languages Set */
Zend_Loader::loadClass('Phprojekt_Language', PHPR_CORE_PATH);
$locale = $config->language;
$oTranslate = new Phprojekt_Language('Phproject',PHPR_ROOT_PATH
                                                 . '/languages/'
                                                 . $locale
                                                 . '.inc.php', $locale);
Zend_Registry::set('translate', $oTranslate);

/* Front controller stuff */
$front = Zend_Controller_Front::getInstance();

foreach (scandir(PHPR_CORE_PATH) as $module)
{
    $front->addControllerDirectory(PHPR_CORE_PATH
                                 . DIRECTORY_SEPARATOR
                                 . $module
                                 . DIRECTORY_SEPARATOR
                                 . 'Controllers', strtolower($module));
}

$front->addModuleDirectory(PHPR_CORE_PATH);
$front->setParam('useDefaultControllerAlways', true);

$front->throwExceptions(true);
$front->dispatch();