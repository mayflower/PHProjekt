<?php
/**
 * Test suite for the todo module. This will help to test default module,
 * because in all cases this module uses parent functions.
 *
 * LICENSE: Licensed under the terms of the GNU Publice License
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *             GNU Public License 2.0
 * @version    CVS: $Id: AllTests.php 667 2008-04-14 02:19:16Z polidor $
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
*/

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Default_AllTests::main');
}

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'Controllers/IndexControllerTest.php';

/**
 * Test suite for the todo module
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *             GNU Public License 2.0
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */
class Todo_AllTests
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
        $suite = new PHPUnit_Framework_TestSuite('Todo Controller');

        $suite->addTestSuite('Todo_IndexController_Test');
        

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Todo_AllTests::main') {
    Framework_AllTests::main();
}
