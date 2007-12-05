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
    public $hasManyAndBelongsToMany = array('users' =>
    array('module' => 'Users',
    'model'  => 'User'),
    'projects'=>
    array('module' => 'Project',
    'model'  => 'Rproject'));

    /**
     * Id of user
     * @var int $user
     */
    protected $_user = 0;


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
     * @param int $user    user ID
     * @param int $project project ID
     * 
     * @return string $_role current role
     */
    public function fetchUserRole($user,$project){
        $db = Zend_Registry::get('db');
        $select = $db->select()->from(array('rel' => 'ProjectUserRoleRelation'));
        $select->joinInner(array('role' => 'Role'),
        sprintf("%s = %s",
        $db->quoteIdentifier("role.id"),
        $db->quoteIdentifier("rel.roleId")));
        $select->where($db->quoteInto('userId = ?', $user));
        $select->where($db->quoteInto('projectId = ?', $project));
        $stmt = $db->query($select);
        $roles = $stmt->fetchObject();
        if (!$roles->id) {
            $projectdata = Phprojekt_Loader::getModel('Project', 'Project');
            $projects = $projectdata->fetchAll();
            $parent = $projectdata->find($project);
            if ($parent->parent > 0) {
                $role = $this->fetchUserRole($user, $parent->parent);
            }
        } else {
            $role=$roles->id;
        }
        return $role;
    }


}