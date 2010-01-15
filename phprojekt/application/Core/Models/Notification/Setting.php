<?php
/**
 * Notification setting model
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
 * @author     Martin Ruprecht <martin.ruprecht@mayflower.de>
 */

/**
 * Settings for the notifications
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Martin Ruprecht <martin.ruprecht@mayflower.de>
 */
class Core_Models_Notification_Setting
{
    const FIELD_LOGIN_LOGOUT  = 'loginlogout';
    const FIELD_DATARECORDS   = 'datarecords';
    const FIELD_USERGENERATED = 'usergenerated';
    const FIELD_ALERTS        = 'alerts';

    /**
     * Return an array of field information.
     *
     * @param integer $ordering An ordering constant
     *
     * @return array
     */
    public static function getFieldDefinition()
    {
        $converted = array();

        // Login/Logout
        $data             = array();
        $data['key']      = self::FIELD_LOGIN_LOGOUT;
        $data['label']    = Phprojekt::getInstance()->translate('Login / Logout');
        $data['type']     = 'checkbox';
        $data['hint']     = Phprojekt::getInstance()->getTooltip('loginLogout');
        $data['order']    = 0;
        $data['position'] = 1;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
                                  'name' => '');
        $data['required'] = false;
        $data['readOnly'] = false;
        $data['tab']      = 1;
        $data['integer']  = true;
        $data['length']   = 0;
        $data['default']  = 1;

        $converted[] = $data;

        // Data records
        $data             = array();
        $data['key']      = self::FIELD_DATARECORDS;
        $data['label']    = Phprojekt::getInstance()->translate('Data Records');
        $data['type']     = 'checkbox';
        $data['hint']     = Phprojekt::getInstance()->getTooltip('datarecords');
        $data['order']    = 0;
        $data['position'] = 2;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
                                  'name' => '');
        $data['required'] = false;
        $data['readOnly'] = false;
        $data['tab']      = 1;
        $data['integer']  = true;
        $data['length']   = 0;
        $data['default']  = 1;

        $converted[] = $data;

        // @TODO The user generated messages and alerts before are event starts are not implemented yet, therefore
        // they are set to readOnly. The checkbox is not checked.

        // User generated messages
        $data             = array();
        $data['key']      = self::FIELD_USERGENERATED;
        $data['label']    = Phprojekt::getInstance()->translate('User generated messages');
        $data['type']     = 'checkbox';
        $data['hint']     = Phprojekt::getInstance()->getTooltip('usergenerated');
        $data['order']    = 0;
        $data['position'] = 3;
        $data['fieldset'] = '';
        $data['range'][]  = array('id'   => '',
                                  'name' => '');
        $data['required'] = false;
        $data['readOnly'] = true;
        $data['tab']      = 1;
        $data['integer']  = true;
        $data['length']   = 0;
        $data['default']  = 0;

        $converted[] = $data;

        // Alerts (event deadlines)
        $data             = array();
        $data['key']      = self::FIELD_ALERTS;
        $data['label']    = Phprojekt::getInstance()->translate('Alerts');
        $data['type']     = 'checkbox';
        $data['hint']     = Phprojekt::getInstance()->getTooltip('alerts');
        $data['order']    = 0;
        $data['position'] = 4;
        $data['fieldset'] = '';
        $data['range'][]  = array('id'   => '',
                                  'name' => '');
        $data['required'] = false;
        $data['readOnly'] = true;
        $data['tab']      = 1;
        $data['integer']  = true;
        $data['length']   = 0;
        $data['default']  = 0;

        $converted[] = $data;

        return $converted;
    }

    /**
     * Collect all the values of the settings and return it in one row
     *
     * @param integer $moduleId The current moduleId
     * @param array   $metadata Array with all the fields
     * @param integer $userId   The user id, if is not setted, the current user is used
     *
     * @return array
     */
    public function getList($moduleId, $metadata, $userId = null)
    {
        $setting = Phprojekt_Loader::getLibraryClass('Phprojekt_Setting');
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
