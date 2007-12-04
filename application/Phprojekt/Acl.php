<?php
/**
 * User class for PHProjekt 6.0
 *
 * @copyright 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license   http://www.phprojekt.com/license PHProjekt6 License
 * @version
 * @author    Nina Schmitt <schmitt@mayflower.de>
 * @package   PHProjekt
 * @subpackage Core
 * @link      http://www.phprojekt.com
 * @since     File available since Release 1.0
 */

/**
 * Phprojekt_ACL for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license   http://www.phprojekt.com/license PHProjekt6 License
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
	 * All roles from Database
	 * @var array
	 */
    protected $_roles = array();

    /**
	 * All rights from Database
	 * @var array
	 */
    protected $_rights = array();

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
        $this->userID = $authNamespace->userID;
        //first construct roles
        $this->setRoles();
        $this->registerRoles();
        //than get rights and assign them to roles and ressources
        $this->setRights();
        $this->assignRights();
    }

    /**
    * This function fetches all roles from DB
    *
    * @param unknown_type $db
    *
    */
    private function setRoles()
    {
        $db = Zend_Registry::get('db');
        $sql = 'SELECT  role.id, parent
						FROM role
						ORDER BY role.parent ASC';
        $roles= $db->fetchAll($sql);
        $this->_roles=$roles;
    }

    /**
	 * This function returns all roles
	 *
	 */
    public function getRoles()
    {

        return $this->_roles;
    }

    /**
     * This function adds all Roles to Zend_Acl
     *
     */
    private function registerRoles()
    {
        $roles = $this->getRoles();

        foreach ($roles as $r) {
            if ($r['parent'] < 1) {
                $r['parent'] = null;
            }
            $this->addRole(new Zend_Acl_Role($r['id']), $r['parent']);
        }
    }

    /**
	 * This function fetches all rights from DB
	 * and saves it in $_rights;
	 *
	 */
    private function setRights()
    {
        $db = Zend_Registry::get('db');
        $sql = 'SELECT  id, roleId, module, permission
                        FROM roleModulePermissions
                        ORDER BY roleId,module ASC';
        $rights = $db->fetchAll($sql);
        $this->_rights = $rights;
    }

    /**
     * this function returns current rights
     * @return  array $_rights
     */
    public function getRights()
    {
        return $this->_rights;
    }

    /**
     * this function assigns all rights to Zend_Acls
     */
    private function assignRights()
    {
        foreach ($this->getRights() as $right) {
            if (!$this->has($right['module'])) {
                $this->add(new Zend_Acl_Resource($right['module']));
                $this->allow($right['roleId'], $right['module'], $right['permission']);
            }
        }
    }
}