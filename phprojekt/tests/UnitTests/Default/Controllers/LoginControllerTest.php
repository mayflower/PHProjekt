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
 * Tests for Login Controller
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 * @group      default
 * @group      controller
 * @group      default-controller
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
        $this->assertContains("function init()", $response);
    }

    /**
     * Tests login action on login controller
     *
     */
    public function testLoginLoginAction()
    {
        $this->setRequestUrl('Login/login');

        // This is the only way I found to set POST values on request
        $_POST['username'] = 'david';
        $_POST['password'] = 'test';

        try {
            $this->front->dispatch($this->request, $this->response);
        } catch (Zend_Controller_Response_Exception $error) {
            $this->assertEquals(0, $error->getCode());
            $authNamespace = new Zend_Session_Namespace('Phprojekt_Auth-login');
            $this->assertEquals(1, $authNamespace->userId);
            $this->assertEquals(1, $authNamespace->admin);
            return;
        }

        $this->fail('An error occured on login action');
    }

    /**
     * Tests logout action on login controller
     *
     */
    public function testLoginLogoutAction()
    {
        $this->setRequestUrl('Login/logout');

        try {
            $this->front->dispatch($this->request, $this->response);
        } catch (Zend_Controller_Response_Exception $error) {
            $this->assertEquals(0, $error->getCode());
            try {
                 $authNamespace = new Zend_Session_Namespace('Phprojekt_Auth-login');
            } catch (Zend_Session_Exception $error) {
                $this->assertEquals(0, $error->getCode());
                return;
            }
        }
        $this->fail('An error occured on logout action');
    }
}
