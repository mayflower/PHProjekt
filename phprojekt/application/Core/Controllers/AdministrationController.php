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
 * Administration Module Controller.
 */
class Core_AdministrationController extends Core_IndexController
{
    /**
     * PreDispatch function.
     *
     * Only admin users can access to these actions,
     * if the user is not an admin, is redirected to the login form or throws an exception.
     *
     * @throws Zend_Controller_Action_Exception If the user is not an admin.
     *
     * @return void
     */
    public function preDispatch()
    {
        parent::preDispatch();

        if (!Phprojekt_Auth::isAdminUser()) {
            $this->getResponse()->setRawHeader('HTTP/1.1 401 Authorization Required');
            $this->getResponse()->sendHeaders();
            exit;
        }
    }

    /**
     * Returns all the modules that contain Configuration.php file.
     *
     * Returns a list of modules that have a Configuration class, with:
     * <pre>
     *  - name  => Name of the module.
     *  - label => Display for the module.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonGetModulesAction()
    {
        $configuration = new Phprojekt_Configuration();
        $data          = $configuration->getModules();

        Phprojekt_Converter_Json::echoConvert($data);
    }

    /**
     * Returns the configuration fields and data for one module.
     *
     * The return have:
     *  - The metadata of each field.
     *  - The data of the setting.
     *  - The number of rows.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - string <b>moduleName</b> Name of the module.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonDetailAction()
    {
        $module   = Cleaner::sanitize('alnum', $this->getRequest()->getParam('moduleName', null));
        $moduleId = (int) Phprojekt_Module::getId($module);

        $configuration = new Phprojekt_Configuration();
        $configuration->setModule($module);
        $metadata = $configuration->getModel()->getFieldDefinition(Phprojekt_ModelInformation_Default::ORDERING_FORM);
        $records  = $configuration->getList($moduleId, $metadata);

        $data = array("metadata" => $metadata,
                      "data"     => $records,
                      "numRows"  => count($records));

        Phprojekt_Converter_Json::echoConvert($data);
    }

    /**
     * Saves the configuration for one module.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - string <b>moduleName</b>              Name of the module.
     *  - mixed  <b>all other module fields</b> All the fields values to save.
     * </pre>
     *
     * The return is a string in JSON format with:
     * <pre>
     *  - type    => 'success' or 'error'.
     *  - message => Success or error message.
     *  - id      => 0.
     * </pre>
     *
     * @throws Zend_Controller_Action_Exception On error in the action save or wrong id.
     *
     * @return void
     */
    public function jsonSaveAction()
    {
        $module = Cleaner::sanitize('alnum', $this->getRequest()->getParam('moduleName', null));
        $this->setCurrentProjectId();

        $configuration = new Phprojekt_Configuration();
        $configuration->setModule($module);

        $message = $configuration->validateConfigurations($this->getRequest()->getParams());

        if (!empty($message)) {
            $type = "error";
        } else {
            $message = Phprojekt::getInstance()->translate(self::EDIT_TRUE_TEXT);
            $configuration->setConfigurations($this->getRequest()->getParams());
            $type = "success";
        }

        $return = array('type'    => $type,
                        'message' => $message,
                        'id'      => 0);

        Phprojekt_Converter_Json::echoConvert($return);
    }
}
