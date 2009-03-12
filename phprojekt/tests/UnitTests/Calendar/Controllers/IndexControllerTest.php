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
        $this->request->setParam('startTime', strtotime('09:00'));
        $this->request->setParam('endTime', strtotime('10:00'));
        $this->request->setParam('dataParticipant', 1);
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, Calendar_IndexController::ADD_TRUE_TEXT) > 0);

        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('id', 1);
        $this->request->setParam('projectId', 1);
        $this->request->setParam('title', 'test');
        $this->request->setParam('notes', 'test note');
        $this->request->setParam('startDate', '2008-06-02');
        $this->request->setParam('startTime', strtotime('09:00'));
        $this->request->setParam('endTime', strtotime('10:00'));
        $this->request->setParam('dataParticipant', 1);
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, Calendar_IndexController::EDIT_TRUE_TEXT) > 0);

        // Multiple Events
        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('rrule', 'FREQ=DAILY;UNTIL=20081202T040000Z;INTERVAL=1');
        $this->request->setParam('startDate', '2008-12-01');
        $this->request->setParam('startTime', strtotime('02:00'));
        $this->request->setParam('endTime', strtotime('03:00'));
        $this->request->setParam('title', 'Multiple');
        $this->request->setParam('projectId', 1);
        $this->request->setParam('dataParticipant', array(1,2));
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, Calendar_IndexController::ADD_TRUE_TEXT) > 0);

        $this->setRequestUrl('Calendar/index/jsonSave/');
        $this->request->setParam('id', 4);
        $this->request->setParam('rrule', 'FREQ=DAILY;UNTIL=20081202T040000Z;INTERVAL=1');
        $this->request->setParam('startDate', '2008-12-01');
        $this->request->setParam('startTime', strtotime('02:00'));
        $this->request->setParam('endTime', strtotime('03:00'));
        $this->request->setParam('title', 'Multiple');
        $this->request->setParam('projectId', 1);
        $this->request->setParam('dataParticipant', array(1,2));
        $this->request->setParam('multipleEvents', true);
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, Calendar_IndexController::EDIT_TRUE_TEXT) > 0);
    }

    /**
     * Test the calendar event detail
     */
    public function testJsonDetailAction()
    {
        $this->setRequestUrl('Calendar/index/jsonDetail/');
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, '"numRows":1') > 0);

        $this->setRequestUrl('Calendar/index/jsonDetail/');
        $this->request->setParam('id', 1);
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, '"numRows":1') > 0);
    }

    /**
     * Test the calendar list
     */
    public function testJsonListAction()
    {
        $this->setRequestUrl('Calendar/index/jsonList/');
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, '"numRows":2}') > 0);

        $this->setRequestUrl('Calendar/index/jsonList/');
        $this->request->setParam('id', 1);
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, '"numRows":1}') > 0);
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
        $this->assertTrue(strpos($response, Calendar_IndexController::DELETE_TRUE_TEXT) > 0);

        // Multiple Event
        $this->setRequestUrl('Calendar/index/jsonDelete/');
        $this->request->setParam('id', 3);
        $this->request->setParam('multipleEvents', true);
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, Calendar_IndexController::DELETE_TRUE_TEXT) > 0);
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
}
