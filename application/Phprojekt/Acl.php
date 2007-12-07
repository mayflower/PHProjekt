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
     * Singleton instance
     * @var PHProjekt_Acl
     */
    private static $_instance = null;


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
        $authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');
        $this->userID  = $authNamespace->userID;
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
        $role = Phprojekt_Loader::getModel('Role', 'Role');
        foreach ($role->fetchAll() as $r) {
            if ($r->parent < 1) {
                $r->parent = null;
            }
            $this->addRole(new Zend_Acl_Role($r->id), $r->parent);
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
        $order = array(0 => 'roleId',
                       1 => 'module ASC');
        foreach ($role->fetchAll(null, $order) as $right) {
            if (!$this->has($right->module)) {
                $this->add(new Zend_Acl_Resource($right->module));
                $this->allow($right->roleId, $right->module, $right->permission);
            }
        }
    }
}