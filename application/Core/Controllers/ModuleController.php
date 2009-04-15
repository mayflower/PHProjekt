<?php
/**
 * Module Controller for PHProjekt 6.0
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
 * @version    $Id$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Module Controller for PHProjekt 6.0
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Core_ModuleController extends Core_IndexController
{
    /**
     * Returns the detail for a model in JSON.
     *
     * For further information see the chapter json exchange
     * in the internals documentantion
     *
     * @requestparam integer id ...
     *
     * @return void
     */
    public function jsonDetailAction()
    {
        $id = (int) $this->getRequest()->getParam('id');

        if (empty($id)) {
            $record = $this->getModelObject();
        } else {
            $record = $this->getModelObject()->find($id);
        }

        Phprojekt_Converter_Json::echoConvert($record, Phprojekt_ModelInformation_Default::ORDERING_FORM);
    }

    /**
     * Saves the current item
     * Save if you are add one or edit one.
     * Use the model module for get the data
     *
     * If there is an error, the save will return a Phprojekt_PublishedException
     * If not, the return is a string with the same format than the Phprojekt_PublishedException
     * but with success type
     *
     * @requestparam integer id ...
     *
     * @return void
     */
    public function jsonSaveAction()
    {
        $id = (int) $this->getRequest()->getParam('id');

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
     * Return all global modules
     *
     * @return array
     */
    function jsonGetGlobalModulesAction()
    {
        $modules = array();
        $model   = Phprojekt_Loader::getLibraryClass('Phprojekt_Module_Module');
        foreach ($model->fetchAll(' active = 1 AND (save_type = 1 OR save_type = 2) ', ' name ASC ') as $module) {
            $modules['data'][$module->id] = array();
            $modules['data'][$module->id]['id']    = $module->id;
            $modules['data'][$module->id]['name']  = $module->name;
            $modules['data'][$module->id]['label'] = $module->name;
        }
        $modules['metadata'] = Phprojekt_Auth::isAdminUser();

        Phprojekt_Converter_Json::echoConvert($modules);
    }

   /**
     * Deletes the module entries, the module itself
     * the databasemanager entry and the table itself
     *
     * @requestparam integer id ...
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

        if ($model instanceof Phprojekt_Model_Interface) {
            $databaseModel   = Phprojekt_Loader::getModel($model->name, $model->name);
            $databaseManager = new Phprojekt_DatabaseManager($databaseModel);
            $tmpModule       = Default_Helpers_Delete::delete($model);
            $tmpDatabase     = $databaseManager->deleteModule();

            if ($tmpModule === false || $tmpDatabase === false) {
                $message = Phprojekt::getInstance()->translate('The module can not be deleted');
            } else {
                $message = Phprojekt::getInstance()->translate('The module was deleted correctly');
            }

            $return = array('type'    => 'success',
                            'message' => $message,
                            'code'    => 0,
                            'id'      => $id);

            Phprojekt_Converter_Json::echoConvert($return);
        } else {
            throw new Phprojekt_PublishedException(self::NOT_FOUND);
        }
    }
}
