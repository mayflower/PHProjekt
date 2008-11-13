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
        $this->name   = ucfirst($params['name']);
        $this->label  = $params['label'];
        $this->active = (int) $params['active'];

        if ($this->recordValidate()) {
            $saveNewModule = false;
            if ($this->id == 0) {
                $saveNewModule = true;
            }
            $this->save();
            
            // Reset cache for root project
            $projectModulePermissionsNamespace = new Zend_Session_Namespace('ProjectModulePermissions'.'-1');
            if (isset($projectModulePermissionsNamespace->modules)
                && !empty($projectModulePermissionsNamespace->modules)) {
                $projectModulePermissionsNamespace->modules = array();
            }

            // Add the new module to the root project
            if ($saveNewModule) {
                $project = Phprojekt_Loader::getModel('Project', 'Project')->find(1);
                $project->addModule($this->id);

                // Save Module into the role 1 with 255 access
                $role  = new Phprojekt_Role_RoleModulePermissions();
                $role->addModuleToAdminRole($this->id);

                // Copy Templates files
                $this->_copyTemplates(array('Template'));
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
     * Prevent delete modules from the Frontend
     * For delete modules use safeDelete
     *
     * @return void
     */
    public function delete()
    {
    }

    /**
     * Delete all the entries for the current module
     * This function is used by the system when a folder module is deleted
     *
     * @return void
     */
    public function safeDelete() {
        parent::delete();
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


    /**
     * Create a folder if this not exists in the system
     * The folder is created with 0770 permissions and then
     * change it to 0775
     *
     * @param array $pathToFolder Array with all the folder names
     *
     * @return void
     */
    private function _makeFolder($pathToFolder)
    {
        $folderPath = PHPR_CORE_PATH . DIRECTORY_SEPARATOR . $this->name;
        if (!is_dir($folderPath)) {
            if (mkdir($folderPath, 0770)) {
                chmod($folderPath, 0775);
            } else {
                Zend_Registry::get('log')->debug('Error on create folder ' . $path);
            }
        }

        foreach ($pathToFolder as $key => $path) {
            if ($key > 0) {
                $folderPath .= DIRECTORY_SEPARATOR . $path;
            }
        }
        if (mkdir($folderPath, 0770)) {
            chmod($folderPath, 0775);
        } else {
            Zend_Registry::get('log')->debug('Error on create folder ' . $folderPath);
        }
    }    

    /**
     * Read the Template folder and try to reproduce it
     * into the aplication folder
     *
     * All the strings ##TEMPLATE##
     * are reemplaced by the name of the module
     *
     * @param array $paths Array with all the folder names
     *
     * @return void
     */
    private function _copyTemplates($paths)
    {
        $templatePath = PHPR_LIBRARY_PATH;
        foreach ($paths as $path) {
            $templatePath .= DIRECTORY_SEPARATOR . $path;
        }
        $files = scandir($templatePath);
        foreach ($files as $file) {
            if ($file != '.'  &&
                $file != '..' &&
                $file != '.svn') {
                if (is_dir($templatePath . DIRECTORY_SEPARATOR .$file)) {
                    array_push($paths, $file);
                    $this->_makeFolder($paths);
                    $this->_copyTemplates($paths);
                    array_pop($paths);
                } else {
                    $templateContent = file_get_contents($templatePath . DIRECTORY_SEPARATOR .$file);
                    $templateContent = ereg_replace("##TEMPLATE##", $this->name, $templateContent);
                    if ($file == 'Template.php') {
                        $file = $this->name . '.php';
                    }

                    $modulePath = PHPR_CORE_PATH . DIRECTORY_SEPARATOR . $this->name;
                    foreach ($paths as $key => $path) {
                        if ($key > 0) {
                            $modulePath .= DIRECTORY_SEPARATOR . $path;
                        }
                    }
                    if ($newFile = fopen($modulePath . DIRECTORY_SEPARATOR .$file, 'w')) {
                        if (false === fwrite($newFile, $templateContent)) {
                            Zend_Registry::get('log')->debug('Error on copy file ' .
                                                             $modulePath .
                                                             DIRECTORY_SEPARATOR .
                                                             $file);
                        }
                        fclose($newFile);
                    }
                }
            }
        }
    }
}
