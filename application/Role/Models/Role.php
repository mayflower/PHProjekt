<?php
/**
 * Role class for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://www.phprojekt.com/license PHProjekt6 License
 * @version    CVS: $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Phprojekt_Role for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://www.phprojekt.com/license PHProjekt6 License
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */
class Role_Models_Role extends Phprojekt_ActiveRecord_Abstract implements Phprojekt_Model_Interface
{
    /**
     * Has many and belongs to many declrations
     *
     * @var array
     */
    public $hasManyAndBelongsToMany = array('users' => array('module' => 'User',
                                                             'model'  => 'User'),
                                            'projects'=> array('module' => 'Project',
                                                               'model'  => 'Project'));

    /**
     * Id of user
     * @var int $user
     */
    protected $_user = 0;

    /**
     * Keep the found project roles in cache
     *
     * @var array
     */
    private $_projectRoles = array();

    /**
     * The standard information manager with hardcoded
     * field definitions
     *
     * @var Phprojekt_ModelInformation_Interface
     */
    protected $_informationManager;

    /**
     * Validate object
     *
     * @var Phprojekt_Model_Validate
     */
    protected $_validate = null;

    /**
     * Constructor for Groups
     *
     * @param Zend_Db $db database
     */
    public function __construct($db = null)
    {
        parent::__construct($db);

        $this->_validate           = new Phprojekt_Model_Validate();
        $this->_informationManager = new Role_Models_Information();
    }

    /**
     * getter for UserRole
     * Returns UserRole for item
     *
     * @param int $userId user ID
     * @param int $projectId project ID
     *
     * @return string $_role current role
     */
    public function fetchUserRole($userId, $projectId)
    {
        $role = 0;
        // Keep the roles in the session for optimize the query
        if (isset($userId) && isset($projectId)) {
            $roleNamespace = new Zend_Session_Namespace('UserRole_'.$userId);
            if (isset($roleNamespace->$projectId) && !empty($roleNamespace->$projectId)) {
                $role = $roleNamespace->$projectId;
            } else {
                $db = Zend_Registry::get('db');
                $select = $db->select()
                             ->from(array('rel' => 'ProjectUserRoleRelation'))
                             ->joinInner(array('role' => 'Role'), 
                             sprintf("%s = %s", $db->quoteIdentifier("role.id"), $db->quoteIdentifier("rel.roleId")))
                             ->where($db->quoteInto('userId = ?', $userId));
                $stmt  = $db->query($select);
                $allroles = $stmt->fetchAll();
                foreach ($allroles as $roles) {
                    $role = $roles['id'];
                    $roleNamespace->$roles['projectId'] = $role;
                }
                if (!$roleNamespace->$projectId) {
                    $projectObject = Phprojekt_Loader::getModel('Project', 'Project');
                    $parent        = $projectObject->find($projectId);
                    if (null != $parent && $parent->projectId > 0) {
                        $role = $this->fetchUserRole($userId, $parent->projectId);
                        $roleNamespace->$projectId = $role;
                    }
                }
            }
        }
        return $role;
    }

    /**
     * Get the information manager
     *
     * @see Phprojekt_Model_Interface::getInformation()
     *
     * @return Phprojekt_ModelInformation_Interface
     */
    public function getInformation()
    {
        return $this->_informationManager;
    }

    /**
     * Get the rigths
     *
     * @param integer $userid use from whom right is needed
     *
     * @return string
     */
    public function getRights($userId)
    {
        return 15;
    }

    /**
     * Get the rigths for other users
     *
     * @return array
     */
    public function getAccessRights()
    {
        return array();
    }

    /**
     * Validate the current record
     *
     * @return boolean
     */
    public function recordValidate()
    {
        $data      = $this->_data;
        $fields    = $this->_informationManager->getFieldDefinition(Phprojekt_ModelInformation_Default::ORDERING_FORM);

        $this->_validate = new Phprojekt_Model_Validate();
        return $this->_validate->recordValidate($this, $data, $fields);
    }

    /**
     * Return the error data
     *
     * @return array
     */
    public function getError()
    {
        return (array) $this->_validate->_error->getError();
    }
}
