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
 * Tests for Calendar Index Controller
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Calendar_IndexController_Test extends FrontInit
{
    /**
     * Test of json save calendar
     */
    public function testJsonSave()
    {
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('projectId', 1);
        $this->request->setParam('title', 'test');
        $this->request->setParam('notes', 'test note');
        $this->request->setParam('startDate', '2008-06-02');
        $this->request->setParam('endDate', '2008-06-02');
        $this->request->setParam('startTime', strtotime('09:00'));
        $this->request->setParam('endTime', strtotime('10:00'));
        $this->request->setParam('dataParticipant', 1);
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::ADD_TRUE_TEXT, $response);

        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('id', 1);
        $this->request->setParam('projectId', 1);
        $this->request->setParam('title', 'test');
        $this->request->setParam('notes', 'test note');
        $this->request->setParam('startDate', '2008-06-02');
        $this->request->setParam('endDate', '2008-06-02');
        $this->request->setParam('startTime', strtotime('09:00'));
        $this->request->setParam('endTime', strtotime('10:00'));
        $this->request->setParam('dataParticipant', 1);
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::EDIT_TRUE_TEXT, $response);

        // Multiple Events
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('rrule', 'FREQ=DAILY;UNTIL=20081202T040000Z;INTERVAL=1');
        $this->request->setParam('startDate', '2008-12-01');
        $this->request->setParam('endDate', '2008-12-01');
        $this->request->setParam('startTime', strtotime('02:00'));
        $this->request->setParam('endTime', strtotime('03:00'));
        $this->request->setParam('title', 'Multiple');
        $this->request->setParam('projectId', 1);
        $this->request->setParam('dataParticipant', array(1,2));
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::ADD_TRUE_TEXT, $response);

        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('id', 4);
        $this->request->setParam('rrule', 'FREQ=DAILY;UNTIL=20081202T040000Z;INTERVAL=1');
        $this->request->setParam('startDate', '2008-12-01');
        $this->request->setParam('endDate', '2008-12-01');
        $this->request->setParam('startTime', strtotime('02:00'));
        $this->request->setParam('endTime', strtotime('03:00'));
        $this->request->setParam('title', 'Multiple');
        $this->request->setParam('projectId', 1);
        $this->request->setParam('dataParticipant', array(1,2));
        $this->request->setParam('multipleEvents', true);
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::EDIT_TRUE_TEXT, $response);
    }

    /**
     * Test the calendar event detail
     */
    public function testJsonDetailAction()
    {
        $this->setRequestUrl('Calendar/index/jsonDetail/');
        $response = $this->getResponse();
        $this->assertContains('"numRows":1', $response);

        $this->setRequestUrl('Calendar/index/jsonDetail/');
        $this->request->setParam('id', 1);
        $response = $this->getResponse();
        $this->assertContains('"numRows":1', $response);
    }

    /**
     * Test the calendar list
     */
    public function testJsonListAction()
    {
        $this->setRequestUrl('Calendar/index/jsonList/');
        $response = $this->getResponse();
        $this->assertContains('"numRows":2}', $response);

        $this->setRequestUrl('Calendar/index/jsonList/');
        $this->request->setParam('id', 1);
        $response = $this->getResponse();
        $this->assertContains('"numRows":1}', $response);
    }

    /**
     * Test the calendar day list self
     */
    public function testJsonDayListSelfAction()
    {
        $this->setRequestUrl('Calendar/index/jsonDayListSelf/');
        $this->request->setParam('date', '2008-12-01');
        $response = $this->getResponse();
        $this->assertContains('"numRows":1}', $response);
    }

    /**
     * Test the calendar day list select
     */
    public function testJsonDayListSelectAction()
    {
        $this->setRequestUrl('Calendar/index/jsonDayListSelect/');
        $this->request->setParam('date', '2008-12-01');
        $this->request->setParam('users', '1,2');
        $response = $this->getResponse();
        $this->assertContains('"numRows":2}', $response);
    }

    /**
     * Test the calendar period list
     */
    public function testJsonPeriodListAction()
    {
        $this->setRequestUrl('Calendar/index/jsonPeriodList/');
        $this->request->setParam('dateStart', '2008-12-01');
        $this->request->setParam('dateEnd', '2008-12-31');
        $response = $this->getResponse();
        $this->assertContains('"numRows":2}', $response);
    }

    /**
     * Test the calendar participants getting
     */
    public function testGetParticipantsAction()
    {
        $this->setRequestUrl('Calendar/index/jsonGetParticipants/');
        $this->request->setParam('id', 4);
        $response = $this->getResponse();
        $this->assertContains('"data":"1,2"', $response);
    }

    /**
     * Test the calendar deletion
     */
    public function testJsonDeleteAction()
    {
        // Single Event
        $this->setRequestUrl('Calendar/index/jsonDelete/');
        $this->request->setParam('id', 1);
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::DELETE_TRUE_TEXT, $response);

        // Multiple Event
        $this->setRequestUrl('Calendar/index/jsonDelete/');
        $this->request->setParam('id', 3);
        $this->request->setParam('multipleEvents', true);
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::DELETE_TRUE_TEXT, $response);
    }

    /**
     * Test the calendar deletion with errors
     */
    public function testJsonDeleteActionWrongId()
    {
        $this->setRequestUrl('Calendar/index/jsonDelete/');
        $this->request->setParam('id', 111);
        try {
            $this->front->dispatch($this->request, $this->response);
        } catch (Phprojekt_PublishedException $error) {
            $this->assertEquals(0, $error->getCode());
            return;
        }

        $this->fail('Error on Delete with Wrong Id');
    }

    /**
     * Test the calendar deletion with errors
     */
    public function testJsonDeleteActionNoId()
    {
        $this->setRequestUrl('Calendar/index/jsonDelete/');
        try {
            $this->front->dispatch($this->request, $this->response);
        } catch (Phprojekt_PublishedException $error) {
            $this->assertEquals(0, $error->getCode());
            return;
        }

        $this->fail('Error on Delete without Id');
    }

    /**
     * Test the calendar specific users getting
     */
    public function testGetSpecificUsersAction()
    {
        $this->setRequestUrl('Calendar/index/jsonGetSpecificUsers/');
        $this->request->setParam('users', '1,2');
        $response = $this->getResponse();
        $this->assertContains('"numRows":2', $response);
    }
}
