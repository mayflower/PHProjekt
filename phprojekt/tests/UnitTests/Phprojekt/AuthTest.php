<?php
/**
 * Unit test
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
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */
require_once 'PHPUnit/Framework.php';

/**
 * Tests for Language Adapter
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 * @group      phprojekt
 * @group      auth
 * @group      phprojekt-auth
 */
class Phprojekt_AuthTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test if login passes with user not logged it
     */
    public function testLoginWithoutSession()
    {
        $this->setExpectedException('Phprojekt_Auth_UserNotLoggedInException');
        $authNamespace = new Zend_Session_Namespace('Phprojekt_Auth-login');
        $authNamespace->unsetAll();
        Phprojekt_Auth::isLoggedIn();
    }

    /**
     * Trying a login with an invalid user
     */
    public function testInvalidUser()
    {
        $this->setExpectedException('Phprojekt_Auth_Exception');
        Phprojekt_Auth::login('invalidUser', 'password');
    }

    /**
     * Trying a login with a valid user and invalid password
     */
    public function testInvalidPass()
    {
        $this->setExpectedException('Phprojekt_Auth_Exception');
        Phprojekt_Auth::login('david', 'iinvalidPassword');
    }

    /**
     * Trying a login with a valid password and invalid user
     */
    public function testInvalidUserValidPass()
    {
        $this->setExpectedException('Phprojekt_Auth_Exception');
        Phprojekt_Auth::login('invalidUser', 'test');
    }

    /**
     * Trying a login with a empty user and a valid password
     */
    public function testEmptyuser() {
        $this->setExpectedException('Phprojekt_Auth_Exception');
        Phprojekt_Auth::login('', 'test');
    }

    /**
     * Trying a login with a valid user and a empty password
     */
    public function testEmptyPass() {
        $this->setExpectedException('Phprojekt_Auth_Exception');
        Phprojekt_Auth::login('david', '');
    }

    /**
     * Trying a login with a valid user and the md5 value on the database
     */
    public function testMd5Login() {
        $this->setExpectedException('Phprojekt_Auth_Exception');
        Phprojekt_Auth::login('david', '156c3239dbfa5c5222b51514e9d12948');
    }

    /**
     * Trying a login with a valid user but inactive
     */
    public function testInactiveUser() {
        $this->setExpectedException('Phprojekt_Auth_Exception');
        Phprojekt_Auth::login('inactive', 'test');
    }

    /**
     * Trying a login with a valid user and its password
     * This try has to log in the user
     */
    public function testLogin() {
        try {
            $tmp = Phprojekt_Auth::login('david', 'test');
        } catch (Phprojekt_Auth_Exception $error) {
            $this->fail($error->getMessage()." ".$error->getCode());
        }
        $this->assertTrue($tmp);

        /* logged in needs to be true */
        $this->assertTrue(Phprojekt_Auth::isLoggedIn());
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
