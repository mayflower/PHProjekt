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
 * Setting Module Controller.
 */
class Core_SettingController extends Core_IndexController
{
    /**
     * Returns all the modules that contain settings.
     *
     * Returns a list of modules that have a Setting class, with:
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
        $setting = new Phprojekt_Setting();
        $data    = $setting->getModules();

        Phprojekt_Converter_Json::echoConvert($data);
    }

    /**
     * Returns the setting fields and data for one module.
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

        $setting = new Phprojekt_Setting();
        $setting->setModule($module);
        $metadata = $setting->getModel()->getFieldDefinition(Phprojekt_ModelInformation_Default::ORDERING_FORM);
        $records  = $setting->getList($moduleId, $metadata);

        $data = array("metadata" => $metadata,
                      "data"     => $records,
                      "numRows"  => count($records));

        Phprojekt_Converter_Json::echoConvert($data);
    }

    /**
     * Saves the settings for one module.
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

        $setting = new Phprojekt_Setting();
        $setting->setModule($module);

        $message = $setting->validateSettings($this->getRequest()->getParams());

        if (!empty($message)) {
            $type = "error";
        } else {
            $message = Phprojekt::getInstance()->translate(self::EDIT_TRUE_TEXT);
            $setting->setSettings($this->getRequest()->getParams());
            $type = "success";
        }

        $return = array('type'    => $type,
                        'message' => $message,
                        'id'      => 0);

        Phprojekt_Converter_Json::echoConvert($return);
    }
}
