<?php
/**
 * Role Rights Class for PHProjekt 6.0
 *
 * @copyright 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license   http://www.phprojekt.com/license PHProjekt6 License
 * @version   CVS: $Id:
 * @author    Nina Schmitt <schmitt@mayflower.de>
 * @package   PHProjekt
 * @subpackage Core
 * @link      http://www.phprojekt.com
 * @since     File available since Release 1.0
 */

/**
 * Phprojekt_Rights for PHProjekt 6.0
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
class Phprojekt_RoleRights
{
    /**
     * item id
     * @var int
     */
    protected $_id = 0;

    /**
     * name of current module
     * @var string
     */
    protected $_module='';

    /**
     * Id of user
     * @var int
     */
    protected $_user = 0;

    /**
     * Id of Project the item belongs to
     * @var int
     */
    protected $_project = 0;

    /**
     * role of user for current item
     * @var string
     */
    protected $_role ='';

    /**
     * Zend acls
     * @var Phprojekt_Acl
     */
    protected $_acl = array();

    /**
     * Constructor
     *
     * @param integer $project Project ID
     * @param string  $module  Current module
     * @param integer $id      Current ID
     * @param integer $user    Current user
     */
    public function __construct($project, $module, $id=0, $user=0)
    {
        $this->_setId($id);
        $this->_setModule($module);
        $this->_setUser($user);
        $this->_setProject($project);
        $this->_setAcl();
        $this->_setUserRole();
    }

    /**
     * checks whether user has a certain right on an item
     *
     * @param string $right  Name of right
     * @param string $module Name of module
     *
     * @return boolean
     */
    public function hasRight($right, $module=null)
    {
        if ($module != null) {
            $this->_setModule($module);
        }

        $role = $this->getUserRole();
        $acl  = $this->getAcl();
        if (null === $module) {
            $module = $this->getModule();
        }
        try {
            return $acl->isAllowed($role, $module, $right);
        }
        catch(Zend_Acl_Exception $e) {
            $logger = Zend_Registry::get('log');
            $logger->debug((string) $e);
            return false;
        }
    }

    /**
     * Setter for item id
     *
     * @param integer $id current id
     *
     * @return void
     */
    private function _setId($id)
    {
        $this->_id = $id;
    }

    /**
     * Getter for Id
     *
     * @return integer $_id Current id
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Sets the project the item belongs to (if item itsel is
     * a project this is the id of item itself)
     *
     * @param integer $project Current project
     *
     * @return void
     */
    private function _setProject($project)
    {
        if ($this->getId() > 0 and $this->getmodule() == 'Project') {
            $this->_project = $this->getId();
        } else {
            $this->_project = $project;
        }
    }

    /**
     * Returns projects
     *
     * @return integer $_project Current project
     */
    public function getProject()
    {
        return $this->_project;
    }

    /**
     * Sets the module
     *
     * @param string $module Current module
     *
     * @return void
     */
    private function _setModule($module)
    {
        $this->_module = $module;
    }

    /**
     * Returns module
     *
     * @return string $module Current module
     */
    public function getModule()
    {
        return $this->_module;
    }

    /**
     * Setter for User
     *
     * @param integer $user Current user
     *
     * @return void
     */
    private function _setUser($user)
    {
        if ($user != 0) {
            $this->_user = $user;
        } else {
            $authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');
            $this->_user   = $authNamespace->userId;
        }
    }

    /**
     * Getter for User
     *
     * @return integer $_user Current user
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * Setter for acl
     *
     * @return void
     */
    private function _setAcl()
    {
        $this->_acl = Phprojekt_Acl::getInstance();
    }

    /**
     * Getter for user
     *
     * @return Phprojekt_Acl $_acl Acls for session
     */
    public function getAcl()
    {
        return $this->_acl;
    }

    /**
     * Setter for UserRole
     * the Role of ther user is fetched from the db
     *
     * @return array
     */
    private function _setUserRole()
    {
        $role        = Phprojekt_Loader::getModel('Role', 'Role');
        $this->_role = $role->fetchUserRole($this->getUser(), $this->getProject());
    }

    /**
     * Getter for UserRole
     * returns UserRole for item
     *
     * @return string $_role
     */
    public function getUserRole()
    {
        return $this->_role;
    }
}