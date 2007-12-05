<?php
/**
 * Role Rights Class for PHProjekt 6.0
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
 * Phprojekt_Rights for PHProjekt 6.0
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
     * @param int    $project project ID
     * @param string $module  current module
     * @param int    $id      current ID
     * @param int    $user    current user  
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
     * @param string $right  name of right
     * @param string $module name of module
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
            return false;
        }
    }

    /**
     * Setter for item id
     * 
     * @param int $id current id
     */
    private function _setId($id)
    {
        $this->_id = $id;
    }

    /**
     * Getter for Id
     *
     * @return int $_id current id
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * sets the project the item belongs to (if item itsel is
     * a project this is the id of item itself)
     * 
     * @param int $project current project
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
     * returns projects
     *
     * @return int $_project current project
     */

    public function getProject(){
        return $this->_project;
    }

    /**
     * sets the module
     * 
     * @param string $module current module
     */
    private function _setModule($module)
    {
        $this->_module = $module;
    }

    /**
     * returns module
     *
     * @return string $module current module
     */
    public function getModule()
    {
        return $this->_module;
    }

    /**
     * Setter for User
     * 
     * @param int $user current user
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
     * getter for User
     * 
     * @return int $_user current user
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * Setter for acl
     */
    private function _setAcl()
    {
        $this->_acl = Phprojekt_Acl::getInstance();
    }

    /**
     * getter for user
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
     */
    private function _setUserRole(){
        $role = Phprojekt_Loader::getModel('Role', 'Role');
        $this->_role=$role->fetchUserRole($this->getUser(), $this->getProject());
    }
    
    /**
     * getter for UserRole
     * returns UserRole for item
     *
     * @return string $_role
     */
    public function getUserRole()
    {
        return $this->_role;
    }
}