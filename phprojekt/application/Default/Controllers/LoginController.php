<?php
/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * Login handling.
 */
class LoginController extends Zend_Controller_Action
{
    /**
     * Default action.
     *
     * The function sets up the template index.phtml and renders it.
     *
     * @return void
     */
    public function indexAction()
    {
        $this->getResponse()->clearHeaders();
        $this->getResponse()->clearBody();

        $this->view->compressedDojo = (bool) Phprojekt::getInstance()->getConfig()->compressedDojo;

        $this->render('login');
    }

    /**
     * Executes the login using the username and password provided on login form.
     *
     * If it works fine, redirect the user to homepage,
     * if not, show the error message.
     *
     * Keep the hash for redirect the user after the login.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - string <b>username</b>   Username for login.
     *  - string <b>password</b>   Password for login.
     *  - string <b>hash</b>       Hash URL for redirect after the login.
     *  - string <b>keepLogged</b> 1 if the user clicks on the checkbox.
     * </pre>
     *
     * @return void
     */
    public function loginAction()
    {
        $username   = Cleaner::sanitize('xss', $this->getRequest()->getParam('username', null));
        $password   = Cleaner::sanitize('xss', $this->getRequest()->getParam('password', null));
        $hash       = Cleaner::sanitize('xss', $this->getRequest()->getParam('hash', null));
        $keepLogged = (int) $this->getRequest()->getParam('keepLogged', 0);
        $keepLogged = ($keepLogged == 1) ? true : false;
        $loginServer = $this->getRequest()->getParam('domain', null);

        $this->view->compressedDojo = (bool) Phprojekt::getInstance()->getConfig()->compressedDojo;

        try {
            $success = Phprojekt_Auth::login(
                $username,
                $password,
                array('keepLogged' => $keepLogged, 'loginServer' => $loginServer)
            );
            if ($success === true) {
                $config = Phprojekt::getInstance()->getConfig();
                $frontendMessage = new Phprojekt_Notification();
                $frontendMessage->setControllProcess(Phprojekt_Notification::LAST_ACTION_LOGIN);
                $frontendMessage->saveFrontendMessage();
                Default_Helpers_Upload::cleanUnusedFiles();
                $this->_redirect('../../index.php' . $hash);
                die();
            }
        } catch (Phprojekt_Auth_Exception $error) {
            $this->view->message  = $error->getMessage();
            $this->view->username = $username;
            $this->view->hash     = $hash;

            $this->render('login');
        }
    }

    /**
     * Executes the login by json using the username and password.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - string <b>username</b> Username for login.
     *  - string <b>password</b> Password for login.
     * </pre>
     *
     * The return is a string in JSON format with:
     * <pre>
     *  - type    => 'success' or 'error'.
     *  - message => Success or error message.
     * </pre>
     *
     * @return void
     */
    public function jsonLoginAction()
    {
        $username = Cleaner::sanitize('xss', $this->getRequest()->getParam('username', null));
        $password = Cleaner::sanitize('xss', $this->getRequest()->getParam('password', null));

        try {
            $success = Phprojekt_Auth::login($username, $password);
            if ($success === true) {
                $return = array('type'    => 'success',
                                'message' => '');
            }
        } catch (Phprojekt_Auth_Exception $error) {
            $return = array('type'    => 'error',
                            'message' => $error->getMessage());
        }

        $this->_helper->viewRenderer->setNoRender();
        $this->view->clearVars();

        Phprojekt_Converter_Json::echoConvert($return);
    }

    /**
     * Logout action.
     *
     * Logout the user, and redirect them to the login page.
     *
     * @return void
     */
    public function logoutAction()
    {
        $frontendMessage = new Phprojekt_Notification();
        $frontendMessage->setControllProcess(Phprojekt_Notification::LAST_ACTION_LOGOUT);
        $frontendMessage->saveFrontendMessage();

        Phprojekt_Auth::logout();
        $config = Phprojekt::getInstance()->getConfig();
        $this->_redirect('../../index.php');
        die();
    }
}
