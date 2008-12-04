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
class Note_IndexController_Test extends FrontInit
{
    /**
     * Test of json save Note -in fact, default json save
     */
    public function testJsonSave()
    {
        $this->request->setParams(array('action' => 'jsonSave', 'controller' => 'index', 'module' => 'Note'));
        $this->request->setBaseUrl($this->config->webpath
            . 'index.php/Note/index/jsonSave/id/null/comments/test/projectId/1/title/test');
        $this->request->setPathInfo('/Note/index/jsonSave/id/null/comments/test/projectId/1/title/test');
        $this->request->setRequestUri('/Note/index/jsonSave/id/null/comments/test/projectId/1/title/test');
        $response = $this->getResponse();       
        $this->assertTrue(strpos($response, 'The Item was added correctly') > 0);
    }

    /**
     * Test of json save  multiple Note
     */
    public function testJsonSaveMultiple()
    {
        $this->request->setParams(array('action' => 'jsonSave', 'controller' => 'index', 'module' => 'Note'));
        $this->request->setBaseUrl($this->config->webpath
            .'index.php/Note/index/jsonSaveMultiple/nodeId/1/data[1][comments]/test save multiple');
        $this->request->setPathInfo('/Note/index/jsonSaveMultiple/nodeId/1/data[1][comments]/test save multiple');
        $this->request->setRequestUri('/Note/index/jsonSaveMultiple/nodeId/1/data[1][comments]/test save multiple');
        $response = $this->getResponse();       
        $this->assertTrue(strpos($response, 'The Items was edited correctly') > 0);
    }

    /**
     * Test the note deletion
     */
    public function testJsonDeleteAction()
    {
        $this->request->setParams(array('action' => 'jsonList', 'controller' => 'index', 'module' => 'Note'));
        $this->request->setBaseUrl($this->config->webpath . 'index.php/Note/index/jsonDelete/id/1');
        $this->request->setPathInfo('/Note/index/jsonDelete/id/1');
        $this->request->setRequestUri('/Note/index/jsonDelete/id/1');
        $response = $this->getResponse();        
        $this->assertTrue(strpos($response, 'The Item was deleted correctly') > 0);
    }
}
