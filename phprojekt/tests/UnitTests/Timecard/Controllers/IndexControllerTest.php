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
class Timecard_IndexController_Test extends FrontInit
{
    /**
     * Test of json Save
     */
    public function testJsonSavePart1()
    {
        // INSERT
        $this->setRequestUrl('Timecard/index/jsonSave/');
        $this->request->setParam('date', '2009-05-16');
        $this->request->setParam('startTime', '09:00');
        $this->request->setParam('endTime', '13:00');
        $response = $this->getResponse();
        $this->assertContains(Timecard_IndexController::ADD_TRUE_TEXT, $response);

        $this->request->setParam('date', '2009-05-16');
        $this->request->setParam('startTime', '14:00');
        $this->request->setParam('endTime', '18:00');
        $response = $this->getResponse();
        $this->assertContains(Timecard_IndexController::ADD_TRUE_TEXT, $response);

        // Check that the period has been added
        $this->setRequestUrl('Timecard/index/jsonDetail/');
        $this->request->setParam('date', '2009-05-16');
        $response = $this->getResponse();
        $expected = '"data":[{"id":"7","startTime":"09:00:00","rights":[],"endTime":"13:00:00"},{"id":"8","startTime":"'
            . '14:00:00","rights":[],"endTime":"18:00:00"}],"numRows":2}';
        $this->assertContains($expected, $response);
    }

    /**
     * Test of json Save
     */
    public function testJsonSavePart2()
    {
        // INSERT
        $this->setRequestUrl('Timecard/index/jsonSave/');
        $this->request->setParam('date', '2009-05-15');
        $this->request->setParam('startTime', '10:00');
        $response = $this->getResponse();
        $this->assertContains(Timecard_IndexController::ADD_TRUE_TEXT, $response);

        // Check that the period has been added
        $this->setRequestUrl('Timecard/index/jsonDetail/');
        $this->request->setParam('date', '2009-05-15');
        $response = $this->getResponse();
        $expected = '"data":[{"id":"9","startTime":"10:00:00","rights":[],"endTime":""}],"numRows":1}';
        $this->assertContains($expected, $response);
    }

    /**
     * Test of json Booking Save
     */
    public function testJsonBookingSave()
    {
        // INSERT
        $this->setRequestUrl('Timecard/index/jsonBookingSave/');
        $this->request->setParam('date', '2009-05-17');
        $this->request->setParam('amount', '02:00');
        $this->request->setParam('notes', 'My note');
        $this->request->setParam('projectId', 1);
        $response = $this->getResponse();
        $this->assertContains(Timecard_IndexController::ADD_TRUE_TEXT, $response);

        // Check that the period has been added
        $this->setRequestUrl('Timecard/index/jsonBookingDetail/');
        $this->request->setParam('date', '2009-05-17');
        $response = $this->getResponse();
        $expected = '"data":{"timecard":[],"timeproj":[{"id":"1","date":"2009-05-17","rights":[],"projectId":"1","notes'
            . '":"My note","amount":"02:00:00"}]},"numRows":2}';
        $this->assertContains($expected, $response);
    }

    /**
     * Test if the limits work
     */
    public function testJsonListAction()
    {
        $this->setRequestUrl('Timecard/index/jsonList/');
        $this->request->setParam('year', 2009);
        $this->request->setParam('month', '05');
        $this->request->setParam('view', 'month');
        $response = $this->getResponse();
        $this->assertContains('"numRows":'.date("t").'}', $response);
    }

    /**
     * Test of json Start
     */
    public function testJsonStartAction()
    {
        $this->setRequestUrl('Timecard/index/jsonStart');
        $response = $this->getResponse();
        $this->assertContains(Timecard_IndexController::ADD_TRUE_TEXT, $response);
    }

    /**
     * Test of json Stop
     */
    public function testJsonStopAction()
    {
        $this->setRequestUrl('Timecard/index/jsonStop');
        $response = $this->getResponse();
        $this->assertContains(Timecard_IndexController::ADD_TRUE_TEXT, $response);
    }

    /**
     * Test of json Stop without open record
     */
    public function testJsonStopActionNoRecordOpen()
    {
        $this->setRequestUrl('Timecard/index/jsonStop');
        $response = $this->getResponse();
        $this->assertContains(Timecard_IndexController::NOT_FOUND, $response);
    }
}
