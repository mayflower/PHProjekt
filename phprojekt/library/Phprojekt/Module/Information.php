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
 * Meta information about the Module model.
 *
 * The fields are hardcore.
 */
class Phprojekt_Module_Information extends Phprojekt_ModelInformation_Default
{
    /**
     * Sets a fields definitions for each field.
     *
     * @return void
     */
    public function setFields()
    {
        // name
        $this->fillField('name', 'Name', 'hidden', 1, 1, array(
            'required' => true,
            'length'   => 255));

        // label
        $this->fillField('label', 'Label', 'text', 2, 2, array(
            'required' => true,
            'length'   => 255));

        // saveType
        $this->fillField('saveType', 'Type', 'selectbox', 3, 3, array(
            'range'   => array($this->getFullRangeValues(0, 'Normal'),
                               $this->getFullRangeValues(1, 'Global')),
            'integer' => true,
            'default' => '0'));

        // active
        $this->fillField('active', 'Active', 'selectbox', 4, 4, array(
            'range'   => array($this->getFullRangeValues(0, 'No'),
                               $this->getFullRangeValues(1, 'Yes')),
            'integer' => true,
            'default' => '1'));
    }
}
