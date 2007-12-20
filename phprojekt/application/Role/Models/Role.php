<?php
/**
 * Role class for PHProjekt 6.0
 *
 * @copyright 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license   http://www.phprojekt.com/license PHProjekt6 License
 * @version   CVS: $Id: User.php,v 1.6 2007/08/30 18:02:36 gustavo Exp $
 * @author    Eduardo Polidor <polidor@mayflower.de>
 * @package   PHProjekt
 * @subpackage Core
 * @link      http://www.phprojekt.com
 * @since     File available since Release 1.0
 */

/**
 * Phprojekt_Role for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license   http://www.phprojekt.com/license PHProjekt6 License
 * @author     Eduardo Polidor <polidor@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */
class Role_Models_Role extends Phprojekt_ActiveRecord_Abstract
{
    /**
     * Has many and belongs to many declrations
     *
     * @var array
     */
    public $hasManyAndBelongsToMany = array('users' => array('module' => 'Users',
                                                             'model'  => 'User'),
                                                             'projects'=> array('module' => 'Project',
                                                                                'model'  => 'Rproject'));

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
     * Constructor for Groups
     *
     * @param Zend_Db $db database
     */
    public function __construct($db = null)
    {
        parent::__construct($db);
    }

    /**
     * getter for UserRole
     * returns UserRole for item
     *
     * @param int $userId    user ID
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
                $db     = Zend_Registry::get('db');
                $select = $db->select()
                ->from(array('rel' => 'ProjectUserRoleRelation'))
                ->joinInner(array('role' => 'Role'), sprintf("%s = %s", $db->quoteIdentifier("role.id"), $db->quoteIdentifier("rel.roleId")))
                ->where($db->quoteInto('userId = ?', $userId));
                $stmt  = $db->query($select);
                $allroles = $stmt->fetchAll();
                foreach ($allroles as $roles) {
                    $role = $roles['id'];
                    $roleNamespace->$roles['projectId'] = $role;
                }
                if (!$roleNamespace-> $projectId) {
                    $projectObject = Phprojekt_Loader::getModel('Project', 'Project');
                    $parent = $projectObject->find($projectId);
                    if (null != $parent && $parent->parent > 0) {
                        $role = $this->fetchUserRole($userId, $parent->parent);
                        $roleNamespace-> $projectId = $role;
                    }
                }
            }
        }
        return $role;
    }
}