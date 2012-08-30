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
 * Settings for the Timecard module
 */
class Timecard_Models_Setting extends Phprojekt_ModelInformation_Default
{
    /**
     * Sets a fields definitions for each field
     *
     * @return void
     */
    public function setFields()
    {
        // favorites
        $this->fillField('favorites', 'Favorite projects', 'multiplefilteringselectbox', 1, 1, array(
            'range'    => $this->getProjectRange(),
            'required' => true));
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
        return unserialize($value);
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
        $namespace = new Zend_Session_Namespace(Phprojekt_Setting::IDENTIFIER . Phprojekt_Auth::getUserId());
        $fields    = $this->getFieldDefinition(Phprojekt_ModelInformation_Default::ORDERING_FORM);
        foreach ($fields as $data) {
            foreach ($params as $key => $value) {
                if ($key == $data['key']) {
                    $setting = new Phprojekt_Setting();
                    $setting->setModule('Timecard');

                    if (($key == 'favorites')) {
                        if (count($value) === 1 && $value[0] === "") {
                            $value = array();
                        }
                        $value = serialize($value);
                    }

                    $where  = sprintf('user_id = %d AND key_value = %s AND module_id = %d',
                        (int) Phprojekt_Auth::getUserId(), $setting->_db->quote($key),
                        (int) Phprojekt_Module::getId('Timecard'));
                    $record = $setting->fetchAll($where);

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
                    $namespace->$key = $value;
                    break;
                }
            }
        }
    }
}
