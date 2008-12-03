<?php
/**
 * Configuration Module Controller for PHProjekt 6.0
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
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    CVS: $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Configuration Module Controller for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Administration_IndexController extends IndexController
{
    /**
     * Return all the modules that contain admin configurations
     *
     * @return void
     */
    public function jsonGetModulesAction()
    {
        $configuration = Phprojekt_Loader::getModel('Administration', 'Configuration');
        $data          = $configuration->getModules();
        
        echo Phprojekt_Converter_Json::convert($data);
    }
    
    /**
     * Return the admin configurations fields for one module
     *
     * @return void
     */
    public function jsonDetailAction()
    {
        $module   = $this->getRequest()->getParam('moduleName', null);
        $moduleId = (int) Phprojekt_Module::getId($module);
        
        $configuration = Phprojekt_Loader::getModel('Administration', 'Configuration');
        $configuration->setModule($module);
        $metadata = $configuration->getModel()->getFieldDefinition();
        $records  = $configuration->getList($moduleId, $metadata);
        
        $data     = array("metadata" => $metadata,
                          "data"     => $records,
                          "numRows"  => count($records));
        
        echo Phprojekt_Converter_Json::convert($data);
    }
        
    /**
     * Saves the admin configurations
     *
     * @requestparam string moduleName ...
     *
     * @return void
     */
    public function jsonSaveAction()
    {
        $translate     = Zend_Registry::get('translate');
        $module        = $this->getRequest()->getParam('moduleName', null);        
        $configuration = Phprojekt_Loader::getModel('Administration', 'Configuration');
        $configuration->setModule($module);

        $message = $configuration->validateConfigurations($this->getRequest()->getParams());
        
        if (!empty($message)) {
            $message = $translate->translate($message);
            $type    = "error";
        } else {
            $message = $translate->translate(self::EDIT_TRUE_TEXT);
            $configuration->setConfigurations($this->getRequest()->getParams());
            $type = "success";
        }

        $return = array('type'    => $type,
                        'message' => $message,
                        'code'    => 0,
                        'id'      => 0);

        echo Phprojekt_Converter_Json::convert($return);
    }
}
