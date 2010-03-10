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
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @version    $Id$
 * @author     Johann-Peter Hartmann <johann-peter.hartmann@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

/**
 * Selenium Test for PHProjekt
 *
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL v3 (See LICENSE file)
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Johann-Peter Hartmann <johann-peter.hartmann@mayflower.de>
 */
class Selenium_ProjectTest extends Selenium_SeleniumInit
{
    function testProjectPage()
    {
        $this->login();
        $this->open("/phprojekt/htdocs/index.php/Project/index");

        try {
            $this->assertTrue($this->isTextPresent("Test Project"));
        } catch (PHPUnit_Framework_AssertionFailedError $error) {
            array_push($this->verificationErrors, $error->toString());
        }
    }

    function testTestProject()
    {
        $this->login();
        $this->open("/phprojekt/htdocs/index.php/Project/index");

        $this->click("link=Test Project");
        $this->waitForPageToLoad("30000");
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
            $this->assertTrue($this->isTextPresent("Priority"));
        } catch (PHPUnit_Framework_AssertionFailedError $error) {
            array_push($this->verificationErrors, $error->toString());
        }
    }

    function testModifyTestProjectMissingFields()
    {
        $this->login();
        $this->open("/phprojekt/htdocs/index.php/Project/index");

        $this->click("link=Test Project");
        $this->waitForPageToLoad("30000");
        $this->type("title", "Test Project 2");
        $this->click("//input[@value='Send']");
        $this->waitForPageToLoad("30000");
        try {
            $this->assertTrue($this->isTextPresent("Start date: Is a required field"));
        } catch (PHPUnit_Framework_AssertionFailedError $error) {
            array_push($this->verificationErrors, $error->toString());
        }
    }

    function testModifyTestProjectWrongFormat()
    {
        $this->login();
        $this->open("/phprojekt/htdocs/index.php/Project/index");
        $this->click("link=Test Project");
        $this->waitForPageToLoad("30000");
        $this->type("title", "Test Project 2");

        $this->type("startDate", "10.10.2008");
        $this->click("//input[@value='Send']");
        $this->waitForPageToLoad("30000");
        try {
            $this->assertTrue($this->isTextPresent("Start date: Invalid format for date"));
        } catch (PHPUnit_Framework_AssertionFailedError $error) {
            array_push($this->verificationErrors, $error->toString());
        }
    }

    function testModifyTestProjectRightFormat()
    {
        $this->login();
        $this->open("/phprojekt/htdocs/index.php/Project/index");
        $this->click("link=Test Project");
        $this->waitForPageToLoad("30000");
        $this->type("title", "Test Project 2");

        $this->type("startDate", "2008-10-10");
        $this->type("endDate", "2008-12-31");
        $this->click("//input[@value='Send']");
        $this->waitForPageToLoad("30000");
        try {
            $this->assertTrue($this->isTextPresent("Oct 10, 2008"));
        } catch (PHPUnit_Framework_AssertionFailedError $error) {
            array_push($this->verificationErrors, $error->toString());
        }
    }

    function testChangeBack()
    {
        $this->login();
        $this->open("/phprojekt/htdocs/index.php/Project/index");
        $this->click("link=Test Project 2");
        $this->waitForPageToLoad("30000");
        $this->type("title", "Test Project");
        $this->click("//input[@value='Send']");
        $this->waitForPageToLoad("30000");
        try {
            $this->assertTrue($this->isTextPresent("Test Project"));
        } catch (PHPUnit_Framework_AssertionFailedError $error) {
            array_push($this->verificationErrors, $error->toString());
        }
    }

    function testAddProject()
    {
        $this->login();
        $this->open("/phprojekt/htdocs/index.php/Project/index");

        $this->click("link=Add");
        $this->waitForPageToLoad("30000");
        $this->type("title", "New Entry to delete");
        $this->type("notes", "Notes for new entry");
        $this->type("priority", "1");
        $this->type("startDate", "2008-10-10");
        $this->type("endDate", "2008-12-31");
        $this->click("//input[@value='Send']");
        $this->waitForPageToLoad("30000");
        try {
            $this->assertTrue($this->isTextPresent("Notes for new entry"));
        } catch (PHPUnit_Framework_AssertionFailedError $error) {
            array_push($this->verificationErrors, $error->toString());
        }
    }

    function testDeleteNewProject()
    {
        $this->login();
        $this->open("/phprojekt/htdocs/index.php/Project/index");

        $this->click("link=New Entry to delete");
        $this->waitForPageToLoad("30000");
        $this->click("link=Delete");
        $this->waitForPageToLoad("30000");
        try {
            $this->assertTrue($this->isTextPresent("Deleted"));
        } catch (PHPUnit_Framework_AssertionFailedError $error) {
            array_push($this->verificationErrors, $error->toString());
        }
    }
}
