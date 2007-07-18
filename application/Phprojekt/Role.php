<?php
/**
 * Role class for PHProjekt 6.0
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


/**
 * Phprojekt_Role for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */
class Phprojekt_Role
{

	function __destruct()
	{
	}

	public function __construct()
	{
	}

	/**
	 * Adds a permission to any module
	 * 
	 * @param string module module where the permission will be granted
	 * @param integer right access level granted
	 * @return boolean true if permissin add was sucessful
	 */
	public function addPermissionForModule(string $module, int $right)
	{
	    return true;
	}

	/**
	 * Delete the permission to a specific module
	 * 
	 * @param string module name of the module
	 * @return boolean true if permission was sucessful deleted
	 */
	public function deletePermissionForModule(string $module)
	{
	    return true;
	}

	
	/**
	 * Gets the role name
	 * 
	 * @return string name the name of the role
	 */
	public function getName()
	{
	    return '';
	}

	
	/**
	 * Gets the permission for a module
	 * 
	 * @param string module name of the module
	 * @return integer the permission value for that module
	 */
	public function getPermissionForModule(string $module)
	{
	    return true;
	}

	
	/**
	 * Gets the permission list for a role
	 * 
	 * @return array with all permissions of the role
	 */
	public function getPermissions()
	{
	    return array();
	}

	
	/**
	 * To be defined 
	 * @param to be defined
	 * @return boolean 
	 */
	public function hasPermission()
	{
	    return true;
	}

}
