<?php
/**
 * Setup routine
 *
 * LICENSE: Licensed under the terms of the GNU Publice License
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *             GNU Public License 2.0
 * @version    CVS: $Id: AllTests.php 828 2008-07-07 02:05:54Z gustavo $
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
*/

define("SETUP_ROUTINE", true);

include_once("setup.inc.php");

// check the server and other stuff before start installation
checkServer();

// create the setup form or install the system
if (empty($_REQUEST['mysql_server'])) {
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
