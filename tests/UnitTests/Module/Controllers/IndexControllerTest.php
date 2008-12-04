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
class Module_IndexController_Test extends FrontInit
{
    /**
     * Test of json save Module -in fact, default json save
     */
    public function testJsonSave()
    {
        $this->request->setParams(array('action' => 'jsonSave', 'controller' => 'index', 'module' => 'Module'));
        $this->request->setBaseUrl($this->config->webpath
            . 'index.php/Core/module/jsonSave/id/null/name/test/saveType/0/active/1');
        $this->request->setPathInfo('/Core/module/jsonSave/id/null/name/test/saveType/0/active/1');
        $this->request->setRequestUri('/Core/module/jsonSave/id/null/name/test/saveType/0/active/1');
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, 'The Item was added correctly') > 0);
    }

    /**
     * Test of json delete Module -in fact, default json save
     */
    public function testJsonDelete()
    {
        $this->request->setParams(array('action' => 'jsonSave', 'controller' => 'index', 'module' => 'Module'));
        $this->request->setBaseUrl($this->config->webpath . 'index.php/Core/module/jsonDelete/id/6');
        $this->request->setPathInfo('/Core/module/jsonDelete/id/6');
        $this->request->setRequestUri('/Core/module/jsonDelete/id/6');
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, 'The Item was deleted correctly') > 0);
    }

    /**
     * Test of json detail Module -in fact, default json save
     */
    public function testJsonDetail()
    {
        $this->request->setParams(array('action' => 'jsonSave', 'controller' => 'index', 'module' => 'Module'));
        $this->request->setBaseUrl($this->config->webpath . 'index.php/Core/module/jsonDetail/id/1');
        $this->request->setPathInfo('/Core/module/jsonDetail/id/1');
        $this->request->setRequestUri('/Core/module/jsonDetail/id/1');
        $response = $this->getResponse();       
        $this->assertTrue(strpos($response, '"name":"Project"') > 0);
    }
}
