<?php
/**
 * User class for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://www.phprojekt.com/license PHProjekt6 License
 * @version    CVS: $Id$
 * @author     Eduardo Polidor <polidor@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Phprojekt_User for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://www.phprojekt.com/license PHProjekt6 License
 * @author     Eduardo Polidor <polidor@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */
class Phprojekt_Auth extends Zend_Auth
{

    /**
     * Checks if user is loggued in or not. It uses the Zend
     *
     * @return boolean true if user is logued in
     */
    public function isLoggedIn()
    {
        $authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');

        if (!isset($authNamespace->userId) || empty($authNamespace->userId)) {
            throw new Phprojekt_Auth_UserNotLoggedInException('User not logged in', 1);
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
        $db     = Zend_Registry::get('db');
        $user   = new Phprojekt_User_User($db);
        $userId = $user->findIdByUsername($username);

        if ($userId > 0) {
            $user->find($userId);
        } else {
            throw new Phprojekt_Auth_Exception('Invalid user or password', 4);
        }

        if (!$user->isActive()) {
            throw new Phprojekt_Auth_Exception('User Inactive', 5);
        }

        try {
            
            $settings = new Phprojekt_User_UserSetting($userId, 1);
            
            // The password does not match with password provided
            if (!Phprojekt_Auth::_compareStringWithPassword((string)$password, (string)$settings->getSetting("password"))) {
                throw new Phprojekt_Auth_Exception('Invalid user or password', 2);
            }
        }
        catch (Exception $e) {
            throw new Phprojekt_Auth_Exception('Invalid user or password', 3);
        }

        // If the user was found we will save the user information on the session
        $authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');
        $authNamespace->userId = $user->id;

        // Please, put any extra info of user to be saved on session here
        return true;
    }

    /**
     * Gets from auth namespace the user id logged in
     *
     * @return integet user ID or false if there isn't user logged
     */
    public function getUserId() {

        $returnValue = false;

        $authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');

        if (isset($authNamespace->userId)) {
            $returnValue = $authNamespace->userId;
        }

        return $returnValue;
    }

    /**
     * Makes the logout process
     *
     * @return boolean true if logout process was sucessful
     */
    public function logout()
    {
        $authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');
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
        // One of the methods to check the password
        $defaultMethod = 'phprojektmd5'.$string;
        $defaultMethod = Phprojekt_Auth::_cryptPassword($defaultMethod);

        if ($defaultMethod == $password) {
            return true;
        }

        // Please add other valid methods here (e.g. not crypted password)
        // None of the methods works
        return false;
    }
    
    /**
     * Compare a string with a user password
     *
     * @param string $string   key uncryted to check if it is the password
     * @param string $password crypted password
     *
     * @return boolean true if the string crypted is equal to provide password
     */
    public static function setPassword($password)
    {
        $userId = Phprojekt_Auth::getUserId();
        
        $cryptedPassword = 'phprojektmd5'.$password;
        $cryptedPassword = Phprojekt_Auth::_cryptPassword($cryptedPassword);
        
        $user = new Phprojekt_User_User();
        if ($user->find($userId)) {
            $settings = new Phprojekt_User_UserSetting($userId, 1);
            $settings->setSetting('password', $cryptedPassword);
        } else {
            return false;
        }
        
        return true;
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
    
    public static function cryptString($string) {
        $cryptedString = 'phprojektmd5'.$string;
        return Phprojekt_Auth::_cryptPassword($cryptedString);
    }
}