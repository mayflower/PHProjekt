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
     * Test of json save calendar for single events
     */
    public function testJsonSave_Single()
    {
        // INSERT: Single event - One participant. Send notification
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('title', 'test');
        $this->request->setParam('notes', 'test note');
        $this->request->setParam('startDate', '2009-06-02');
        $this->request->setParam('endDate', '2009-06-02');
        $this->request->setParam('startTime', strtotime('09:00'));
        $this->request->setParam('endTime', strtotime('10:00'));
        $this->request->setParam('dataParticipant', 1);
        $this->request->setParam('sendNotification', 'on');
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::ADD_TRUE_TEXT, $response);

        // EDIT: Last inserted event
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('id', 1);
        $this->request->setParam('title', 'test');
        $this->request->setParam('notes', 'test note');
        $this->request->setParam('startDate', '2009-06-02');
        $this->request->setParam('endDate', '2009-06-02');
        $this->request->setParam('startTime', strtotime('09:00'));
        $this->request->setParam('endTime', strtotime('10:00'));
        $this->request->setParam('dataParticipant', 1);
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::EDIT_TRUE_TEXT, $response);

        // INSERT: Single Event - Two participants
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('dataParticipantAdd', '2');
        $this->request->setParam('title', 'Single - 2 particip');
        $this->request->setParam('startDate', '2009-04-30');
        $this->request->setParam('endDate', '2008-04-30');
        $this->request->setParam('startTime', strtotime('22:00'));
        $this->request->setParam('endTime', strtotime('23:00'));
        $this->request->setParam('dataParticipant', array(2 => 2));
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::ADD_TRUE_TEXT, $response);

        // EDIT: Last Event - take out participant
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('id', 2);
        $this->request->setParam('title', 'Single - 2 particip');
        $this->request->setParam('startDate', '2009-04-30');
        $this->request->setParam('endDate', '2008-04-30');
        $this->request->setParam('startTime', strtotime('22:00'));
        $this->request->setParam('endTime', strtotime('23:00'));
        $this->request->setParam('participantId', 1);
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::ADD_TRUE_TEXT, $response);
    }

    /**
     * Test of json save calendar for multiple events
     */
    public function testJsonSave_Multiple()
    {
        // INSERT: Multiple Events - Two days long - One participant
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('title', 'Multiple1');
        $this->request->setParam('startDate', '2009-05-01');
        $this->request->setParam('endDate', '2009-05-02');
        $this->request->setParam('startTime', strtotime('09:00'));
        $this->request->setParam('endTime', strtotime('15:00'));
        $this->request->setParam('projectId', 1);
        $this->request->setParam('dataParticipant', 1);
        $this->request->setParam('rrule', 'FREQ=MONTHLY;UNTIL=20090601T040000Z;INTERVAL=1;BYDAY=');
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::ADD_TRUE_TEXT, $response);

        // INSERT: Multiple Events - Two participants
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('title', 'Multiple2');
        $this->request->setParam('startDate', '2008-12-01');
        $this->request->setParam('endDate', '2008-12-01');
        $this->request->setParam('startTime', strtotime('12:00'));
        $this->request->setParam('endTime', strtotime('13:00'));
        $this->request->setParam('dataParticipant', array(2 => 2));
        $this->request->setParam('rrule', 'FREQ=DAILY;UNTIL=20081202T040000Z;INTERVAL=1;BYDAY=');
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::ADD_TRUE_TEXT, $response);

        // EDIT: last inserted events adding it another two recurrence days. Also send notification
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('id', 6);
        $this->request->setParam('title', 'Multiple2');
        $this->request->setParam('startDate', '2008-12-01');
        $this->request->setParam('endDate', '2008-12-01');
        $this->request->setParam('startTime', strtotime('12:00'));
        $this->request->setParam('endTime', strtotime('13:00'));
        $this->request->setParam('dataParticipant', array(2 => 2));
        $this->request->setParam('multipleEvents', true);
        $this->request->setParam('rrule', 'FREQ=DAILY;UNTIL=20081204T040000Z;INTERVAL=1;BYDAY=');
        $this->request->setParam('sendNotification', 'on');
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::EDIT_TRUE_TEXT, $response);

        // EDIT: again the same events taking it out the last recurrence
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('id', 6);
        $this->request->setParam('title', 'Multiple2');
        $this->request->setParam('startDate', '2008-12-01');
        $this->request->setParam('endDate', '2008-12-01');
        $this->request->setParam('startTime', strtotime('12:00'));
        $this->request->setParam('endTime', strtotime('13:00'));
        $this->request->setParam('participantId', 1);
        $this->request->setParam('multipleEvents', true);
        $this->request->setParam('rrule', 'FREQ=DAILY;UNTIL=20081203T040000Z;INTERVAL=1;BYDAY=');
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::EDIT_TRUE_TEXT, $response);

        // INSERT: Multiple events - Two participants
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('title', 'Multiple3');
        $this->request->setParam('startDate', '2009-02-01');
        $this->request->setParam('endDate', '2009-02-01');
        $this->request->setParam('startTime', strtotime('15:00'));
        $this->request->setParam('endTime', strtotime('20:00'));
        $this->request->setParam('dataParticipant', array(2 => 2));
        $this->request->setParam('rrule', 'FREQ=WEEKLY;UNTIL=20090208T040000Z;INTERVAL=1;BYDAY=');
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::ADD_TRUE_TEXT, $response);

        // EDIT: Last events, just a SINGLE event of the recurrence
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('id', 14);
        $this->request->setParam('title', 'Multiple3 !!');
        $this->request->setParam('startDate', '2009-02-01');
        $this->request->setParam('endDate', '2009-02-01');
        $this->request->setParam('startTime', strtotime('15:00'));
        $this->request->setParam('endTime', strtotime('20:00'));
        $this->request->setParam('dataParticipant', array(2 => 2));
        $this->request->setParam('participantId', 1);
        $this->request->setParam('multipleEvents', false);
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::EDIT_TRUE_TEXT, $response);

        // EDIT: last event, take out participant
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('id', 14);
        $this->request->setParam('title', 'Multiple3');
        $this->request->setParam('startDate', '2009-02-01');
        $this->request->setParam('endDate', '2009-02-01');
        $this->request->setParam('startTime', strtotime('15:00'));
        $this->request->setParam('endTime', strtotime('20:00'));
        $this->request->setParam('participantId', 1);
        $this->request->setParam('multipleEvents', true);
        $this->request->setParam('rrule', 'FREQ=WEEKLY;UNTIL=20090208T040000Z;INTERVAL=1;BYDAY=');
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::EDIT_TRUE_TEXT, $response);

        // INSERT: Multiple events - One participant
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('title', 'Multiple4');
        $this->request->setParam('startDate', '2009-03-01');
        $this->request->setParam('endDate', '2009-03-01');
        $this->request->setParam('startTime', strtotime('08:00'));
        $this->request->setParam('endTime', strtotime('18:00'));
        $this->request->setParam('participantId', 1);
        $this->request->setParam('rrule', 'FREQ=MONTHLY;UNTIL=20090901T040000Z;INTERVAL=2;BYDAY=');
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::ADD_TRUE_TEXT, $response);

        // EDIT: Last events - Add one participant
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('id', 18);
        $this->request->setParam('title', 'Multiple4');
        $this->request->setParam('startDate', '2009-03-01');
        $this->request->setParam('endDate', '2009-03-01');
        $this->request->setParam('startTime', strtotime('08:00'));
        $this->request->setParam('endTime', strtotime('18:00'));
        $this->request->setParam('participantId', 1);
        $this->request->setParam('dataParticipant', array(2 => 2));
        $this->request->setParam('multipleEvents', true);
        $this->request->setParam('rrule', 'FREQ=MONTHLY;UNTIL=20090901T040000Z;INTERVAL=2;BYDAY=');
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::ADD_TRUE_TEXT, $response);
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
        $this->assertContains('"numRows":13}', $response);

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
        $this->assertContains('{"key":"title","label":"Title","type":"text","hint":"","order":0,"position":1',
            $response);
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
        $this->assertContains('{"key":"title","label":"Title","type":"text","hint":"","order":0,"position":1',
            $response);
        $this->assertContains('"numRows":1}', $response);
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
        $this->assertContains('{"key":"title","label":"Title","type":"text","hint":"","order":0,"position":1',
            $response);
        $this->assertContains('"numRows":3}', $response);
    }

    /**
     * Test the calendar period list for an empty period
     */
    public function testJsonPeriodListAction_emptyPeriod()
    {
        $this->setRequestUrl('Calendar/index/jsonPeriodList/');
        $this->request->setParam('dateStart', '2008-07-01');
        $this->request->setParam('dateEnd', '2008-07-31');
        $response = $this->getResponse();
        $this->assertContains('({"metadata":[]}', $response);
    }

    /**
     * Test the calendar participants getting
     */
    public function testGetParticipantsAction()
    {
        $this->setRequestUrl('Calendar/index/jsonGetParticipants/');
        $this->request->setParam('id', 1);
        $response = $this->getResponse();
        $this->assertContains('"data":"1"', $response);

        $this->request->setParam('id', 2);
        $response = $this->getResponse();
        $this->assertContains('"data":"1"', $response);

        $this->request->setParam('id', 18);
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
        $this->assertContains('{"key":"username","label":"Username","type":"text","hint":"","order":0,'
            . '"position":1', $response);
        $this->assertContains('"numRows":2', $response);
    }
}
