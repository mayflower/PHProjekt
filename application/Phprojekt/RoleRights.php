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
    protected $_user =0;
    /**
     * Id of Project the item belongs to
     * @var int
     */
    protected $_project =0;
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
     */
    public function __construct($project,$module,$id=0,$user=0)
    {
        $this->setId($id);
        $this->setmodule($module);
        $this->setUser($user);
        $this->setproject($project);
        $this->setacl();
        $this->setUserRole();
    }


    /**
	 * checks whether user has a certain right on an item
	 * @param string $right
	 * @param string $module
	 * @return boolean
	 */
    public function hasRight($right,$module=null){
        if($module!=null)$this->setmodule($module);
        $role = $this->getUserRole();
        $acl = $this->getacl();
        if ($module === null) {
            $module = $this->getmodule();
        }
        try{
            return $acl->isAllowed($role, $module, $right);
        }
        catch(Zend_Acl_Exception $e){
            return false;
        }
    }

    /**
     * Setter for item id
     * @package int $id
     */
    private function setId($id){
        $this->_id = $id;
    }
    
    /**
     * Getter for Id
     * 
     * @return int $_id
     */
    public function getId(){
        return $this->_id;
    }
    
    /**
     * sets the project the item belongs to (if item itsel is 
     * a project this is the id of item itself)
     * @param int $project
     */
    private function setproject($project){
        if ($this->getId() > 0 and $this->getmodule() == 'Project') {
            $this->_project = $this->getId();
        } else {
            $this->_project = $project;
        }
    }
    /**
     * returns projects
     *
     * @return int $_project
     */
    function getproject(){
        return $this->_project;
    }
    
    /**
     * sets the module 
     * @param string module
     */
    private function setmodule($module){
        $this->_module = $module;
    }
    
    /**
     * returns module
     *
     * @return string $module
     */
    public function getmodule(){
        return $this->_module;
    }
    
    /**
     * Setter for User
     * @param int $user
     */
    private function setUser($user){
        if ($user != 0) {
            $this->_user = $user;
        } else {
            $authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');
            $this->_user = $authNamespace->userId;
        }
    }
    
   /**
    * getter for User
    * @return int $_user
    */
    public function getUser(){
        return $this->_user;
    }
    
    /**
     * Setter for acl
     */
    private function setacl(){
        $this->_acl =Phprojekt_Acl::getInstance();
    }
    
    /**
     * getter for user
     * @return Phprojekt_Acl $_acl
     */
    public function getacl(){
        return $this->_acl;
    }
    
    /**
     * Setter for UserRole
     * the Role of ther user is fetched from the db
     */
    private function setUserRole(){
        $db = Zend_Registry::get('db');
        $sql = 'SELECT role.id
					   FROM	projectuserrolerelation as rel
					   LEFT JOIN role
					   ON rel.roleId=role.ID
					   WHERE userId='.(int)$this->getUser()
        .' AND projectId='.(int)$this->getproject();


        $roles = $db->fetchRow($sql);
        if (!$roles['id']) {
            $sqlParent = 'SELECT parent
					   FROM	project
					   WHERE ID ='.(int)$this->getproject();
            $parent= $db->fetchCol($sqlParent);
            if ($parent[0] > 0) {
                $this->_project = $parent[0];
                $this->setUserRole();
            }
        } else {
            $this->_role=$roles['id'];
        }
    }
    /**
     * getter for UserRole
     * returns UserRole for item
     * 
     * @return string $_role
     */
    public function getUserRole(){
        return $this->_role;
    }

}