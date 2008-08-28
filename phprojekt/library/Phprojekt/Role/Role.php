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
class Phprojekt_Role_Role extends Phprojekt_ActiveRecord_Abstract implements Phprojekt_Model_Interface
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
        $this->_informationManager = new Phprojekt_Role_Information();
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
     * Get the rigths for other users
     *
     * @return array
     */
    public function getRights()
    {
        return array();
    }

    /**
     * Save the rights for each modules
     *
     * @param array $rights Array with the modules and the bitmask access
     *
     * @return void
     */
    public function saveRights($rights)
    {
        $role    = new Phprojekt_Role_RoleModulePermissions();
        $roleId  = $this->id;
        $role->saveRights($rights, $roleId);
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

    /**
     * Delete a role and all his relations. It prevents deletion of role 1 -admin role-
     *
     * @return void
     */
    public function delete() {
        if ((!empty($this->id)) && ($this->id != 1)) {
            parent::delete();
        }
    }
}
