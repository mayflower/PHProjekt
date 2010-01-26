<?php
/**
 * Meta information about the Minutes model. Acts as a layer over
 * database manager to filter readonly fields to yes if minutes is final.
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
 * @package    PHProjekt
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id:  $
 * @link       http://www.phprojekt.com
 * @author     Gustavo Solt <solt@mayflower.de>
 * @since      File available since Release 6.0
 */

/**
 * Meta information about the Calendar model. Acts as a layer over
 * database manager to filter readonly fields to yes if the event is from other participant
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Calendar_Models_CalendarInformation extends Phprojekt_DatabaseManager
    implements Phprojekt_ModelInformation_Interface
{
    /**
     * Set the db table name to use to this fixed value. The database used by the parent
     * class must be used here as well, independent of the class name.
     *
     * @return string
     */
    public function getTableName()
    {
        return "database_manager";
    }

    /**
     * Return an array of field information.
     *
     * @param int $ordering
     *
     * @return array
     */
    public function getFieldDefinition($ordering = Phprojekt_ModelInformation_Default::ORDERING_DEFAULT)
    {
        $meta = parent::getFieldDefinition($ordering);

        // If ownerId != currentUser then set readOnly for all fields except status
        if ($this->_model->ownerId && (Phprojekt_Auth::getUserId() != $this->_model->ownerId)) {
            foreach (array_keys($meta) as $key) {
                if ('status' != $meta[$key]['key']) {
                    $meta[$key]['readOnly'] = 1;
                }
            }
        }

        return $meta;
    }
}