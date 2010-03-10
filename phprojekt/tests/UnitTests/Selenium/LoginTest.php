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
 * @author     Johann-Peter Hartmann <johann-peter.hartmann@mayflower.de>
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
 * @author     Johann-Peter Hartmann <johann-peter.hartmann@mayflower.de>
 */
class Selenium_LoginTest extends Selenium_SeleniumInit
{
    /**
     * Test the login method with wrong and correct data
     *
     * @return void
     */
    function testLogin()
    {
        // Open phprojekt document root and get redirected
        $this->open($this->config->webpath);

        $this->assertEquals("Login", $this->getTitle());
    }

    function testEmptyUsername()
    {
        // Open phprojekt document root and get redirected
        $this->open($this->config->webpath);

        // Check for empty username
        $this->type("password", "nothing");
        $this->click("//input[@value='Send']");
        $this->waitForPageToLoad("30000");
        try {
            $this->assertTrue($this->isTextPresent("Invalid user or password"));
        } catch (PHPUnit_Framework_AssertionFailedError $error) {
            array_push($this->verificationErrors, $error->toString());
        }
    }

    function testEmptyPassword()
    {
        // Open phprojekt document root and get redirected
        $this->open($this->config->webpath);

        // Check for empty password
        $this->type("username", "nothing");
        $this->click("//input[@value='Send']");
        $this->waitForPageToLoad("30000");
        try {
            $this->assertTrue($this->isTextPresent("Invalid user or password"));
        } catch (PHPUnit_Framework_AssertionFailedError $error) {
            array_push($this->verificationErrors, $error->toString());
        }
    }

    function testWrongCombination()
    {
        // Open phprojekt document root and get redirected
        $this->open($this->config->webpath);
        $this->type("username", "david");
        $this->type("password", "wrong");

        // Check for wrong combination
        $this->click("//input[@value='Send']");
        $this->waitForPageToLoad("30000");
        try {
            $this->assertTrue($this->isTextPresent("Invalid user or password"));
        } catch (PHPUnit_Framework_AssertionFailedError $error) {
            array_push($this->verificationErrors, $error->toString());
        }
    }

    function testCorrectCombination()
    {
        // Open phprojekt document root and get redirected
        $this->open($this->config->webpath);
        $this->type("username", "david");

        // Actual login with correct data
        $this->type("password", "test");
        $this->click("//input[@value='Send']");
        $this->waitForPageToLoad("30000");

        $this->assertEquals($this->_config->webpath . 'index.php', $this->getLocation());
    }
}
