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
 * Convert a model into a json structure.
 * This is usally done by a controller to send data to the client.
 * The Phprojekt_Convert_Json takes care that a apporpriate structure
 * is made from the given model.
 *
 * The fields are hardcore.
 */
class Timecard_Models_Information extends Phprojekt_ModelInformation_Default
{
    /**
     * Sets a fields definitions for each field
     *
     * @return void
     */
    public function setFields()
    {
        // startDatetime
        $this->fillField('startDatetime', 'Start', 'datetime', 1, 1, array(
            'required' => true));

        // endTime
        $this->fillField('endTime', 'End', 'time', 2, 2);

        // minutes
        $this->fillField('minutes', 'Minutes', 'text', 3, 3, array(
            'integer' => true));

        // projectId
        $this->fillField('projectId', 'Project', 'selectbox', 4, 4, array(
            'range'    => $this->getProjectRange(),
            'required' => true,
            'integer'  => true));

        // notes
        $this->fillField('notes', 'Notes', 'textarea', 5, 5);
    }
}
