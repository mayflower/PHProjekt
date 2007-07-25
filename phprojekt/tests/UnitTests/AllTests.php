<?php
/**
 * AllTests merges all test from the modules
 *
 * AllTests merges defined test suites for each
 * module, while each module itself has its own AllTests
 *
 * LICENSE: Licensed under the terms of the GNU Publice License
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *             GNU Public License 2.0
 * @version    CVS: $Id$
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */


if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'Default/AllTests.php';
require_once 'Phprojekt/AllTests.php';

/**
 * AllTests merges all test from the modules
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *             GNU Public License 2.0
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class AllTests
{
    /**
     * Initialize the TestRunner
     *
     */
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    /**
     * Merges the test suites
     *
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        // $log = new Phprojekt_Log(new Zend_Config_Ini('../../configuration.ini', 'production'));
        Zend_Registry::set('log', $log);

        $suite = new PHPUnit_Framework_TestSuite('PHPUnit');
        $suite->addTest(Default_AllTests::suite());
        $suite->addTest(Phprojekt_AllTests::suite());

        // add here additional test suites

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'AllTests::main') {
    AllTests::main();
}
