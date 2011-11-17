<?php
/**
 * Unit test
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @category   PHProjekt
 * @package    UnitTests
 * @subpackage Calendar
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */


/**
 * Tests for Calendar Index Controller
 *
 * @category   PHProjekt
 * @package    UnitTests
 * @subpackage Calendar
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 * @group      calendar
 * @group      controller
 * @group      calendar-controller
 */
class Calendar_IndexController_Test extends FrontInit
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
        $this->_listingExpectedString = '{"key":"title","label":"Title","originalLabel":"Title","type":"text",'
            . '"hint":"","listPosition":1,"formPosition":1';
        $this->_model = new Calendar_Models_Calendar();
    }

    /**
     * Test of json save calendar for single events
     */
    public function testJsonSaveSinglePart1()
    {
        // INSERT: Single event - One participant. Send notification
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('title', 'test');
        $this->request->setParam('place', 'Buenos Aires');
        $this->request->setParam('notes', 'test note');
        $this->request->setParam('startDatetime', '2009-06-02 09:00:00');
        $this->request->setParam('endDatetime', '2009-06-02 10:00:00');
        $this->request->setParam('status', 1);
        $this->request->setParam('dataParticipant', 1);
        $this->request->setParam('sendNotification', 1);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::ADD_TRUE_TEXT, $response);
    }

    /**
     * Test of json save calendar for single events
     */
    public function testJsonSaveSinglePart2()
    {
        // EDIT: Last inserted event
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('id', 1);
        $this->request->setParam('title', 'test edited');
        $this->request->setParam('place', 'Bariloche');
        $this->request->setParam('notes', 'test note edited');
        $this->request->setParam('startDatetime', '2009-06-03 10:00:00');
        $this->request->setParam('endDatetime', '2009-06-03 11:00:00');
        $this->request->setParam('status', 1);
        $this->request->setParam('dataParticipant', 1);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::EDIT_TRUE_TEXT, $response);
    }

    /**
     * Test of json save calendar for single events
     */
    public function testJsonSaveSinglePart2Check()
    {
        // Check saved data
        $calendarModel = clone($this->_model);
        $calendarModel->find(1);
        $this->assertEquals('test edited', $calendarModel->title);
        $this->assertEquals('Bariloche', $calendarModel->place);
        $this->assertEquals('test note edited', $calendarModel->notes);
        $this->assertEquals('2009-06-03 10:00:00', $calendarModel->startDatetime);
        $this->assertEquals('2009-06-03 11:00:00', $calendarModel->endDatetime);
    }

    /**
     * Test of json save calendar for single events
     */
    public function testJsonSaveSinglePart3()
    {
        // Store current amount of rows
        $rowsBefore = count($this->_model->fetchAll());

        // INSERT: Single Event - Two participants
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('dataParticipantAdd', '2');
        $this->request->setParam('title', 'Single - 2 particip');
        $this->request->setParam('place', 'Buenos Aires');
        $this->request->setParam('notes', 'test note');
        $this->request->setParam('startDatetime', '2009-04-30 22:00:00');
        $this->request->setParam('endDatetime', '2009-04-30 23:00:00');
        $this->request->setParam('status', 1);
        $this->request->setParam('dataParticipant', array(2 => 2));
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::ADD_TRUE_TEXT, $response);

        // Check total amount of rows
        $rowsAfter = count($this->_model->fetchAll());
        $this->assertEquals($rowsBefore + 2, $rowsAfter);
    }

    /**
     * Test of json save calendar for single events
     */
    public function testJsonSaveSinglePart4()
    {
        // Store current amount of rows
        $rowsBefore = count($this->_model->fetchAll());

        // EDIT: Last Event - take out participant
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('id', 2);
        $this->request->setParam('title', 'Single - 2 particip');
        $this->request->setParam('place', 'Buenos Aires');
        $this->request->setParam('notes', 'test note');
        $this->request->setParam('startDatetime', '2009-04-30 22:00:00');
        $this->request->setParam('endDatetime', '2009-04-30 23:00:00');
        $this->request->setParam('status', 1);
        $this->request->setParam('participantId', 1);
        $this->request->setParam('multipleParticipants', true);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::EDIT_TRUE_TEXT, $response);

        // Check total amount of rows
        $rowsAfter = count($this->_model->fetchAll());
        $this->assertEquals($rowsBefore - 1, $rowsAfter);
    }

    /**
     * Test of json save calendar for single events
     */
    public function testJsonSaveSinglePart5()
    {
        // INSERT: Single event - One participant. WRONG Data: start date after end date
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('title', 'test');
        $this->request->setParam('place', 'Buenos Aires');
        $this->request->setParam('notes', 'test note');
        $this->request->setParam('startDatetime', '2009-06-03 09:00:00');
        $this->request->setParam('endDatetime', '2009-06-02 10:00:00');
        $this->request->setParam('status', 1);
        $this->request->setParam('dataParticipant', 1);
        $this->request->setParam('nodeId', 1);

        try {
            $this->front->dispatch($this->request, $this->response);
        } catch (Phprojekt_PublishedException $error) {
            $expected = 'Event duration: End date and time has to be after Start date and time';
            $this->assertEquals($expected, $error->getMessage());
            return;
        }
        $this->fail('Error on inserting with start date after end date');
    }

    /**
     * Test of json save calendar for single events
     */
    public function testJsonSaveSinglePart6()
    {
        // INSERT: Single event - One participant. WRONG Data: start time after end time
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('title', 'test');
        $this->request->setParam('place', 'Buenos Aires');
        $this->request->setParam('notes', 'test note');
        $this->request->setParam('startDatetime', '2009-06-02 11:00:00');
        $this->request->setParam('endDatetime', '2009-06-02 10:00:00');
        $this->request->setParam('status', 1);
        $this->request->setParam('dataParticipant', 1);
        $this->request->setParam('nodeId', 1);

        try {
            $this->front->dispatch($this->request, $this->response);
        } catch (Phprojekt_PublishedException $error) {
            $expected = 'Event duration: End date and time has to be after Start date and time';
            $this->assertEquals($expected, $error->getMessage());
            return;
        }
        $this->fail('Error on inserting with start time after end time');
    }

    /**
     * Test of json save calendar for multiple events
     */
    public function testJsonSaveMultiplePart1()
    {
        // Store current amount of rows
        $rowsBefore = count($this->_model->fetchAll());

        // INSERT: Multiple Events - Two days long - One participant
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('title', 'Multiple1');
        $this->request->setParam('place', 'Buenos Aires');
        $this->request->setParam('notes', 'test note');
        $this->request->setParam('startDatetime', '2009-05-01 09:00:00');
        $this->request->setParam('endDatetime', '2009-05-02 15:00:00');
        $this->request->setParam('projectId', 1);
        $this->request->setParam('status', 1);
        $this->request->setParam('dataParticipant', 1);
        $this->request->setParam('rrule', 'FREQ=MONTHLY;UNTIL=20090601T040000Z;INTERVAL=1;BYDAY=');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::ADD_TRUE_TEXT, $response);

        // Check total amount of rows
        $rowsAfter = count($this->_model->fetchAll());
        $this->assertEquals($rowsBefore + 2, $rowsAfter);
    }

    /**
     * Test of json save calendar for multiple events
     */
    public function testJsonSaveMultiplePart2()
    {
        // Store current amount of rows
        $rowsBefore = count($this->_model->fetchAll());

        // INSERT: Multiple Events - Two participants
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('title', 'Multiple2');
        $this->request->setParam('place', 'Buenos Aires');
        $this->request->setParam('notes', 'test note');
        $this->request->setParam('startDatetime', '2008-12-01 12:00:00');
        $this->request->setParam('endDatetime', '2008-12-01 13:00:00');
        $this->request->setParam('dataParticipant', array(2 => 2));
        $this->request->setParam('status', 1);
        $this->request->setParam('rrule', 'FREQ=DAILY;UNTIL=20081202T040000Z;INTERVAL=1;BYDAY=');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::ADD_TRUE_TEXT, $response);

        // Check total amount of rows
        $rowsAfter = count($this->_model->fetchAll());
        $this->assertEquals($rowsBefore + 4, $rowsAfter);
    }

    /**
     * Test of json save calendar for multiple events
     */
    public function testJsonSaveMultiplePart3()
    {
        // Store current amount of rows
        $where      = "parent_id = 6 OR id = 6";
        $rowsBefore = count($this->_model->fetchAll($where));

        // EDIT: last inserted events adding it another two recurrence days. Also send notification
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('id', 6);
        $this->request->setParam('title', 'Multiple2');
        $this->request->setParam('place', 'Buenos Aires');
        $this->request->setParam('notes', 'test note');
        $this->request->setParam('startDatetime', '2008-12-01 12:00:00');
        $this->request->setParam('endDatetime', '2008-12-01 13:00:00');
        $this->request->setParam('status', 1);
        $this->request->setParam('dataParticipant', array(2 => 2));
        $this->request->setParam('multipleEvents', true);
        $this->request->setParam('rrule', 'FREQ=DAILY;UNTIL=20081204T040000Z;INTERVAL=1;BYDAY=');
        $this->request->setParam('sendNotification', 1);
        $this->request->setParam('multipleParticipants', true);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::EDIT_TRUE_TEXT, $response);

        // Check total amount of rows
        $rowsAfter = count($this->_model->fetchAll($where));
        $this->assertEquals($rowsBefore + 4, $rowsAfter);
    }

    /**
     * Test of json save calendar for multiple events
     */
    public function testJsonSaveMultiplePart4()
    {
        // Store current amount of rows
        $where      = "parent_id = 6 OR id = 6";
        $rowsBefore = count($this->_model->fetchAll($where));

        // EDIT: again the same events taking it out the last recurrence
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('id', 6);
        $this->request->setParam('title', 'Multiple2');
        $this->request->setParam('place', 'Buenos Aires');
        $this->request->setParam('notes', 'test note');
        $this->request->setParam('startDatetime', '2008-12-01 12:00:00');
        $this->request->setParam('endDatetime', '2008-12-01 13:00:00');
        $this->request->setParam('status', 1);
        $this->request->setParam('dataParticipant', array(2 => 2));
        $this->request->setParam('rrule', 'FREQ=DAILY;UNTIL=20081203T040000Z;INTERVAL=1;BYDAY=');
        $this->request->setParam('multipleEvents', true);
        $this->request->setParam('multipleParticipants', true);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::EDIT_TRUE_TEXT, $response);

        // Check total amount of rows
        $rowsAfter = count($this->_model->fetchAll($where));
        $this->assertEquals($rowsBefore - 2, $rowsAfter);
    }

    /**
     * Test of json save calendar for multiple events
     */
    public function testJsonSaveMultiplePart5()
    {
        // Store current amount of rows
        $rowsBefore = count($this->_model->fetchAll());

        // INSERT: Multiple events - Two participants
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('title', 'Multiple3');
        $this->request->setParam('place', 'Buenos Aires');
        $this->request->setParam('notes', 'test note');
        $this->request->setParam('startDatetime', '2009-02-01 15:00:00');
        $this->request->setParam('endDatetime', '2009-02-01 22:00:00');
        $this->request->setParam('status', 1);
        $this->request->setParam('dataParticipant', array(2 => 2));
        $this->request->setParam('rrule', 'FREQ=WEEKLY;UNTIL=20090208T040000Z;INTERVAL=1;BYDAY=');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::ADD_TRUE_TEXT, $response);

        // Check total amount of rows
        $rowsAfter = count($this->_model->fetchAll());
        $this->assertEquals($rowsBefore + 4, $rowsAfter);
    }

    /**
     * Test of json save calendar for multiple events
     */
    public function testJsonSaveMultiplePart6()
    {
        // EDIT: Last events, just a SINGLE event of the recurrence
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('id', 14);
        $this->request->setParam('title', 'Multiple3 modified');
        $this->request->setParam('place', 'Bariloche');
        $this->request->setParam('startDatetime', '2009-02-01 16:00:00');
        $this->request->setParam('endDatetime', '2009-02-01 21:00:00');
        $this->request->setParam('status', 1);
        $this->request->setParam('dataParticipant', array(2 => 2));
        $this->request->setParam('participantId', 1);
        $this->request->setParam('multipleEvents', false);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::EDIT_TRUE_TEXT, $response);

        // Check saved data
        $calendarModel = clone($this->_model);
        $calendarModel->find(14);
        $this->assertEquals('Multiple3 modified', $calendarModel->title);
        $this->assertEquals('Bariloche', $calendarModel->place);
        $this->assertEquals('2009-02-01 16:00:00', $calendarModel->startDatetime);
        $this->assertEquals('2009-02-01 21:00:00', $calendarModel->endDatetime);

        // Check the next occurrence of same series of events: should have NOT been modified
        $calendarModel = clone($this->_model);
        $calendarModel->find(15);
        $this->assertEquals('Multiple3', $calendarModel->title);
        $this->assertEquals('Buenos Aires', $calendarModel->place);
        $this->assertEquals('2009-02-08 15:00:00', $calendarModel->startDatetime);
        $this->assertEquals('2009-02-08 22:00:00', $calendarModel->endDatetime);
    }

    /**
     * Test of json save calendar for multiple events
     */
    public function testJsonSaveMultiplePart7()
    {
        // Store current amount of rows
        $where      = "parent_id = 14 OR id = 14";
        $rowsBefore = count($this->_model->fetchAll($where));

        // EDIT: last event, take out participant
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('id', 14);
        $this->request->setParam('title', 'Multiple3');
        $this->request->setParam('place', 'Buenos Aires');
        $this->request->setParam('notes', 'test note');
        $this->request->setParam('startDatetime', '2009-02-01 15:00:00');
        $this->request->setParam('endDatetime', '2009-02-01 20:00:00');
        $this->request->setParam('status', 1);
        $this->request->setParam('participantId', 1);
        $this->request->setParam('rrule', 'FREQ=WEEKLY;UNTIL=20090208T040000Z;INTERVAL=1;BYDAY=');
        $this->request->setParam('multipleEvents', true);
        $this->request->setParam('multipleParticipants', true);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::EDIT_TRUE_TEXT, $response);

        // Check total amount of rows
        $rowsAfter = count($this->_model->fetchAll($where));
        $this->assertEquals($rowsBefore - 2, $rowsAfter);
    }

    /**
     * Test of json save calendar for multiple events
     */
    public function testJsonSaveMultiplePart8()
    {
        // Store current amount of rows
        $rowsBefore = count($this->_model->fetchAll());

        // INSERT: Multiple events - One participant
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('title', 'Multiple4');
        $this->request->setParam('place', 'Buenos Aires');
        $this->request->setParam('notes', 'test note');
        $this->request->setParam('startDatetime', '2009-03-01 08:00:00');
        $this->request->setParam('endDatetime', '2009-03-01 18:00:00');
        $this->request->setParam('status', 1);
        $this->request->setParam('participantId', 1);
        $this->request->setParam('rrule', 'FREQ=MONTHLY;UNTIL=20090901T040000Z;INTERVAL=2;BYDAY=');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::ADD_TRUE_TEXT, $response);

        // Check total amount of rows
        $rowsAfter = count($this->_model->fetchAll());
        $this->assertEquals($rowsBefore + 4, $rowsAfter);
    }

    /**
     * Test of json save calendar for multiple events
     */
    public function testJsonSaveMultiplePart9()
    {
        // Store current amount of rows
        $where      = "parent_id = 18 OR id = 18";
        $rowsBefore = count($this->_model->fetchAll($where));

        // EDIT: Last events - Add one participant
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('id', 18);
        $this->request->setParam('title', 'Multiple4');
        $this->request->setParam('place', 'Buenos Aires');
        $this->request->setParam('notes', 'test note');
        $this->request->setParam('startDatetime', '2009-03-01 08:00:00');
        $this->request->setParam('endDatetime', '2009-03-01 18:00:00');
        $this->request->setParam('status', 1);
        $this->request->setParam('participantId', 1);
        $this->request->setParam('dataParticipant', array(2 => 2));
        $this->request->setParam('rrule', 'FREQ=MONTHLY;UNTIL=20090901T040000Z;INTERVAL=2;BYDAY=');
        $this->request->setParam('multipleEvents', true);
        $this->request->setParam('multipleParticipants', true);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::EDIT_TRUE_TEXT, $response);

        // Check total amount of rows
        $rowsAfter = count($this->_model->fetchAll($where));
        $this->assertEquals($rowsBefore + 4, $rowsAfter);
    }

    /**
     * Test of json save calendar for multiple events
     */
    public function testJsonSaveMultiplePart10()
    {
        $rowsBefore = count($this->_model->fetchAll());

        // INSERT: Multiple events - Two dates in total, one participant
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('title', 'Multiple5');
        $this->request->setParam('place', 'Buenos Aires');
        $this->request->setParam('notes', 'test note');
        $this->request->setParam('startDatetime', '2009-06-12 08:00:00');
        $this->request->setParam('endDatetime', '2009-06-12 10:00:00');
        $this->request->setParam('status', 1);
        $this->request->setParam('participantId', 1);
        $this->request->setParam('rrule', 'FREQ=DAILY;UNTIL=20090613T040000Z;INTERVAL=1;BYDAY=');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::ADD_TRUE_TEXT, $response);

        // Check total amount of rows
        $rowsAfter = count($this->_model->fetchAll());
        $this->assertEquals($rowsBefore + 2, $rowsAfter);
    }

    /**
     * Test of json save calendar for multiple events
     */
    public function testJsonSaveMultiplePart11()
    {
        $rowsBefore = count($this->_model->fetchAll());

        // EDIT LAST EVENTS: Add them one extra event and also one extra participant for all events
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('id', 26);
        $this->request->setParam('title', 'Multiple5');
        $this->request->setParam('place', 'Buenos Aires');
        $this->request->setParam('notes', 'test note');
        $this->request->setParam('startDatetime', '2009-06-12 08:00:00');
        $this->request->setParam('endDatetime', '2009-06-12 10:00:00');
        $this->request->setParam('status', 1);
        $this->request->setParam('participantId', 1);
        $this->request->setParam('dataParticipant', array(2 => 2));
        $this->request->setParam('multipleEvents', true);
        $this->request->setParam('multipleParticipants', true);
        $this->request->setParam('rrule', 'FREQ=DAILY;UNTIL=20090614T040000Z;INTERVAL=1;BYDAY=');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::EDIT_TRUE_TEXT, $response);

        // Check total amount of rows
        $rowsAfter = count($this->_model->fetchAll());
        $this->assertEquals($rowsBefore + 4, $rowsAfter);
    }

    /**
     * Test of json save calendar for multiple events
     */
    public function testJsonSaveMultiplePart12()
    {
        $rowsBefore = count($this->_model->fetchAll());

        // EDIT: Take out the participant in the second of the three dates
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('id', 27);
        $this->request->setParam('title', 'Multiple5');
        $this->request->setParam('place', 'Buenos Aires');
        $this->request->setParam('notes', 'test note');
        $this->request->setParam('startDatetime', '2009-06-13 08:00:00');
        $this->request->setParam('endDatetime', '2009-06-13 10:00:00');
        $this->request->setParam('status', 1);
        $this->request->setParam('participantId', 1);
        $this->request->setParam('multipleEvents', false);
        $this->request->setParam('multipleParticipants', true);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::EDIT_TRUE_TEXT, $response);

        // Check total amount of rows
        $rowsAfter = count($this->_model->fetchAll());
        $this->assertEquals($rowsBefore - 1, $rowsAfter);
    }

    /**
     * Test of json save calendar for multiple events
     */
    public function testJsonSaveMultiplePart13()
    {
        $rowsBefore = count($this->_model->fetchAll());

        // EDIT: Decrease in 1 day the start date of all the occurrences of the last series of events just for me
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('id', 27);
        $this->request->setParam('title', 'Multiple5');
        $this->request->setParam('place', 'Buenos Aires');
        $this->request->setParam('notes', 'test note');
        $this->request->setParam('startDatetime', '2009-06-12 08:00:00');
        $this->request->setParam('endDatetime', '2009-06-12 10:00:00');
        $this->request->setParam('status', 1);
        $this->request->setParam('participantId', 1);
        $this->request->setParam('multipleEvents', true);
        $this->request->setParam('multipleParticipants', false);
        $this->request->setParam('rrule', 'FREQ=DAILY;UNTIL=20090614T040000Z;INTERVAL=1;BYDAY=');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::EDIT_TRUE_TEXT, $response);

        // Check total amount of rows
        $rowsAfter = count($this->_model->fetchAll());
        $this->assertEquals($rowsBefore + 1, $rowsAfter);
    }

    /**
     * Test the calendar event detail
     */
    public function testJsonDetailNewItem()
    {
        // New event data request
        $this->setRequestUrl('Calendar/index/jsonDetail/');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $expected = '"data":[{"id":0,"title":"","place":"","notes":"","startDatetime":"","endDatetime":"",'
            . '"visibility":0,"status":1,"participantId":0,"rrule":"","rights":{"currentUser":{'
            . '"moduleId":5,"itemId":0,"userId":1,"none":false,"read":true,"write":true,"access":true,"create":true,'
            . '"copy":true,"delete":true,"download":true,"admin":true}}}],"numRows":1})';
        $this->assertContains($expected, $response);
    }

    /**
     * Test the calendar event detail
     */
    public function testJsonDetail()
    {
        // Existing event
        $this->setRequestUrl('Calendar/index/jsonDetail/');
        $this->request->setParam('id', 1);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $expected = '"data":[{"id":1,"title":"test edited","place":"Bariloche","notes":"test note edited",'
            . '"startDatetime":"2009-06-03 10:00:00","endDatetime":"2009-06-03 11:00:00","visibility":0,"status":1,'
            . '"participantId":1,"rrule":"","rights":{"currentUser":{"moduleId":5,"itemId":1,"userId":1,"none":false,'
            . '"read":true,"write":true,"access":true,"create":true,"copy":true,"delete":true,"download":true,'
            . '"admin":true}}}],"numRows":1})';
        $this->assertContains($expected, $response);
    }

    /**
     * Test the calendar list
     */
    public function testJsonListAction()
    {
        $this->setRequestUrl('Calendar/index/jsonList/');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains($this->_listingExpectedString, $response);
        $this->assertContains('"numRows":17}', $response);

    }

    /**
     * Test the calendar list
     */
    public function testJsonListActionWithParent()
    {
        $this->setRequestUrl('Calendar/index/jsonList/');
        $this->request->setParam('id', 1);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains($this->_listingExpectedString, $response);
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
        $this->assertContains($this->_listingExpectedString, $response);
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
        $this->assertContains($this->_listingExpectedString, $response);
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
        $this->assertContains($this->_listingExpectedString, $response);
        $this->assertContains('"numRows":3}', $response);
    }

    /**
     * Test the calendar period list for an empty period
     */
    public function testJsonPeriodListActionEmptyPeriod()
    {
        $this->setRequestUrl('Calendar/index/jsonPeriodList/');
        $this->request->setParam('dateStart', '2008-07-01');
        $this->request->setParam('dateEnd', '2008-07-31');
        $response = $this->getResponse();
        $this->assertContains('({"metadata":[]}', $response);
    }

    /**
     * Test the calendar participants and related events getting
     */
    public function testGetRelatedDataAction1()
    {
        $this->setRequestUrl('Calendar/index/jsonGetRelatedData/');
        $this->request->setParam('id', 1);
        $response = $this->getResponse();
        $this->assertContains('"data":{"participants":"1","relatedEvents":""}', $response);
    }

    /**
     * Test the calendar participants and related events getting
     */
    public function testGetRelatedDataAction2()
    {
        $this->setRequestUrl('Calendar/index/jsonGetRelatedData/');
        $this->request->setParam('id', 28);
        $response = $this->getResponse();
        $this->assertContains('"data":{"participants":"1,2","relatedEvents":"26,27,32"}', $response);
    }

    /**
     * Test the calendar participants and related events getting
     */
    public function testGetRelatedDataAction3()
    {
        $this->setRequestUrl('Calendar/index/jsonGetRelatedData/');
        $this->request->setParam('id', 15);
        $response = $this->getResponse();
        $this->assertContains('"data":{"participants":"1","relatedEvents":"14"}', $response);
    }

    /**
     * Test the calendar participants and related events getting
     */
    public function testGetRelatedDataAction4()
    {
        $this->setRequestUrl('Calendar/index/jsonGetRelatedData/');
        $this->request->setParam('id', 100);
        $response = $this->getResponse();
        $this->assertContains('"data":[]', $response);
    }

    /**
     * Test the calendar deletion
     */
    public function testJsonDeleteActionSingle()
    {
        // Store current amount of rows
        $rowsBefore = count($this->_model->fetchAll());

        // Single Event
        $this->setRequestUrl('Calendar/index/jsonDelete/');
        $this->request->setParam('id', 1);
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::DELETE_TRUE_TEXT, $response);

        // Check total amount of rows
        $rowsAfter = count($this->_model->fetchAll());
        $this->assertEquals($rowsBefore - 1, $rowsAfter);
    }

    /**
     * Test the calendar deletion
     */
    public function testJsonDeleteActionMultiplePart1()
    {
        // Store current amount of rows
        $rowsBefore = count($this->_model->fetchAll());

        // Multiple Event
        $this->setRequestUrl('Calendar/index/jsonDelete/');
        $this->request->setParam('id', 4);
        $this->request->setParam('multipleEvents', true);
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::DELETE_TRUE_TEXT, $response);

        // Check total amount of rows
        $rowsAfter = count($this->_model->fetchAll());
        $this->assertEquals($rowsBefore - 2, $rowsAfter);
    }

    /**
     * Test the calendar deletion
     */
    public function testJsonDeleteActionMultiplePart2()
    {
        // Store current amount of rows
        $rowsBefore = count($this->_model->fetchAll());

        // Multiple Event - Take out all the occurrences of this series of events for participant #1
        $this->setRequestUrl('Calendar/index/jsonDelete/');
        $this->request->setParam('id', 6);
        $this->request->setParam('multipleEvents', true);
        $this->request->setParam('multipleParticipants', true);
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::DELETE_TRUE_TEXT, $response);

        // Check total amount of rows
        $rowsAfter = count($this->_model->fetchAll());
        $this->assertEquals($rowsBefore - 6, $rowsAfter);
    }

    /**
     * Test the calendar deletion with errors
     */
    public function testJsonDeleteActionWrongId()
    {
        // Store current amount of rows
        $rowsBefore = count($this->_model->fetchAll());

        $this->setRequestUrl('Calendar/index/jsonDelete/');
        $this->request->setParam('id', 111);
        try {
            $this->front->dispatch($this->request, $this->response);
        } catch (Phprojekt_PublishedException $error) {
            $this->assertEquals(0, $error->getCode());
            // Check total amount of rows
            $rowsAfter = count($this->_model->fetchAll());
            $this->assertEquals($rowsBefore, $rowsAfter);
            return;
        }

        $this->fail('Error on Delete with Wrong Id');
    }

    /**
     * Test the calendar deletion with errors
     */
    public function testJsonDeleteActionNoId()
    {
        // Store current amount of rows
        $rowsBefore = count($this->_model->fetchAll());

        $this->setRequestUrl('Calendar/index/jsonDelete/');
        try {
            $this->front->dispatch($this->request, $this->response);
        } catch (Phprojekt_PublishedException $error) {
            $this->assertEquals(0, $error->getCode());
            // Check total amount of rows
            $rowsAfter = count($this->_model->fetchAll());
            $this->assertEquals($rowsBefore, $rowsAfter);
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
        $expected = '{"id":2,"display":"Solt, Gustavo"},{"id":1,"display":"Soria Parra, David"}';
        $this->assertContains($expected, $response);
    }

    /**
     * Test of csv
     */
    public function testCsvDayListSelf()
    {
        $this->setRequestUrl('Calendar/index/csvDayListSelf/');
        $this->request->setParam('date', '2009-06-14');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains('"Title","Start","End","Status"' . "\n"
            . '"Multiple5","2009-06-14 08:00:00","2009-06-14 10:00:00","Accepted"' . "\n", $response);
    }

    /**
     * Test of csv
     */
    public function testCsvDayListSelect()
    {
        $this->setRequestUrl('Calendar/index/csvDayListSelect/');
        $this->request->setParam('date', '2009-06-14');
        $this->request->setParam('users', '1,2,3');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains('"Title","Place","Notes","Start","End","Visibility","Status","Participant",'
            . '"Recurrence rule"' . "\n"
            . '"Multiple5","Buenos Aires","test note","2009-06-14 08:00:00","2009-06-14 10:00:00","Public","Accepted",'
            . '"1","FREQ=DAILY;UNTIL=20090614T040000Z;INTERVAL=1;BYDAY="' . "\n"
            . '"Multiple5","Buenos Aires","test note","2009-06-14 08:00:00","2009-06-14 10:00:00","Public","Pending",'
            . '"2","FREQ=DAILY;UNTIL=20090614T040000Z;INTERVAL=1;BYDAY="' . "\n", $response);
    }

    /**
     * Test of csv
     */
    public function testCsvPeriodList()
    {
        $this->setRequestUrl('Calendar/index/csvPeriodList/');
        $this->request->setParam('dateStart', '2009-06-01');
        $this->request->setParam('dateEnd', '2009-06-25');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains('"Title","Place","Notes","Start","End","Visibility","Status","Participant",'
            . '"Recurrence rule"' . "\n"
            . '"Multiple5","Buenos Aires","test note","2009-06-11 08:00:00","2009-06-11 10:00:00","Public","Accepted",'
            . '"1","FREQ=DAILY;UNTIL=20090614T040000Z;INTERVAL=1;BYDAY="' . "\n"
            . '"Multiple5","Buenos Aires","test note","2009-06-12 08:00:00","2009-06-12 10:00:00","Public","Accepted",'
            . '"1","FREQ=DAILY;UNTIL=20090613T040000Z;INTERVAL=1;BYDAY="' . "\n"
            . '"Multiple5","Buenos Aires","test note","2009-06-13 08:00:00","2009-06-13 10:00:00","Public","Accepted",'
            . '"1","FREQ=DAILY;UNTIL=20090613T040000Z;INTERVAL=1;BYDAY="' . "\n"
            . '"Multiple5","Buenos Aires","test note","2009-06-14 08:00:00","2009-06-14 10:00:00","Public","Accepted",'
            . '"1","FREQ=DAILY;UNTIL=20090614T040000Z;INTERVAL=1;BYDAY="' . "\n", $response);
    }
}
