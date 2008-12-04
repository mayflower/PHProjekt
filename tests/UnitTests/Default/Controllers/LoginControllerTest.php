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
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_LoginController_Test extends FrontInit
{
    /**
     * Test if the login page is displayed correctly
     */
    public function testLoginIndexAction()
    {
        $this->request->setActionName('index');
        $this->request->setBaseUrl($this->config->webpath . 'index.php');
        $this->request->setPathInfo('Login/index');
        $this->request->setRequestUri('/Login/index');
        $response = $this->getResponse();
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
        $this->request->setActionName('login');
        $this->request->setBaseUrl($this->config->webpath . 'index.php');
        $this->request->setPathInfo('login/login');
        $this->request->setRequestUri('/login/login');

        // This is the only way I found to set POST values on request
        $_POST['username'] = 'david';
        $_POST['password'] = 'test';

        try {
            $this->front->dispatch($this->request, $this->response);
        } catch (Zend_Controller_Response_Exception $e) {
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
        $this->request->setActionName('logout');
        $this->request->setBaseUrl($this->config->webpath . 'index.php');
        $this->request->setPathInfo('login/logout');
        $this->request->setRequestUri('/login/logout');

        try {
            $this->front->dispatch($this->request, $this->response);
        } catch (Zend_Controller_Response_Exception $e) {
            try {
                $authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');
            } catch (Zend_Session_Exception $e) {
                return;
            }
        }

        $this->fail('An error occurs on logout action');
    }
}
