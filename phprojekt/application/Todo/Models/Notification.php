<?php
/**
 * Notification class of Todo model for PHProjekt 6.0
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
 * @version    $Id$
 * @link       http://www.phprojekt.com
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 * @since      File available since Release 6.0
 */

/**
 * Notification class for Todo module
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 */
class Todo_Models_Notification extends Phprojekt_Notification
{
    /**
     * Returns the recipients for this Todo item
     *
     * @return array
     */
    public function getTo()
    {
        $userId = Phprojekt_Auth::getUserId();

        // Gets only the recipients with at least a 'read' right.
        $recipients = parent::getTo();

        // Assigned user
        if (isset($this->_model->userId) && $this->_model->userId != $userId) {
            $recipients[] = $this->_model->userId;
        }

        // Owner user
        if (isset($this->_model->ownerId) && $this->_model->ownerId != $userId) {
            $recipients[] = $this->_model->ownerId;
        }

        // If the item has been reassigned, add the previous assigned user to the recipients
        $history = Phprojekt_Loader::getLibraryClass('Phprojekt_History');
        $olUser  = $history->getLastAssignedUser($this->_model, 'userId');
        if ($olUser > 0) {
            $recipients[] = $olUser;
        }

        // Return without duplicates
        return array_unique($recipients);
    }
}
