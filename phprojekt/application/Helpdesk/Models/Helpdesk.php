<?php
/**
 * Helpdesk model class.
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Helpdesk
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 */

/**
 * Helpdesk model class.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Helpdesk
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 */
class Helpdesk_Models_Helpdesk extends Phprojekt_Item_Abstract
{
    /**
     * Solved status value
     */
    const STATUS_SOLVED = 3;

    /**
     * Returns an instance of notification class for this module.
     *
     * @return Phprojekt_Notification An instance of Phprojekt_Notification.
     */
    public function getNotification()
    {
        $notification = new Helpdesk_Models_Notification();
        $notification->setModel($this);

        return $notification;
    }
}
