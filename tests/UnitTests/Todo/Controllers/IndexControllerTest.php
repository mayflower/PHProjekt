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
 * Tests for Todo Index Controller
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Todo_IndexController_Test extends FrontInit
{
    /**
     * Test of json list todoe -in fact, default json list
     */
    public function testJsonList()
    {
        $this->request->setParams(array('action' => 'jsonList', 'controller' => 'index', 'module' => 'Todo'));
        $this->request->setBaseUrl($this->config->webpath . 'index.php/Todo/index/jsonList');
        $this->request->setPathInfo('/Todo/index/jsonList');
        $this->request->setRequestUri('/Todo/index/jsonList');
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, '"numRows":1') > 0);
    }
}
