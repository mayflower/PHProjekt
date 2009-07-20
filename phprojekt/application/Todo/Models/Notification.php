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
     * Fills and returns a variable with recipients using a custom criterion for Todo class
     *
     * @return array
     */
    public function setTo()
    {
        $phpUser = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
        $setting = Phprojekt_Loader::getModel('Setting', 'Setting');

        $recipientsIdsIds = Array();
        $recipientsIds[] = $this->_model->ownerId;
        if ($this->_model->userId != 0 && $this->_model->userId != $this->_model->ownerId) {
            $recipientsIds[] = $this->_model->userId;
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
