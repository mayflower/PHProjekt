<?php
/**
 * Module Controller.
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Core
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
 * Module Controller.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Core
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Core_ModuleController extends Core_IndexController
{
    /**
     * String to use on error when try to delete a system module.
     */
    const CAN_NOT_DELETE_SYSTEM_MODULE = "You can not delete system modules";

    /**
     * Saves a module.
     *
     * If the request parameter "id" is null or 0, the function will add a new module,
     * if the "id" is an existing module, the function will update it.
     *
     * The save action will try also to copy files into the application folder
     * if the module is a new one.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - integer <b>id</b>                      id of the module to save.
     *  - string  <b>name</b>                    Name of the module.
     *  - string  <b>label</b>                   Display of the module.
     *  - mixed   <b>all other module fields</b> All the fields values to save.
     * </pre>
     *
     * If there is an error, the save will return a Phprojekt_PublishedException,
     * if not, it returns a string in JSON format with:
     * <pre>
     *  - type    => 'success'.
     *  - message => Success message.
     *  - code    => 0.
     *  - id      => Id of the module.
     * </pre>
     *
     * @throws Phprojekt_PublishedException On error in the action save.
     *
     * @return void
     */
    public function jsonSaveAction()
    {
        $id = (int) $this->getRequest()->getParam('id');
        $this->setCurrentProjectId();

        if (empty($id)) {
            $model   = $this->getModelObject();
            $message = Phprojekt::getInstance()->translate('The module was added correctly');
        } else {
            $model   = $this->getModelObject()->find($id);
            $message = Phprojekt::getInstance()->translate('The module was edited correctly');
        }

        // Set the hidden name to name or label
        // use ucfirst and delete spaces
        $module = Cleaner::sanitize('alnum', $this->getRequest()->getParam('name', null));
        if (empty($module)) {
            $module = Cleaner::sanitize('alnum', $this->getRequest()->getParam('label', null));
        }
        $module = ucfirst(str_replace(" ", "", $module));
        $this->getRequest()->setParam('name', $module);

        $model->saveModule($this->getRequest()->getParams());

        $return = array('type'    => 'success',
                        'message' => $message,
                        'code'    => 0,
                        'id'      => $model->id);

        Phprojekt_Converter_Json::echoConvert($return);
    }

    /**
     * Returns all global modules.
     *
     * Returns a list of all the global modules with:
     * <pre>
     *  - id     => id of the module.
     *  - name   => Name of the module.
     *  - label  => Display for the module.
     * </pre>
     * Also return in the metadata, if the user is an admin or not.
     *
     * The return is in JSON format.
     *
     * @return array
     */
    function jsonGetGlobalModulesAction()
    {
        $modules = array();
        $model   = Phprojekt_Loader::getLibraryClass('Phprojekt_Module_Module');
        foreach ($model->fetchAll('active = 1 AND (save_type = 1 OR save_type = 2)', 'name ASC') as $module) {
            $modules['data'][$module->id] = array();
            $modules['data'][$module->id]['id']    = $module->id;
            $modules['data'][$module->id]['name']  = $module->name;
            $modules['data'][$module->id]['label'] = Phprojekt::getInstance()->translate($module->label, null,
                $module->name);
        }
        $modules['metadata'] = Phprojekt_Auth::isAdminUser();

        Phprojekt_Converter_Json::echoConvert($modules);
    }

    /**
     * Deletes a module.
     *
     * Deletes the module entries, the module itself,
     * the databasemanager entry and the table itself.
     *
     * REQUIRES request parameters:
     * <pre>
     *  - integer <b>id</b> id of the item to delete.
     * </pre>
     *
     * The return is a string in JSON format with:
     * <pre>
     *  - type    => 'success'.
     *  - message => Success message.
     *  - code    => 0.
     *  - id      => id of the deleted item.
     * </pre>
     *
     * @throws Phprojekt_PublishedException On missing or wrong id, or on error in the action delete.
     *
     * @return void
     */
    public function jsonDeleteAction()
    {
        $id = (int) $this->getRequest()->getParam('id');

        if (empty($id)) {
            throw new Phprojekt_PublishedException(self::ID_REQUIRED_TEXT);
        }

        $model = $this->getModelObject()->find($id);

        if ($model instanceof Phprojekt_ActiveRecord_Abstract) {
            if (is_dir(PHPR_CORE_PATH . $model->name)) {
                throw new Phprojekt_PublishedException(self::CAN_NOT_DELETE_SYSTEM_MODULE);
            }

            $databaseModel = Phprojekt_Loader::getModel($model->name, $model->name);
            if ($databaseModel instanceof Phprojekt_Item_Abstract) {
                $databaseManager = new Phprojekt_DatabaseManager($databaseModel);

                if (Default_Helpers_Delete::delete($model)) {
                    $return = $databaseManager->deleteModule();
                } else {
                    $return = false;
                }
            } else {
                $return = Default_Helpers_Delete::delete($model);
            }

            if ($return === false) {
                $message = Phprojekt::getInstance()->translate('The module can not be deleted');
                $type    = 'error';
            } else {
                Phprojekt::removeControllersFolders();
                $message = Phprojekt::getInstance()->translate('The module was deleted correctly');
                $type    = 'success';
            }

            $return = array('type'    => $type,
                            'message' => $message,
                            'code'    => 0,
                            'id'      => $id);

            Phprojekt_Converter_Json::echoConvert($return);
        } else {
            throw new Phprojekt_PublishedException(self::NOT_FOUND);
        }
    }
}
