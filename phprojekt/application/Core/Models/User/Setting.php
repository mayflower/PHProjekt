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
 * Settings on a per user base.
 */
class Core_Models_User_Setting extends Phprojekt_ModelInformation_Default
{
    /**
     * Range of dates language setting.
     *
     * @var array
     */
    private $_languageRange = array();

    /**
     * Range of available timezones.
     *
     * @var array
     */
    private $_timeZoneRange = array();

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->_languageRange = Phprojekt_LanguageAdapter::getLanguageList();
        $this->_timeZoneRange = Phprojekt_Converter_Time::getTimeZones();
    }

    /**
     * Sets a fields definitions for each field.
     *
     * @return void
     */
    public function setFields()
    {
        // password
        $this->fillField('password', 'Password', 'password', 0, 1, array(
            'length' => 50));

        // confirmValue
        $this->fillField('confirmValue', 'Confirm Password', 'password', 0, 2, array(
            'length' => 50));

        // oldValue
        $this->fillField('oldValue', 'Old Password', 'password', 0, 3, array(
            'length' => 50));

        // email
        $this->fillField('email', 'Email', 'text', 0, 4, array(
            'length' => 255));

        // language
        $range = array();
        foreach ($this->_languageRange as $key => $value) {
            $range[] = $this->getRangeValues($key, $value);
        }
        $this->fillField('language', 'Language', 'selectbox', 0, 5, array(
            'range'    => $range,
            'required' => true,
            'default'  => 'en'));

        // timeZone
        $range = array();
        foreach ($this->_timeZoneRange as $key => $value) {
            $range[] = $this->getRangeValues($key, $value);
        }
        $this->fillField('timeZone', 'Time zone', 'selectbox', 0, 6, array(
            'range'    => $range,
            'required' => true,
            'default'  => '000'));
        // Proxies
        Phprojekt::setCurrentProjectId(IndexController::INVISIBLE_ROOT);
        $user  = new Phprojekt_User_User();
        $range = $user->getAllowedUsers();
        // remove ourselves from the proxy list
        $i = 0;
        foreach ($range as $entry) {
            if ((int) $entry['id'] == Phprojekt_Auth::getUserId()) {
                array_splice($range, $i, 1);
                break;
            }
            $i++;
        }
        $this->fillField('proxies', 'Proxies', 'multiplefilteringselectbox', 0, 7, array(
            'range'    => $range,
            'required' => true));
    }

    /**
     * Getter for password field.
     *
     * @return string Empty string.
     */
    public function getPassword()
    {
        return '';
    }

    /* Return the value of the proxy setting for the effective user.
     *
     * @return Array list of proxies.
     */
    public function getProxiesValue()
    {
        $proxyTable = new Phprojekt_Auth_ProxyTable();
        return $proxyTable->getProxyIdsForUserId(Phprojekt_Auth::getUserId());
    }

    /**
     * Validate the settings.
     *
     * @param array $params Array with values to save.
     *
     * @return string|null Error message.
     */
    public function validateSettings($params)
    {
        $message = null;

        // Passwords
        $confirmPassValue = (isset($params['confirmValue'])) ? $params['confirmValue'] : null;
        $oldPassValue     = (isset($params['oldValue'])) ? $params['oldValue'] : null;
        $newPassValue     = $params['password'];
        $currentPassValue = Phprojekt_Auth::getRealUser()->getSetting('password');

        $isInSettings       = !array_key_exists('id', $params);
        $isNew              = !$isInSettings && $params['id'] == 0;
        $passwordGiven      = !empty($newPassValue);
        $passwordsMatch     = $newPassValue === $confirmPassValue;
        $currentPassCorrect = $currentPassValue == Phprojekt_Auth::cryptString($oldPassValue);

        if ($isNew && !$passwordGiven) {
            $message = Phprojekt::getInstance()->translate('Password') . ': '
                . Phprojekt::getInstance()->translate('Is a required field');
        } else if ($passwordGiven && !$passwordsMatch) {
            $message = Phprojekt::getInstance()->translate('The password and confirmation are different or one of them '
                . 'is empty');
        } else if ($isInSettings && $passwordGiven && !$currentPassCorrect) {
            $message = Phprojekt::getInstance()->translate('The old password provided is invalid');
        }

        // TimeZone
        if (!array_key_exists($params['timeZone'], $this->_timeZoneRange)) {
            $message = Phprojekt::getInstance()->translate('The Time zone value is out of range');
        }

        // Language
        if (!array_key_exists($params['language'], $this->_languageRange)) {
            $message = Phprojekt::getInstance()->translate('The Language value do not exists');
        }

        // Email
        if (!empty($params['email'])) {
            $validator = new Zend_Validate_EmailAddress();
            if (!$validator->isValid($params['email'])) {
                $message = Phprojekt::getInstance()->translate('Invalid email address');
            }
        }

        return $message;
    }

    /**
     * Save the settings into the table.
     *
     * @param array   $params $_POST fields.
     * @param integer $userId The user ID, if is not setted, the current user is used.
     *
     * @return void
     */
    public function setSettings($params, $userId = 0)
    {
        if (!$userId) {
            $userId = Phprojekt_Auth::getUserId();
        }
        if (empty($params['password'])) {
            $password = Phprojekt_Auth::getRealUser()->getSetting('password', $userId);
        } else {
            $password = Phprojekt_Auth::cryptString($params['password']);
        }

        $namespace = new Zend_Session_Namespace(Phprojekt_Setting::IDENTIFIER . $userId);
        $fields    = $this->getFieldDefinition(Phprojekt_ModelInformation_Default::ORDERING_FORM);
        foreach ($fields as $data) {
            foreach ($params as $key => $value) {
                if ($key == $data['key'] && $key != 'oldValue' && $key != 'confirmValue') {
                    if ($key == 'proxies') {
                        if (count($value) === 1 && $value[0] === "") {
                            $value = array();
                        }
                        $proxyTable = new Phprojekt_Auth_ProxyTable();
                        $proxyTable->setProxyIdsForUserId($value);
                    } else {
                        $setting = new Phprojekt_Setting();
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
                    }
                    break;
                }
            }
        }
    }
}
