<?php
/**
 * Convert a model into a json structure.
 * This is usually done by a controller to send data to the
 * client
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
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Convert a model into a json structure.
 * This is usally done by a controller to send data to the client.
 * The Phprojekt_Convert_Json takes care that a apporpriate structure
 * is made from the given model.
 *
 * The fields are hardcore.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */
class Phprojekt_User_Information extends EmptyIterator implements Phprojekt_ModelInformation_Interface
{
    /**
     * Return an array of field information.
     *
     * @param integer $ordering An ordering constant
     *
     * @return array
     */
    public function getFieldDefinition($ordering = Phprojekt_ModelInformation_Default::ORDERING_DEFAULT)
    {
        $converted = array();

        // username
        $data = array();
        $data['key']      = 'username';
        $data['label']    = Phprojekt::getInstance()->translate('Username');
        $data['type']     = 'text';
        $data['hint']     = Phprojekt::getInstance()->getTooltip('username');
        $data['order']    = 0;
        $data['position'] = 1;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
                                  'name' => '');
        $data['required'] = true;
        $data['readOnly'] = false;
        $data['tab']      = 1;
        $data['integer']  = false;
        $data['length']   = 255;
        $data['default']  = null;

        $converted[] = $data;

        if ($ordering == Phprojekt_ModelInformation_Default::ORDERING_FORM) {
            // password
            $data = array();
            $data['key']      = 'password';
            $data['label']    = Phprojekt::getInstance()->translate('Password');
            $data['type']     = 'password';
            $data['hint']     = Phprojekt::getInstance()->getTooltip('password');
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
        }

        // firstname
        $data = array();
        $data['key']      = 'firstname';
        $data['label']    = Phprojekt::getInstance()->translate('First name');
        $data['type']     = 'text';
        $data['hint']     = Phprojekt::getInstance()->getTooltip('firstname');
        $data['order']    = 0;
        $data['position'] = 3;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
                                  'name' => '');
        $data['required'] = true;
        $data['readOnly'] = false;
        $data['tab']      = 1;
        $data['integer']  = false;
        $data['length']   = 255;
        $data['default']  = null;

        $converted[] = $data;

        // lastname
        $data = array();
        $data['key']      = 'lastname';
        $data['label']    = Phprojekt::getInstance()->translate('Last name');
        $data['type']     = 'text';
        $data['hint']     = Phprojekt::getInstance()->getTooltip('lastname');
        $data['order']    = 0;
        $data['position'] = 4;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
                                  'name' => '');
        $data['required'] = true;
        $data['readOnly'] = false;
        $data['tab']      = 1;
        $data['integer']  = false;
        $data['length']   = 255;
        $data['default']  = null;

        $converted[] = $data;

        if ($ordering == Phprojekt_ModelInformation_Default::ORDERING_FORM) {
            // email
            $data = array();
            $data['key']      = 'email';
            $data['label']    = Phprojekt::getInstance()->translate('Email');
            $data['type']     = 'text';
            $data['hint']     = Phprojekt::getInstance()->getTooltip('email');
            $data['order']    = 0;
            $data['position'] = 5;
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
            $data['position'] = 6;
            $data['fieldset'] = '';
            $languageRange    = Phprojekt_LanguageAdapter::getLanguageList();
            foreach ($languageRange as $key => $value) {
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
            $timeZoneRange = array(
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
                "0" => "(GMT) Casablanca",
                "00" => "(GMT) Coordinated Universal Time",
                "000" => "(GMT) Greenwich Mean Time: Dublin, Edinburgh, Lisbon, London",
                "0000" => "(GMT) Monrovia, Reykjavik",
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

            $data = array();
            $data['key']      = 'timeZone';
            $data['label']    = Phprojekt::getInstance()->translate('Time Zone');
            $data['type']     = 'selectbox';
            $data['hint']     = Phprojekt::getInstance()->getTooltip('timeZone');
            $data['order']    = 0;
            $data['position'] = 7;
            $data['fieldset'] = '';
            $data['range'] = array();
            foreach ($timeZoneRange as $key => $value) {
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
        }

        // status
        $data = array();
        $data['key']      = 'status';
        $data['label']    = Phprojekt::getInstance()->translate('Status');
        $data['type']     = 'selectbox';
        $data['hint']     = Phprojekt::getInstance()->getTooltip('status');
        $data['order']    = 0;
        $data['position'] = 8;
        $data['fieldset'] = '';
        $data['range']    = array(array('id'   => 'A',
                                        'name' => Phprojekt::getInstance()->translate('Active')),
                                  array('id'   => 'I',
                                        'name' => Phprojekt::getInstance()->translate('Inactive')));
        $data['required'] = false;
        $data['readOnly'] = false;
        $data['tab']      = 1;
        $data['integer']  = false;
        $data['length']   = 0;
        $data['default']  = 'A';

        $converted[] = $data;

        // admin
        $data = array();
        $data['key']      = 'admin';
        $data['label']    = Phprojekt::getInstance()->translate('Admin');
        $data['type']     = 'selectbox';
        $data['hint']     = Phprojekt::getInstance()->getTooltip('admin');
        $data['order']    = 0;
        $data['position'] = 9;
        $data['fieldset'] = '';
        $data['range']    = array(array('id'   => 0,
                                        'name' => Phprojekt::getInstance()->translate('No')),
                                  array('id'   => 1,
                                        'name' => Phprojekt::getInstance()->translate('Yes')));
        $data['required'] = false;
        $data['readOnly'] = false;
        $data['tab']      = 1;
        $data['integer']  = true;
        $data['length']   = 0;
        $data['default']  = 0;

        $converted[] = $data;

        return $converted;
    }
}
