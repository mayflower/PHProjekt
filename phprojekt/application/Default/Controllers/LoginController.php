<?php
/**
 * Login handling
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @author     Eduardo Polidor <soria_parra@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Login handling
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Eduardo Polidor <epolidor@mayflower.de>
 */
class LoginController extends Zend_Controller_Action
{
    /**
     * Default error action
     *
     * @return void
     */
    public function indexAction()
    {
        $config = Zend_Registry::get('config');
        $logger = Zend_Registry::get('log');

        $logger->err('Login handler called');


        $this->getResponse()->clearHeaders();
        $this->getResponse()->clearBody();

        $this->render('login');
    }

    /**
     * Executes the login using the username and password provided on login form
     * If it works fine you will be redirect to homepage
     * 
     * @todo redirect to the correct page
     * 
     * @return void
     */
    public function loginAction()
    {

        $success = false;

        $request = new Zend_Controller_Request_Http();

        $username = $request->getPost('username');

        $password = $request->getPost('password');

        try {
            $success = Phprojekt_Auth::login($username, $password);
        }
        catch (Phprojekt_Auth_Exception $e) {

            $this->view->message = $e->getMessage();
            $this->view->username = $username;
        }

        if ($success) {
            $this->_redirect($config->webpath);
            die();
        }

    }
}