<?php
/**
 * Unit test
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
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */
require_once 'PHPUnit/Framework.php';

/**
 * Tests for Helpdesk Index Controller
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 */
class Helpdesk_IndexController_Test extends FrontInit
{
    private $_listingExpectedString = '{"key":"title","label":"Title","type":"text","hint":"","order":0,"position":1';

    private $_model = null;

    /**
     * setUp method for PHPUnit
     */
    public function setUp()
    {
        $this->_model = new Helpdesk_Models_Helpdesk();
    }

    /**
     * Test of json save Helpdesk
     */
    public function testJsonSavePart1()
    {
        // INSERT
        $this->setRequestUrl('Helpdesk/index/jsonSave/');
        $this->request->setParam('title', 'My Helpdesk task');
        $this->request->setParam('assigned', '2');
        $this->request->setParam('contactId', '0');
        $this->request->setParam('dueDate', '2009-05-30');
        $this->request->setParam('projectId', '1');
        $this->request->setParam('priority', '5');
        $this->request->setParam('attachments', '');
        $this->request->setParam('description', 'This is the description');
        $this->request->setParam('status', '1');
        $response = $this->getResponse();
        $this->assertContains(Helpdesk_IndexController::ADD_TRUE_TEXT, $response);

        // INSERT. Send notification
        $this->setRequestUrl('Helpdesk/index/jsonSave/');
        $this->request->setParam('title', 'My Helpdesk task 2');
        $this->request->setParam('assigned', '0');
        $this->request->setParam('contactId', '0');
        $this->request->setParam('dueDate', '2009-05-17');
        $this->request->setParam('projectId', '1');
        $this->request->setParam('priority', '5');
        $this->request->setParam('attachments', '');
        $this->request->setParam('description', 'This is the description 2');
        $this->request->setParam('status', '2');
        $this->request->setParam('string', '');
        $this->request->setParam('sendNotification', 'on');
        $response = $this->getResponse();
        $this->assertContains(Helpdesk_IndexController::ADD_TRUE_TEXT, $response);

        // Check that there is another new row
        $rowsAfter = count($this->_model->fetchAll());
        $this->assertEquals(2, $rowsAfter);
    }

    /**
     * Test of json save Helpdesk
     */
    public function testJsonSavePart2()
    {
        // EDIT: First inserted item. Change status to Solved. Send notification.
        $this->setRequestUrl('Helpdesk/index/jsonSave/');
        $this->request->setParam('id', 1);
        $this->request->setParam('title', 'My Helpdesk task MODIFIED');
        $this->request->setParam('assigned', '2');
        $this->request->setParam('contactId', '0');
        $this->request->setParam('dueDate', '2009-05-30');
        $this->request->setParam('projectId', '1');
        $this->request->setParam('priority', '2');
        $this->request->setParam('attachments', '');
        $this->request->setParam('description', 'This is the description MODIFIED');
        $this->request->setParam('status', '3');
        $this->request->setParam('sendNotification', 'on');
        $response = $this->getResponse();
        $this->assertContains(Helpdesk_IndexController::EDIT_TRUE_TEXT, $response);

        // Check saved data - Solved user and date should have been autocompleted
        $model = clone($this->_model);
        $model->find(1);
        $this->assertEquals('My Helpdesk task MODIFIED', $model->title);
        $this->assertEquals('This is the description MODIFIED', $model->description);
        $this->assertEquals(2, $model->priority);
        $this->assertEquals(1, $model->solvedBy);
        $this->assertEquals(date("Y-m-d"), $model->solvedDate);
        $this->assertEquals(3, $model->status);

        // EDIT: First inserted item. This call, similar to the previous one will cover another line of the Controller
        $this->setRequestUrl('Helpdesk/index/jsonSave/');
        $this->request->setParam('id', 1);
        $this->request->setParam('title', 'My Helpdesk task MODIFIED');
        $this->request->setParam('assigned', '2');
        $this->request->setParam('contactId', '0');
        $this->request->setParam('dueDate', '2009-05-30');
        $this->request->setParam('projectId', '1');
        $this->request->setParam('priority', '2');
        $this->request->setParam('attachments', '');
        $this->request->setParam('description', 'This is the description MODIFIED');
        $this->request->setParam('status', '3');
        $response = $this->getResponse();
        $this->assertContains(Helpdesk_IndexController::EDIT_TRUE_TEXT, $response);
    }

    /**
     * Test of json save Helpdesk
     */
    public function testJsonSavePart3()
    {
        // EDIT: First inserted item. Change status from Solved to Verified and 'assigned' field to 1
        $this->setRequestUrl('Helpdesk/index/jsonSave/');
        $this->request->setParam('id', 1);
        $this->request->setParam('title', 'My Helpdesk task MODIFIED');
        $this->request->setParam('assigned', '1');
        $this->request->setParam('contactId', '0');
        $this->request->setParam('dueDate', '2009-05-30');
        $this->request->setParam('projectId', '1');
        $this->request->setParam('priority', '2');
        $this->request->setParam('attachments', '');
        $this->request->setParam('description', 'This is the description MODIFIED');
        $this->request->setParam('status', '4');
        $this->request->setParam('sendNotification', 'on');
        $response = $this->getResponse();
        $this->assertContains(Helpdesk_IndexController::EDIT_TRUE_TEXT, $response);

        // Check saved data - Solved user and date should have been emptied
        $model = clone($this->_model);
        $model->find(1);
        $this->assertEquals('', $model->solvedBy);
        $this->assertEquals(4, $model->status);
    }

    /**
     * Test of json save Helpdesk with wrong id
     */
    public function testJsonSaveWrongId()
    {
        // EDIT
        $this->setRequestUrl('Helpdesk/index/jsonSave/');
        $this->request->setParam('id', 50);
        $this->request->setParam('title', 'My Helpdesk task MODIFIED');
        $this->request->setParam('assigned', '2');
        $this->request->setParam('contactId', '0');
        $this->request->setParam('dueDate', '2009-05-30');
        $this->request->setParam('projectId', '1');
        $this->request->setParam('priority', '2');
        $this->request->setParam('attachments', '');
        $this->request->setParam('description', 'This is the description MODIFIED');
        $this->request->setParam('status', '4');
        try {
            $this->front->dispatch($this->request, $this->response);
        } catch (Phprojekt_PublishedException $error) {
            $expectedErrorMsg = Phprojekt::getInstance()->translate(Helpdesk_IndexController::NOT_FOUND);
            $this->assertEquals(0, $error->getCode());
            $this->assertEquals($expectedErrorMsg, $error->message);
            return;
        }

        $this->fail('Error on Edit with wrong Id');
    }

    /**
     * Test of json save multiple
     */
    public function testJsonSaveMultiple()
    {
        $this->setRequestUrl('Helpdesk/index/jsonSaveMultiple/');
        $items = array(1 => array('title' => 'My completely new title',
                                  'status' => 3),
                       2 => array('title' => 'My completely new title 2',
                                  'status' => 4));
        $this->request->setParam('data', $items);
        $response = $this->getResponse();
        $this->assertContains(Helpdesk_IndexController::EDIT_MULTIPLE_TRUE_TEXT, $response);

        // Check saved data
        $model = clone($this->_model);
        $model->find(1);
        $this->assertEquals('My completely new title', $model->title);
        $this->assertEquals(3, $model->status);
        $this->assertEquals(1, $model->solvedBy);
        $model = clone($this->_model);
        $model->find(2);
        $this->assertEquals('My completely new title 2', $model->title);
        $this->assertEquals(4, $model->status);
    }

    /**
     * Test of json list Helpdesk -in fact, default json list
     */
    public function testJsonList()
    {
        $this->setRequestUrl('Helpdesk/index/jsonList');
        $response = $this->getResponse();
        $this->assertContains($this->_listingExpectedString, $response);
        $this->assertContains('"numRows":2', $response);

        $this->setRequestUrl('Helpdesk/index/jsonList');
        $this->request->setParam('id', 1);
        $response = $this->getResponse();
        $this->assertContains($this->_listingExpectedString, $response);
        $this->assertContains('"numRows":1', $response);
    }

    public function testJsonDetailAction()
    {
        // New item data request
        $this->setRequestUrl('Helpdesk/index/jsonDetail/');
        $response = $this->getResponse();

        $expected = '"data":[{"id":0,"title":"","rights":{"currentUser":{"moduleId":10,"itemId":0,'
            . '"userId":1,"none":false,"read":true,"write":true,"access":true,"create":true,"copy":true,'
            . '"delete":true,"download":true,"admin":true}},"author":1,"assigned":0,"date":"' . date("Y-m-d") . '",'
            . '"dueDate":"","projectId":0,"priority":0,"attachments":"","solvedBy":0,"solvedDate":"",'
            . '"description":"","status":0,"contactId":0}],"numRows":1})';
        $this->assertContains($expected, $response);

        // Existing item
        $this->setRequestUrl('Helpdesk/index/jsonDetail/');
        $this->request->setParam('id', 1);
        $response = $this->getResponse();

        $expected = '"data":[{"id":1,"title":"My completely new title","rights":{"currentUser":{"moduleId":10,'
            . '"itemId":1,"userId":1,"access":true,"none":false,"read":true,"write":true,"create":true,"copy":true,'
            . '"delete":true,"download":true,"admin":true}},"author":1,"assigned":1,'
            . '"date":"' . date("Y-m-d") . '","dueDate":"2009-05-30","projectId":1,"priority":2,"attachments":"",'
            . '"solvedBy":1,"solvedDate":"' . date("Y-m-d") . '","description":"This is the description MODIFIED",'
            . '"status":3,"contactId":0}],"numRows":1}';
        $this->assertContains($expected, $response);

        // Existing item
        $this->setRequestUrl('Helpdesk/index/jsonDetail/');
        $this->request->setParam('id', 2);
        $response = $this->getResponse();

        $expected = '"data":[{"id":2,"title":"My completely new title 2","rights":{"currentUser":{"moduleId":10,'
            . '"itemId":2,"userId":1,"access":true,"none":false,"read":true,"write":true,"create":true,"copy":true,'
            . '"delete":true,"download":true,"admin":true}},"author":1,"assigned":0,'
            . '"date":"' . date("Y-m-d") . '","dueDate":"2009-05-17","projectId":1,"priority":5,"attachments":"",'
            . '"solvedBy":0,"solvedDate":"","description":"This is the description 2","status":4,"contactId":0}],'
            . '"numRows":1}';
        $this->assertContains($expected, $response);
    }
}
