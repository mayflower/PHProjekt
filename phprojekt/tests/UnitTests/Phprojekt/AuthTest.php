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
class Phprojekt_AuthTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test if login passes with user not logged it
     */
    public function testLoginWithoutSession()
    {
        try {
            $authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');
            $authNamespace->unsetAll();

            Phprojekt_Auth::isLoggedIn();
        }
        catch (Phprojekt_Auth_UserNotLoggedInException $ae) {
            return ;
        }

        $this->fail('The user is logued');
    }

    /**
     * Trying a login with an invalid user
     */
     public function testInvalidUser() {
        try {
            Zend_Registry::set('db',$this->sharedFixture);
            Phprojekt_Auth::login('invalidUser', 'password');
        }
        catch (Phprojekt_Auth_Exception $ae) {
            return ;
        }

        $this->fail('An invalid user is able to log in!');
     }

     /**
      * Trying a login with a valid user and invalid password
      */
     public function testInvalidPass() {
        try {
            Phprojekt_Auth::login('david', 'iinvalidPassword');
        }
        catch (Phprojekt_Auth_Exception $ae) {
            return ;
        }
        $this->fail('An user is able to log in with an invalid password!');

     }

     /**
      * Trying a login with a valid password and invalid user
      */
     public function testInvalidUserValidPass() {
        try {
            Phprojekt_Auth::login('invalidUser', 'test');
        }
        catch (Phprojekt_Auth_Exception $ae) {
            return ;
        }
        $this->fail('An invalid user is able to log in using a valid password!');
    }

    /**
     * Trying a login with a empty user and a valid password
     */
    public function testEmptyuser() {
        try {
            Phprojekt_Auth::login('', 'test');
        }
        catch (Phprojekt_Auth_Exception $ae) {
            return ;
        }
        $this->fail('An empty user is able to log in!');
    }

    /**
     * Trying a login with a valid user and a empty password
     */
    public function testEmptyPass() {
        try {
            Phprojekt_Auth::login('david', '');
        }
        catch (Phprojekt_Auth_Exception $ae) {
            return ;
        }
        $this->fail('An user is able to log in without password!');
    }

    /**
     * Trying a login with a valid user and the md5 value on the database
     */
    public function testMd5Login() {
        try {
            Phprojekt_Auth::login('david', '156c3239dbfa5c5222b51514e9d12948');
        }
        catch (Phprojekt_Auth_Exception $ae) {
            return ;
        }
        $this->fail('An user is able to log in with the crypted password string!');
    }

    /**
     * Trying a login with a valid user but inactive
     */
    public function testInactiveUser() {
        try {
            Phprojekt_Auth::login('inactive', 'test');
        }
        catch (Phprojekt_Auth_Exception $ae) {
            return ;
        }
        $this->fail('An inactive user is able to log in!');
    }

    /**
     * Trying a login with a valid user and its password
     * This try has to log in the user
     */
    public function testLogin() {
        try {
            $tmp = Phprojekt_Auth::login('david', 'test');
        }
        catch (Phprojekt_Auth_Exception $ae) {
            $this->fail($ae->getMessage()." ".$ae->getCode());
        }
        $this->assertTrue($tmp);

        /* logged in needs to be true */
        $this->assertTrue(Phprojekt_Auth::isLoggedIn());

        /* trying to logout
        $this->assertTrue(Phprojekt_Auth::logout());

        // after logout user needs to be logged out
        try {
            Phprojekt_Auth::isLoggedIn();
        }
        catch (Phprojekt_Auth_UserNotLoggedInException $ae) {
            return ;
        }
        $this->fail('The user is still logged in after logout!');
        */
    }

    /**
     * Test found user by id
     */
    public function testUserId() {
        $user = new Phprojekt_User_User(array ('db' => $this->sharedFixture));
        $clone = $user->findUserById(1);
        $this->assertEquals('david', $clone->username);

        $clone = $user->findUserById(0);
        $this->assertEquals('', $clone->username);
    }
}
