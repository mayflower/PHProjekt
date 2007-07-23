<?php
/**
 * User class for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license
 * @version    CVS: $Id$
 * @author     Eduardo Polidor <polidor@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @TODO       Check with phpcs ASAP
 * @todo       EVERYTHING, THIS IS A MOCKUP.
 */

/* Phprojekt_DatabaseManagerObject */
require_once ('Phprojekt_DatabaseManagerObject.php');

/**
 * Phprojekt_User for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */

class Phprojekt_User extends Phprojekt_Item
{
    /**
     * User id
     * @var integer
     */
    public $id;

    /**
     * Username
     * @var string
     */
    public $username;

    /**
     * Firstname
     * @var string
     */
    public $firstname;

    /**
     * Lastname
     * @var string
     */
    public $lastname;

    /**
     * Prefered languange
     * @var string
     */
    public $language;

    function __construct()
    {
    }

    function __destruct()
    {
    }

    /**
     * Get a setting value of the user
     * @param string name of the setting to be get
     * @return mixed value of the setting
     */
    public function getSetting(mixed $name)
    {
        return $this->$name;
    }

    /**
     * Checks if user is active
     * @return boolean id user is active or not
     */
    public function isActive()
    {
        return true;
    }

    /**
     * Makes the login process
     * @return boolean true if login process was sucessful
     */
    public function login()
    {
        return true;
    }

    /**
     * Makes the logout process
     *@return boolean true if logout process was sucessful
     */
    public function logout()
    {
        return true;
    }

    /**
     * Sets any setting of the user
     * @param string name of the setting to be changed
     * @param string value new value
     * @return mixed The value set on the setting
     */
    public function setSetting(string $name, string $value)
    {
        return $this->$name = $value;
    }
}