<?php
/**
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
 */


/**
 * Tests Todo Model class
 *
 * @group      todo
 * @group      model
 * @group      todo-model
 */
class Todo_Models_Todo_Test extends DatabaseTest
{
    protected function getDataSet()
    {
        return new PHPUnit_Extensions_Database_DataSet_CompositeDataSet(
            array(
                $this->createFlatXMLDataSet(dirname(__FILE__) . '/../../common.xml'),
                $this->createFlatXMLDataSet(dirname(__FILE__) . '/../data.xml')
            )
        );
    }

    /**
     * Test getTo method
     */
    public function testGetNotificationRecipients()
    {
        $todoModel = new Todo_Models_Todo();
        $todoModel->find(1);
        $response = $todoModel->getNotification()->getTo();
        $expected = array(2);
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
