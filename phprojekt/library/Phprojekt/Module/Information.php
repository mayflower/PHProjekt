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
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
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
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */
class Phprojekt_Module_Information extends Phprojekt_ModelInformation_Default
{
    /**
     * Sets a fields definitions for each field
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
