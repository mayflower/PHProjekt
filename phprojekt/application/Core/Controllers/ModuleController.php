<?php
/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * Module Controller.
 */
class Core_ModuleController extends Core_IndexController
{
    /**
     * String to use on error when try to delete a system module.
     */
    const CAN_NOT_DELETE_SYSTEM_MODULE = "You can not delete system modules";

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
        $model   = new Phprojekt_Module_Module();
        foreach ($model->fetchAll('active = 1 AND (save_type = 1 OR save_type = 2)', 'name ASC') as $module) {
            $modules['data'][$module->id] = array();
            $modules['data'][$module->id]['id']    = $module->id;
            $modules['data'][$module->id]['name']  = $module->name;
            $modules['data'][$module->id]['label']  = $module->label;
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
     *  - id      => id of the deleted item.
     * </pre>
     *
     * @throws Zend_Controller_Action_Exception On missing or wrong id, or on error in the action delete.
     *
     * @return void
     */
    public function jsonDeleteAction()
    {
        $id = (int) $this->getRequest()->getParam('id');

        if (empty($id)) {
            throw new Zend_Controller_Action_Exception(self::ID_REQUIRED_TEXT, 400);
        }

        $model = $this->getModelObject()->find($id);

        if ($model instanceof Phprojekt_ActiveRecord_Abstract) {
            if (is_dir(PHPR_CORE_PATH . $model->name)) {
                throw new Zend_Controller_Action_Exception(self::CAN_NOT_DELETE_SYSTEM_MODULE, 422);
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
                            'id'      => $id);

            Phprojekt_Converter_Json::echoConvert($return);
        } else {
            throw new Zend_Controller_Action_Exception(self::NOT_FOUND, 404);
        }
    }
}
