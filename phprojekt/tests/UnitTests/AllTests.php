<?php
/**
 * AllTests merges all test from the modules and provides
 * several switches to control unit testing
 *
 * AllTests merges defined test suites for each
 * module, while each module itself has its own AllTests
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @category   PHProjekt
 * @package    UnitTests
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */

// Force quotes off to run in cruisecontrol
ini_set("magic_quotes_gpc", 0);
ini_set("magic_quotes_runtime", 0);
ini_set("magic_quotes_sybase", 0);

$config = "configuration.php";
if(getenv('P6_TEST_CONFIG')) {
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
Zend_Db_Table_Abstract::getDefaultMetadataCache()->clean();

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}

require_once 'PHPUnit/Autoload.php';

require_once 'Todo/AllTests.php';
require_once 'Default/AllTests.php';
require_once 'Phprojekt/AllTests.php';
require_once 'Timecard/AllTests.php';
//require_once 'History/AllTests.php';
require_once 'User/AllTests.php';
//require_once 'Calendar/AllTests.php';
require_once 'Note/AllTests.php';
require_once 'Role/AllTests.php';
require_once 'Tab/AllTests.php';
//require_once 'Module/AllTests.php';
require_once 'Project/AllTests.php';
require_once 'Helpdesk/AllTests.php';
require_once 'Contact/AllTests.php';
require_once 'Filemanager/AllTests.php';
require_once 'Gantt/AllTests.php';
require_once 'Statistic/AllTests.php';

/**
 * AllTests merges all test from the modules
 *
 * @category   PHProjekt
 * @package    UnitTests
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class AllTests extends PHPUnit_Framework_TestSuite
{
    /**
     * Initialize the TestRunner
     */
    public static function main()
    {
        // for compability with phpunit offer suite() without any parameter.
        // in that case use defaults

        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    /**
     * Merges the test suites
     *
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        $authNamespace         = new Zend_Session_Namespace('Phprojekt_Auth-login');
        $authNamespace->userId = 1;
        $authNamespace->admin  = 1;

        $suite = new PHPUnit_Framework_TestSuite('PHPUnit');

        $suite->sharedFixture = Phprojekt::getInstance()->getDb();

        $suite->addTest(User_AllTests::suite());
        $suite->addTest(Timecard_AllTests::suite());
        $suite->addTest(Statistic_AllTests::suite());
        $suite->addTest(Todo_AllTests::suite());
        $suite->addTest(Phprojekt_AllTests::suite());
        $suite->addTest(Role_AllTests::suite());
        $suite->addTest(Tab_AllTests::suite());
        $suite->addTest(Project_AllTests::suite());
        $suite->addTest(Contact_AllTests::suite());
        $suite->addTest(Filemanager_AllTests::suite());
        $suite->addTest(Gantt_AllTests::suite());
        $suite->addTest(Default_AllTests::suite());

        //$suite->addTest(Calendar_AllTests::suite());
        //$suite->addTest(Note_AllTests::suite());
        //$suite->addTest(Helpdesk_AllTests::suite());
        //$suite->addTest(History_AllTests::suite());
        //$suite->addTest(Module_AllTests::suite());
        // Add here additional test suites

        //$suite->addTestSuite(Selenium_AllTests::suite());

        return $suite;
    }
}

/**
 * This is actually our entry point. If we run from the commandline
 * we support several switches to the AllTest file.
 *
 * To see the switches try
 *   php AllTests.php -h
 */
if (PHPUnit_MAIN_METHOD == 'AllTests::main') {
    AllTests::main();
}
