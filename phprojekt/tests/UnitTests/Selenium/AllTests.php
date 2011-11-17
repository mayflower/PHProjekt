<?php
/**
 * Unit test
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
 * @subpackage Selenium
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Johann-Peter Hartmann <johann-peter.hartmann@mayflower.de>
 */

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Selenium_AllTests::main');
}

require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'LoginTest.php';
require_once 'ProjectTest.php';
require_once 'TodoTest.php';

/**
 * Static test suite.
 *
 * @category   PHProjekt
 * @package    UnitTests
 * @subpackage Selenium
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Johann-Peter Hartmann <johann-peter.hartmann@mayflower.de>
 */
class Selenium_AllTests
{
    /**
     * Runs the test suite
     */
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Selenium Test Suite');

        $suite->addTestSuite('Selenium_LoginTest');
        $suite->addTestSuite('Selenium_ProjectTest');
        $suite->addTestSuite('Selenium_TodoTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Selenium_AllTests::main') {
    Selenium_AllTests::main();
}
