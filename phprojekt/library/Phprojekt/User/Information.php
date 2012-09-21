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
 * Meta information about the User model.
 *
 * The fields are hardcore.
 */
class Phprojekt_User_Information extends Phprojekt_ModelInformation_Default
{
    /**
     * Sets a fields definitions for each field.
     *
     * @return void
     */
    public function setFields()
    {
        // username
        $this->fillField('username', 'Username', 'text', 1, 1, array(
            'required' => true,
            'length'   => 255));

        // password
        $this->fillField('password', 'Password', 'password', 0, 2, array(
            'length' => 50));

        // confirmValue
        $this->fillField('confirmValue', 'Confirm Password', 'password', 0, 3, array(
            'length' => 50));

        // firstname
        $this->fillField('firstname', 'First name', 'text', 2, 4, array(
            'required' => true,
            'length'   => 255));

        // lastname
        $this->fillField('lastname', 'Last name', 'text', 3, 5, array(
            'required' => true,
            'length'   => 255));

        // email
        $this->fillField('email', 'Email', 'text', 0, 6, array(
            'length'   => 255));

        // language
        $range         = array();
        $languageRange = Phprojekt_LanguageAdapter::getLanguageList();
        foreach ($languageRange as $key => $value) {
            $range[] = $this->getRangeValues($key, $value);
        }
        $this->fillField('language', 'Language', 'selectbox', 0, 7, array(
            'range'    => $range,
            'required' => true,
            'default'  => 'en'));

        // timeZone
        $range         = array();
        $timeZoneRange = Phprojekt_Converter_Time::getTimeZones();
        foreach ($timeZoneRange as $key => $value) {
            $range[] = $this->getRangeValues($key, $value);
        }
        $this->fillField('timeZone', 'Time zone', 'selectbox', 0, 8, array(
            'range'    => $range,
            'required' => true,
            'default'  => '000'));

        // status
        $this->fillField('status', 'Status', 'selectbox', 4, 9, array(
            'range'    => array($this->getFullRangeValues('A', 'Active'),
                                $this->getFullRangeValues('I', 'Inactive')),
            'default'  => 'A'));

        // admin
        $this->fillField('admin', 'Admin', 'checkbox', 5, 10, array(
            'range'    => array($this->getFullRangeValues(0, 'No'),
                                $this->getFullRangeValues(1, 'Yes')),
            'integer'  => true,
            'default'  => 0));
    }
}
