<?php
/**
 * Module class for PHProjekt 6.0
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
 * Phprojekt_Module for PHProjekt 6.0
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
class Phprojekt_Module_Module extends Phprojekt_ActiveRecord_Abstract implements Phprojekt_Model_Interface
{
    /**
     * Has many and belongs to many declrations
     *
     * @var array
     */
    public $hasManyAndBelongsToMany = array('tabs' =>  array('classname' => 'Phprojekt_Tab_Tab',
                                                             'module'    => 'Tab',
                                                             'model'     => 'Tab'));

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
     * Initialize new tab
     *
     * @param array $db Configuration for Zend_Db_Table
     *
     * @return void
     */
    public function __construct($db = null)
    {
        if (null === $db) {
            $db = Zend_Registry::get('db');
        }
        parent::__construct($db);

        $this->_validate           = new Phprojekt_Model_Validate();
        $this->_informationManager = new Phprojekt_Module_Information();
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
     * Help to save a model by setting the models properties.
     * Validation is based on the ModelInformation implementation
     *
     * @param Phprojekt_Model_Interface $model  The model
     * @param array                     $params The parameters used to feed the model
     *
     * @throws Exception
     *
     * @return boolean
     */
    public function saveModule(array $params)
    {
        $this->name   = $params['name'];
        $this->active = (int) $params['active'];

        if (empty($this->id)) {
           $this->internalName = $params['internalName'];
        }

        if ($this->recordValidate()) {
            $saveNewModule = false;
            if ($this->id == 0) {
                $saveNewModule = true;
            }
            $this->save();
            if (isset($params['tabs'])) {
                Phprojekt_Tabs::saveModuleTabRelation($params['tabs'], $this->id);
            }
            // Reset cache for root project
            $projectModulePermissionsNamespace = new Zend_Session_Namespace('ProjectModulePermissions'.'-1');
            if (isset($projectModulePermissionsNamespace->modules)
                && !empty($projectModulePermissionsNamespace->modules)) {
                $projectModulePermissionsNamespace->modules = array();
            }

            // Add the new module to the root project
            if ($saveNewModule) {
                $project = Phprojekt_Loader::getModel('Project', 'ProjectModulePermissions');
                $project->addModule($this->id, 1);
            }
            return $this->id;
        } else {
            $error = array_pop($this->getError());
            throw new Phprojekt_PublishedException($error['field'] . ' ' . $error['message']);
        }
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
     * Extencion of the Active Record deletion to prevent deletion of module 1 (project)
     *
     * @return void
     */
    public function delete()
    {
        if ($this->id != 1) {
            parent::delete();
        }
    }

    /**
     * Return the error data
     *
     * @return array
     */
    public function getError()
    {
        return (array) $this->_validate->error->getError();
    }

    /**
     * Save rights
     *
     * @return void
     */
    public function saveRights()
    {
    }
}
