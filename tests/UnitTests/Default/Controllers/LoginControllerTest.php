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
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    CVS: $Id:
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */
require_once 'PHPUnit/Framework.php';

/**
 * Tests for Login Controller
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */
class Phprojekt_LoginController_Test extends PHPUnit_Framework_TestCase
{
    /**
     * Test if the login page is displayed correctly
     */
    public function testLoginIndexAction()
    {
        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();

        $config = Zend_Registry::get('config');

        $request->setModuleName('Default');

        $request->setActionName('index');

        $request->setBaseUrl($config->webpath.'index.php');
        $request->setPathInfo('Login/index');
        $request->setRequestUri('/Login/index');

        $frontController = Zend_Controller_Front::getInstance();

        // Getting the output, otherwise the login screen will be displayed
        ob_start();
        $frontController->dispatch($request, $response);

        $response = ob_get_contents();

        ob_end_clean();

        // checking some parts of the login screen
        $this->assertTrue(strpos($response, "index.php/login/login") > 0);
        $this->assertTrue(strpos($response, "<form") > 0);
        $this->assertTrue(strpos($response, "</html>") > 0);

    }

    /**
     * Tests login action on login controller
     *
     */
    public function testLoginLoginAction()
    {
        $config = Zend_Registry::get('config');

        $request = new Zend_Controller_Request_Http();
        $response = new Zend_Controller_Response_Http();

        $request->setModuleName('Default');

        $request->setActionName('login');

        $request->setBaseUrl($config->webpath.'index.php');
        $request->setPathInfo('login/login');
        $request->setRequestUri('/login/login');

        // This is the only way I found to set POST values on request
        $_POST['username'] = 'david';
        $_POST['password'] = 'test';

        //$request->setControllerName('Login');
        $frontController = Zend_Controller_Front::getInstance();
        try {
            $frontController->dispatch($request, $response);
        }
        catch (Zend_Controller_Response_Exception $e) {
            $this->assertEquals(0,$e->getCode());
            return;
        }
        
        $this->fail('An error occurs on login action');


    }

    /**
     * Tests logout action on login controller
     *
     */
    public function testLoginLogoutAction()
    {
        $config = Zend_Registry::get('config');

        $request = new Zend_Controller_Request_Http();

        $response = new Zend_Controller_Response_Http();


        $request->setModuleName('Default');

        $request->setActionName('logout');

        $request->setBaseUrl($config->webpath.'index.php');
        $request->setPathInfo('login/logout');
        $request->setRequestUri('/login/logout');

        $frontController = Zend_Controller_Front::getInstance();
        try {
            $frontController->dispatch($request, $response);
        }
        catch (Zend_Controller_Response_Exception $e) {

            $authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');

            // if it works ok, the userId needs to be unset
            $this->assertFalse(isset($authNamespace->userId));

            $this->assertEquals(0, $e->getCode());

            // restoring userId value for next tests
            $authNamespace->userId = 1;

            return;
        }

        $this->fail('An error occurs on logout action');

    }

}
