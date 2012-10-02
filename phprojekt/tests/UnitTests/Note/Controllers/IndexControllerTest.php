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
 * @version    Release: 6.1.0
 */


/**
 * Tests for Index Controller
 *
 * @version    Release: 6.1.0
 * @group      note
 * @group      controller
 * @group      note-controller
 */
class Note_IndexController_Test extends FrontInit
{
    private $_listingExpectedString = null;
    private $_model                 = null;

    protected function getDataSet() {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/../../common.xml');
    }

    /**
     * setUp method for PHPUnit
     */
    public function setUp()
    {
        $this->_listingExpectedString = '{"key":"title","label":"Title","originalLabel":"Title","type":"text",'
            . '"hint":"","listPosition":1,"formPosition":1';
        $this->_model = new Note_Models_Note();
    }

    /**
     * Test of json save Note -in fact, default json save
     */
    public function testJsonSavePart1()
    {
        // INSERT
        $this->setRequestUrl('Note/index/jsonSave/');
        $this->request->setParam('projectId', 1);
        $this->request->setParam('title', 'test title');
        $this->request->setParam('comments', 'comment test');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Note_IndexController::ADD_TRUE_TEXT, $response);
    }

    /**
     * Test of json save Note -in fact, default json save
     */
    public function testJsonSavePart2()
    {
        // INSERT
        $this->setRequestUrl('Note/index/jsonSave/');
        $this->request->setParam('projectId', 1);
        $this->request->setParam('title', 'test title 2');
        $this->request->setParam('comments', 'comment test 2');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Note_IndexController::ADD_TRUE_TEXT, $response);

        // Check that there are two rows total
        $rowsAfter = count($this->_model->fetchAll());
        $this->assertEquals(2, $rowsAfter);
    }

    /**
     * Test of json save Note -in fact, default json save
     */
    public function testJsonSaveEdit()
    {
        // EDIT
        $this->setRequestUrl('Note/index/jsonSave/');
        $this->request->setParam('id', 1);
        $this->request->setParam('projectId', 1);
        $this->request->setParam('title', 'test title MODIFIED');
        $this->request->setParam('comments', 'comment test MODIFIED');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Note_IndexController::EDIT_TRUE_TEXT, $response);

        // Check saved data
        $model = clone($this->_model);
        $model->find(1);
        $this->assertEquals('test title MODIFIED', $model->title);
        $this->assertEquals('comment test MODIFIED', $model->comments);
    }

    /**
     * Test of json save multiple Note -in fact, default jsonSaveMultipleAction
     */
    public function testJsonSaveMultiple()
    {
        $this->setRequestUrl('Note/index/jsonSaveMultiple/');
        $items = array(1 => array('title' => 'test title MODIFIED AGAIN',
                                  'comments' => 'comment test MODIFIED AGAIN'),
                       2 => array('title' => 'test title 2 MODIFIED',
                                  'comments' => 'comment test 2 MODIFIED'));
        $this->request->setParam('data', $items);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Note_IndexController::EDIT_MULTIPLE_TRUE_TEXT, $response);

        // Check saved data
        $model = clone($this->_model);
        $model->find(1);
        $this->assertEquals('test title MODIFIED AGAIN', $model->title);
        $this->assertEquals('comment test MODIFIED AGAIN', $model->comments);

        $model = clone($this->_model);
        $model->find(2);
        $this->assertEquals('test title 2 MODIFIED', $model->title);
        $this->assertEquals('comment test 2 MODIFIED', $model->comments);
    }

    /**
     * Test of json list Note -in fact, default json list
     */
    public function testJsonList()
    {
        $this->setRequestUrl('Note/index/jsonList');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains($this->_listingExpectedString, $response);
        $this->assertContains('"numRows":2', $response);
    }

    /**
     * Test of json list Note -in fact, default json list
     */
    public function testJsonListWithParent()
    {
        $this->setRequestUrl('Note/index/jsonList');
        $this->request->setParam('id', 1);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains($this->_listingExpectedString, $response);
        $this->assertContains('"numRows":1', $response);
    }

    /**
     * Test of json list Note -in fact, default json detail
     */
    public function testJsonDetailNewItem()
    {
        // New item data request
        $this->setRequestUrl('Note/index/jsonDetail/');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $expected = '"data":[{"id":0,"title":"","comments":"","projectId":0,"rights":{"currentUser":{"moduleId":3,'
            . '"itemId":0,"userId":1,"none":false,"read":true,"write":true,"access":true,"create":true,"copy":true,'
            . '"delete":true,"download":true,"admin":true}}}],"numRows":1})';
        $this->assertContains($expected, $response);
    }

    /**
     * Test of json list Note -in fact, default json detail
     */
    public function testJsonDetail()
    {
        // Existing item
        $this->setRequestUrl('Note/index/jsonDetail/');
        $this->request->setParam('id', 1);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $expected = '"data":[{"id":1,"title":"test title MODIFIED AGAIN","comments":"comment test MODIFIED AGAIN",'
            . '"projectId":1,"rights":{"currentUser":{"moduleId":3,"itemId":1,"userId":1,"none":false,"read":true,'
            . '"write":true,"access":true,"create":true,"copy":true,"delete":true,"download":true,"admin":true}}}],'
            . '"numRows":1})';
        $this->assertContains($expected, $response);
    }

    /**
     * Test the note deletion -in fact, default jsonDeleteAction
     */
    public function testJsonDeleteAction()
    {
        // Store current amount of rows
        $rowsBefore = count($this->_model->fetchAll());

        $this->setRequestUrl('Note/index/jsonDelete/');
        $this->request->setParam('id', 1);
        $response = $this->getResponse();
        $this->assertContains(Note_IndexController::DELETE_TRUE_TEXT, $response);

        // Check that there is one less row
        $rowsAfter = count($this->_model->fetchAll());
        $this->assertEquals($rowsBefore - 1, $rowsAfter);
    }

    /**
     * Test the note deletion -in fact, default jsonDeleteAction
     */
    public function testJsonDeleteActionWrongId()
    {
        $this->setRequestUrl('Note/index/jsonDelete/');
        $this->request->setParam('id', 50);
        try {
            $this->front->dispatch($this->request, $this->response);
        } catch (Zend_Controller_Action_Exception $error) {
            $expectedErrorMsg = Phprojekt::getInstance()->translate(Note_IndexController::NOT_FOUND);
            $this->assertEquals(0, $error->getCode());
            $this->assertEquals($expectedErrorMsg, $error->message);
            return;
        }

        $this->fail('Error on Delete with wrong Id');
    }
}
