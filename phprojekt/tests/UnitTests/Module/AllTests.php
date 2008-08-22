<?php
/**
 * Test suite for the Module module
 *
 * LICENSE: Licensed under the terms of the GNU Publice License
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *             GNU Public License 2.0
 * @version    CVS: $Id: AllTests.php 853 2008-08-11 06:34:28Z polidor $
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
*/

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Default_AllTests::main');
}

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

// require_once 'Controllers/BaseTest.php';
require_once 'Controllers/IndexControllerTest.php';
require_once 'Models/ModuleTest.php';
require_once 'Models/InformationTest.php';

/**
 * Test suite for the Module module
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *             GNU Public License 2.0
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class Module_AllTests
{
    /**
     * Runs the test suite
     *
     */
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    /**
     * Builds the test suite containing all
     * tests of this module and returns the suite
     *
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Module Controller');

        $suite->addTestSuite('Module_IndexController_Test');
        $suite->addTestSuite('Phprojekt_ModuleModelModule_Test');
        $suite->addTestSuite('Phprojekt_ModuleModelInformation_Test');
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Default_AllTests::main') {
    Framework_AllTests::main();
}