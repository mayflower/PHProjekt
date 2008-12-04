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
 * Tests for History Controller
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class History_IndexController_Test extends FrontInit
{
    /**
     * Test the history list
     */
    public function testJsonListAction()
    {
        $this->request->setParams(array('action' => 'jsonList', 'controller' => 'index', 'module' => 'History'));
        $this->request->setBaseUrl($this->config->webpath . 'index.php/Core/history/jsonList/moduleId/1/itemId/5');
        $this->request->setPathInfo('/Core/history/jsonList/moduleId/1/itemId/5');
        $this->request->setRequestUri('/Core/history/jsonList/moduleId/1/itemId/5');
        $response = $this->getResponse();       
        $this->assertTrue(strpos(strtolower($response),
            strtolower('{"userId":"1","moduleId":"1","itemId":"5","field":"title","oldValue"')) > 0);
    }

    /**
     * Test the history list providing module name instead of module id
     */
    public function testJsonLisWithModuleNametAction()
    {
        $this->request->setParams(array('action' => 'jsonList', 'controller' => 'index', 'module' => 'History'));
        $this->request->setBaseUrl($this->config->webpath
            . 'index.php/Core/history/jsonList/moduleName/Project/itemId/5');
        $this->request->setPathInfo('/Core/history/jsonList/moduleName/Project/itemId/5');
        $this->request->setRequestUri('/Core/history/jsonList/moduleName/Project/itemId/5');
        $response = $this->getResponse();        
        $this->assertTrue(strpos(strtolower($response),
            strtolower('{"userId":"1","moduleId":"1","itemId":"5","field":"title","oldValue":')) > 0);
    }

    /**
     * Test the history error when no id is provided
     */
    public function testJsonListNoIdAction()
    {
        $this->request->setParams(array('action' => 'jsonList', 'controller' => 'index', 'module' => 'History'));
        $this->request->setBaseUrl($this->config->webpath . 'index.php/Core/history/jsonList/moduleId/2/itemId/');
        $this->request->setPathInfo('/Core/history/jsonList/moduleId/2/itemId/');
        $this->request->setRequestUri('/Core/history/jsonList/moduleId/2/itemId/');
        $this->getResponse();        
        $this->assertTrue($this->error);
    }
}
