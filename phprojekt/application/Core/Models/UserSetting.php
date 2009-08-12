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
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
 * Settings on a per user base
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Core_Models_UserSetting
{
    /**
     * Range of dates language setting
     *
     * @var array
     */
    private $_languageRange = array();

    /**
     * Range of available timezones
     *
     * @var array
     */
    private $_timeZoneRange = array("0" => 0, "1" => 1, "2" => 2, "3" => 3, "4" => 4, "5" => 5,
                                    "6" => 6, "7" => 7, "8" => 8, "9" => 9, "10" =>10, "11" => 11,
                                    "12" => 12, "-12" => -12, "-11" => -11, "-10" => -10, "-9" => -9,
                                    "-8" => -8, "-7" => -7, "-6" => -6, "-5" => -5, "-4" => -4,
                                    "-3" => -3, "-2" => -2, "-1" => -1);

    public function __construct()
    {
        $this->_languageRange = Phprojekt_LanguageAdapter::getLanguageList();
    }

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

        // password
        $data = array();
        $data['key']      = 'password';
        $data['label']    = Phprojekt::getInstance()->translate('Password');
        $data['type']     = 'password';
        $data['hint']     = Phprojekt::getInstance()->getTooltip('password');
        $data['order']    = 0;
        $data['position'] = 1;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
                                  'name' => '');
        $data['required'] = true;
        $data['readOnly'] = false;
        $data['tab']      = 1;
        $data['integer']  = false;
        $data['length']   = 50;

        $converted[] = $data;

        $data = array();
        $data['key']      = 'confirmValue';
        $data['label']    = Phprojekt::getInstance()->translate('Confirm Password');
        $data['type']     = 'password';
        $data['hint']     = Phprojekt::getInstance()->getTooltip('confirmValue');
        $data['order']    = 0;
        $data['position'] = 2;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
                                  'name' => '');
        $data['required'] = true;
        $data['readOnly'] = false;
        $data['tab']      = 1;
        $data['integer']  = false;
        $data['length']   = 50;

        $converted[] = $data;

        $data = array();
        $data['key']      = 'oldValue';
        $data['label']    = Phprojekt::getInstance()->translate('Old Password');
        $data['type']     = 'password';
        $data['hint']     = Phprojekt::getInstance()->getTooltip('oldValue');
        $data['order']    = 0;
        $data['position'] = 3;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
                                  'name' => '');
        $data['required'] = true;
        $data['readOnly'] = false;
        $data['tab']      = 1;
        $data['integer']  = false;
        $data['length']   = 50;

        $converted[] = $data;

        // email
        $data = array();
        $data['key']      = 'email';
        $data['label']    = Phprojekt::getInstance()->translate('Email');
        $data['type']     = 'text';
        $data['hint']     = Phprojekt::getInstance()->getTooltip('email');
        $data['order']    = 0;
        $data['position'] = 2;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
                                  'name' => '');
        $data['required'] = true;
        $data['readOnly'] = false;
        $data['tab']      = 1;
        $data['integer']  = false;
        $data['length']   = 255;

        $converted[] = $data;

        // language
        $data = array();
        $data['key']      = 'language';
        $data['label']    = Phprojekt::getInstance()->translate('Language');
        $data['type']     = 'selectbox';
        $data['hint']     = Phprojekt::getInstance()->getTooltip('language');
        $data['order']    = 0;
        $data['position'] = 3;
        $data['fieldset'] = '';
        foreach ($this->_languageRange as $key => $value) {
            $data['range'][] = array('id'   => $key,
                                     'name' => $value);
        }
        $data['required'] = true;
        $data['readOnly'] = false;
        $data['tab']      = 1;
        $data['integer']  = false;
        $data['length']   = 0;

        $converted[] = $data;

        // timeZone
        $data = array();
        $data['key']      = 'timeZone';
        $data['label']    = Phprojekt::getInstance()->translate('Time Zone');
        $data['type']     = 'selectbox';
        $data['hint']     = Phprojekt::getInstance()->getTooltip('timeZone');
        $data['order']    = 0;
        $data['position'] = 2;
        $data['fieldset'] = '';
        foreach ($this->_timeZoneRange as $key => $value) {
            $data['range'][] = array('id'   => $key,
                                     'name' => $value);
        }
        $data['required'] = true;
        $data['readOnly'] = false;
        $data['tab']      = 1;
        $data['integer']  = false;
        $data['length']   = 0;

        $converted[] = $data;

        return $converted;
    }

    public function getPassword()
    {
        return '';
    }

    public function validateSettings($params)
    {
        $message = null;
        $setting = Phprojekt_Loader::getModel('Setting', 'Setting');
        $setting->setModule('User');

        // Passwords
        $confirmPassValue = $params['confirmValue'];
        $oldPassValue     = $params['oldValue'];
        $newPassValue     = $params['password'];
        $currentPassValue = $setting->getSetting('password');

        if ((!empty($newPassValue) && $newPassValue != $confirmPassValue)
            || (empty($newPassValue) && !empty($confirmPassValue))) {
            $message = Phprojekt::getInstance()->translate("The password and confirmation are different or one of them "
                . "is empty");
        } else if (!empty($newPassValue) && $currentPassValue != Phprojekt_Auth::cryptString($oldPassValue)) {
            $message = Phprojekt::getInstance()->translate("The old password provided is invalid");
        }

        // TimeZone
        if (!in_array($params['timeZone'], $this->_timeZoneRange)) {
            $message = Phprojekt::getInstance()->translate("The Time Zone value is out of range");
        }

        // Language
        if (!array_key_exists($params['language'], $this->_languageRange)) {
            $message = Phprojekt::getInstance()->translate("The Language value do not exists");
        }

        return $message;
    }

    /**
     * Save the settings into the table
     *
     * @param array   $params $_POST fields
     * @param integer $userId The user id, if is not setted, the current user is used.
     *
     * @return void
     */
    public function setSettings($params, $userId = 0)
    {
        if (!$userId) {
            $userId = Phprojekt_Auth::getUserId();
        }
        $setting = Phprojekt_Loader::getModel('Setting', 'Setting');
        $setting->setModule('User');
        if (empty($params['password'])) {
            $password = $setting->getSetting('password', $userId);
        } else {
            $password = Phprojekt_Auth::cryptString($params['password']);
        }

        $namespace = new Zend_Session_Namespace(Setting_Models_Setting::NAMESPACE . $userId);
        $fields    = $this->getFieldDefinition();
        foreach ($fields as $data) {
            foreach ($params as $key => $value) {
                if ($key == $data['key'] && $key != 'oldValue' && $key != 'confirmValue') {
                    $setting = Phprojekt_Loader::getModel('Setting', 'Setting');
                    $setting->setModule('User');

                    if (($key == 'password')) {
                        $value = $password;
                    }

                    $where = sprintf('user_id = %d AND key_value = %s AND module_id = %d', (int) $userId,
                        $setting->_db->quote($key), 0);
                    $record = $setting->fetchAll($where);

                    if (isset($record[0])) {
                        $record[0]->keyValue = $key;
                        $record[0]->value    = $value;
                        $record[0]->save();
                    } else {
                        $setting->userId     = $userId;
                        $setting->moduleId   = 0;
                        $setting->keyValue   = $key;
                        $setting->value      = $value;
                        $setting->identifier = 'Core';
                        $setting->save();
                    }
                    $namespace->$key = $value;
                    break;
                }
            }
        }
    }
}
