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
class Phprojekt_IndexController_Test extends FrontInit
{
    /**
     * Test if the index page is displayed correctly
     */
    public function testIndexIndexAction()
    {
        $this->setRequestUrl('index/index');
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, "PHProjekt") > 0);
        $this->assertTrue(strpos($response, "<!-- template: index.phml -->") > 0);
    }

    /**
     * Test if the list json response is ok
     */
    public function testJsonListAction()
    {
        $this->setRequestUrl('Project/index/jsonList/');
        $this->request->setParam('nodeId', null);
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, '"numRows":4}') > 0);
    }

    /**
     * Test if the list json response is ok
     */
    public function testJsonListActionWithNodeId()
    {
        $this->setRequestUrl('Project/index/jsonList/');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, '"numRows":2}') > 0);
    }

    /**
     * Test of json detail model
     */
    public function testJsonDetailAction()
    {
        $this->setRequestUrl('Project/index/jsonDetail/');
        $this->request->setParam('id', 1);
        $response = $this->getResponse();
        $this->assertTrue(strpos(strtolower($response),
            strtolower('{"key":"title","label":"Title","type":"text","hint":"title","order":0,"position":1')) > 0);
        $this->assertTrue(strpos($response, '"numRows":1}') > 0);
    }

    /**
     * Test of json detail model
     */
    public function testJsonDetailActionWithoutId()
    {
        $this->setRequestUrl('Project/index/jsonDetail');
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, '[{"id":"1","name":"Invisible Root"}') > 0);
        $this->assertTrue(strpos($response, '{"id":"2","name":"....Project 1"}') > 0);
    }

    /**
     * Test of json tree
     */
    public function testJsonTreeAction()
    {
        $this->setRequestUrl('Project/index/jsonTree');
        $response = $this->getResponse();
        $this->assertTrue(strpos(strtolower($response),
            strtolower('"identifier":"id","label":"name","items":[{"name":"Invisible Root"')) > 0);
        $this->assertTrue(strpos($response, '"parent":"1","path":"/1/","children":[]}]}') > 0);
    }

    /**
     * Test of json get submodules
     */
    public function testJsonGetModulesPermission()
    {
        $this->setRequestUrl('Project/index/jsonGetModulesPermission/');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertTrue(strpos($response,
            '"name":"Note","label":"Note","inProject":true,"rights":{"none":false,') > 0);
        $this->assertTrue(strpos($response,
            '"name":"Project","label":"Project","inProject":true,"rights":{"none":false,') > 0);
        $this->assertTrue(strpos($response,
            '"name":"Todo","label":"Todo","inProject":true,"rights":{"none":false,') > 0);
    }

    /**
     * Test of json get submodules -without a project Id-
     */
    public function testJsonGetModulesPermissionNoId()
    {
        $this->setRequestUrl('Project/index/jsonGetModulesPermission/');
        $this->request->setParam('nodeId', null);
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, '&&({"metadata":[]})') > 0);
    }

    /**
     * Test of json delete project -without a project Id-
     */
    public function testJsonDeleteNoId()
    {
        $this->setRequestUrl('Project/index/jsonDelete');
        $this->getResponse();
        $this->assertTrue($this->error);
    }
}
