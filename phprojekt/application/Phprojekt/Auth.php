<?php
/**
 * User class for PHProjekt 6.0
 *
 * @copyright 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license   http://www.phprojekt.com/license PHProjekt6 License
 * @version   CVS: $Id$
 * @author    Eduardo Polidor <polidor@mayflower.de>
 * @package   PHProjekt
 * @link      http://www.phprojekt.com
 * @since     File available since Release 1.0
 */

/**
 * Phprojekt_User for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license   http://www.phprojekt.com/license PHProjekt6 License
 * @author     Eduardo Polidor <polidor@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */
class Phprojekt_Auth extends Zend_Auth
{


    /**
     * Create object of type PHProjekt_Auth
     *
     */
    function __construct()
    {
    }

    /**
     * Destruct object
     *
     */
    function __destruct()
    {
    }


    /**
     * Checks if user is loggued in or not. It uses the Zend
     *
     * @return boolean true if user is logued in
     */
    public function isLogguedIn()
    {

        $authNamespace = new Zend_Session_Namespace('PHProjek_Auth');

        if (!isset($authNamespace->userId) || empty($authNamespace->userId)) {

            throw new Phprojekt_Auth_Exception('User not logged in', 1);
        }

        return true;
    }

    /**
     * Makes the login process
     *
     * @param string $username username provided
     * @param stirng $password clean password typed by user
     * 
     * @return boolean true if login process was sucessful
     */
    public function login($username, $password)
    {


        $db = Zend_Registry::get('db');
        /* @var $db Zend_Db_Adapter_Abstract */

        $oUser = new Phprojekt_User(array ('db' => $db));

        $userId = $oUser->findIdByUsername($username);

        if ($userId > 0) {
            $oUser->find($userId);
        } else {
            throw new Phprojekt_Auth_Exception('Invalid user or password', 4);
        }


        try {
            /* the password does not match with password provided */
            if (!Phprojekt_Auth::_compareStringWithPassword((string)$password, (string)$oUser->password)) {
                throw new Phprojekt_Auth_Exception('Invalid user or password', 2);
            }
        }
        catch (Exception $e) {
            throw new Phprojekt_Auth_Exception('Invalid user or password', 3);
        }

        /* if the user was found we will save the user information on the session */
        $authNamespace = new Zend_Session_Namespace('PHProjek_Auth');

        $authNamespace->userId = $oUser->id;


        /* please, put any extra info of user to be saved on session here */

        return true;

    }

    /**
     * Makes the logout process
     *
     * @return boolean true if logout process was sucessful
     */
    public function logout()
    {

        $authNamespace = new Zend_Session_Namespace('PHProjek_Auth');

        $authNamespace->unsetAll();

        return true;
    }

    /**
     * Compare a string with a user password
     *
     * @param string $string   key uncryted to check if it is the password
     * @param string $password crypted password
     * 
     * @return boolean true if the string crypted is equal to provide password
     */
    private function _compareStringWithPassword($string, $password)
    {

        /* one of the methods to check the password */
        $defaultMethod = 'phprojektmd5'.$string;
        $defaultMethod = Phprojekt_Auth::_cryptPassword($defaultMethod);

        if ($defaultMethod == $password) {

            return true;

        }

        /* please add other valid methods here (e.g. not crypted password) */

        /* none of the methods works */

        return false;
    }

    /**
     * String to be crytped
     *
     * @param string $password string to be cripted
     * 
     * @return scring crypted password
     */
    private function _cryptPassword($password)
    {
        return md5($password);
    }
}