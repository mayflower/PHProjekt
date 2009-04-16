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
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Project_IndexController_Test extends FrontInit
{
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
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, Project_IndexController::ADD_TRUE_TEXT) > 0);
    }

    /**
     * Test of json save  multiple Project
     */
    public function testJsonSaveMultiple()
    {
        $this->setRequestUrl('Project/index/jsonSaveMultiple/');
        $this->request->setParam('data[1][notes]', 'test');
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, Project_IndexController::EDIT_MULTIPLE_TRUE_TEXT) > 0);
    }

    /**
     * Test the get all the modules active and their relation with the projectId
     */
    public function testJsonGetModulesProjectRelation()
    {
        $this->setRequestUrl('Project/index/jsonGetModulesProjectRelation/');
        $this->request->setParam('id', 2);
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, '"2":{"id":"2","name":"Todo","label":"Todo","inProject":true}') > 0);
    }

    /**
     * Test the get all the role-user relation with the projectId
     */
    public function testJsonGetProjectRoleUserRelation()
    {
        $this->setRequestUrl('Project/index/jsonGetProjectRoleUserRelation/');
        $this->request->setParam('id', 1);
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, '{"1":{"id":"1","name":"admin",') > 0);
    }
}
