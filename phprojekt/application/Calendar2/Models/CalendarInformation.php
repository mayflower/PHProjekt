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
 * Meta information about the Calendar2 model. Acts as a layer over database
 * manager to filter readonly fields to yes if the event is from other
 * participant.
 */
class Calendar2_Models_CalendarInformation extends Phprojekt_ModelInformation_Default
{
    public function setFields()
    {
        $this->fillField('summary', 'Summary', 'text', 1, 1, array('required' => true));
        $this->fillField('description', 'Description', 'text', 2, 2);
        $this->fillField('location', 'Location', 'text', 3, 3);
        $this->fillField('comments', 'Comments', 'textarea', 0, 4);
        $this->fillField('start', 'Start', 'datetime', 6, 5, array('required' => true));
        $this->fillField('end', 'End', 'datetime', 7, 6, array('required' => true));
        $this->fillfield('occurrence', 'Occurrence', 'hidden', 0, 7); // The start time in UTC
        $this->fillField(
            'confirmationStatus',
            'Confirmation Status',
            'selectbox',
            0,
            7,
            array(
                'range'   => array(
                    $this->getFullRangeValues(1, 'Pending'),
                    $this->getFullRangeValues(2, 'Accepted'),
                    $this->getFullRangeValues(3, 'Rejected')
                ),
                'default' => 2
            )
        );
        $this->fillField(
            'visibility',
            'Visibility',
            'selectbox',
            0,
            8,
            array(
                'range' => array(
                    $this->getFullRangeValues(1, 'Public'),
                    $this->getFullRangeValues(2, 'Private')
                ),
                'default' => 1,
                'integer' => true
            )
        );
        $this->fillField('participants', 'Participants', 'hidden', 0, 9);
        $this->fillField('rrule', 'Rrule', 'hidden', 0, 10);
        $this->fillField('recurrence', 'Recurrence', 'hidden', 5, 0);
        $this->fillField('confirmationStatuses', 'Confirmation Statuses', 'hidden', 0, 11);
        $this->fillField('ownerId', 'ownerId', 'hidden', 0, 12, array('integer' => true));
    }

    /**
     * This function is copied from the database manager because Phprojekt_Item_Abstract is too cool to work with the
     * documented input data. It takes a modelinterface, but expects it to be a database manager. This sucks.
     */
    public function getInfo($order, $column)
    {
        $column = Phprojekt_ActiveRecord_Abstract::convertVarFromSql($column);
        $fields = $this->_getFields();
        $result = array();

        foreach ($fields as $field) {
            if (isset($field->$column)) {
                $result[] = $field->$column;
            }
        }

        return $result;
    }
}
