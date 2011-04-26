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
     * Returns list of modules that DO NOT depend on -Core-.
     *
     * The return have:
     *  - The metadata of each field.
     *  - The data of all the rows.
     *  - The number of rows.
     *
     * The function use Phprojekt_ModelInformation_Default::ORDERING_LIST for get and sort the fields.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - integer <b>id</b>     List only this id.
     *  - integer <b>nodeId</b> List all the items with projectId == nodeId.
     *  - integer <b>count</b>  Use for SQL LIMIT count.
     *  - integer <b>offset</b> Use for SQL LIMIT offset.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonListAction()
    {
        $count     = (int) $this->getRequest()->getParam('count', null);
        $offset    = (int) $this->getRequest()->getParam('start', null);
        $this->setCurrentProjectId();

        $where = sprintf('dependence = %s',
            Phprojekt::getInstance()->getDb()->quote(Phprojekt_Module::DEPENDENCE_APPLICATION));
        $where   = $this->getFilterWhere($where);
        $records = $this->getModelObject()->fetchAll($where, null, $count, $offset);

        Phprojekt_Converter_Json::echoConvert($records, Phprojekt_ModelInformation_Default::ORDERING_LIST);
    }

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

        $params = $this->setParams($this->getRequest()->getParams());
        Default_Helpers_Save::save($model, $params);

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
        $where   = 'active = 1 AND (save_type = 1 OR save_type = 2) AND dependence = ' .
            Phprojekt::getInstance()->getDb()->quote(Phprojekt_Module::DEPENDENCE_APPLICATION);
        foreach ($model->fetchAll($where, 'name ASC') as $module) {
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
            if (is_dir(PHPR_CORE_PATH . DIRECTORY_SEPARATOR . $model->name)) {
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

    /**
     * Sets some values depending on the parameters.
     * Each module can implement this function to change their values.
     *
     * The function needs at least one parameter
     * (The array of parameters itself for return it).
     *
     * @throws Phprojekt_PublishedException If the arguments are missing.
     *
     * @return array
     */
    public function setParams()
    {
        $args = func_get_args();
        if (1 > count($args)) {
            throw new InvalidArgumentException('Missing arguments in setParams function');
        }
        $params = $args[0];

        // Set the hidden name to name or label
        // use ucfirst and delete spaces
        $module = null;
        if (isset($params['name'])) {
            $module = Cleaner::sanitize('alnum', $params['name'], null);
        }
        if (empty($module)) {
            if (isset($params['label'])) {
                $module = Cleaner::sanitize('alnum', $params['label'], null);
            } else {
                $module = null;
            }
        }
        $params['name'] = ucfirst(str_replace(" ", "", $module));

        if (isset($params['active'])) {
            $params['active'] = (int) $params['active'];
        }

        if (isset($params['saveType'])) {
            $params['saveType'] = (int) $params['saveType'];
        }

        $params['version'] = Phprojekt::getInstance()->getVersion();

        return $params;
    }
}
