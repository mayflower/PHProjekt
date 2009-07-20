<?php
/**
 * Helpdesk model class
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
 * Helpdesk model class
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 */
class Helpdesk_Models_Helpdesk extends Phprojekt_Item_Abstract
{
    /**
     * Status values
     */
    const STATUS_OPEN     = 1;
    const STATUS_ASSIGNED = 2;
    const STATUS_SOLVED   = 3;
    const STATUS_VERIFIED = 4;
    const STATUS_CLOSED   = 5;

    /**
     * Initializes new object.
     * Replaces the default Notification class by this specific one for Helpdesk module.
     *
     * @param array $db Configuration for Zend_Db_Table
     */
    public function __construct($db = null)
    {
        parent::__construct($db);

        $this->_notification = Phprojekt_Loader::getLibraryClass('Helpdesk_Models_Notification', 'UTF-8');
    }

    /**
     * Defines the clone function to prevent the same point to same object.
     * Replaces the default Notification class by this specific one for Calendar module.
     *
     * @return void
     */
    public function __clone()
    {
        parent::__clone();
        $this->_notification = Phprojekt_Loader::getLibraryClass('Helpdesk_Models_Notification', 'UTF-8');
    }
}
