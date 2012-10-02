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
 * Wrapper around Calendar2_Models_CalendarInformation that sets all
 */
class Calendar2_Models_InformationDecoratorReadonly extends Phprojekt_ModelInformation_Default
{
    protected $_wrapee;

    public function __construct(Calendar2_Models_CalendarInformation $calendarInformation)
    {
        $this->_wrapee = $calendarInformation;
    }

    /**
     * Return an array of field information.
     *
     * @param integer $ordering An ordering constant.
     *
     * @return array Array with fields definitions.
     */
    public function getFieldDefinition(
            $ordering = Phprojekt_ModelInformation_Default::ORDERING_DEFAULT)
    {
        $meta = $this->_wrapee->getFieldDefinition($ordering);

        // If ownerId != currentUser then set all fields except status readonly
        foreach (array_keys($meta) as $key) {
            if ('confirmationStatus' != $meta[$key]['key']) {
                $meta[$key]['readOnly'] = 1;
            }
        }

        return $meta;
    }

    /**
     * This function is copied from the database manager because Phprojekt_Item_Abstract mistakenly expects
     * it to be a database manager.
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
