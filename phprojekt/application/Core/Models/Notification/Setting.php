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
 * Settings for the notifications.
 */
class Core_Models_Notification_Setting extends Phprojekt_ModelInformation_Default
{
    /**
     * Name for login / logut setting.
     */
    const FIELD_LOGIN_LOGOUT = 'loginlogout';

    /**
     * Name for Data records setting.
     */
    const FIELD_DATARECORDS = 'datarecords';

    /**
     * Name for User generated setting.
     */
    const FIELD_USERGENERATED = 'usergenerated';

    /**
     * Name for Alerts setting.
     */
    const FIELD_ALERTS = 'alerts';

    /**
     * Sets a fields definitions for each field.
     *
     * @return void
     */
    public function setFields()
    {
        // Login/Logout
        $this->fillField(self::FIELD_LOGIN_LOGOUT, 'Login / Logout', 'checkbox', 1, 1, array(
            'integer' => true,
            'default' => 1));

        // Data records
        $this->fillField(self::FIELD_DATARECORDS, 'Data Records', 'checkbox', 2, 2, array(
            'integer' => true,
            'default' => 1));

        // @TODO The user generated messages is not implemented yet
        // User generated messages
        //$this->fillField(self::FIELD_USERGENERATED, 'User generated messages', 'checkbox', 3, 3, array(
        //    'readOnly' => true,
        //    'integer'  => true,
        //    'default'  => 0));

        // Alerts (event deadlines)
        $this->fillField(self::FIELD_ALERTS, 'Alerts', 'checkbox', 4, 4, array(
            'integer' => true,
            'default' => 1));
    }

    /**
     * Collect all the values of the settings and return it in one row.
     *
     * @param integer $moduleId The current moduleId.
     * @param array   $metadata Array with all the fields.
     * @param integer $userId   The user ID, if is not setted, the current user is used.
     *
     * @return array Array with all the settings and values.
     */
    public function getList($moduleId, $metadata, $userId = null)
    {
        $setting = new Phprojekt_Setting();
        $setting->setModule('Notification');

        $settings = array();

        if ($userId === null) {
            $userId = (int) Phprojekt_Auth::getUserId();
        }

        $where  = sprintf('module_id = %d AND user_id = %d', (int) $moduleId, (int) $userId);
        $record = $setting->fetchAll($where);

        $data       = array();
        $data['id'] = 0;
        foreach ($metadata as $meta) {
            $data[$meta['key']] = $meta['default']; // This is to use the default value defined in getFieldDefinition()
            foreach ($record as $oneSetting) {
                if ($oneSetting->keyValue == $meta['key']) {
                    $getter = 'get' . ucfirst($oneSetting->keyValue);
                    if (method_exists($this, $getter)) {
                        $data[$meta['key']] = call_user_func(array($this, $getter), $oneSetting->value);
                    } else {
                        $data[$meta['key']] = $oneSetting->value;
                    }
                    break;
                }
            }
        }
        $settings[] = $data;

        return $settings;
    }
}
