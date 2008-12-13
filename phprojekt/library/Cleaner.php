<?php
/**
 * Santizing class
 *
 * @author     Peter Voringer <peter.voringer@mayflower.de>
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    $Id$
 * @license
 * @package    Cleaner
 * @link       http://www.thinkforge.org/projects/Cleaners
 * @since      File available since Release 6.0
 *
 */

set_include_path(dirname(__FILE__) . PATH_SEPARATOR . get_include_path());

require_once 'Cleaner/Cage.php';
require_once 'Cleaner/Engine.php';
require_once 'Cleaner/Exception.php';
require_once 'Cleaner/Messages.php';
require_once 'Cleaner/Parameter.php';
require_once 'Cleaner/Sanitizer.php';
require_once 'Cleaner/Validator.php';
require_once 'Cleaner/Escaper.php';
require_once 'Cleaner/Util.php';

/**
 * Cleaner is a PHP written sanitizing and
 * escaping class.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license
 * @package    Cleaner
 * @link       http://www.thinkforge.org/projects/Cleaner
 * @author     Peter Voringer <peter.voringer@mayflower.de>
 * @since      File available since Release 6.0
 */
class Cleaner
{

    const INPUT_SCOPE_GET     = 'get';
    const INPUT_SCOPE_POST    = 'post';
    const INPUT_SCOPE_REQUEST = 'request';
    const INPUT_SCOPE_SESSION = 'session';
    const INPUT_SCOPE_COOKIE  = 'cookie';
    const INPUT_SCOPE_FILES   = 'files';
    const INPUT_SCOPE_ENV     = 'env';
    const INPUT_SCOPE_SERVER  = 'server';

    const OUPUT_SCOPE_SQL        = 'sql';
    const OUPUT_SCOPE_HTML       = 'html';
    const OUPUT_SCOPE_CSS        = 'html';
    const OUPUT_SCOPE_JAVASCRIPT = 'javascript';

    const PLUGIN_TYPE_VALIDATOR = 'validator';
    const PLUGIN_TYPE_SANITIZER = 'sanitizer';
    const PLUGIN_TYPE_ESCAPER   = 'escaper';

    /**
     * Sigleton Instance of Cleaner
     *
     * @var Cleaner Instance (Singleton) of Cleaner
     */
    private static $_instance = null;

    /**
     * Cage Instances for default scopes
     *
     * @var array
     */
    private $_cages;


    /**
     * Getter for Singleton instance of Cleaner
     *
     * @return Cleaner Instance (Singleton) of Cleaner
     */
    private static function _getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new Cleaner();
        }

        return self::$_instance;
    }


    /**
     * Set a individual Implementation of Cleaner_Messages
     *
     * @param string $className Name of class to use
     *
     * @return void
     */
    public static function setMessagesClass($className)
    {
        Cleaner_Engine::_getInstance()->setMessagesClass($className);
    }


    /**
     * Set a individual Implementation of Cleaner_Sanitizer
     *
     * @param string $className Name of class to use
     *
     * @return void
     */
    public static function setSanitizerClass($className)
    {
        Cleaner_Engine::_getInstance()->setSanitizerClass($className);
    }


    /**
     * Set a individual Implementation of Cleaner_Validator
     *
     * @param string $className Name of class to use
     *
     * @return void
     */
    public static function setValidatorClass($className)
    {
        Cleaner_Engine::_getInstance()->setValidatorClass($className);
    }

    /**
     * Set a individual Implementation of Cleaner_Escaper
     *
     * @param string $className Name of class to use
     *
     * @return void
     */
    public static function setEscaperClass($className)
    {
        Cleaner_Engine::_getInstance()->setEscaperClass($className);
    }


    /**
     * Get the scope abstraction object
     *
     * @param string $scope Name of Scope (use constants
     *
     * @return Cleaner_Cage scope abstraction object
     */
    public static function getCage($scope)
    {
        $instance = self::_getInstance();

        if (array_key_exists($scope, $instance->_cages)) {
            return $instance->_cages[$scope];
        }

        throw new Cleaner_Exception("Can't return Cage because
        given Scope is not defined");
    }


    /**
     * Escape a value
     *
     * @param string $scope   Scope category, in which the value will be displayed
     * @param string $type    Subtype/Subscope, in which the value will be displayed
     * @param mixed  $value   Value to escape
     * @param mixed  $default Default value, if value is null/empty
     *
     * @return mixed Escaped value
     */
    public static function escape($scope, $type, $value, $default = null)
    {
        if (Cleaner_Util::isBlank($value)) {
            return $default;
        }

        return Cleaner_Engine::getInstance()->escape($scope, $type, $value);
    }


    /**
     * Validates a value against a certain type
     *
     * @param string $type      Type against parameter/item should be validated
     * @param mixed  $value     Value to validate
     * @param object &$messages Messages generated while validation
     * @param bool   $empty     Must parameter/item be not null or empty
     *
     * @return bool true=>valid, false=>invalid
     */
    public static function validate($type, $value, &$messages, $empty = false)
    {
        if (!isset($messages)) {
            $messages = Cleaner_Engine::getMessages();
        }

        if (Cleaner_Util::isBlank($value)) {

            if (!$empty) {
                $messages->add('INVALID_REQUIRED');
            }

            return $empty;
        }

        return Cleaner_Engine::getInstance()->validate($type, $value, $messages);

    }

    /**
     * Sanitizes a value to a certain type
     *
     * @param string $type      Type of parameter/item to sanitize
     * @param mixed  $value     Value to sanitize
     * @param object &$messages Messages generated while sanitizing
     * @param mixed  $default   Return value, if parameter/item
     *                          is null/empty/notset/..
     * @param bool   $empty     Must parameter/item be not null or empty
     *
     * @return mixed
     */
    public static function sanitize($type, $value, &$messages,
    $default = null, $empty = false)
    {

        if (!isset($messages)) {
            $messages = Cleaner_Engine::getMessages();
        }

        /* If strings are empty they are totally valid as
           long as $empty is not specified */
        if (Cleaner_Util::isBlank($value) && ($type != 'string' || $empty)) {

            if (!$empty) {
                $messages->add('SANITIZE_DEFAULT');
            }

            return $default;
        }

        $instance = Cleaner_Engine::getInstance();
        $result   = $instance->sanitize($type, $value, $messages);

        if (is_null($result)) {

            if (!$empty) {
                $messages->add('SANITIZE_DEFAULT');
            }

            return $default;
        }

        return $result;
    }

    /**
     * Validates an item in the wrapped scope for a certain type and if
     * invalid sanitizes the value of the parameter/item
     *
     * @param string $type     Type of parameter/item to validate and/or sanitize
     * @param string $scope    Name of scope to use (GET, POST, ...)
     * @param string $name     Name of parameter / Name of item in scope
     * @param bool   $empty    Must parameter/item be not null or empty
     * @param mixed  $default  Return value, if parameter/item and/or sanitizes
     *                         parameter/item is null/empty/notset/..
     * @param bool   $sanitize Wheather sanitize value of parameter/item, when
     *                         value is invalid
     *
     * @return Cleaner_Parameter Instance, representing selected
     *         parameter/item in Scope
     */
    public static function getParameter($type, $scope, $name,
    $empty = false, $default = null, $sanitize = true)
    {
        $cage = self::getCage($scope);
        return $cage->getParameter($type, $name, $empty, $default, $sanitize);
    }



    /**
     * Creates a new Instance (Singleton) of Cleaner
     *
     */
    protected function __construct()
    {

        $this->_configuration = Cleaner_Engine::_getInstance();

        $this->_cages = array(
            self::INPUT_SCOPE_GET     => Cleaner_Cage::getInstance($_GET, self::INPUT_SCOPE_GET),
            self::INPUT_SCOPE_POST    => Cleaner_Cage::getInstance($_POST, self::INPUT_SCOPE_POST),
            self::INPUT_SCOPE_REQUEST => Cleaner_Cage::getInstance($_REQUEST, self::INPUT_SCOPE_REQUEST),
            self::INPUT_SCOPE_COOKIE  => Cleaner_Cage::getInstance($_COOKIE, self::INPUT_SCOPE_COOKIE),
            self::INPUT_SCOPE_FILES   => Cleaner_Cage::getInstance($_FILES, self::INPUT_SCOPE_FILES),
            self::INPUT_SCOPE_SESSION => Cleaner_Cage::getInstance($_SESSION, self::INPUT_SCOPE_SESSION),
            self::INPUT_SCOPE_ENV     => Cleaner_Cage::getInstance($_ENV, self::INPUT_SCOPE_ENV),
            self::INPUT_SCOPE_SERVER  => Cleaner_Cage::getInstance($_SERVER, self::INPUT_SCOPE_SERVER)
        );

        if (isset($GLOBALS['HTTP_SERVER_VARS'])) {
            $GLOBALS['HTTP_SERVER_VARS'] = null;
        }

        if (isset($GLOBALS['HTTP_GET_VARS'])) {
            $GLOBALS['HTTP_GET_VARS'] = null;
        }

        if (isset($GLOBALS['HTTP_POST_VARS'])) {
            $GLOBALS['HTTP_POST_VARS'] = null;
        }

        if (isset($GLOBALS['HTTP_COOKIE_VARS'])) {
            $GLOBALS['HTTP_COOKIE_VARS'] = null;
        }

        if (isset($GLOBALS['HTTP_ENV_VARS'])) {
            $GLOBALS['HTTP_ENV_VARS'] = null;
        }

        if (isset($GLOBALS['HTTP_SESSION_VARS'])) {
            $GLOBALS['HTTP_SESSION_VARS'] = null;
        }

        if (isset($GLOBALS['HTTP_REQUEST_VARS'])) {
            $GLOBALS['HTTP_REQUEST_VARS'] = null;
        }

        if (isset($GLOBALS['HTTP_FILES_VARS'])) {
            $GLOBALS['HTTP_FILES_VARS'] = null;
        }


    }

}
