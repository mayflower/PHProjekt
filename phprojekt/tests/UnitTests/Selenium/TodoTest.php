<?php
/**
 * PHProjekt Selenium Test
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
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

/**
 * Selenium Test for PHProjekt
 *
 * @category   PHProjekt
 * @package    UnitTests
 * @subpackage Selenium
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Eduardo Polidor <epolidor@mayflower.de>
 */
class Selenium_TodoTest extends Selenium_SeleniumInit
{
    function testTodoList()
    {
        $this->login();
        $this->open("/phprojekt/htdocs/index.php/Project/index");
        $this->click("link=Todo");
        $this->waitForPageToLoad("30000");
        try {
            $this->assertTrue($this->isTextPresent("Todo / List"));
        } catch (PHPUnit_Framework_AssertionFailedError $error) {
            array_push($this->verificationErrors, $error->toString());
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
        } catch (PHPUnit_Framework_AssertionFailedError $error) {
            array_push($this->verificationErrors, $error->toString());
        }
        try {
            $this->assertTrue($this->isTextPresent("Title"));
        } catch (PHPUnit_Framework_AssertionFailedError $error) {
            array_push($this->verificationErrors, $error->toString());
        }
        try {
            $this->assertTrue($this->isTextPresent("Notes"));
        } catch (PHPUnit_Framework_AssertionFailedError $error) {
            array_push($this->verificationErrors, $error->toString());
        }
        try {
            $this->assertTrue($this->isTextPresent("Start date"));
        } catch (PHPUnit_Framework_AssertionFailedError $error) {
            array_push($this->verificationErrors, $error->toString());
        }
        try {
            $this->assertTrue($this->isTextPresent("End date"));
        } catch (PHPUnit_Framework_AssertionFailedError $error) {
            array_push($this->verificationErrors, $error->toString());
        }
        try {
            $this->assertTrue($this->isTextPresent("Priority"));
        } catch (PHPUnit_Framework_AssertionFailedError $error) {
            array_push($this->verificationErrors, $error->toString());
        }
        try {
            $this->assertTrue($this->isTextPresent("Current Status"));
        } catch (PHPUnit_Framework_AssertionFailedError $error) {
            array_push($this->verificationErrors, $error->toString());
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
        } catch (PHPUnit_Framework_AssertionFailedError $error) {
            array_push($this->verificationErrors, $error->toString());
        }
        try {
            $this->assertTrue($this->isTextPresent("End date: Is a required field"));
        } catch (PHPUnit_Framework_AssertionFailedError $error) {
            array_push($this->verificationErrors, $error->toString());
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
        } catch (PHPUnit_Framework_AssertionFailedError $error) {
            array_push($this->verificationErrors, $error->toString());
        }
        try {
            $this->assertTrue($this->isTextPresent("Start date: Invalid format for date"));
        } catch (PHPUnit_Framework_AssertionFailedError $error) {
            array_push($this->verificationErrors, $error->toString());
        }
        try {
            $this->assertTrue($this->isTextPresent("End date: Invalid format for date"));
        } catch (PHPUnit_Framework_AssertionFailedError $error) {
            array_push($this->verificationErrors, $error->toString());
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
        } catch (PHPUnit_Framework_AssertionFailedError $error) {
            array_push($this->verificationErrors, $error->toString());
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
        } catch (PHPUnit_Framework_AssertionFailedError $error) {
            array_push($this->verificationErrors, $error->toString());
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
        } catch (PHPUnit_Framework_AssertionFailedError $error) {
            array_push($this->verificationErrors, $error->toString());
        }
        try {
            $this->assertFalse($this->isTextPresent("Todo of Test Project Saved"));
        } catch (PHPUnit_Framework_AssertionFailedError $error) {
            array_push($this->verificationErrors, $error->toString());
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
        $this->type("startDate", "2008-10-10");
        $this->type("endDate", "2010-10-10");
        $this->click("//input[@value='Send']");
        $this->waitForPageToLoad("30000");
        try {
            $this->assertTrue($this->isTextPresent("Todo to be deleted"));
        } catch (PHPUnit_Framework_AssertionFailedError $error) {
            array_push($this->verificationErrors, $error->toString());
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
        } catch (PHPUnit_Framework_AssertionFailedError $error) {
            array_push($this->verificationErrors, $error->toString());
        }
        try {
            $this->assertTrue($this->isTextPresent("Deleted"));
        } catch (PHPUnit_Framework_AssertionFailedError $error) {
            array_push($this->verificationErrors, $error->toString());
        }
    }
}
