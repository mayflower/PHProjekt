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
 * Todo model class
 */
class Todo_Models_Todo extends Phprojekt_Item_Abstract
{
    /**
     * Returns an instance of notification class for this module
     *
     * @return Phprojekt_Notification An instance of Phprojekt_Notification.
     */
    public function getNotification()
    {
        $notification = new Todo_Models_Notification();
        $notification->setModel($this);

        return $notification;
    }

    /**
     * Validate the data of the current record.
     *
     * @return boolean True for valid.
     */
    public function recordValidate()
    {
        if (!$this->_validate->validateDateRange($this->startDate, $this->endDate)) {
            return false;
        } else {
            return parent::recordValidate();
        }
    }
}
