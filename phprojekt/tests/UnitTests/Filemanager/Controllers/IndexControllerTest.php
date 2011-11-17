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
 * @subpackage Filemanager
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 */


/**
 * Tests for Index Controller
 *
 * @category   PHProjekt
 * @package    UnitTests
 * @subpackage Filemanager
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 * @group      filemanager
 * @group      controller
 * @group      filemanager-controller
 */
class Filemanager_IndexController_Test extends FrontInit
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
        parent::setUp();
        $this->_listingExpectedString = '{"key":"title","label":"Title","originalLabel":"Title","type":"text",'
            . '"hint":"","listPosition":1,"formPosition":1';
        $this->_model = new Filemanager_Models_Filemanager();
    }

    /**
     * Test of json save Filemanager -in fact, default json save
     */
    public function testJsonSaveAddPart1()
    {
        // INSERT
        $this->setRequestUrl('Filemanager/index/jsonSave/');
        $this->request->setParam('projectId', 1);
        $this->request->setParam('title', 'test title');
        $this->request->setParam('comments', 'comment test');
        $this->request->setParam('files', '966f9bfa01ec4a2a3fa6282bb8fa8d56|articles.txt');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Filemanager_IndexController::ADD_TRUE_TEXT, $response);
    }

    /**
     * Test of json save Filemanager -in fact, default json save
     */
    public function testJsonSaveEdit()
    {
        // EDIT
        $this->setRequestUrl('Filemanager/index/jsonSave/');
        $this->request->setParam('id', 1);
        $this->request->setParam('projectId', 1);
        $this->request->setParam('title', 'test title MODIF');
        $this->request->setParam('comments', 'comment test MODIF');
        $this->request->setParam('files', 'a66f9bfa01ec4a2a3fa6282bb8fa8d56|articles2.txt');
        $this->request->setParam('nodeId', 1);
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
        $this->request->setParam('nodeId', 1);
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
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains($this->_listingExpectedString, $response);
        $this->assertContains('"numRows":2', $response);
    }

    /**
     * Test of json list Filemanager -in fact, default json list
     */
    public function testJsonListWithParent()
    {
        $this->setRequestUrl('Filemanager/index/jsonList');
        $this->request->setParam('id', 1);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains($this->_listingExpectedString, $response);
        $this->assertContains('"numRows":1', $response);
    }

    /**
     * Test of json list Filemanager -in fact, default json detail
     */
    public function testJsonDetailNewItem()
    {
        // New item data request
        $this->setRequestUrl('Filemanager/index/jsonDetail/');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $expected = '"data":[{"id":0,"title":"","comments":"","projectId":0,"files":"","rights":{"currentUser":'
            . '{"moduleId":7,"itemId":0,"userId":1,"none":false,"read":true,"write":true,"access":true,"create":true,'
            . '"copy":true,"delete":true,"download":true,"admin":true}}}],"numRows":1})';
        $this->assertContains($expected, $response);
    }

    /**
     * Test of json list Filemanager -in fact, default json detail
     */
    public function testJsonDetail()
    {
        // Existing item
        $this->setRequestUrl('Filemanager/index/jsonDetail/');
        $this->request->setParam('id', 1);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $expected = '"data":[{"id":1,"title":"Test","comments":"Foobar",'
            . '"projectId":1,"files":"966f9bfa01ec4a2a3fa6282bb8fa8d56|articles.txt","rights":{"currentUser":{'
            . '"moduleId":7,"itemId":1,"userId":1,"none":false,"read":true,"write":true,"access":true,"create":true,'
            . '"copy":true,"delete":true,"download":true,"admin":true}}}],"numRows":1})';
        $this->assertContains($expected, $response);
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
