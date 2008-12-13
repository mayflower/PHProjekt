<?php
/**
 * Module class for PHProjekt 6.0
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id:$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Phprojekt_Module for PHProjekt 6.0
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
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
        // Delete all the project-module relations
        $project  = new Project_Models_ProjectModulePermissions();
        $project->deleteModuleRelation($this->id);

        // Delete all the role-module relations
        $role  = new Phprojekt_Role_RoleModulePermissions();
        $role->deleteModuleRelation($this->id);

        // Delete the items and tags
        $tag     = Phprojekt_Tags_Default::getInstance();
        $model   = Phprojekt_Loader::getModel($this->name, $this->name);
        $results = $model->fetchAll();
        foreach ($results as $record) {
            $tag->deleteTagsByItem($this->id, $record->id);
            $record->delete();
        }

        // Delete Files
        $this->_deleteFolder(PHPR_CORE_PATH . DIRECTORY_SEPARATOR . $this->name);

        // Delete module entry
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

    /**
     * Delete a folder with subfolders and files
     *
     * @param srting $path The full path of the folder
     *
     * @return boolean
     */
    private function _deleteFolder($path)
    {
        if (is_dir($path) && !is_link($path)) {
            if ($directory = opendir($path)) {
                while (($file = readdir($directory)) !== false) {
                    if ($file == '.' || $file == '..') {
                        continue;
                    }
                    if (!$this->_deleteFolder($path . DIRECTORY_SEPARATOR . $file)) {
                        Zend_Registry::get('log')->debug($path . DIRECTORY_SEPARATOR . $file .' could not be deleted');
                    }
                }
                closedir($directory);
            }
            return rmdir($path);
        }
        return unlink($path);
    }
}
