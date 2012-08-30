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
 * Tests for Index Controller
 *
 * @group      user
 * @group      controller
 * @group      user-controller
 */
class User_IndexController_Test extends FrontInit
{
    protected function getDataSet() {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/../../common.xml');
    }

    /**
     * Test the user list
     */
    public function testGetUsersAction()
    {
        $this->setRequestUrl('Core/user/jsonGetUsers');
        $this->request->setParam('nodeId', 1);
        $response = FrontInit::phprJsonToArray($this->getResponse());
        $expected = array(
            'data' => array(
                array('id' => 2, 'display' => ', Luise Marie'),
                array('id' => 1, 'display' => 'Mustermann, Max')
            )
        );
        $this->assertEquals($expected, $response);
    }

    /**
     * Test the multiple save with error (actually Default jsonSaveMultipleAction)
     */
    public function testJsonSaveMultipleError()
    {
        $this->markTestSkipped("Not yet");
        $this->setRequestUrl('Core/user/jsonSaveMultiple');
        $items = array(2 => array('username' => 'Test'));
        $this->request->setParam('data', $items);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $expected = '{"type":"error","message":"ID 2. Username: Already exists, choose another one please","code":0,'
            . '"id":"2"}';
        $this->assertContains($expected, $response);
    }

    /**
     * Test the multiple save with one item wrong and another one well (actually Default jsonSaveMultipleAction)
     */
    public function testJsonSaveMultipleErrorPart2()
    {
        $this->setRequestUrl('Core/user/jsonSaveMultiple');
        $items = array(2 => array('admin'     => '1'),
                       3 => array('firstname' => 'Yo'));
        $this->request->setParam('data', $items);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $expected = '{"type":"error","message":"ID 2. Last name: Is a required field","id":"2"}';
        $this->assertContains($expected, $response);
    }
}
