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
 * Tests for Index Controller
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 */
class Filemanager_IndexController_Test extends FrontInit
{
    private $_listingExpectedString = '{"key":"title","label":"Title","type":"text","hint":"","order":0,"position":1';

    private $_model = null;

    /**
     * setUp method for PHPUnit
     */
    public function setUp()
    {
        $this->_model = new Filemanager_Models_Filemanager();
    }

    /**
     * Test of json save Filemanager -in fact, default json save
     */
    public function testJsonSavePart1()
    {
        // INSERT
        $this->setRequestUrl('Filemanager/index/jsonSave/');
        $this->request->setParam('projectId', 2);
        $this->request->setParam('title', 'test title');
        $this->request->setParam('comments', 'comment test');
        $this->request->setParam('category', 'my category');
        $this->request->setParam('files', '966f9bfa01ec4a2a3fa6282bb8fa8d56|articles.txt');
        $response = $this->getResponse();
        $this->assertContains(Filemanager_IndexController::ADD_TRUE_TEXT, $response);

        // INSERT
        $this->setRequestUrl('Filemanager/index/jsonSave/');
        $this->request->setParam('projectId', 1);
        $this->request->setParam('title', 'test title 2');
        $this->request->setParam('comments', 'comment test 2');
        $this->request->setParam('files', 'tdyrgdbfa01ec4a2a3fa6282bb8fa8d5|stuff.txt');
        $response = $this->getResponse();
        $this->assertContains(Filemanager_IndexController::ADD_TRUE_TEXT, $response);

        // Check that there are two rows in total
        $rowsAfter = count($this->_model->fetchAll());
        $this->assertEquals(2, $rowsAfter);
    }

    /**
     * Test of json save Filemanager -in fact, default json save
     */
    public function testJsonSavePart2()
    {
        // EDIT
        $this->setRequestUrl('Filemanager/index/jsonSave/');
        $this->request->setParam('id', 1);
        $this->request->setParam('projectId', 1);
        $this->request->setParam('title', 'test title MODIF');
        $this->request->setParam('comments', 'comment test MODIF');
        $this->request->setParam('files', 'a66f9bfa01ec4a2a3fa6282bb8fa8d56|articles2.txt');
        $response = $this->getResponse();
        $this->assertContains(Filemanager_IndexController::EDIT_TRUE_TEXT, $response);

        // Check saved data
        $model = clone($this->_model);
        $model->find(1);
        $this->assertEquals('test title MODIF', $model->title);
        $this->assertEquals('comment test MODIF', $model->comments);
        $this->assertEquals('a66f9bfa01ec4a2a3fa6282bb8fa8d56|articles2.txt', $model->files);
    }

    /**
     * Test of json save multiple Filemanager -in fact, default jsonSaveMultipleAction
     */
    public function testJsonSaveMultiple()
    {
        $this->setRequestUrl('Filemanager/index/jsonSaveMultiple/');
        $items = array(1 => array('title' => 'test title MODIFIED AGAIN',
                                  'comments' => 'comment test MODIFIED AGAIN'),
                       2 => array('title' => 'test title 2 MODIFIED',
                                  'comments' => 'comment test 2 MODIFIED'));
        $this->request->setParam('data', $items);
        $response = $this->getResponse();
        $this->assertContains(Filemanager_IndexController::EDIT_MULTIPLE_TRUE_TEXT, $response);

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
     * Test of json list Filemanager -in fact, default json list
     */
    public function testJsonList()
    {
        $this->setRequestUrl('Filemanager/index/jsonList');
        $response = $this->getResponse();
        $this->assertContains($this->_listingExpectedString, $response);
        $this->assertContains('"numRows":2', $response);

        $this->setRequestUrl('Filemanager/index/jsonList');
        $this->request->setParam('id', 1);
        $response = $this->getResponse();
        $this->assertContains($this->_listingExpectedString, $response);
        $this->assertContains('"numRows":1', $response);
    }

    /**
     * Test of json list Filemanager -in fact, default json detail
     */
    public function testJsonDetailAction()
    {
        // New item data request
        $this->setRequestUrl('Filemanager/index/jsonDetail/');
        $response = $this->getResponse();
        $expectedContent = '"data":[{"id":null,"title":"","rights":{"currentUser":{"moduleId":"7","itemId":null,"userId'
             . '":1,"none":false,"read":true,"write":true,"access":true,"create":true,"copy":true,"delete":true,"downlo'
             . 'ad":true,"admin":true}},"comments":"","projectId":"","category":"","files":""}],"numRows":1})';
        $this->assertContains($expectedContent, $response);

        // Existing item
        $this->setRequestUrl('Filemanager/index/jsonDetail/');
        $this->request->setParam('id', 1);
        $response = $this->getResponse();
        $expectedContent = '"data":[{"id":"1","title":"test title MODIFIED AGAIN","rights":{"currentUser":{"module_id":'
            . '"7","item_id":"1","user_id":"1","access":true,"moduleId":"7","itemId":"1","userId":"1","none":false,"rea'
            . 'd":true,"write":true,"create":true,"copy":true,"delete":true,"download":true,"admin":true}},"comments":"'
            . 'comment test MODIFIED AGAIN","projectId":"1","category":"my category","files":"a66f9bfa01ec4a2a3fa6282bb'
            . '8fa8d56|articles2.txt"}],"numRows":1})';
        $this->assertContains($expectedContent, $response);
    }

    /**
     * Test the Filemanager deletion -in fact, default jsonDeleteAction
     */
    public function testJsonDeleteAction()
    {
        // Store current amount of rows
        $rowsBefore = count($this->_model->fetchAll());

        $this->setRequestUrl('Filemanager/index/jsonDelete/');
        $this->request->setParam('id', 1);
        $response = $this->getResponse();
        $this->assertContains(Filemanager_IndexController::DELETE_TRUE_TEXT, $response);

        // Check that there is one less row
        $rowsAfter = count($this->_model->fetchAll());
        $this->assertEquals($rowsBefore - 1, $rowsAfter);
    }

    /**
     * Test the Filemanager deletion -in fact, default jsonDeleteAction
     */
    public function testJsonDeleteActionWrongId()
    {
        $this->setRequestUrl('Filemanager/index/jsonDelete/');
        $this->request->setParam('id', 50);
        try {
            $this->front->dispatch($this->request, $this->response);
        } catch (Phprojekt_PublishedException $error) {
            $expectedErrorMsg = Phprojekt::getInstance()->translate(Filemanager_IndexController::NOT_FOUND);
            $this->assertEquals(0, $error->getCode());
            $this->assertEquals($expectedErrorMsg, $error->message);
            return;
        }

        $this->fail('Error on Delete with wrong Id');
    }
}
