<?php
ini_set("magic_quotes_gpc", 0);
ini_set("magic_quotes_runtime", 0);
ini_set("magic_quotes_sybase", 0);

$config = "configuration.php";
if (getenv('P6_TEST_CONFIG')) {
    $config = getenv('P6_TEST_CONFIG');
}

/* use command line switches to overwrite this */
define("DEFAULT_CONFIG_FILE", $config);
define("PHPR_CONFIG_FILE", $config);
define("DEFAULT_CONFIG_SECTION", "testing-mysql");
define("PHPR_CONFIG_SECTION", "testing-mysql");

define('PHPR_ROOT_PATH', realpath(dirname(__FILE__) . '/../../'));

require_once PHPR_ROOT_PATH . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'Phprojekt.php';
Phprojekt::getInstance();
// Phprojekt::getInstance() indirectly sets the error handler which eats our errors.
restore_error_handler();
Zend_Db_Table_Abstract::getDefaultMetadataCache()->clean();

$authNamespace         = new Zend_Session_Namespace('Phprojekt_Auth-login');
$authNamespace->userId = 1;
$authNamespace->admin  = 1;

Phprojekt::getInstance()->getDb()->query('SET sql_mode="STRICT_ALL_TABLES"');
Zend_Controller_Front::getInstance()->setBaseUrl('');
