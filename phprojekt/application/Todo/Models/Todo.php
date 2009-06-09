<?php
/**
 * Todo model class
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
 * @author     Gustavo Solt <solt@mayflower.de>
 * @since      File available since Release 6.0
 */

/**
 * Todo model class
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Todo_Models_Todo extends Phprojekt_Item_Abstract
{
    /**
     * Get all the recipients for the mail notification
     *
     * @return string
     */
    public function getNotificationRecipients()
    {
        $recipients = $this->ownerId;
        if ($this->userId != 0 && $this->userId != $this->ownerId) {
            $recipients .= "," . $this->userId;
        }

        // If the todo has been reassigned, add the previous assigned user to the recipients
        $history = Phprojekt_Loader::getLibraryClass('Phprojekt_History');
        $changes = $history->getLastHistoryData($this);
        if ($changes[0]['action'] == 'edit') {
            foreach ($changes as $change) {
                if ($change['field'] == 'userId') {
                    // The user has changed
                    if ($change['oldValue'] != $this->ownerId && $change['oldValue'] != '0'
                        && $change['oldValue'] !== null) {
                        $recipients .= "," . $change['oldValue'];
                        break;
                    }
                }
            }
        }

        return $recipients;
    }
}
