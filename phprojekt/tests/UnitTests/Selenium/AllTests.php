<?php

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Selenium_AllTests::main');
}

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'LoginTest.php';
require_once 'ProjectTest.php';
require_once 'TodoTest.php';

/**
 * Static test suite.
 */
class Selenium_AllTests 
{
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

