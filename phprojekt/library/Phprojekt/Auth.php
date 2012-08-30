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
 * Auth class.
 */
class Phprojekt_Auth extends Zend_Auth
{
    /**
     * Prefix for use in cookies.
     */
    const COOKIES_PREFIX = 'p6.';

    /**
     * Token.
     */
    const LOGGED_TOKEN = 'keepLoggedToken';

    /**
     * An error message that can be shown if the user is not logged in.
     */
    const NOT_LOGGED_IN_MESSAGE = 'User not logged in';

    /**
     * Checks in the session if user is loggued in or not.
     * If it is not logged, tries to log him/her using browser cookies.
     *
     * @return boolean True if user is logued in.
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
                $setting           = new Phprojekt_Setting();
                $setting->setModule('User');
                $tokenDbHash    = $setting->getSetting(self::LOGGED_TOKEN . '_hash', $tokenCookieUserId);
                $tokenDbExpires = (int) $setting->getSetting(self::LOGGED_TOKEN . '_expires', (int) $tokenCookieUserId);

                // Is there valid DB token data, which has not expired?
                if ($tokenDbExpires > time()) {
                    // Yes - The expiration time exists and is valid. The hashes match?
                    if ($tokenCookieHash == $tokenDbHash) {
                        // Yes - Log in the user
                        $user = new Phprojekt_User_User();
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
                    return false;
                }
            } else {
                return false;
            }
        }

        return true;
    }

   public static function getLoginMode()
   {
       $conf = Phprojekt::getInstance()->getConfig();

       $mode = isset($conf->authentication->mode) ? strtolower($conf->authentication->mode) : null;
       if (in_array($mode, array("default", "ldap"))) {
           return $mode;
       }
       return "default";
   }

    /**
     * Makes the login process.
     *
     * @param string  $username   Username provided.
     * @param string  $password   Clean password typed by user.
     * @param boolean $keepLogged Keep the user logued for next uses.
     *
     * @throws Phprojekt_Auth_Exception On login errors.
     *
     * @return boolean True if login process was sucessful.
     */
    public static function login($username, $password, $loginOptions = array())
    {
       $mode = self::getLoginMode();
       $options = array(
           'keepLogged' => false,
           'loginServer' => null
       );
       if (is_array($loginOptions)) {
           $options = array_merge($options, $loginOptions);
       }

       $success = false;
       if ($mode == 'default') {
           $success = self::_defaultLogin($username, $password, $options['keepLogged']);
       } else if ($mode == 'ldap') {
           $success = self::_ldapLogin($username, $password, $options['keepLogged'], $options['loginServer']);
       } else {
           Phprojekt::getInstance()->getLog()->err('Invalid authentication mode, please check configuration.php');
           throw new Phprojekt_Auth_Exception('Configuration error. Please contact your administrator', 4);
       }

       if ($success) {
           // Regenerate the id if we are not in the unitTest
           if (!headers_sent()) {
               Zend_Session::regenerateId();
           }
           return true;
       } else {
           throw new Phprojekt_Auth_Exception('Invalid user or password', 4);
       }
    }

    private static function _defaultLogin($username, $password, $keepLogged = false)
    {
        $user   = new Phprojekt_User_User();
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
            $setting = new Phprojekt_Setting();
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

    private static function _ldapLogin($username, $password, $keepLogged = false, $loginServer = null)
    {
        if (!function_exists('ldap_connect')) {
            throw new Phprojekt_Auth_Exception('LDAP extension not loaded for PHP', 7);
        }

        // Get PHProjekt user for integration purposes
        $user   = new Phprojekt_User_User();
        $userId = $user->findIdByUsername($username);

        // We don't want LDAP authentication for PHProjekt system admin
        if ($userId !== 1) {
            $auth        = Zend_Auth::getInstance();
            $conf        = Phprojekt::getInstance()->getConfig();
            $ldapOptions = $conf->authentication->ldap->toArray();
            $adapter     = new Zend_Auth_Adapter_Ldap($ldapOptions, $username, $password);
            $result      = $auth->authenticate($adapter);

            if ($result->isValid()) {
                // Authentication ok with LDAP
                self::_ldapIntegration($userId, $username, $password, $loginServer);
            } else if ($result->getCode() !== Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND
                    && $result->getCode() !== Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID) {
                Phprojekt::getInstance()->getLog()->debug(
                    "An error occured while trying to authenticate {$result->getIdentity()}\n\n"
                    . implode("\n", $result->getMessages())
                );
                throw new Phprojekt_Auth_Exception(
                    'An error occured while trying to authenticate with the Ldap-Server.'
                    . ' Please contact the administrator.'
                );
            }
        }

        // Default login after LDAP authentication and integration
        return self::_defaultLogin($username, $password, $keepLogged);
    }

    private static function _ldapIntegration($userId, $username, $password, $loginServer = null)
    {
        $userId = intval($userId);

        $conf = Phprojekt::getInstance()->getConfig();
        $ldapOptions = $conf->authentication->ldap->toArray();

        // Zend library does not allow determining from which server the user was found from
        // That's why we need to request the server from the user during login.
        $account = null;
        if ($loginServer !== null && array_key_exists($loginServer, $ldapOptions)) {
            $searchOpts = $ldapOptions[$loginServer];
            try {
                $ldap = new Zend_Ldap($searchOpts);
                $ldap->connect();
                $ldap->bind($username, $password);

                $filter = sprintf(
                    "(
                        &(
                           |(objectclass=posixAccount)
                            (objectclass=Person)
                        )
                        (
                            |(uid=%s)
                             (samAccountName=%s)
                         )
                    )",
                    $username,
                    $username
                );
                $result = $ldap->search($filter, $searchOpts['baseDn']);

                $account = $result->getFirst();

                $ldap->disconnect();
            } catch (Exception $e) {
                throw new Phprojekt_Auth_Exception(
                    'Failed to establish a search connection to the LDAP server:'
                    . ' ' . $server . ' ' . 'Please check your configuration for that server.',
                    8
                );
            }
        } else {
            throw new Phprojekt_Auth_Exception(
                'Server not specified during login! "
                . "Please check that your login screen contains the login domain selection.',
                9
            );
        }

        if ($account !== null) {
            // User found

            $integration = isset($conf->authentication->integration) ? $conf->authentication->integration->toArray()
                                                                     : array();

            $firstname = "";
            $lastname = "";
            $email = "";

            if (isset($account['givenname'])) {
                $firstname = $account['givenname'][0];
            }
            if (isset($account['sn'])) {
                $lastname = $account['sn'][0];
            }
            if (isset($account['mail'])) {
                $email = $account['mail'][0];
            }

            // Set user params
            $params = array();
            $params['id']       = intval($userId); // New user has id = 0
            $params['username'] = $username;
            $params['password'] = $password;

            $admins = array();
            if (isset($integration['systemAdmins'])) {
                $admins = split(",", $integration['systemAdmins']);
                foreach ($admins as $key => $admin) {
                    $admins[$key] = trim($admin);
                }
            }
            $params['admin'] = in_array($username, $admins) ? 1 : 0; // Default to non-admin (0)
            if ($userId > 0) {
                $user = self::_getUser($userId);
                $params['admin'] = intval($user->admin);
            }

            // Integrate with parameters found from LDAP server
            $params['firstname'] = $firstname;
            $params['lastname'] = $lastname;
            $params['email'] = $email;

            if ($userId > 0) {
                // Update user parameters with those found from LDAP server
                $user->find($userId);

                $params['id'] = $userId;
                if (!self::_saveUser($params)) {
                    throw new Phprojekt_Auth_Exception('User update failed for LDAP parameters', 10);
                }
            } else {
                // Add new user to PHProjekt

                // TODO: Default conf could be defined in configuration
                // Lists needed for checks ?

                // Set default parameters for users
                $params['status']   = "A";          // Active user
                $params['language'] =
                    isset($conf->language) ?
                        $conf->language : "en";     // Conf language / English
                $params['timeZone'] = "0000";       // (GMT) Greenwich Mean Time: Dublin, Edinburgh, Lisbon, London

                // Default integration vals from config
                if (isset($integration['admin']) && $params['admin'] == 0) {
                    $val = intval($integration['admin']);
                    if ($val == 1 || $val == 0) {
                        $params['admin'] = $val;
                    }
                }
                if (isset($integration['status'])) {
                    $val = trim(strtoupper($integration['status']));
                    if (in_array($val, array("A", "I"))) {
                        $params['status'] = $val;
                    }
                }
                if (isset($integration['language'])) {
                    $val = trim(strtolower($integration['language']));

                    $languages = Phprojekt_LanguageAdapter::getLanguageList();
                    if (array_key_exists($val, $languages)) {
                        $params['language'] = $val;
                    } else if (($val = array_search('(' . $val . ')', $languages)) !== false) {
                        $params['language'] = $val;
                    }
                }
                if (isset($integration['timeZone'])) {
                    $val = trim(strtolower($integration['timeZone']));

                    $timezones = Phprojekt_Converter_Time::getTimeZones();
                    if (array_key_exists($val, $timezones)) {
                        $params['timeZone'] = $val;
                    } else if (($val = array_search($val, $timezones)) !== false) {
                        $params['timeZone'] = $val;
                    }
                }

                if (!self::_saveUser($params)) {
                    throw new Phprojekt_Auth_Exception('User creation failed after LDAP authentication', 10);
                }
            }

        } else {
            throw new Phprojekt_Auth_Exception('Failed to find the LDAP user with the given username', 11);
        }
    }

    /**
     * This function is used only for integration purposes and should only
     * be called when integrating the user data!
     *
    **/
    static private function _saveUser($params)
    {
        $moduleName = "Phprojekt_User_User";
        if (Phprojekt_Loader::tryToLoadLibClass($moduleName)) {
            // Temporarily login as admin user
            // This is only to get rights to add user
            // Ugly hack but PHProjekt backend has not thought of this situation
            $authNamespace = new Zend_Session_Namespace('Phprojekt_Auth-login');
            $authNamespace->userId = 1;
            $authNamespace->admin  = true;

            $exc = null;
            try {
                $db = Phprojekt::getInstance()->getDb();
                $model = new $moduleName($db);
                if ($params['id'] > 0) {
                    $model = $model->find($params['id']);
                }
                Default_Helpers_Save::save($model, $params);

                $setting = new Phprojekt_Setting();
                $setting->setModule('User');
                $setting->setSettings($params, $model->id);
            } catch (Exception $e) {
                $exc = $e;
            }

            // Set user not logged in
            unset($authNamespace->userId);
            unset($authNamespace->admin);
            if ($exc instanceof Exception) {
                // Throw exception if user creation failed
                throw $exc;
            }

            return true;
        }
        return false;
    }

    static private function _getUser($id)
    {
         $user = new Phprojekt_User_User();
         $user->find($id);

        return $user;
    }

    /**
     * Gets from auth namespace the user ID logged in.
     *
     * @return integer|false user ID or false if there isn't user logged.
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

    static public function getRealUser()
    {
        static $user = null;
        if (null === $user) {
            $u    = new Phprojekt_User_User();
            $user = $u->findUserById(Phprojekt_Auth::getUserId());
        }
        return $user;
    }

    /**
     * Gets from auth namespace if the user is admin or not.
     *
     * @return boolean
     */
    public static function isAdminUser()
    {
        $returnValue   = 0;
        $authNamespace = new Zend_Session_Namespace('Phprojekt_Auth-login');

        if (isset($authNamespace->admin)) {
            $returnValue = $authNamespace->admin;
        }
        return $returnValue > 0;
    }

    /**
     * Makes the logout process.
     *
     * @return boolean True if logout process was sucessful.
     */
    public static function logout()
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
     * Check a username/password combination for validity.
     *
     * @param string $username The username
     * @param string $password The password
     *
     * @return boolean Whether the password matches the user.
     */
    public static function checkCredentials($username, $password)
    {
        $user   = new Phprojekt_User_User();
        $userId = $user->findIdByUsername($username);
        if (0 == $userId) {
            return false;
        }
        $setting = new Phprojekt_Setting();
        $setting->setModule('User');
        return self::_compareStringWithPassword($password, $setting->getSetting("password", $userId));
    }

    /**
     * Compare a string with a user password.
     *
     * @param string $string   Key uncryted to check if it is the password.
     * @param string $password Crypted password.
     *
     * @return boolean True if the string crypted is equal to provide password.
     */
    private static function _compareStringWithPassword($string, $password)
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
     * String to be crytped.
     *
     * @param string $password String to be cripted.
     *
     * @return scring Crypted password.
     */
    private static function _cryptPassword($password)
    {
        return md5($password);
    }

    /**
     * String to be crytped.
     *
     * @param string $string String to be cripted.
     *
     * @return scring Crypted password.
     */
    public static function cryptString($string)
    {
        $cryptedString = 'phprojektmd5' . $string;

        return Phprojekt_Auth::_cryptPassword($cryptedString);
    }

    /**
     * Deletes login data on DB and cookies.
     *
     * @param integer $userId ID of the user.
     *
     * @return void
     */
    private static function _deleteDbAndCookies($userId)
    {
        if ($userId) {
            // Delete all DB settings table token rows
            $db      = Phprojekt::getInstance()->getDb();
            $setting = new Phprojekt_Setting();
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
     * Save the login data into Settings and Cookies.
     *
     * @param integer $userId Current user ID.
     *
     * @return void
     */
    private static function _saveLoginData($userId)
    {
        // The hash string is changed everytime it is used, and the expiration time updated.
        // DB Settings table: create new md5 hash and update expiration time for it

        // Set the settings pair to save
        $pair = array(self::LOGGED_TOKEN . '_hash'    => md5(time() . mt_rand()),
                      self::LOGGED_TOKEN . '_expires' => strtotime('+1 week'));

        // Store matching keepLogged data in DB and browser
        $user = new Phprojekt_User_User();
        $user->find($userId);
        $settings = $user->settings->fetchAll();

        foreach ($pair as $key => $value) {
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
     * Set the cookies.
     *
     * @param string  $hash    User hash for check.
     * @param integer $userId  Current userId.
     * @param integer $expires Timestamp for expire.
     *
     * @return void
     */
    private static function _setCookies($hash, $userId, $expires)
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
