<?php

/**
 * PHProjekt Selenium Test
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @author     Your Name <your.name@mayflower.de>
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
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Your Name <your.name@mayflower.de>
 */

class Selenium_ProjectTest extends PHPUnit_Extensions_SeleniumTestCase
{
    /**
     * Handle for the current configuration
     *
     * @var Zend_Config_Ini
     */
    private $_config;

    /**
     * setup the unit test. Use firefox as a browser and the document
     * root from the configuration file
     *
     * @return void
     */
    function setUp()
    {
        $this->_config = new Zend_Config_Ini(DEFAULT_CONFIG_FILE, DEFAULT_CONFIG_SECTION);
        $this->setBrowser('*firefox');
        $this->verificationErrors = array();
        $this->setBrowserUrl($this->_config->webpath);
    }

    function testLogin()
    {
        $this->open("/phprojekt/htdocs/index.php/Login/index");
        $this->type("username", "david");
        $this->type("password", "test");
        $this->click("//input[@value='Send']");
        $this->waitForPageToLoad("30000");
    }
    
    function testProjectPage()
    {
        $this->open("/phprojekt/htdocs/index.php/Project/index");
        try {
            $this->assertTrue($this->isTextPresent("Test Project"));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            array_push($this->verificationErrors, $e->toString());
        }
        }
    
    function testTestProject()
    {
        $this->click("link=Test Project");
        $this->waitForPageToLoad("30000");
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
            $this->assertTrue($this->isTextPresent("Priority"));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            array_push($this->verificationErrors, $e->toString());
        }
    }
    
    function testModifyTestProjectMissingFields()
    {
        $this->type("title", "Test Project 2");
        $this->click("//input[@value='Send']");
        $this->waitForPageToLoad("30000");
        try {
            $this->assertTrue($this->isTextPresent("Start date: Is a required field"));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            array_push($this->verificationErrors, $e->toString());
        }
        }
    
    function testModifyTestProjectWrongFormat()
    {
        $this->type("startDate", "10.10.2007");
        $this->click("//input[@value='Send']");
        $this->waitForPageToLoad("30000");
        try {
            $this->assertTrue($this->isTextPresent("Start date: Invalid format for date"));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            array_push($this->verificationErrors, $e->toString());
        }
     }
    
    function testModifyTestProjectRightFormat()
    {
        $this->type("startDate", "2007-10-10");
        $this->click("//input[@value='Send']");
        $this->waitForPageToLoad("30000");
        try {
            $this->assertTrue($this->isTextPresent("Oct 10, 2007"));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            array_push($this->verificationErrors, $e->toString());
        }
    }
    
    function testChangeBack()
    {
        $this->click("link=Test Project 2");
        $this->waitForPageToLoad("30000");
        $this->type("title", "Test Project");
        $this->click("//input[@value='Send']");
        $this->waitForPageToLoad("30000");
    }
    
    function testAddProject()
    {
        $this->click("link=Add");
        $this->waitForPageToLoad("30000");
        $this->type("title", "New Entry to delete");
        $this->type("notes", "Notes for new entry");
        $this->type("priority", "1");
        $this->type("startDate", "2007-10-10");
        $this->click("//input[@value='Send']");
        $this->waitForPageToLoad("30000");
        try {
            $this->assertTrue($this->isTextPresent("Notes for new entry"));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            array_push($this->verificationErrors, $e->toString());
        }
    }
    
    function testDeleteNewProject()
    {
        $this->click("link=Delete");
        $this->waitForPageToLoad("30000");
        $this->click("link=New Entry to delete");
        $this->waitForPageToLoad("30000");
        $this->click("link=Delete");
        $this->waitForPageToLoad("30000");
        try {
            $this->assertTrue($this->isTextPresent("Deleted"));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            array_push($this->verificationErrors, $e->toString());
        }
    }
}
