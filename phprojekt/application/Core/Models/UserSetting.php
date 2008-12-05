<?php
/**
 * User setting model
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
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
 * Settings on a per user base
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Core_Models_UserSetting
{
    /**
     * Range of dates language setting
     *
     * @var array
     */
    private $_languageRange = array('de' => 'German', 'en' => 'English', 'es' => 'Spanish');

    /**
     * Range of available timezones
     *
     * @var array
     */
    private $_timeZoneRange = array(-12, -11, -10, -9, -8, -7, -6, -5, -4, -3, -2, -1, 0,
                                    1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12);
    
    /**
     * Return an array of field information.
     *
     * @param integer $ordering An ordering constant
     *
     * @return array
     */
    public function getFieldDefinition()
    {
        $converted = array();
        $translate = Zend_Registry::get('translate');

        // password
        $data = array();
        $data['key']      = 'password';
        $data['label']    = $translate->translate('Password');
        $data['type']     = 'password';
        $data['hint']     = $translate->translate('Password');
        $data['order']    = 0;
        $data['position'] = 1;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
                                  'name' => '');
        $data['required'] = true;
        $data['readOnly'] = false;
        $data['tab']      = 1;
                
        $converted[] = $data;
        
        $data = array();
        $data['key']      = 'confirmValue';
        $data['label']    = $translate->translate('Confirm Password');
        $data['type']     = 'password';
        $data['hint']     = $translate->translate('Confirm Password');
        $data['order']    = 0;
        $data['position'] = 2;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
                                  'name' => '');
        $data['required'] = true;
        $data['readOnly'] = false;
        $data['tab']      = 1;
        
        $converted[] = $data;
                    
        $data = array();
        $data['key']      = 'oldValue';
        $data['label']    = $translate->translate('Old Password');
        $data['type']     = 'password';
        $data['hint']     = $translate->translate('Old Password');
        $data['order']    = 0;
        $data['position'] = 3;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
                                  'name' => '');
        $data['required'] = true;
        $data['readOnly'] = false;
        $data['tab']      = 1;
                
        $converted[] = $data;      
                    
        // email
        $data = array();
        $data['key']      = 'email';
        $data['label']    = $translate->translate('Email');
        $data['type']     = 'text';
        $data['hint']     = $translate->translate('Email');
        $data['order']    = 0;
        $data['position'] = 2;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
                                  'name' => '');
        $data['required'] = true;
        $data['readOnly'] = false;
        $data['tab']      = 1;
                
        $converted[] = $data;
        
        // language
        $data = array();
        $data['key']      = 'language';
        $data['label']    = $translate->translate('Language');
        $data['type']     = 'selectbox';
        $data['hint']     = $translate->translate('Language');
        $data['order']    = 0;
        $data['position'] = 3;
        $data['fieldset'] = '';
        $values = $this->_languageRange;
        foreach ($values as $key => $value) {
            $data['range'][] = array('id'   => $key,
                                     'name' => $value);
        }
        $data['required'] = true;
        $data['readOnly'] = false;
        $data['tab']      = 1;
                
        $converted[] = $data;

        // timeZone
        $data = array();
        $data['key']      = 'timeZone';
        $data['label']    = $translate->translate('Time Zone');
        $data['type']     = 'selectbox';
        $data['hint']     = $translate->translate('Time Zone');
        $data['order']    = 0;
        $data['position'] = 2;
        $data['fieldset'] = '';
        $values = $this->_timeZoneRange;
        foreach ($values as $key => $value) {
            $data['range'][] = array('id'   => $key,
                                     'name' => $value);
        }
        $data['required'] = true;
        $data['readOnly'] = false;
        $data['tab']      = 1;
        
        $converted[] = $data;

        return $converted;
    }

    public function getPassword()
    {
        return '';
    }
    
    public function validateSettings($params)
    {
        $translate = Zend_Registry::get('translate');
        $message   = null;
        $setting   = Phprojekt_Loader::getModel('Setting', 'Setting');
        $setting->setModule('User');        
        
        // Passwords
        $confirmPassValue = $params['confirmValue'];
        $oldPassValue     = $params['oldValue'];
        $newPassValue     = $params['password'];
        $currentPassValue = $setting->getSetting('password');
                        
        if (!empty($newPassValue) && $newPassValue != $confirmPassValue) {
            $message = $translate->translate("The password and confirmation are different or empty");
        } else if (!empty($newPassValue) && $currentPassValue != Phprojekt_Auth::cryptString($oldPassValue)) {
            $message = $translate->translate("The old password provided is invalid");
        }
        
        // TimeZone
        if (!in_array($params['timeZone'], $this->_timeZoneRange)) {
            $message = $translate->translate("The Time Zone value is out of range");
        }
        
        // Language
        if (!array_key_exists($params['language'], $this->_languageRange)) {
            $message = $translate->translate("The Language value do not exists");
        }
        return $message;
    }
    
    public function setSettings($params)
    {    
        $setting = Phprojekt_Loader::getModel('Setting', 'Setting');
        $setting->setModule('User');
        if (empty($params['password'])) {
            $password = $setting->getSetting('password');
        } else {
            $password = Phprojekt_Auth::cryptString($params['password']);
        }     
        $fields = $this->getFieldDefinition();          
        foreach ($fields as $data) {
            foreach ($params as $key => $value) {
                if ($key == $data['key'] && $key != 'oldValue' && $key != 'confirmValue') {
                    $setting = Phprojekt_Loader::getModel('Setting', 'Setting');
                    $setting->setModule('User');                       
                    if (($key == 'password')) {
                        $value = $password;
                    }                    
                    $record = $setting->fetchAll("userId = ". Phprojekt_Auth::getUserId() .
                                                 " AND keyValue = ". $setting->_db->quote($key) .
                                                 " AND moduleId = 0");                        
                    if (isset($record[0])) {
                        $record[0]->keyValue = $key;
                        $record[0]->value    = $value;
                        $record[0]->save();                        
                    } else {                         
                        $setting->userId     = Phprojekt_Auth::getUserId();
                        $setting->moduleId   = 0;
                        $setting->keyValue   = $key;
                        $setting->value      = $value;
                        $setting->identifier = 'Core';
                        $setting->save();
                    }
                }
            }
        }
    }
}
