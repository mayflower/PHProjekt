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
 * Tests for History Controller
 *
 * @version    Release: 6.1.0
 * @group      history
 * @group      controller
 * @group      history-controller
 */
class History_IndexController_Test extends FrontInit
{
    /**
     * Test the history list
     */
    public function testJsonListAction()
    {
        $this->setRequestUrl('Core/history/jsonList/');
        $this->request->setParam('moduleId', 1);
        $this->request->setParam('itemId', 5);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $expected = '{"userId":1,"moduleId":1,"itemId":5,"field":"title","label":"Title","oldValue"';
        $this->assertContains($expected, $response);
    }

    /**
     * Test the history list providing module name instead of module id
     */
    public function testJsonLisWithModuleNametAction()
    {
        $this->setRequestUrl('Core/history/jsonList/');
        $this->request->setParam('moduleName', 'Project');
        $this->request->setParam('itemId', 5);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $expected = '{"userId":1,"moduleId":1,"itemId":5,"field":"title","label":"Title","oldValue":';
        $this->assertContains($expected, $response);
    }

    /**
     * Test the history error when no id is provided
     */
    public function testJsonListNoIdAction()
    {
        $this->setRequestUrl('Core/history/jsonList/');
        $this->request->setParam('moduleId', 2);
        $this->request->setParam('itemId', null);
        $this->request->setParam('nodeId', 1);
        $this->getResponse();
        $this->assertTrue($this->error);
    }
}
