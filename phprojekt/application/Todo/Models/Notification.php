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
        $recipients = Array();
        $recipients[] = $this->_model->ownerId;
        if ($this->_model->userId != 0 && $this->_model->userId != $this->_model->ownerId) {
            $recipients[] = $this->_model->userId;
        }

        // If the todo has been reassigned, add the previous assigned user to the recipients
        $history = Phprojekt_Loader::getLibraryClass('Phprojekt_History');
        $changes = $history->getLastHistoryData($this->_model);
        if ($changes[0]['action'] == 'edit') {
            foreach ($changes as $change) {
                if ($change['field'] == 'userId') {
                    // The user has changed
                    if ($change['oldValue'] != $this->_model->ownerId && $change['oldValue'] != '0'
                        && $change['oldValue'] !== null) {
                        $recipients[] = $change['oldValue'];
                        break;
                    }
                }
            }
        }

        return $recipients;
    }
}
