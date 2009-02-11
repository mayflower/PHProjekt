<?php
/**
 * Timecard setting model
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
 * Settings for the Timecard module
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
class Timecard_Models_TimecardSetting
{
    /**
     * Return an array of field information.
     *
     * @return array
     */
    public function getFieldDefinition()
    {
        $converted = array();
        $hints     = Phprojekt::getInstance()->translate('Tooltip');

        // Amount
        $data = array();
        $data['key']      = 'amount';
        $data['label']    = Phprojekt::getInstance()->translate('Max Number of favorites projects');
        $data['type']     = 'text';
        $data['hint']     = (isset($hints['amount'])) ? $hints['amount'] : '';
        $data['order']    = 0;
        $data['position'] = 1;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
                                  'name' => '');
        $data['required'] = true;
        $data['readOnly'] = false;
        $data['tab']      = 1;

        $converted[] = $data;

        // Favorites
        $data = array();
        $data['key']      = 'favorites';
        $data['label']    = Phprojekt::getInstance()->translate('Favorite projects');
        $data['type']     = 'multipleselectbox';
        $data['hint']     = (isset($hints['favorites'])) ? $hints['favorites'] : '';
        $data['order']    = 0;
        $data['position'] = 1;
        $data['fieldset'] = '';
        $activeRecord = Phprojekt_Loader::getModel('Project', 'Project');
        $result       = $activeRecord->fetchAll();
        $data['range'] = array();
        foreach ($result as $item) {
            $data['range'][] = array('id'   => $item->id,
                                     'name' => $item->title);
        }
        $data['required'] = true;
        $data['readOnly'] = false;
        $data['tab']      = 1;

        $converted[] = $data;

        return $converted;
    }

    /**
     * getter for the "favorites" field
     *
     * @param string $value Serialized array of Ids
     *
     * @return array
     */
    public function getFavorites($value)
    {
        return implode(",", unserialize($value));
    }

    /**
     * Save the settings for the timecard
     *
     * @param array $params $_POST values
     *
     * @return void
     */
    public function setSettings($params)
    {
        $fields = $this->getFieldDefinition();
        foreach ($fields as $data) {
            foreach ($params as $key => $value) {
                if ($key == $data['key']) {
                    $setting = Phprojekt_Loader::getModel('Setting', 'Setting');
                    $setting->setModule('Timecard');
                    if (($key == 'favorites')) {
                        $value = serialize($value);
                    }
                    $record = $setting->fetchAll("userId = ". Phprojekt_Auth::getUserId() .
                                                 " AND keyValue = ". $setting->_db->quote($key) .
                                                 " AND moduleId = ". Phprojekt_Module::getId('Timecard'));
                    if (isset($record[0])) {
                        $record[0]->keyValue = $key;
                        $record[0]->value    = $value;
                        $record[0]->save();
                    } else {
                        $setting->userId     = Phprojekt_Auth::getUserId();
                        $setting->moduleId   = Phprojekt_Module::getId('Timecard');
                        $setting->keyValue   = $key;
                        $setting->value      = $value;
                        $setting->identifier = 'Timecard';
                        $setting->save();
                    }
                }
            }
        }
    }
}
