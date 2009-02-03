<?php
/**
 * Setting Module Controller for PHProjekt 6.0
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
 * @author     Eduardo Polidor <polidor@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Default Setting Module Controller for PHProjekt 6.0
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */
class Setting_IndexController extends IndexController
{
    /**
     * Return all the modules that contain settings
     *
     * @return void
     */
    public function jsonGetModulesAction()
    {
        $setting = Phprojekt_Loader::getModel('Setting', 'Setting');
        $data    = $setting->getModules();

        echo Phprojekt_Converter_Json::convert($data);
    }

    /**
     * Return the setting fields for one module
     *
     * @return void
     */
    public function jsonDetailAction()
    {
        $module   = Cleaner::sanitize('alnum', $this->getRequest()->getParam('moduleName', null));
        $moduleId = (int) Phprojekt_Module::getId($module);

        $setting = Phprojekt_Loader::getModel('Setting', 'Setting');
        $setting->setModule($module);
        $metadata = $setting->getModel()->getFieldDefinition();
        $records  = $setting->getList($moduleId, $metadata, Phprojekt_Auth::getUserId());

        $data = array("metadata" => $metadata,
                      "data"     => $records,
                      "numRows"  => count($records));

        echo Phprojekt_Converter_Json::convert($data);
    }

    /**
     * Saves the settings
     *
     * @requestparam string moduleName ...
     *
     * @return void
     */
    public function jsonSaveAction()
    {
        $module  = Cleaner::sanitize('alnum', $this->getRequest()->getParam('moduleName', null));
        $setting = Phprojekt_Loader::getModel('Setting', 'Setting');
        $setting->setModule($module);

        $message = $setting->validateSettings($this->getRequest()->getParams());

        if (!empty($message)) {
            $message = Phprojekt::getInstance()->translate($message);
            $type = "error";
        } else {
            $message = Phprojekt::getInstance()->translate(self::EDIT_TRUE_TEXT);
            $setting->setSettings($this->getRequest()->getParams());
            $type = "success";
        }

        $return = array('type'    => $type,
                        'message' => $message,
                        'code'    => 0,
                        'id'      => 0);

        echo Phprojekt_Converter_Json::convert($return);
    }
}
