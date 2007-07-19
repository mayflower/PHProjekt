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


define('PHPR_ROOT_PATH', realpath(dirname(__FILE__) . '/../../../'));
define('PHPR_LIBRARY_PATH', PHPR_ROOT_PATH . DIRECTORY_SEPARATOR . 'library');
define('PHPR_CORE_PATH', PHPR_ROOT_PATH . DIRECTORY_SEPARATOR . 'application');

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

set_include_path('.' . PATH_SEPARATOR
               . PHPR_LIBRARY_PATH . PATH_SEPARATOR
               . PHPR_CORE_PATH . PATH_SEPARATOR
               . get_include_path());


require_once 'Zend/Loader.php';
Zend_Loader::registerAutoload();

require_once 'ActiveRecordTest.php';

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

        $suite->addTestSuite('Phprojekt_ActiveRecordTest');
        // ...

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Phprojekt_AllTests::main') {
       Phprojekt_AllTests::main();
}
