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
     * id of current module
     * @var int
     */
    protected $_moduleId = 1;

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
     * @param integer $project  Project Id
     * @param string  $moduleId Current module Id
     * @param integer $id       Current Id
     * @param integer $user     Current user
     */
    public function __construct($project, $moduleId = 1, $id = 0, $user = 0)
    {
        $this->_setId($id);
        $this->_setModule($moduleId);
        $this->_setUser($user);
        $this->_setProject($project);
        $this->_setAcl();
        $this->_setUserRole();
    }

    /**
     * Checks whether user has a certain right on an item
     *
     * @param string $right    Name of right
     * @param string $moduleId Module Id
     *
     * @return boolean
     */
    public function hasRight($right, $moduleId = null)
    {
        if (null != $moduleId) {
            $this->_setModule($moduleId);
        }

        $role = $this->getUserRole();
        $acl  = $this->getAcl();
        if (null === $moduleId) {
            $moduleId = $this->getModule();
        }
        try {
            return $acl->isAllowed($role, $moduleId, $right);
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
     * @param string $moduleId Current module Id
     *
     * @return void
     */
    private function _setModule($moduleId)
    {
        $this->_module = $moduleId;
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
            $this->_user = Phprojekt_Auth::getUserId();
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
        $project = Phprojekt_Loader::getModel('Project', 'ProjectRoleUserPermissions');
        $this->_role = $project->fetchUserRole($this->getUser(), $this->getProject());
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