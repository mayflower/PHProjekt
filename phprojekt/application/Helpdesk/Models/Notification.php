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
     * Fills and returns a variable with recipients using a custom criterion for Helpdesk class
     *
     * @return array
     */
    public function setTo()
    {
        $phpUser       = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
        $setting       = Phprojekt_Loader::getModel('Setting', 'Setting');
        $recipientsIds = Array();

        // Currently the user selects whether to send a notification or not, so the criteria is the following:
        // Is there any assigned user?
        if ($this->_model->assigned != 0) {
            // Yes
            $phpUser = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
            $phpUser->find(Phprojekt_Auth::getUserId());
            // The assigned user is the logged user?
            if ($this->_model->assigned != $phpUser->id) {
                // No - Send it to the assigned user
                $recipientsIds[] = $this->_model->assigned;
            } else {
                // Yes - Send it to the creator of the ticket
                $recipientsIds[] = $this->_model->author;
            }
        } else {
            // No - Send it to the creator of the ticket
            $recipientsIds[] = $this->_model->author;
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
                        $recipientsIds[] = $change['oldValue'];
                        break;
                    }
                }
            }
        }

        // All the recipients IDs are inside $recipientsIds, now add emails and descriptive names to $recipients
        $recipients = array();
        foreach ($recipientsIds as $recipient) {
            $email = $setting->getSetting('email', (int) $recipient);

            if ((int) $recipient) {
                $phpUser->find($recipient);
            } else {
                $phpUser->find(Phprojekt_Auth::getUserId());
            }

            $recipients[]             = array();
            $lastItem                 = count($recipients) - 1;
            $recipients[$lastItem][0] = $email;

            $fullname = trim($phpUser->firstname . ' ' . $phpUser->lastname);
            if (!empty($fullname)) {
                $recipients[$lastItem][1] = $fullname . ' (' . $phpUser->username . ')';
            } else {
                $recipients[$lastItem][1] = $phpUser->username;
            }
        }

        return $recipients;
    }
}
