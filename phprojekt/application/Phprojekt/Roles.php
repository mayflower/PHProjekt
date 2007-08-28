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
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @todo       EVERYTHING, THIS IS A MOCKUP
 */

/**
 * Phprojekt_Roles for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */
class Phprojekt_Roles
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
     * @param int    $right  Right access level granted
     *
     * @return boolean True if permissin add was sucessful
     */
    public function addPermissionForModule($module, $right)
    {
        if (isset($module) && isset($right)) {
            // apply rights
            if (Zend_Registry::isRegistered('log')) {
                $oLog = Zend_Registry::get('log');
                $oLog->debug('Add permission requested and not implemented yet');
            }
        }
        
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
        if (isset($module)) {
            // delete module permission
            if (Zend_Registry::isRegistered('log')) {
                $oLog = Zend_Registry::get('log');
                $oLog->debug('Delete permission requested and not implemented yet');
            }
        }
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
        if (isset($module)) {
            // get module permission
            if (Zend_Registry::isRegistered('log')) {
                $oLog = Zend_Registry::get('log');
                $oLog->debug('Get permission requested and not implemented yet');
            }
        }
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
