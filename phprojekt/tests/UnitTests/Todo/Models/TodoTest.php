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
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @version    $Id$
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */
require_once 'PHPUnit/Framework.php';

/**
 * Tests Todo Model class
 *
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 * @group      todo
 * @group      model
 * @group      todo-model
 */
class Todo_Models_Todo_Test extends PHPUnit_Framework_TestCase
{
    /**
     * Test getTo method
     */
    public function testGetNotificationRecipients()
    {
        $todoModel = new Todo_Models_Todo();
        $todoModel->find(2);
        $response = $todoModel->getNotification()->getTo();
        $expected = array();
        $this->assertEquals($expected, $response);

        $todoModel->userId = 2;
        $todoModel->save();
        $response   = $todoModel->getNotification()->getTo();
        $expected = array(2);
        $this->assertEquals($expected, $response);

        $todoModel->userId = 3;
        $todoModel->save();
        $response   = $todoModel->getNotification()->getTo();
        $expected = array(3, 2);
        $this->assertEquals($expected, $response);
    }
}
