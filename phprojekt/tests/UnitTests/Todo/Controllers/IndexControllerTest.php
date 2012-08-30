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
 * Tests for Todo Index Controller
 *
 * @group      todo
 * @group      controller
 * @group      todo-controller
 */
class Todo_IndexController_Test extends FrontInit
{
    private $_listingExpectedString = null;
    private $_model                 = null;

    protected function getDataSet() {
        return new PHPUnit_Extensions_Database_DataSet_CompositeDataSet(
            array(
                $this->createFlatXMLDataSet(dirname(__FILE__) . '/../../common.xml'),
                $this->createFlatXMLDataSet(dirname(__FILE__) . '/../data.xml')));
    }

    /**
     * setUp method for PHPUnit
     */
    public function setUp()
    {
        parent::setUp();
        $this->_listingExpectedString = '{"key":"title","label":"Title","originalLabel":"Title","type":"text",'
            . '"hint":"","listPosition":1,"formPosition":1';
        $this->_model = new Todo_Models_Todo();
    }

    /**
     * Test of json save Todo
     */
    public function testJsonSaveAdd()
    {
        // INSERT
        $this->setRequestUrl('Todo/index/jsonSave/');
        $this->request->setParam('title', 'My todo task');
        $this->request->setParam('notes', 'My note');
        $this->request->setParam('projectId', 2);
        $this->request->setParam('startDate', '2009-05-15');
        $this->request->setParam('endDate', '2009-05-17');
        $this->request->setParam('priority', 5);
        $this->request->setParam('currentStatus', 1);
        $this->request->setParam('userId', 1);
        $this->request->setParam('string', 'My todo tag');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Todo_IndexController::ADD_TRUE_TEXT, $response, $this->errormessage);
    }

    /**
     * Test of json save Todo
     */
    public function testJsonSaveAddNotification()
    {
        // INSERT. Send notification
        $this->setRequestUrl('Todo/index/jsonSave/');
        $this->request->setParam('title', 'My todo task 2');
        $this->request->setParam('notes', 'My note 2');
        $this->request->setParam('projectId', 2);
        $this->request->setParam('startDate', '2009-07-15');
        $this->request->setParam('endDate', '2009-07-17');
        $this->request->setParam('priority', 2);
        $this->request->setParam('currentStatus', 3);
        $this->request->setParam('userId', 1);
        $this->request->setParam('string', 'My todo tag2');
        $this->request->setParam('sendNotification', 1);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Todo_IndexController::ADD_TRUE_TEXT, $response, $this->errormessage);
    }

    /**
     * Test of json save Todo
     */
    public function testJsonSaveEdit()
    {
        // EDIT: First inserted item. Send notification.
        $this->setRequestUrl('Todo/index/jsonSave/');
        $this->request->setParam('id', 1);
        $this->request->setParam('title', 'My todo task MODIFIED');
        $this->request->setParam('notes', 'My note');
        $this->request->setParam('projectId', 2);
        $this->request->setParam('startDate', '2009-05-16');
        $this->request->setParam('endDate', '2009-05-17');
        $this->request->setParam('priority', 7);
        $this->request->setParam('currentStatus', 2);
        $this->request->setParam('userId', 1);
        $this->request->setParam('sendNotification', 1);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Todo_IndexController::EDIT_TRUE_TEXT, $response, $this->errormessage);
    }

    /**
     * Test of json save Todo
     */
    public function testJsonSaveEditNotification()
    {
        $this->setRequestUrl('Todo/index/jsonSave/');
        $this->request->setParam('id', 1);
        $this->request->setParam('title', 'My todo task MODIFIED');
        $this->request->setParam('notes', 'My note');
        $this->request->setParam('projectId', 2);
        $this->request->setParam('startDate', '2009-05-16');
        $this->request->setParam('endDate', '2009-05-17');
        $this->request->setParam('priority', 7);
        $this->request->setParam('currentStatus', 2);
        $this->request->setParam('userId', 1);
        $this->request->setParam('sendNotification', 1);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        // Check saved data
        $model = clone($this->_model);
        $model->find(1);
        $this->assertEquals('My todo task MODIFIED', $model->title);
        $this->assertEquals('My note', $model->notes);
        $this->assertEquals(2, $model->projectId);
        $this->assertEquals('2009-05-16', $model->startDate);
        $this->assertEquals('2009-05-17', $model->endDate);
        $this->assertEquals(7, $model->priority);
        $this->assertEquals(2, $model->currentStatus);
        $this->assertEquals(1, $model->userId);
    }

    /**
     * Test of json save Todo with wrong id
     */
    public function testJsonSaveWrongId()
    {
        // EDIT
        $this->setRequestUrl('Todo/index/jsonSave/');
        $this->request->setParam('id', 50);
        $this->request->setParam('title', 'My todo 2');
        $this->request->setParam('notes', 'My note');
        $this->request->setParam('projectId', 3);
        $this->request->setParam('startDate', '2009-05-16');
        $this->request->setParam('endDate', '2009-05-17');
        $this->request->setParam('priority', 7);
        $this->request->setParam('currentStatus', 2);
        $this->request->setParam('userId', 2);
        $this->request->setParam('nodeId', 1);
        try {
            $this->front->dispatch($this->request, $this->response);
        } catch (Zend_Controller_Action_Exception $error) {
            $this->assertEquals(404, $error->getCode());
            return;
        }

        $this->fail('Error on Edit with wrong Id');
    }

    /**
     * Test of json save multiple
     */
    public function testJsonSaveMultiple()
    {
        $this->setRequestUrl('Todo/index/jsonSaveMultiple/');
        $items = array(2 => array('title' => 'My todo task CHANGED',
                                  'projectId' => 1,
                                  'currentStatus' => 3),
                       3 => array('title' => 'My todo task 2 CHANGED',
                                  'projectId' => 1,
                                  'currentStatus' => 4));
        $this->request->setParam('data', $items);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Todo_IndexController::EDIT_MULTIPLE_TRUE_TEXT, $response, $this->errormessage);
    }

    /**
     * Test of json list todo -in fact, default json list
     */
    public function testJsonList()
    {
        $this->setRequestUrl('Todo/index/jsonList');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains($this->_listingExpectedString, $response, $this->errormessage);
        $this->assertContains('"numRows":1', $response, $this->errormessage);
    }

    /**
     * Test of json list todo -in fact, default json list
     */
    public function testJsonListWithParent()
    {
        $this->setRequestUrl('Todo/index/jsonList');
        $this->request->setParam('id', 1);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains($this->_listingExpectedString, $response, $this->errormessage);
        $this->assertContains('"numRows":1', $response, $this->errormessage);
    }
}
