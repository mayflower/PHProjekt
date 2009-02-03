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
 * Tests for Tag Controller
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_TagController_Test extends FrontInit
{
    /**
     * Test of json get tags
     */
    public function testJsonGetTagsAction()
    {
        $this->setRequestUrl('Default/Tag/jsonGetTags/');
        $this->request->setParam('nodeId', 1);
        $this->request->setParam('limit', 2);
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
        $this->setRequestUrl('Default/Tag/jsonSaveTags/');
        $this->request->setParam('moduleName', 'Project');
        $this->request->setParam('string', 'test');
        $this->request->setParam('id', 1);
        $this->request->setParam('projectId', 1);
        $this->getResponse();
    }

    /**
     * Test of GetModulesByTag
     */
    public function testJsonGetModulesByTagAction()
    {
        $this->setRequestUrl('Default/Tag/jsonGetModulesByTag/');
        $this->request->setParam('nodeId', 1);
        $this->request->setParam('tag', 'test');
        $this->request->setParam('limit', 2);
        $response = $this->getResponse();
        $this->assertTrue(strpos(strtolower($response),
            strtolower('{"id":"1","moduleId":"1","moduleName":"Project",'
            . '"moduleLabel":"Project","firstDisplay":"test","secondDisplay":null,"projectId":"1"}')) > 0);
    }
}
