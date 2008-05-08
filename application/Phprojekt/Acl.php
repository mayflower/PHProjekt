<?php
/**
 * User class for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://www.phprojekt.com/license PHProjekt6 License
 * @version    CVS: $Id:
 * @author     Nina Schmitt <schmitt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Phprojekt_ACL for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://www.phprojekt.com/license PHProjekt6 License
 * @author     Nina Schmitt <schmitt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */
class Phprojekt_Acl extends Zend_Acl
{
    /**
     * Fixed permission values for items and modules
     *
     */
    const NO_ACCESS = 0;
    const ACCESS    = 1;
    const READ      = 2;
    const WRITE     = 4;
    const ADMIN     = 8;

    /**
     * Singleton instance
     * @var PHProjekt_Acl
     */
    protected static $_instance = null;

    /**
     * Return this class only one time
     *
     * @return PHProjekt_Acl
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructs a Phprojekt ACL
     */
    private function __construct()
    {
        //first construct roles
        $this->_registerRoles();
        //than get rights and assign them to roles and ressources
        $this->_registerRights();
    }

    /**
     * This function adds all Roles to Zend_Acl
     *
     * @return void
     */
    private function _registerRoles()
    {
        $roles = Phprojekt_Loader::getModel('Role', 'Role');
        foreach ($roles->fetchAll() as $role) {
            if ($role->parent < 1) {
                $role->parent = null;
            }
            $this->addRole(new Zend_Acl_Role($role->id), $role->parent);
        }
    }

    /**
     * This function assigns all rights to Zend_Acls
     *
     * @return void
     */
    private function _registerRights()
    {
        $role  = Phprojekt_Loader::getModel('Role', 'RoleModulePermissions');
        foreach ($role->fetchAll(null, 'roleId ASC') as $right) {
            if (!$this->has($right->moduleId)) {
                $this->add(new Zend_Acl_Resource($right->moduleId));
                $this->allow($right->roleId, $right->moduleId, $right->permission);
            }
        }
    }
}