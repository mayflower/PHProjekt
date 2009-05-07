<?php
/**
 * Test suite for the default module
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Phprojekt_AllTests::main');
}

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

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
require_once 'AclTest.php';
require_once 'RoleRightsTest.php';
require_once 'GroupTest.php';
require_once 'Converter/JsonTest.php';
require_once 'Converter/CsvTest.php';
require_once 'Converter/TextTest.php';
require_once 'ModelInformation/DefaultTest.php';
require_once 'LoaderTest.php';
require_once 'Tags/DefaultTest.php';
require_once 'Filter/ParseTreeTest.php';
require_once 'Search/DefaultTest.php';
require_once 'ModuleTest.php';
require_once 'TabsTest.php';
require_once 'PhprojektTest.php';

/**
 * Test suite for the default module
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
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

        $suite->addTestSuite('Phprojekt_ModuleTest');
        $suite->addTestSuite('Phprojekt_GroupTest');
        $suite->addTestSuite('Phprojekt_LoaderTest');
        $suite->addTestSuite('Phprojekt_DatabaseManagerTest');
        $suite->addTestSuite('Phprojekt_DatabaseManager_FieldTest');
        $suite->addTestSuite('Phprojekt_Item_AbstractTest');
        $suite->addTestSuite('Phprojekt_LanguageTest');
        $suite->addTestSuite('Phprojekt_LanguageAdapterTest');
        $suite->addTestSuite('Phprojekt_LogTest');
        $suite->addTestSuite('Phprojekt_ErrorTest');
        $suite->addTestSuite('Phprojekt_ActiveRecord_AbstractTest');
        $suite->addTestSuite('Phprojekt_HistoryTest');
        $suite->addTestSuite('Phprojekt_Filter_UserFilterTest');
        $suite->addTestSuite('Phprojekt_Tree_Node_DatabaseTest');
        $suite->addTestSuite('Phprojekt_AuthTest');
        $suite->addTestSuite('Phprojekt_AclTest');
        $suite->addTestSuite('Phprojekt_RoleRightsTest');
        $suite->addTestSuite('Phprojekt_Converter_JsonTest');
        $suite->addTestSuite('Phprojekt_Converter_CsvTest');
        $suite->addTestSuite('Phprojekt_Converter_TextTest');
        $suite->addTestSuite('Phprojekt_ModelInformation_DefaultTest');
        $suite->addTestSuite('Phprojekt_Tags_DefaultTest');
        //$suite->addTestSuite('Phprojekt_Filter_ParseTreeTest');
        $suite->addTestSuite('Phprojekt_Search_DefaultTest');
        $suite->addTestSuite('Phprojekt_TabsTest');
        $suite->addTestSuite('Phprojekt_PhprojektTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Phprojekt_AllTests::main') {
       Phprojekt_AllTests::main();
}
