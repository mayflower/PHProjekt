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
 * Tests for Index Controller
 *
 * @version    Release: 6.1.0
 * @group      gantt
 * @group      controller
 * @group      gantt-controller
 */
class Gantt_IndexController_Test extends FrontInit
{
    private $_listingExpectedString = null;
    private $_model                 = null;

    protected function getDataSet() {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/../../common.xml');
    }

    /**
     * setUp method for PHPUnit
     */
    public function setUp()
    {
        parent::setUp();
        $this->_listingExpectedString = '{"key":"title","label":"Title","originalLabel":"Title","type":"text",'
            . '"hint":"","listPosition":1,"formPosition":1';
        $this->_model = new Gantt_Models_Gantt();
    }

    /**
     * Test of json Get Projects for Gantt
     */
    public function testJsonGetProjectsPart1()
    {
        $this->setRequestUrl('Gantt/index/jsonGetProjects/');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();

        $expected = '"data":{"projects":[{"id":2,"level":10,"parent":1,"childs":1,"caption":"Test Project",'
            . '"start":1243850400,"end":1256947200,"startD":"01","startM":"06","startY":"2009","endD":"31","endM":"10",'
            . '"endY":"2009"},{"id":5,"level":20,"parent":2,"childs":2,"caption":"Sub Project","start":1243936800,'
            . '"end":1248998400,"startD":"02","startM":"06","startY":"2009","endD":"31","endM":"07","endY":"2009"}],'
            . '"rights":{"currentUser":{"write":true,"2":true,"5":true}},"min":1230768000,"max":1262217600,'
            . '"step":365}})';

        $this->assertContains($expected, $response, 'Response was: ' . $response);
    }

    public function testJsonGetProjectsPart2() {
        $this->setRequestUrl('Gantt/index/jsonGetProjects/');
        $this->request->setParam('nodeId', 5);
        $response = $this->getResponse();

        $expected = '"data":{"projects":[{"id":5,"level":0,"parent":0,"childs":2,'
            . '"caption":"Sub Project","start":1243936800,"end":1248998400,'
            . '"startD":"02","startM":"06","startY":"2009","endD":"31",'
            . '"endM":"07","endY":"2009"}],"rights":{"currentUser":'
            . '{"write":false,"5":true}},"min":1230768000,"max":1262217600,"step":365}})';

        $this->assertContains($expected, $response, 'Response was: ' . $response);
    }

    /**
     * Test of json save Gantt
     */
    public function testJsonSave()
    {
        // EDIT two projects
        $this->setRequestUrl('Gantt/index/jsonSave/');
        $projects = array('5,2009-06-01,2009-06-25',
                          '13,2008-01-01,2008-12-31');
        $this->request->setParam('projects', $projects);
        $this->request->setParam('nodeId', 5);
        $response = $this->getResponse();
        $this->assertContains(Gantt_IndexController::EDIT_MULTIPLE_TRUE_TEXT, $response);
    }
}
