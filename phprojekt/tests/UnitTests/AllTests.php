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

/* use command line switches to overwrite this */
define("DEFAULT_CONFIG_FILE", "configuration.php");
define("PHPR_CONFIG_FILE", "configuration.php");
define("DEFAULT_CONFIG_SECTION", "testing-mysql");
define("PHPR_CONFIG_SECTION", "testing-mysql");

define('PHPR_ROOT_PATH', realpath(dirname(__FILE__) . '/../../'));

require_once PHPR_ROOT_PATH . DIRECTORY_SEPARATOR . 'library' . DIRECTORY_SEPARATOR . 'Phprojekt.php';
Phprojekt::getInstance();
Zend_Db_Table_Abstract::getDefaultMetadataCache()->clean();

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'PHPUnit/Util/Filter.php';

require_once 'Default/AllTests.php';
require_once 'Phprojekt/AllTests.php';
require_once 'Timecard/AllTests.php';
require_once 'History/AllTests.php';
require_once 'User/AllTests.php';
require_once 'Calendar/AllTests.php';
require_once 'Note/AllTests.php';
require_once 'Role/AllTests.php';
require_once 'Todo/AllTests.php';
require_once 'Tab/AllTests.php';
require_once 'Module/AllTests.php';
require_once 'Project/AllTests.php';
require_once 'Minutes/AllTests.php';
require_once 'Helpdesk/AllTests.php';
require_once 'Contact/AllTests.php';
require_once 'Filemanager/AllTests.php';
require_once 'Gantt/AllTests.php';
require_once 'Statistic/AllTests.php';

// require_once 'Selenium/AllTests.php';

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
        // These directories are covered for the code coverage even they are not part of unit testing
        PHPUnit_Util_Filter::addDirectoryToWhitelist(dirname(dirname(dirname(__FILE__))) . '/application');
        PHPUnit_Util_Filter::addDirectoryToWhitelist(dirname(dirname(dirname(__FILE__))) . '/library/Phprojekt');
        // Avoid Selenium checks
        PHPUnit_Util_Filter::addDirectoryToFilter(dirname(__FILE__) . '/Selenium');

        $authNamespace         = new Zend_Session_Namespace('Phprojekt_Auth-login');
        $authNamespace->userId = 1;
        $authNamespace->admin  = 1;

        $suite = new PHPUnit_Framework_TestSuite('PHPUnit');

        $suite->sharedFixture = Phprojekt::getInstance()->getDb();

        $suite->addTest(Timecard_AllTests::suite());
        $suite->addTest(Statistic_AllTests::suite());
        $suite->addTest(User_AllTests::suite());
        $suite->addTest(Calendar_AllTests::suite());
        $suite->addTest(Note_AllTests::suite());
        $suite->addTest(Todo_AllTests::suite());
        $suite->addTest(Helpdesk_AllTests::suite());
        $suite->addTest(Phprojekt_AllTests::suite());
        $suite->addTest(History_AllTests::suite());
        $suite->addTest(Role_AllTests::suite());
        $suite->addTest(Tab_AllTests::suite());
        $suite->addTest(Project_AllTests::suite());
        $suite->addTest(Module_AllTests::suite());
        $suite->addTest(Contact_AllTests::suite());
        $suite->addTest(Filemanager_AllTests::suite());
        $suite->addTest(Gantt_AllTests::suite());
        $suite->addTest(Minutes_AllTests::suite());

        // Add here additional test suites


        $suite->addTest(Default_AllTests::suite());
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

    /* default settings */
    $whiteListing = true;
    $configFile   = DEFAULT_CONFIG_FILE;
    $configSect   = DEFAULT_CONFIG_SECTION;

    if (function_exists('getopt') && isset($argv)) { /* Not available on windows */
        $options = getopt('s:c:hd');
        if (array_key_exists('h', $options)) {
            usage();
        }

        if (array_key_exists('c', $options)) {
            $configFile = $options['c'];
        }

        if (array_key_exists('s', $options)) {
            $configSect = $options['s'];
        }

        if (array_key_exists('d', $options)) {
            $whiteListing = false;
        }

        if (!is_readable($configFile)) {
            fprintf(STDERR, "Cannot read %s\nAborted\n", $configFile);
            exit;
        }
    } elseif (isset($_GET)) {
        /* @todo make checks to avoid security leaks */
        if (array_key_exists('c', $_GET)) {
            $configFile = realpath($_GET['c']);
        }

        if (array_key_exists('s', $_GET)) {
            $configSect = $_GET['s'];
        }

        if (array_key_exists('d', $_GET)) {
            $whiteListing = ! $whiteListing;
        }
    }

    $config = new Zend_Config_Ini($configFile, $configSect, array("allowModifications" => true));
    Zend_Registry::set('config', $config);

    if ($whiteListing) {
         // Enable whitelisting for unit tests, these directories are
         // covered for the code coverage even they are not part of unit testing
        PHPUnit_Util_Filter::addDirectoryToWhitelist($config->applicationDir . '/application');
        PHPUnit_Util_Filter::addDirectoryToWhitelist(PHPR_LIBRARY_PATH . '/Phprojekt');
    }

    // Avoid Selenium checks
    PHPUnit_Util_Filter::addDirectoryToFilter('Selenium');

    AllTests::main();
}

function usage() {
    $doc = <<<EOF
PHProjekt UnitTesting suite. Uses PHPUnit by Sebastian Bergmann.

usage:
 php AllTests.php [OPTIONS]

 OPTIONS:
    -h           show help
    -c <file>    use <file> as configuration file, default 'configuration.php'
    -s <section> <section> is used to read the ini, default 'testing-mysql'
    -d           disable whitelist filtering
    -l           disable logging

EOF;
    print $doc."\n";
    exit;
}
