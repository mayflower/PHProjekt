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
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 */
class Gantt_IndexController_Test extends FrontInit
{
    private $_listingExpectedString = '{"key":"title","label":"Title","type":"text","hint":"","order":0,"position":1';

    private $_model = null;

    /**
     * setUp method for PHPUnit
     */
    public function setUp()
    {
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
        $expected = '"data":{"rights":{"currentUser":{"write":true}},"projects":[{"id":"2","level":10,"parent":"1","chi'
            . 'lds":1,"caption":"Project 1","start":1243850400,"end":1256947200,"startD":"01","startM":"06","startY":"2'
            . '009","endD":"31","endM":"10","endY":"2009"},{"id":"5","level":20,"parent":"2","childs":0,"caption":"Test'
            . ' Project","start":1249120800,"end":1256947200,"startD":"01","startM":"08","startY":"2009","endD":"31","e'
            . 'ndM":"10","endY":"2009"},{"id":"12","level":10,"parent":"1","childs":0,"caption":"test","start":12181032'
            . '00,"end":1598832000,"startD":"07","startM":"08","startY":"2008","endD":"31","endM":"08","endY":"2020"}],'
            . '"min":1199145600,"max":1609372800,"step":4749}})';
        $this->assertContains($response, $expected);
        $this->assertContains($expected, $response);
    }

    public function testJsonGetProjectsPart2() {
        $this->setRequestUrl('Gantt/index/jsonGetProjects/');
        $this->request->setParam('nodeId', 5);
        $response = $this->getResponse();
        $expected = '"data":{"rights":{"currentUser":{"write":true}},"projects":[{"id":"5","level":0,"parent":0,"childs'
            . '":0,"caption":"Test Project","start":1249120800,"end":1256947200,"startD":"01","startM":"08","startY":"2'
            . '009","endD":"31","endM":"10","endY":"2009"}],"min":1230768000,"max":1262217600,"step":365}})';
        $this->assertContains($response, $expected);
        $this->assertContains($expected, $response);
    }

    /**
     * Test of json save Gantt
     */
    public function testJsonSave()
    {
        // EDIT two projects
        $this->setRequestUrl('Gantt/index/jsonSave/');
        $projects = array('5,2009-06-01,2009-06-25',
                          '12,2008-01-01,2008-12-31');
        $this->request->setParam('projects', $projects);
        $response = $this->getResponse();
        $this->assertContains(Gantt_IndexController::EDIT_MULTIPLE_TRUE_TEXT, $response);

        // Verify it
        $this->setRequestUrl('Gantt/index/jsonGetProjects/');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $expected = '"data":{"rights":{"currentUser":{"write":true}},"projects":[{"id":"2","level":10,"parent":"1","chi'
            . 'lds":1,"caption":"Project 1","start":1243850400,"end":1256947200,"startD":"01","startM":"06","startY":"2'
            . '009","endD":"31","endM":"10","endY":"2009"},{"id":"5","level":20,"parent":"2","childs":0,"caption":"Test'
            . ' Project","start":1243850400,"end":1245888000,"startD":"01","startM":"06","startY":"2009","endD":"25","e'
            . 'ndM":"06","endY":"2009"},{"id":"12","level":10,"parent":"1","childs":0,"caption":"test","start":11991816'
            . '00,"end":1230681600,"startD":"01","startM":"01","startY":"2008","endD":"31","endM":"12","endY":"2008"}],'
            . '"min":1199145600,"max":1262217600,"step":731}})';
        $this->assertContains($expected, $response);
    }
}
