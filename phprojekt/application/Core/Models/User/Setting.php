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
class Core_Models_User_Setting
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
    private $_timeZoneRange = array(
        "-12" => "(GMT -12:00) International Date Line West",
        "-11" => "(GMT -11:00) Midway Island, Samoa",
        "-10" => "(GMT -10:00) Hawaii",
        "-9" => "(GMT -9:00) Alaska",
        "-8" => "(GMT -8:00) Pacific Time (US & Canada)",
        "-08" => "(GMT -8:00) Tijuana, Baja California",
        "-7" => "(GMT -7:00) Arizona",
        "-07" => "(GMT -7:00) Chihuahua, La Paz, Mazatlan",
        "-007" => "(GMT -7:00) Mountain Time (US & Canada)",
        "-6" => "(GMT -6:00) Central America",
        "-06" => "(GMT -6:00) Central Time (US & Canada)",
        "-006" => "(GMT -6:00) Gudalajara, Mexico City, Monterrey",
        "-0006" => "(GMT -6:00) Saskatchewan",
        "-5" => "(GMT -5:00) Bogota, Lima, Quito",
        "-05" => "(GMT -5:00) Eastern Time (US & Canada)",
        "-005" => "(GMT -5:00) Indiana (East)",
        "-4_-30" => "(GMT -4:30) Caracas",
        "-4" => "(GMT -4:00) Asuncion",
        "-04" => "(GMT -4:00) Atlantic Time (Canada)",
        "-004" => "(GMT -4:00) Manaus",
        "-0004" => "(GMT -4:00) Santiago",
        "-3_-30" => "(GMT -3:30) Newfoundland",
        "-3" => "(GMT -3:00) Brasilia",
        "-03" => "(GMT -3:00) Buenos Aires",
        "-003" => "(GMT -3:00) Cayenne",
        "-0003" => "(GMT -3:00) Greenland",
        "-00003" => "(GMT -3:00) Montevideo",
        "-2" => "(GMT -2:00) Mid-Atlantic",
        "-1" => "(GMT -1:00) Azores",
        "-01" => "(GMT -1:00) Cape Verde Islands",
        "00" => "(GMT) Casablanca",
        "000" => "(GMT) Coordinated Universal Time",
        "0000" => "(GMT) Greenwich Mean Time: Dublin, Edinburgh, Lisbon, London",
        "00000" => "(GMT) Monrovia, Reykjavik",
        "1" => "(GMT +1:00) Amsterdam, Berlin, Bern, Rome, Stockholm, Vienna",
        "01" => "(GMT +1:00) Belgrade, Bratislava, Budapest, Ljubljana, Prague",
        "001" => "(GMT +1:00) Brussels, Copenhagen, Madrid, Paris",
        "0001" => "(GMT +1:00) Sarajevo, Skopje, Warsaw, Zagreb",
        "2" => "(GMT +2:00) West Central Africa",
        "02" => "(GMT +2:00) Amman",
        "002" => "(GMT +2:00) Athens, Bucharest, Istambul",
        "0002" => "(GMT +2:00) Beirut",
        "00002" => "(GMT +2:00) Cairo",
        "000002" => "(GMT +2:00) Harare, Pretonia",
        "0000002" => "(GMT +2:00) Helsinki, Kyiv, Riga, Sofia, Tallinn, Vilnius",
        "00000002" => "(GMT +2:00) Jerusalem",
        "000000002" => "(GMT +2:00) Minsk",
        "0000000002" => "(GMT +2:00) Windhoek",
        "3" => "(GMT +3:00) Baghdad",
        "03" => "(GMT +3:00) Kuwait, Riyadh",
        "003" => "(GMT +3:00) Moscow, St. Petersburg, Volgograd",
        "0003" => "(GMT +3:00) Nairobi",
        "00003" => "(GMT +3:00) Tibilisi",
        "3_30" => "(GMT +3:30) Tehran",
        "4" => "(GMT +4:00) Abu Dhabi, Muscat",
        "04" => "(GMT +4:00) Baku",
        "004" => "(GMT +4:00) Caucasus Standar Time",
        "0004" => "(GMT +4:00) Port Louis",
        "00004" => "(GMT +4:00) Yerevan",
        "4_30" => "(GMT +4:30) Kabul",
        "5" => "(GMT +5:00) Ekaterinburg",
        "05" => "(GMT +5:00) Islamabad, Karachi",
        "005" => "(GMT +5:00) Tashkent",
        "5_30" => "(GMT +5:30) Chennai, Kolkata, Mumbai, New Delhi",
        "5_030" => "(GMT +5:30) Sri Jayawardenepura",
        "5_45" => "(GMT +5:45) Kathmandu",
        "6" => "(GMT +6:00) Almaty, Novosibirsk",
        "06" => "(GMT +6:00) Astana, Dhaka",
        "6_30" => "(GMT +6:30) Yangoon (Rangoon)",
        "7" => "(GMT +7:00) Bangkok, Hanoi, Jakarta",
        "07" => "(GMT +7:00) Krasnoyarsk",
        "8" => "(GMT +8:00) Beijing, Chongging, Hong Kong, Urumqi",
        "08" => "(GMT +8:00) Irkutsk, Ulaan Bataar",
        "008" => "(GMT +8:00) Kuala Lumpur, Singapore",
        "0008" => "(GMT +8:00) Perth",
        "00008" => "(GMT +8:00) Taiperi",
        "9" => "(GMT +9:00) Osaka, Sapporo, Tokyo",
        "09" => "(GMT +9:00) Seoul",
        "009" => "(GMT +9:00) Yakutsk",
        "9_30" => "(GMT +9:30) Adelaide",
        "9_030" => "(GMT +9:30) Darwin",
        "10" => "(GMT +10:00) Brisbane",
        "010" => "(GMT +10:00) Canberra, Melbourne, Sydney",
        "0010" => "(GMT +10:00) Guam, Port Moresby",
        "00010" => "(GMT +10:00) Hobart",
        "000010" => "(GMT +10:00) Vladivostok",
        "11" => "(GMT +11:00) Magadan, Solomon Islands, New Caledonia",
        "12" => "(GMT +12:00) Auckland, Wellington",
        "012" => "(GMT +12:00) Fiji, Marshall Island",
        "0012" => "(GMT +12:00) Petropavlovsk-Kamchatsky");

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
        $data['required'] = false;
        $data['readOnly'] = false;
        $data['tab']      = 1;
        $data['integer']  = false;
        $data['length']   = 50;
        $data['default']  = null;

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
        $data['required'] = false;
        $data['readOnly'] = false;
        $data['tab']      = 1;
        $data['integer']  = false;
        $data['length']   = 50;
        $data['default']  = null;

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
        $data['required'] = false;
        $data['readOnly'] = false;
        $data['tab']      = 1;
        $data['integer']  = false;
        $data['length']   = 50;
        $data['default']  = null;

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
        $data['required'] = false;
        $data['readOnly'] = false;
        $data['tab']      = 1;
        $data['integer']  = false;
        $data['length']   = 255;
        $data['default']  = null;

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
        $data['default']  = 'en';

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
        $data['default']  = 0;

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
        $setting = Phprojekt_Loader::getLibraryClass('Phprojekt_Setting');
        $setting->setModule('User');

        // Passwords
        $confirmPassValue = (isset($params['confirmValue'])) ? $params['confirmValue'] : null;
        $oldPassValue     = (isset($params['oldValue'])) ? $params['oldValue'] : null;
        $newPassValue     = $params['password'];
        $currentPassValue = $setting->getSetting('password');

        if (isset($params['id']) && $params['id'] == 0 && empty($newPassValue)) {
            $message = Phprojekt::getInstance()->translate('Password') . ': '
                . Phprojekt::getInstance()->translate('Is a required field');
        } else if (!isset($params['id']) && ((!empty($newPassValue) && $newPassValue != $confirmPassValue)
            || (empty($newPassValue) && !empty($confirmPassValue)))) {
            $message = Phprojekt::getInstance()->translate('The password and confirmation are different or one of them '
                . 'is empty');
        } else if (!isset($params['id']) &&
            (!empty($newPassValue) && $currentPassValue != Phprojekt_Auth::cryptString($oldPassValue))) {
            $message = Phprojekt::getInstance()->translate('The old password provided is invalid');
        }

        // TimeZone
        if (!array_key_exists($params['timeZone'], $this->_timeZoneRange)) {
            $message = Phprojekt::getInstance()->translate('The Time Zone value is out of range');
        }

        // Language
        if (!array_key_exists($params['language'], $this->_languageRange)) {
            $message = Phprojekt::getInstance()->translate('The Language value do not exists');
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
        $setting = Phprojekt_Loader::getLibraryClass('Phprojekt_Setting');
        $setting->setModule('User');
        if (empty($params['password'])) {
            $password = $setting->getSetting('password', $userId);
        } else {
            $password = Phprojekt_Auth::cryptString($params['password']);
        }

        $namespace = new Zend_Session_Namespace(Phprojekt_Setting::IDENTIFIER, $userId);
        $fields    = $this->getFieldDefinition();
        foreach ($fields as $data) {
            foreach ($params as $key => $value) {
                if ($key == $data['key'] && $key != 'oldValue' && $key != 'confirmValue') {
                    $setting = Phprojekt_Loader::getLibraryClass('Phprojekt_Setting');
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
