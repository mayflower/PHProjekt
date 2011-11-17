<?php
/**
 * Unit test
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
 * @package    UnitTests
 * @subpackage Helpdesk
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 */


/**
 * Tests Helpdesk Model class
 *
 * @category   PHProjekt
 * @package    UnitTests
 * @subpackage Helpdesk
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 * @group      helpdesk
 * @group      model
 * @group      helpdesk-model
 */
class Helpdesk_Models_Helpdesk_Test extends PHPUnit_Framework_TestCase
{
    /**
     * Test getTo method
     */
    public function testGetNotificationRecipients()
    {
        $helpdeskModel = new Helpdesk_Models_Helpdesk();
        $helpdeskModel->find(1);
        $response = $helpdeskModel->getNotification()->getTo();
        $expected = array();
        $this->assertEquals($expected, $response);

        $helpdeskModel->find(2);
        $helpdeskModel->assigned = 2;
        $helpdeskModel->save();
        $helpdeskModel->assigned = 1;
        $helpdeskModel->save();
        $response = $helpdeskModel->getNotification()->getTo();
        $expected = array(2);
        $this->assertEquals($expected, $response);
    }
}
