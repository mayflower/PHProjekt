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
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    CVS: $Id:
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */
require_once 'PHPUnit/Framework.php';

/**
 * Tests for Index Controller
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Project_IndexController_Test extends FrontInit
{
    /**
     * Test of json save Project -in fact, default json save
     */
    public function testJsonSave()
    {
        $this->request->setParams(array('action' => 'jsonSave',
            'controller' => 'index', 'module' => 'Project'));
        $this->request->setBaseUrl($this->config->webpath
            . 'index.php/Project/index/jsonSave/id/null/title/test/startDate/2008-08-07/'
            . 'endDate/2020-08-31/priority/2/projectId/1');
        $this->request->setPathInfo('/Project/index/jsonSave/id/null/title/test/startDate/2008-08-07/'
            . 'endDate/2020-08-31/priority/2/projectId/1');
        $this->request->setRequestUri('/Project/index/jsonSave/id/null/title/test/startDate/2008-08-07/'
            . 'endDate/2020-08-31/priority/2/projectId/1');

        $response = $this->getResponse();
        
        $this->assertTrue(strpos($response, 'The Item was added correctly') > 0);
    }

    /**
     * Test of json save  multiple Project
     */
    public function testJsonSaveMultiple()
    {
        $this->request->setParams(array('action' => 'jsonSave',
            'controller' => 'index', 'module' => 'Project'));
        $this->request->setBaseUrl($this->config->webpath
            . 'index.php/Project/index/jsonSaveMultiple/nodeId/1/data[1][notes]/test');
        $this->request->setPathInfo('/Project/index/jsonSaveMultiple/nodeId/1/data[1][notes]/test');
        $this->request->setRequestUri('/Project/index/jsonSaveMultiple/nodeId/1/data[1][notes]/test');

        $response = $this->getResponse();
        
        $this->assertTrue(strpos($response, 'The Items was edited correctly') > 0);
    }

    /**
     * Test the get all the modules active and their relation with the projectId
     */
    public function testJsonGetModulesProjectRelation()
    {
        $this->request->setParams(array('action' => 'jsonList',
            'controller' => 'index', 'module' => 'Project'));
        $this->request->setBaseUrl($this->config->webpath
            . 'index.php/Project/index/jsonGetModulesProjectRelation/id/2');
        $this->request->setPathInfo('/Project/index/jsonGetModulesProjectRelation/id/2');
        $this->request->setRequestUri('/Project/index/jsonGetModulesProjectRelation/id/2');

        $response = $this->getResponse();

        $this->assertTrue(strpos($response, '"2":{"id":"2","name":"Todo","label":"Todo","inProject":true}') > 0);
    }

    /**
     * Test the get all the role-user relation with the projectId
     */
    public function testJsonGetProjectRoleUserRelation()
    {
        $this->request->setParams(array('action' => 'jsonList',
            'controller' => 'index', 'module' => 'Project'));
        $this->request->setBaseUrl($this->config->webpath
            . 'index.php/Project/index/jsonGetProjectRoleUserRelation/id/1');
        $this->request->setPathInfo('/Project/index/jsonGetProjectRoleUserRelation/id/1');
        $this->request->setRequestUri('/Project/index/jsonGetProjectRoleUserRelation/id/1');

        $response = $this->getResponse();
        
        $this->assertTrue(strpos($response, '{"1":{"id":"1","name":"admin",') > 0);
    }
}
