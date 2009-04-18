<?php
/**
 * Setup routine
 *
 * LICENSE: Licensed under the terms of the GNU Publice License
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *             GNU Public License 2.0
 * @version    $Id$
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
*/

define("SETUP_ROUTINE", true);
error_reporting(0);
session_start();

ini_set('max_execution_time', 0);

include_once("setup.inc.php");

define('PHPR_ROOT_PATH', realpath(dirname(__FILE__) . '/../../'));
define('PHPR_CORE_PATH', PHPR_ROOT_PATH . DIRECTORY_SEPARATOR . 'application');
define('PHPR_LIBRARY_PATH', PHPR_ROOT_PATH . DIRECTORY_SEPARATOR . 'library');

set_include_path('.' . PATH_SEPARATOR . PHPR_LIBRARY_PATH . PATH_SEPARATOR
    . PHPR_CORE_PATH . PATH_SEPARATOR . get_include_path());

require_once 'Zend/Loader.php';
require_once 'Phprojekt/Loader.php';

Zend_Loader::registerAutoload('Phprojekt_Loader');

// check the server and other stuff before start installation
checkServer();

// create the setup form or install the system
if (empty($_REQUEST['server_host'])) {
    // draw first page
    displaySetupForm();
} else {
    if (preInstallChecks()) {
        // process installation
        installPhprojekt();
        // installation finished, let's show the final page
        displayFinished();
    } else {
        displaySetupForm();
    }
}
