<?php
/**
 * Unit test
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
*/
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Extensions/ExceptionTestCase.php';

/**
 * Tests for Language Adapter
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */
class Phprojekt_AuthTest extends PHPUnit_Extensions_ExceptionTestCase
{
    /**
     * Test the login function
     *
     * @return void
     */
    public function testLogin()
    {
        /* Test if login passes with user not logged it */
        try {
            $authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');
            $authNamespace->unsetAll();

            $tmp = Phprojekt_Auth::isLoggedIn();
            if ($tmp) {
                $this->fail('The user is not logged in!');
            }
        } catch (Phprojekt_Auth_UserNotLoggedInException $ae) {
            
                $this->fail($ae->getMessage());
            
        }

        /* trying a login with an invalid user */
        try {
            // $db = Zend_Registry::get('db');

            $db = clone $this->sharedFixture;

            Zend_Registry::set('db',$db);

            $tmp = Phprojekt_Auth::login('invalidUser', 'password');
            if ($tmp) {
                $this->fail('An invalid user is able to log in!');
            }
        } catch (Phprojekt_Auth_UserNotLoggedInException $e) {

                $this->fail($ae->getMessage()." ".$ae->getCode());
        }

        /* trying a login with a valid user and invalid password */
        try {
            $tmp = Phprojekt_Auth::login('david', 'iinvalidPassword');
            if ($tmp) {
                $this->fail('An user is able to log in with an invalid password!');
            }
        } catch (Phprojekt_Auth_UserNotLoggedInException $e) {

                $this->fail($ae->getMessage()." ".$ae->getCode());
        }

        /* trying a login with a valid password and invalid user */
        try {
            $tmp = Phprojekt_Auth::login('invalidUser', 'test');
            if ($tmp) {
                $this->fail('An invalid user is able to log in using a valid password!');
            }
        } catch (Phprojekt_Auth_UserNotLoggedInException $e) {

                $this->fail($ae->getMessage()." ".$ae->getCode());
        }

        /* trying a login with a empty user and a valid password */
        try {
            $tmp = Phprojekt_Auth::login('', 'test');
            if ($tmp) {
                $this->fail('An empty user is able to log in!');
            }
        } catch (Phprojekt_Auth_UserNotLoggedInException $e) {

                $this->fail($ae->getMessage()." ".$ae->getCode());
        }

        /* trying a login with a valid user and a empty password */
        try {
            $tmp = Phprojekt_Auth::login('david', '');
            if ($tmp) {
                $this->fail('An user is able to log in without password!');
            }
        } catch (Phprojekt_Auth_UserNotLoggedInException $e) {

                $this->fail($ae->getMessage()." ".$ae->getCode());
        }

        /* trying a login with a valid user and the md5 value on the database */
        try {
            $tmp = Phprojekt_Auth::login('david', '156c3239dbfa5c5222b51514e9d12948');
            if ($tmp) {
                $this->fail('An user is able to log in with the crypted password string!');
            }
        } catch (Phprojekt_Auth_UserNotLoggedInException $e) {

                $this->fail($ae->getMessage()." ".$ae->getCode());
        }
        
        /* trying a login with a valid user but inactive */
        try {
            $tmp = Phprojekt_Auth::login('inactive', 'test');
            if ($tmp) {
                $this->fail('An inactive user is able to log in!');
            }
        } catch (Phprojekt_Auth_UserNotLoggedInException $e) {

                $this->fail($ae->getMessage()." ".$ae->getCode());
        }

        /* trying a login with a valid user and its password */
        /* This try has to log in the user */
        try {
            $tmp = Phprojekt_Auth::login('david', 'test');

        } catch (Phprojekt_Auth_UserNotLoggedInException $e) {

            $this->fail($ae->getMessage()." ".$ae->getCode());
        }
        $this->assertTrue($tmp);

        /* logged in needs to be true */
        $this->assertTrue(Phprojekt_Auth::isLoggedIn());

        /* trying to logout */
        $this->assertTrue(Phprojekt_Auth::logout());

        /* after logout user needs to be logged out */
        try {
        
            $tmp = Phprojekt_Auth::isLoggedIn();

            if ($tmp) {
                $this->fail('The user is still logged in after logout!');
            }
        } catch (Phprojekt_Auth_UserNotLoggedInException $ae) {

                $this->fail($ae->getMessage());
        }

    }
    
}
