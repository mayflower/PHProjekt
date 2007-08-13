<?php
/** PHPUnit_Extensions_SeleniumTestCase */
require_once 'PHPUnit/Extensions/SeleniumTestCase.php';

/**
 * Test-Suite for selenium based acceptance tests 
 *
 */
class Selenium_LoginTest extends PHPUnit_Extensions_SeleniumTestCase
{
    function setUp()
    {
        $this->setBrowser('*firefox');
        $this->verificationErrors = array();
        $this->setBrowserUrl('http://localhost/'); 
    }

    function testLogin()
    {
                // Open phprojekt document root and get redirected 
                $this->open("/phprojekt/htdocs/");

                $this->assertEquals("Login", $this->getTitle());
                // Check for empty username
                $this->type("password", "nothing");
                $this->click("//input[@value='Send']");
                $this->waitForPageToLoad("30000");
                try {
                                $this->assertTrue($this->isTextPresent("Invalid user or password"));
                } catch (PHPUnit_Framework_AssertionFailedError $e) {
                                array_push($this->verificationErrors, $e->toString());
                }
                // Check for empty password
                $this->type("username", "nothing");
                $this->click("//input[@value='Send']");
                $this->waitForPageToLoad("30000");
                $this->type("username", "david");
                $this->type("password", "wrong");
                try {
                                $this->assertTrue($this->isTextPresent("Invalid user or password"));
                } catch (PHPUnit_Framework_AssertionFailedError $e) {
                                array_push($this->verificationErrors, $e->toString());
                }
                // Check for wrong combination
                $this->click("//input[@value='Send']");
                $this->waitForPageToLoad("30000");
                try {
                                $this->assertTrue($this->isTextPresent("Invalid user or password"));
                } catch (PHPUnit_Framework_AssertionFailedError $e) {
                                array_push($this->verificationErrors, $e->toString());
                }
                // Actual login with correct data
                $this->type("password", "test");
                $this->click("//input[@value='Send']");
                $this->waitForPageToLoad("30000");

                $this->assertEquals("http://localhost/phprojekt/htdocs/index.php", $this->getLocation());

    }
}
?>
