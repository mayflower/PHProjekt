<?php
/**
 * Role class for PHProjekt 6.0
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @author     Eduardo Polidor <polidor@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @todo       EVERYTHING, THIS IS A MOCKUP
 */

/**
 * Phprojekt_Role for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */
class Phprojekt_Role
{
    /**
     * Constructor
     */
    function __destruct()
    {
    }

    /**
     * Destructor
     */
    public function __construct()
    {
    }

    /**
     * Adds a permission to any module
     *
     * @param string $module Module where the permission will be granted
     * @param int $right          Right access level granted
     *
     * @return boolean True if permissin add was sucessful
     */
    public function addPermissionForModule($module, $right)
    {
        return true;
    }

    /**
     * Delete the permission to a specific module
     *
     * @param string $module Name of the module
     *
     * @return boolean True if permission was sucessful deleted
     */
    public function deletePermissionForModule($module)
    {
        return true;
    }

    /**
     * Gets the role name
     *
     * @return string Name the name of the role
     */
    public function getName()
    {
        return '';
    }

    /**
     * Gets the permission for a module
     *
     * @param string $module Name of the module
     *
     * @return integer The permission value for that module
     */
    public function getPermissionForModule($module)
    {
        return true;
    }

    /**
     * Gets the permission list for a role
     *
     * @return array Array with all permissions of the role
     */
    public function getPermissions()
    {
        return array();
    }

    /**
     * To be defined
     *
     * @return boolean
     */
    public function hasPermission()
    {
        return true;
    }
}