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
class Role_IndexController_Test extends FrontInit
{
    /**
     * Test the role list
     */
    public function testGetRolesAction()
    {
        $this->setRequestUrl('Core/role/jsonList');
        $response = $this->getResponse();
        $this->assertTrue(strpos(strtolower($response),
            strtolower('{"id":"1","name":"admin","rights":[]}],"numRows":1}')) > 0);
    }

    /**
     * Test the role save
     */
    public function testSaveAction()
    {
        $this->setRequestUrl('Core/role/jsonSave/');
        $this->request->setParam('name', 'test');
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, Core_RoleController::ADD_TRUE_TEXT) > 0);
    }
}
