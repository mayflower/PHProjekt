<?php
/**
 * Test suite for the default module
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
    define('PHPUnit_MAIN_METHOD', 'Phprojekt_AllTests::main');
}



require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

set_include_path('.' . PATH_SEPARATOR
               . PHPR_LIBRARY_PATH . PATH_SEPARATOR
               . PHPR_CORE_PATH . PATH_SEPARATOR
               . get_include_path());


require_once 'Zend/Loader.php';
Zend_Loader::registerAutoload();

require_once 'ActiveRecord/AbstractTest.php';
require_once 'Tree/Node/DatabaseTest.php';
require_once 'LoaderTest.php';
require_once 'DatabaseManagerTest.php';
require_once 'DatabaseManager/FieldTest.php';
require_once 'Item/AbstractTest.php';
require_once 'LanguageAdapterTest.php';
require_once 'LanguageTest.php';
require_once 'LogTest.php';
require_once 'ErrorTest.php';
require_once 'HistoryTest.php';
require_once 'Filter/UserFilterTest.php';
require_once 'Mail/NotificationTest.php';
require_once 'DispatcherTest.php';
require_once 'AuthTest.php';

/**
 * Test suite for the default module
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *             GNU Public License 2.0
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class Phprojekt_AllTests
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
        $suite = new PHPUnit_Framework_TestSuite('Default Controller');

        $suite->addTestSuite('Phprojekt_ActiveRecord_AbstractTest');
        $suite->addTestSuite('Phprojekt_Tree_Node_DatabaseTest');
        $suite->addTestSuite('Phprojekt_HistoryTest');
        $suite->addTestSuite('Phprojekt_LoaderTest');
        $suite->addTestSuite('Phprojekt_DatabaseManagerTest');
        $suite->addTestSuite('Phprojekt_DatabaseManager_FieldTest');
        $suite->addTestSuite('Phprojekt_Item_AbstractTest');
        $suite->addTestSuite('Phprojekt_LanguageAdapterTest');
        $suite->addTestSuite('Phprojekt_LanguageTest');
        $suite->addTestSuite('Phprojekt_LogTest');
        $suite->addTestSuite('Phprojekt_ErrorTest');
        $suite->addTestSuite('Phprojekt_Filter_UserFilterTest');
        $suite->addTestSuite('Phprojekt_AuthTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Phprojekt_AllTests::main') {
       Phprojekt_AllTests::main();
}
