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
 * @group      project
 * @group      controller
 * @group      project-controller
 */
class Project_IndexController_Test extends FrontInit
{
    protected function getDataSet() {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/../../common.xml');
    }

    /**
     * Test of json save Project -in fact, default json save
     */
    public function testJsonSave()
    {
        $this->setRequestUrl('Project/index/jsonSave/');
        $this->request->setParam('id', null);
        $this->request->setParam('title', 'test');
        $this->request->setParam('startDate', '2008-08-07');
        $this->request->setParam('endDate', '2020-08-31');
        $this->request->setParam('priority', 2);
        $this->request->setParam('projectId', 1);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Project_IndexController::ADD_TRUE_TEXT, $response);
    }

    /**
     * Test of json save  multiple Project
     */
    public function testJsonSaveMultiple()
    {
        $this->setRequestUrl('Project/index/jsonSaveMultiple/');
        $this->request->setParam('data[1][notes]', 'test');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Project_IndexController::EDIT_MULTIPLE_TRUE_TEXT, $response);
    }

    /**
     * Test the get all the modules active and their relation with the projectId
     */
    public function testJsonGetModulesProjectRelation()
    {
        $this->setRequestUrl('Project/index/jsonGetModulesProjectRelation/');
        $this->request->setParam('id', 2);
        $response = $this->getResponse();
        $this->assertContains('"2":{"id":2,"name":"Todo","label":"Todo","inProject":true}', $response);
    }

    /**
     * Test the get all the role-user relation with the projectId
     */
    public function testJsonGetProjectRoleUserRelation()
    {
        $this->setRequestUrl('Project/index/jsonGetProjectRoleUserRelation/');
        $this->request->setParam('id', 1);
        $response = $this->getResponse();
        $this->assertContains('{"1":{"id":1,"name":"Admin",', $response);
    }

    /**
     * Test the multiple save with error
     */
    public function testJsonSaveMultipleError()
    {
        $this->setRequestUrl('Project/index/jsonSaveMultiple');
        $items = array(2 => array('projectId' => '2'));
        $this->request->setParam('data', $items);
        $this->request->setParam('nodeId', 1);
        $response = FrontInit::phprJsonToArray($this->getResponse());
        $expected = array(
            'type' => 'error',
            'message' => 'ID 2. Parent: The project can not be saved under itself',
            'id' => 2
        );
        $this->assertEquals($expected, $response);
    }
}
