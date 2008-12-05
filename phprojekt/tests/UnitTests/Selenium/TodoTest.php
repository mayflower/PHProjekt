<?php
/**
 * PHProjekt Selenium Test
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
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    CVS: $Id$
 * @author     Eduardo Polidor <polidor@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';


/**
 * Selenium Test for PHProjekt
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Eduardo Polidor <epolidor@mayflower.de>
 */
class Selenium_TodoTest extends PHPUnit_Extensions_SeleniumTestCase
{
    /**
     * Handle for the current configuration
     *
     * @var Zend_Config_Ini
     */
    private $_config;

    /**
     * List of the used Browser
     *
     * @var array the used test browser and selenium-rc hosts
     */
    public static $browsers = array(
      array(
        'name'    => 'Firefox on Linux',
        'browser' => '*chrome',
        'host'    => 'localhost',
        'port'    => 4444,
        'timeout' => 30000,
      ),
/*      array(
        'name'    => 'Internet Explorer on Windows Vista',
        'browser' => '*iexplore',
        'host'    => 'vistatest.mf-muc.nop',
        'port'    => 4444,
        'timeout' => 30000,
      ), */
    );

    /**
     * Collect coverage data at this url
     *
     * @var string Url of the coverage php file
     */
    protected $_coverageScriptUrl = 'http://cruisecontrol.mf-muc.nop/phpunit_coverage_phprojekt6.php';

    /**
     * setup the unit test. Use firefox as a browser and the document
     * root from the configuration file
     *
     * @return void
     */
    function setUp()
    {
        $this->setAutoStop(false); 
        $this->_config = Zend_Registry::get('config');
        $this->verificationErrors = array();
        $this->setBrowserUrl($this->_config->webpath);
    }

    function login()
    {
        $this->open("/phprojekt/htdocs/index.php/Login/index");
        $this->type("username", "david");
        $this->type("password", "test");
        $this->click("//input[@value='Send']");
        $this->waitForPageToLoad("30000");
    }

    function testTodoList()
    {
        $this->login();
        $this->open("/phprojekt/htdocs/index.php/Project/index");
        $this->click("link=Todo");
        $this->waitForPageToLoad("30000");
        try {
            $this->assertTrue($this->isTextPresent("Todo / List"));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            array_push($this->verificationErrors, $e->toString());
        }
    }

    function testTodoForm()
    {
        $this->login();
        $this->open("/phprojekt/htdocs/index.php/Todo/index/list");
        $this->click("link=Todo of Test Project");
        $this->waitForPageToLoad("30000");
        try {
            $this->assertTrue($this->isTextPresent("Project"));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            array_push($this->verificationErrors, $e->toString());
        }
        try {
            $this->assertTrue($this->isTextPresent("Title"));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            array_push($this->verificationErrors, $e->toString());
        }
        try {
            $this->assertTrue($this->isTextPresent("Notes"));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            array_push($this->verificationErrors, $e->toString());
        }
        try {
            $this->assertTrue($this->isTextPresent("Start date"));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            array_push($this->verificationErrors, $e->toString());
        }
        try {
            $this->assertTrue($this->isTextPresent("End date"));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            array_push($this->verificationErrors, $e->toString());
        }
        try {
            $this->assertTrue($this->isTextPresent("Priority"));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            array_push($this->verificationErrors, $e->toString());
        }
        try {
            $this->assertTrue($this->isTextPresent("Current Status"));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            array_push($this->verificationErrors, $e->toString());
        }
    }

    function testSaveEmptyDates()
    {
        $this->login();
        $this->open("/phprojekt/htdocs/index.php/Todo/index/list");
        $this->click("link=Todo of Test Project");
        $this->waitForPageToLoad("30000");


        $this->type("startDate", "");
        $this->type("endDate", "");
        $this->click("//input[@value='Send']");
        $this->waitForPageToLoad("30000");
        try {
            $this->assertTrue($this->isTextPresent("Start date: Is a required field"));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            array_push($this->verificationErrors, $e->toString());
        }
        try {
            $this->assertTrue($this->isTextPresent("End date: Is a required field"));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            array_push($this->verificationErrors, $e->toString());
        }


    }

    function testEmptyTitleWrongDateFormat()
    {
        $this->login();
        $this->open("/phprojekt/htdocs/index.php/Todo/index/list");
        $this->click("link=Todo of Test Project");
        $this->waitForPageToLoad("30000");

        $this->type("title", "");
        $this->click("//input[@value='Send']");
        $this->waitForPageToLoad("30000");
        try {
            $this->assertTrue($this->isTextPresent("Title: Is a required field"));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            array_push($this->verificationErrors, $e->toString());
        }
        try {
            $this->assertTrue($this->isTextPresent("Start date: Invalid format for date"));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            array_push($this->verificationErrors, $e->toString());
        }
        try {
            $this->assertTrue($this->isTextPresent("End date: Invalid format for date"));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            array_push($this->verificationErrors, $e->toString());
        }
    }



    function testChangePriority()
    {
        $this->login();
        $this->open("/phprojekt/htdocs/index.php/Todo/index/list");
        $this->click("link=Todo of Test Project");
        $this->waitForPageToLoad("30000");
        $this->select("currentStatus", "label=Waiting");
        $this->click("//input[@value='Send']");
        $this->waitForPageToLoad("30000");
        try {
            $this->assertTrue($this->isTextPresent("Waiting"));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            array_push($this->verificationErrors, $e->toString());
        }
    }


    function testSaveInfo()
    {
        $this->login();
        $this->open("/phprojekt/htdocs/index.php/Todo/index/list");
        $this->click("link=Todo of Test Project");
        $this->waitForPageToLoad("30000");
        $this->type("startDate", "2005-05-06");
        $this->type("endDate", "2020-02-04");
        $this->type("title", "Todo of Test Project Saved");
        $this->click("//input[@value='Send']");
        $this->waitForPageToLoad("30000");
        try {
            $this->assertTrue($this->isTextPresent("Todo of Test Project Saved"));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            array_push($this->verificationErrors, $e->toString());
        }

    }


    function testChangeBack()
    {
        $this->login();
        $this->open("/phprojekt/htdocs/index.php/Todo/index/list");
        $this->click("link=Todo of Test Project Saved");
        $this->waitForPageToLoad("30000");
        $this->type("title", "Todo of Test Project");
        $this->select("currentStatus", "label=Accepted");
        $this->select("priority", "label=1");
        $this->click("//input[@value='Send']");
        $this->waitForPageToLoad("30000");
        try {
            $this->assertTrue($this->isTextPresent("Todo of Test Project"));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            array_push($this->verificationErrors, $e->toString());
        }
        try {
            $this->assertFalse($this->isTextPresent("Todo of Test Project Saved"));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            array_push($this->verificationErrors, $e->toString());
        }
    }

    function testAddTodo()
    {
        $this->login();
        $this->open("/phprojekt/htdocs/index.php/Todo/index/list");
        $this->click("link=Add");
        $this->waitForPageToLoad("30000");
        $this->type("title", "Todo to be deleted");
        $this->type("notes", "This todo was created by Selenium tests");
        $this->type("startDate", "2007-10-10");
        $this->type("endDate", "2010-10-10");
        $this->click("//input[@value='Send']");
        $this->waitForPageToLoad("30000");
        try {
            $this->assertTrue($this->isTextPresent("Todo to be deleted"));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            array_push($this->verificationErrors, $e->toString());
        }



    }

    function testDeleteNewTodo()
    {
        $this->login();
        $this->open("/phprojekt/htdocs/index.php/Todo/index/list");
        $this->click("link=Todo to be deleted");
        $this->waitForPageToLoad("30000");
        $this->click("link=Delete");
        $this->waitForPageToLoad("30000");
        try {
            $this->assertFalse($this->isTextPresent("Todo to be deleted"));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            array_push($this->verificationErrors, $e->toString());
        }
        try {
            $this->assertTrue($this->isTextPresent("Deleted"));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            array_push($this->verificationErrors, $e->toString());
        }


    }
}
