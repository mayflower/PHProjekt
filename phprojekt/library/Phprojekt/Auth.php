<?php
/**
 * User class for PHProjekt 6.0
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
 * @author     Eduardo Polidor <polidor@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Phprojekt_User for PHProjekt 6.0
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @author     Eduardo Polidor <polidor@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */
class Phprojekt_Auth extends Zend_Auth
{
    const COOKIES_PREFIX = 'p6.';
    const LOGGED_TOKEN   = 'keepLoggedToken';

    /**
     * Checks in the session if user is loggued in or not. It uses the Zend
     * If it is not logged, tries to log him/her using browser cookies.
     *
     * @return boolean true if user is logued in
     */
    static public function isLoggedIn()
    {
        $authNamespace = new Zend_Session_Namespace('Phprojekt_Auth-login');

        // Is there session data?
        if (!isset($authNamespace->userId) || empty($authNamespace->userId)) {
            // No - Read cookies
            $readingPrefix  = str_replace('.', '_', self::COOKIES_PREFIX);
            $cookieHashName = $readingPrefix . self::LOGGED_TOKEN . '_hash';
            $cookieUserId   = $readingPrefix . self::LOGGED_TOKEN . '_user';
            // Are there cookies?
            if (isset($_COOKIE[$cookieHashName]) && isset($_COOKIE[$cookieUserId])
                && (int) $_COOKIE[$cookieUserId] > 0) {
                // Yes
                $tokenCookieHash   = Cleaner::sanitize('alnum', $_COOKIE[$cookieHashName]);
                $tokenCookieUserId = (int) $_COOKIE[$cookieUserId];
                $goToLoginPage     = false;
                $setting           = Phprojekt_Loader::getModel('Setting', 'Setting');
                $setting->setModule('User');
                $tokenDbHash    = $setting->getSetting(self::LOGGED_TOKEN . '_hash', $tokenCookieUserId);
                $tokenDbExpires = (int) $setting->getSetting(self::LOGGED_TOKEN . '_expires', (int) $tokenCookieUserId);

                // Is there valid DB token data, which has not expired?
                if ($tokenDbExpires > time()) {
                    // Yes - The expiration time exists and is valid. The hashes match?
                    if ($tokenCookieHash == $tokenDbHash) {
                        // Yes - Log in the user
                        $user = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
                        $user->find($tokenCookieUserId);
                        // If the user was found we will save the user information in the session
                        $authNamespace->userId = $user->id;
                        $authNamespace->admin  = $user->admin;

                        // Save the data into the DB and Cookies
                        self::_saveLoginData($tokenCookieUserId);
                    } else {
                        $goToLoginPage = true;
                    }
                } else {
                    $goToLoginPage = true;
                }

                if ($goToLoginPage) {
                    self::_deleteDbAndCookies($tokenCookieUserId);
                    throw new Phprojekt_Auth_UserNotLoggedInException('User not logged in', 1);
                }
            } else {
                throw new Phprojekt_Auth_UserNotLoggedInException('User not logged in', 1);
            }
        }

        return true;
    }

    /**
     * Makes the login process
     *
     * @param string  $username   username provided
     * @param string  $password   Clean password typed by user
     * @param boolean $keepLogged Keep the user logued for next uses
     *
     * @return boolean true if login process was sucessful
     */
    public function login($username, $password, $keepLogged = false)
    {
        $user   = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
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
            $setting = Phprojekt_Loader::getModel('Setting', 'Setting');
            $setting->setModule('User');

            // The password does not match with password provided
            if (!Phprojekt_Auth::_compareStringWithPassword($password, $setting->getSetting("password", $userId))) {
                throw new Phprojekt_Auth_Exception('Invalid user or password', 2);
            }
        } catch (Exception $error) {
            $error->getMessage();
            throw new Phprojekt_Auth_Exception('Invalid user or password', 3);
        }

        // If the user was found we will save the user information on the session
        $authNamespace = new Zend_Session_Namespace('Phprojekt_Auth-login');
        $authNamespace->userId = $user->id;
        $authNamespace->admin  = $user->admin;

        if ($keepLogged) {
            // Delete previous existing data, just in case
            self::_deleteDbAndCookies($userId);
            // Store matching keepLogged data in DB and browser
            self::_saveLoginData($userId);
        }

        // Please, put any extra info of user to be saved on session here
        return true;
    }

    /**
     * Gets from auth namespace the user id logged in
     *
     * @return integet user ID or false if there isn't user logged
     */
    static public function getUserId()
    {
        $returnValue   = 0;
        $authNamespace = new Zend_Session_Namespace('Phprojekt_Auth-login');

        if (isset($authNamespace->userId)) {
            $returnValue = $authNamespace->userId;
        }

        return (int) $returnValue;
    }

    /**
     * Gets from auth namespace if the user is admin or not
     *
     * @return 1 or 0
     */
    public function isAdminUser()
    {
        $returnValue   = 0;
        $authNamespace = new Zend_Session_Namespace('Phprojekt_Auth-login');

        if (isset($authNamespace->admin)) {
            $returnValue = $authNamespace->admin;
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
        $userId        = 0;
        $authNamespace = new Zend_Session_Namespace('Phprojekt_Auth-login');
        // Try to read user id from PHP session
        if (isset($authNamespace->userId) && !empty($authNamespace->userId)) {
            $userId = $authNamespace->userId;
        } else {
            // Try to read user id from cookies
            $readingPrefix = str_replace('.', '_', self::COOKIES_PREFIX);
            $cookieUserId  = $readingPrefix . self::LOGGED_TOKEN . '_user';
            if (isset($_COOKIE[$cookieUserId])) {
                $userId = (int) $_COOKIE[$cookieUserId];
            }
        }

        self::_deleteDbAndCookies($userId);
        Zend_Session::destroy();
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
        $defaultMethod = 'phprojektmd5' . (string) $string;
        $defaultMethod = Phprojekt_Auth::_cryptPassword($defaultMethod);

        if ($defaultMethod == (string) $password) {
            return true;
        }

        // Please add other valid methods here (e.g. not crypted password)
        // None of the methods works
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

    /**
     * String to be crytped
     *
     * @param string $string string to be cripted
     *
     * @return scring crypted password
     */
    public static function cryptString($string)
    {
        $cryptedString = 'phprojektmd5'.$string;
        return Phprojekt_Auth::_cryptPassword($cryptedString);
    }

    /**
     * Deletes login data on DB and cookies
     *
     * @param int $userId  Id of the user
     *
     * @return void
     */
    private function _deleteDbAndCookies($userId) {
        if ($userId) {
            // Delete all DB settings table token rows
            $db      = Phprojekt::getInstance()->getDb();
            $setting = Phprojekt_Loader::getModel('Setting', 'Setting');
            $setting->setModule('User');
            $where = sprintf("user_id = %d AND key_value LIKE %s", (int) $userId, $db->quote(self::LOGGED_TOKEN . '%'));
            $rows  = $setting->fetchAll($where);
            foreach ($rows as $row) {
                $row->delete();
            }
        }

        // Don't work with cookies if headers have already been sent (when unittest are being executed)
        if (headers_sent()) {
            return;
        }

        self::_setCookies("", 0, 1);
    }

    /**
     * Save the login data into Settings and Cookies
     *
     * @param int $userId Current userId
     *
     * @return void
     */
    private function _saveLoginData($userId)
    {
        // The hash string is changed everytime it is used, and the expiration time updated.
        // DB Settings table: create new md5 hash and update expiration time for it

        // Set the settings pair to save
        $pair = array(self::LOGGED_TOKEN . '_hash'    => md5(time()),
                      self::LOGGED_TOKEN . '_expires' => strtotime('+1 week'));

        // Store matching keepLogged data in DB and browser
        $user = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
        $user->find($userId);
        $settings = $user->settings->fetchAll();

        foreach($pair as $key => $value) {
            $found = false;
            foreach ($settings as $setting) {
                // Update
                if ($setting->keyValue == $key) {
                    $setting->value = $value;
                    $setting->save();
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                // Create
                $record             = $user->settings->create();
                $record->moduleId   = 0;
                $record->keyValue   = $key;
                $record->value      = $value;
                $record->identifier = 'Login';
                $record->save();
            }
        }

        // Cookies: update md5 hash and expiration time
        // If we are under Unittest execution, don't work with cookies:
        if (!headers_sent()) {
            self::_setCookies($pair[self::LOGGED_TOKEN . '_hash'], $userId, $pair[self::LOGGED_TOKEN . '_expires']);
        }
    }

    /**
     * Set the cookies
     *
     * @param string $hash    User hash for check
     * @param int    $userId  Current userId
     * @param int    $expires Timestamp for expire
     *
     * @return void
     */
    private function _setCookies($hash, $userId, $expires)
    {
        // Set cookies
        $completePath     = Phprojekt::getInstance()->getConfig()->webpath;
        $partialPathBegin = strpos($completePath, "/", 8);
        $partialPath      = substr($completePath, $partialPathBegin);
        $cookieHash       = self::COOKIES_PREFIX . self::LOGGED_TOKEN . '.hash';
        $cookieUser       = self::COOKIES_PREFIX . self::LOGGED_TOKEN . '.user';
        setcookie($cookieHash, $hash, $expires, $partialPath);
        setcookie($cookieUser, $userId, $expires, $partialPath);
    }
}
