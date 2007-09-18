<?php

/**
 * PHProjekt Selenium Test
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @author     Johann-Peter Hartmann <johann-peter.hartmann@mayflower.de>
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
 * @author     Johann-Peter Hartmann <johann-peter.hartmann@mayflower.de>
 */
class Selenium_LoginTest extends PHPUnit_Extensions_SeleniumTestCase
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
    protected $coverageScriptUrl = 'http://cruisecontrol.mf-muc.nop/phpunit_coverage_phprojekt6.php';

    /**
     * setup the unit test. Use firefox as a browser and the document
     * root from the configuration file
     *
     * @return void
     */
    function setUp()
    {
        $this->_config = Zend_Registry::get('config');
        $this->verificationErrors = array();
        $this->setBrowserUrl($this->_config->webpath);
    }

    /**
     * Test the login method with wrong and correct data
     *
     * @return void
     */
    function testLogin()
    {
        // Open phprojekt document root and get redirected
        $this->open($this->_config->webpath);

        $this->assertEquals("Login", $this->getTitle());
    }

    function testEmptyUsername()
    {
        // Open phprojekt document root and get redirected
        $this->open($this->_config->webpath);

        // Check for empty username
        $this->type("password", "nothing");
        $this->click("//input[@value='Send']");
        $this->waitForPageToLoad("30000");
        try {
            $this->assertTrue($this->isTextPresent("Invalid user or password"));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            array_push($this->verificationErrors, $e->toString());
        }
    }

    function testEmptyPassword()
    {
        // Open phprojekt document root and get redirected
        $this->open($this->_config->webpath);

        // Check for empty password
        $this->type("username", "nothing");
        $this->click("//input[@value='Send']");
        $this->waitForPageToLoad("30000");
        try {
            $this->assertTrue($this->isTextPresent("Invalid user or password"));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            array_push($this->verificationErrors, $e->toString());
        }
    }

    function testWrongCombination()
    {
        // Open phprojekt document root and get redirected
        $this->open($this->_config->webpath);
        $this->type("username", "david");
        $this->type("password", "wrong");

        // Check for wrong combination
        $this->click("//input[@value='Send']");
        $this->waitForPageToLoad("30000");
        try {
            $this->assertTrue($this->isTextPresent("Invalid user or password"));
        } catch (PHPUnit_Framework_AssertionFailedError $e) {
            array_push($this->verificationErrors, $e->toString());
        }
    }

    function testCorrectCombination()
    {
        // Open phprojekt document root and get redirected
        $this->open($this->_config->webpath);
        $this->type("username", "david");

        // Actual login with correct data
        $this->type("password", "test");
        $this->click("//input[@value='Send']");
        $this->waitForPageToLoad("30000");

        $this->assertEquals($this->_config->webpath , $this->getLocation());

    }
}
