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
 * Tests for Tag Controller
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_TagController_Test extends FrontInit
{
    /**
     * Test of json get tags
     */
    public function testJsonGetTagsAction()
    {
        $this->request->setParams(array('action' => 'jsonGetTags', 'controller' => 'Tag', 'module' => 'Default'));
        $this->request->setBaseUrl($this->config->webpath . 'index.php/Default/Tags/jsonGetTags/nodeId/1/limit/2');
        $this->request->setPathInfo('/Default/Tag/jsonGetTags/nodeId/1/limit/2');
        $this->request->setRequestUri('/Default/Tag/jsonGetTags/nodeId/1/limit/2');
        $response = $this->getResponse();
        $this->assertTrue(strpos(strtolower($response),
            strtolower('"metadata":[{"key":"string","label":"Tag"},'
            . '{"key":"count","label":"Count"}],"data":[{"string":"this","count":3}')) > 0);
    }

    /**
     * Test of GetModulesByTag
     */
    public function testJsonSaveTagsAction()
    {
        $this->request->setParams(array('action' => 'jsonGetTags', 'controller' => 'Tag', 'module' => 'Default'));
        $this->request->setBaseUrl($this->config->webpath
            . 'index.php/Default/Tags/jsonSaveTags/moduleName/Project/string/test/id/1/projectId/1');
        $this->request->setPathInfo('/Default/Tag/jsonSaveTags/moduleName/Project/string/test/id/1/projectId/1');
        $this->request->setRequestUri('/Default/Tag/jsonSaveTags/moduleName/Project/string/test/id/1/projectId/1');
        $this->getResponse();
    }

    /**
     * Test of GetModulesByTag
     */
    public function testJsonGetModulesByTagAction()
    {
        $this->request->setParams(array('action' => 'jsonGetTags', 'controller' => 'Tag', 'module' => 'Default'));
        $this->request->setBaseUrl($this->config->webpath
            . 'index.php/Default/Tags/jsonGetModulesByTag/nodeId/1/tag/test/limit/2');
        $this->request->setPathInfo('/Default/Tag/jsonGetModulesByTag/nodeId/1/tag/test/limit/2');
        $this->request->setRequestUri('/Default/Tag/jsonGetModulesByTag/nodeId/1/tag/test/limit/2');
        $response = $this->getResponse();
        $this->assertTrue(strpos(strtolower($response),
            strtolower('{"id":"1","moduleId":"1","moduleName":"Project",'
            . '"moduleLabel":"Project","firstDisplay":"test","secondDisplay":null,"projectId":"1"}')) > 0);
    }
}
