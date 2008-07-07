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
 * @subpackage Default
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
        $logger = Zend_Registry::get('log');

        $logger->debug('Login handler called');

        $this->getResponse()->clearHeaders();
        $this->getResponse()->clearBody();
        $this->view->webpath = Zend_Registry::get('config')->webpath;
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
        $username = $this->getRequest()->getParam('username');
        $password = $this->getRequest()->getParam('password');
        $this->view->webpath = Zend_Registry::get('config')->webpath;

        try {
            $success = Phprojekt_Auth::login($username, $password);
            if ($success === true) {
                $config = Zend_Registry::get('config');
                $this->_redirect($config->webpath.'index.php');
                die();
            }
        }
        catch (Phprojekt_Auth_UserNotLoggedInException $e) {
            $this->view->message  = $e->getMessage();
            $this->view->username = $username;
        }
        catch (Phprojekt_Auth_Exception $e) {
            $this->view->message  = $e->getMessage();
            $this->view->username = $username;
        }
    }

    /**
     * Logout action
     *
     * @return void
     */
    public function logoutAction()
    {
        Phprojekt_Auth::logout();
        $config = Zend_Registry::get('config');
        $this->_redirect($config->webpath.'index.php/login/index');
        Zend_Session_Namespace::unsetAll();
        die();
    }
}