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
 * @group      role
 * @group      controller
 * @group      role-controller
 */
class Role_IndexController_Test extends FrontInit
{
    protected function getDataSet() {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/../../common.xml');
    }

    /**
     * Test the role list
     */
    public function testGetRolesAction()
    {
        $this->setRequestUrl('Core/role/jsonList');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains('{"id":1,"name":"Admin","rights":[]}],"numRows":1}', $response);
    }

    /**
     * Test the role save
     */
    public function testSaveAction()
    {
        $this->setRequestUrl('Core/role/jsonSave/');
        $this->request->setParam('name', 'test');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Core_RoleController::ADD_TRUE_TEXT, $response);
    }
}
