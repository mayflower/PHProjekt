<?php
/**
 * Setting Module Controller for PHProjekt 6.0
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @author     Eduardo Polidor <polidor@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Default Setting Module Controller for PHProjekt 6.0
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
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
        $module   = $this->getRequest()->getParam('moduleName', null);
        $moduleId = (int) Phprojekt_Module::getId($module);
        
        $setting = Phprojekt_Loader::getModel('Setting', 'Setting');
        $setting->setModule($module);
        $metadata = $setting->getModel()->getFieldDefinition();
        $records  = $setting->getList($moduleId, $metadata);
        
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
        $translate = Zend_Registry::get('translate');
        $module    = $this->getRequest()->getParam('moduleName', null);        
        $setting   = Phprojekt_Loader::getModel('Setting', 'Setting');
        $setting->setModule($module);

        $message = $setting->validateSettings($this->getRequest()->getParams());
        
        if (!empty($message)) {
            $message = $translate->translate($message);
            $type = "error";
        } else {
            $message = $translate->translate(self::EDIT_TRUE_TEXT);
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