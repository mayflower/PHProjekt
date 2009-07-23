<?php
/**
 * Notification class of Helpdesk model for PHProjekt 6.0
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
 * Notification class for Helpdesk module
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 */
class Helpdesk_Models_Notification extends Phprojekt_Notification
{
    /**
     * Returns the recipients for this Helpdesk item
     *
     * @return array
     */
    public function getTo()
    {
        $phpUser    = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
        $recipients = Array();

        // Currently the user selects whether to send a notification or not, so the criteria is the following:
        // Is there any assigned user?
        if ($this->_model->assigned != 0) {
            // Yes
            $phpUser = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
            $phpUser->find(Phprojekt_Auth::getUserId());
            // The assigned user is the logged user?
            if ($this->_model->assigned != $phpUser->id) {
                // No - Send it to the assigned user
                $recipients[] = $this->_model->assigned;
            } else {
                // Yes - Send it to the creator of the ticket
                $recipients[] = $this->_model->author;
            }
        } else {
            // No - Send it to the creator of the ticket
            $recipients[] = $this->_model->author;
        }

        // If the item has been reassigned, add the previous assigned user to the recipients
        $history = Phprojekt_Loader::getLibraryClass('Phprojekt_History');
        $changes = $history->getLastHistoryData($this->_model);
        if ($changes[0]['action'] == 'edit') {
            foreach ($changes as $change) {
                if ($change['field'] == 'assigned') {
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
