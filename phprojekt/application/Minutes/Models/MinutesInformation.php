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
 * Meta information about the Minutes model. Acts as a layer over
 * database manager to filter readonly fields to yes if minutes is final.
 */
class Minutes_Models_MinutesInformation extends Phprojekt_DatabaseManager
    implements Phprojekt_ModelInformation_Interface
{
    /**
     * Set the db table name to use to this fixed value. The database used by the parent
     * class must be used here as well, independent of the class name.
     *
     * @return string The table name.
     */
    public function getTableName()
    {
        return "database_manager";
    }

    /**
     * Return an array of field information.
     *
     * @param integer $ordering An ordering constant.
     *
     * @return array Array with fields definitions.
     */
    public function getFieldDefinition($ordering = Phprojekt_ModelInformation_Default::ORDERING_DEFAULT)
    {
        $meta = parent::getFieldDefinition($ordering);

        // If itemStatus == final then set readOnly for all fields except itemStatus
        if (4 == $this->_model->itemStatus) {
            foreach (array_keys($meta) as $key) {
                if ('itemStatus' != $meta[$key]['key']) {
                    $meta[$key]['readOnly'] = 1;
                }
            }
        }

        return $meta;
    }
}