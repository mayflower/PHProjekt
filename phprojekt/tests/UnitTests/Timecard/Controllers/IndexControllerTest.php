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
 * @group      timecard
 * @group      controller
 * @group      timecard-controller
 */
class Timecard_IndexController_Test extends FrontInit
{
    /**
     * Test of json Save
     */
    public function testJsonSaveNotFound()
    {
        // No startime with empty table. Will return error
        $this->setRequestUrl('Timecard/index/jsonSave/');
        $this->request->setParam('date', '2009-05-16');
        $this->request->setParam('endTime', '13:00');
        $response = $this->getResponse();
        $this->assertContains(Timecard_IndexController::NOT_FOUND, $response);
    }

    /**
     * Test of json Save
     */
    public function testJsonSaveCommon()
    {
        // INSERT. Defined start and end time.
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
        $expected = '"data":[{"id":7,"startTime":"09:00:00","rights":[],"endTime":"13:00:00"},{"id":8,'
            . '"startTime":"14:00:00","rights":[],"endTime":"18:00:00"}],"numRows":2}';
        $this->assertContains($expected, $response);
    }

    /**
     * Test of json Save
     */
    public function testJsonSaveJustStart()
    {
        // INSERT. Just defined start time.
        $this->setRequestUrl('Timecard/index/jsonSave/');
        $this->request->setParam('date', date("Y-m-d"));
        $this->request->setParam('startTime', '10:00');
        $response = $this->getResponse();
        $this->assertContains(Timecard_IndexController::ADD_TRUE_TEXT, $response);

        // Check that the period has been added
        $this->setRequestUrl('Timecard/index/jsonDetail/');
        $this->request->setParam('date', date("Y-m-d"));
        $response = $this->getResponse();
        $expected = '"data":[{"id":9,"startTime":"10:00:00","rights":[],"endTime":""}],"numRows":1}';
        $this->assertContains($expected, $response);
    }

    /**
     * Test of json Save
     */
    public function testJsonSaveJustEnd()
    {
        // INSERT. Just defined end time.
        $this->setRequestUrl('Timecard/index/jsonSave/');
        $this->request->setParam('date', date("Y-m-d"));
        $this->request->setParam('endTime', '19:00');
        $response = $this->getResponse();
        $this->assertContains(Timecard_IndexController::ADD_TRUE_TEXT, $response);

        // Check that the period has been modified
        $this->setRequestUrl('Timecard/index/jsonDetail/');
        $this->request->setParam('date', date("Y-m-d"));
        $response = $this->getResponse();
        $expected = '"data":[{"id":9,"startTime":"10:00:00","rights":[],"endTime":"19:00:00"}],"numRows":1}';
        $this->assertContains($expected, $response);
    }

    /**
     * Test of json Save
     */
    public function testJsonSaveEdit()
    {
        // INSERT. Sending id
        $this->setRequestUrl('Timecard/index/jsonSave/');
        $this->request->setParam('id', 7);
        $this->request->setParam('date', '2009-05-16');
        $this->request->setParam('startTime', '10:30');
        $this->request->setParam('endTime', '12:30');
        $response = $this->getResponse();
        $this->assertContains(Timecard_IndexController::EDIT_TRUE_TEXT, $response);

        // Check that the period has been modified
        $this->setRequestUrl('Timecard/index/jsonDetail/');
        $this->request->setParam('date', '2009-05-16');
        $response = $this->getResponse();
        $expected = '"data":[{"id":7,"startTime":"10:30:00","rights":[],"endTime":"12:30:00"},{"id":8,'
            . '"startTime":"14:00:00","rights":[],"endTime":"18:00:00"}],"numRows":2})';
        $this->assertContains($expected, $response);
    }

    /**
     * Test of json Save
     */
    public function testJsonSaveOutOfSchedule()
    {
        // Try to INSERT a very early period. Returns nothing here
        $this->setRequestUrl('Timecard/index/jsonSave/');
        $this->request->setParam('date', '2009-05-16');
        $this->request->setParam('startTime', '5:30');
        $this->request->setParam('endTime', '12:30');
        $response = $this->getResponse();
        $this->assertEquals('', $response);

        // Try to INSERT a very late period. Returns nothing here
        $this->setRequestUrl('Timecard/index/jsonSave/');
        $this->request->setParam('date', '2009-05-16');
        $this->request->setParam('startTime', '15:30');
        $this->request->setParam('endTime', '22:30');
        $response = $this->getResponse();
        $this->assertEquals('', $response);
    }

    /**
     * Test of json Save
     */
    public function testJsonSaveOverlapping()
    {
        // Try to INSERT an overlapping period. Returns nothing here
        $this->setRequestUrl('Timecard/index/jsonSave/');
        $this->request->setParam('date', '2009-05-16');
        $this->request->setParam('startTime', '10:00');
        $this->request->setParam('endTime', '12:00');
        $response = $this->getResponse();
        $this->assertEquals('', $response);
    }

    /**
     * Test of json Save
     */
    public function testJsonSaveJustStartOverlapping()
    {
        // Try to INSERT an overlapping period just with Start time. Returns nothing here
        $this->setRequestUrl('Timecard/index/jsonSave/');
        $this->request->setParam('date', '2009-05-16');
        $this->request->setParam('startTime', '11:00');
        $response = $this->getResponse();
        $this->assertEquals('', $response);
    }

    /**
     * Test of json Save
     */
    public function testJsonSaveJustEndOverlapping()
    {
        // Try to INSERT an overlapping period just with End time. Returns nothing here
        $this->setRequestUrl('Timecard/index/jsonSave/');
        $this->request->setParam('date', '2009-05-16');
        $this->request->setParam('startTime', '09:00');
        $response = $this->getResponse();
        $this->assertContains(Timecard_IndexController::ADD_TRUE_TEXT, $response);

        $this->request->setParam('date', '2009-05-16');
        $this->request->setParam('endTime', '18:00');
        $response = $this->getResponse();
        $this->assertEquals('', $response);
    }

    /**
     * Test of json Save
     */
    public function testJsonSaveTooEarly()
    {
        // Try to INSERT a very early event. Returns nothing here
        $this->setRequestUrl('Timecard/index/jsonSave/');
        $this->request->setParam('date', '2009-05-16');
        $this->request->setParam('startTime', '5:30');
        $this->request->setParam('endTime', '12:30');
        $response = $this->getResponse();
        $this->assertEquals('', $response);
    }

    /**
     * Test of json Save
     */
    public function testJsonSaveWrongStartTime()
    {
        // Try to INSERT a period with wrong start time. Returns nothing here
        $this->setRequestUrl('Timecard/index/jsonSave/');
        $this->request->setParam('date', '2009-05-20');
        $this->request->setParam('startTime', '11:60');
        $response = $this->getResponse();
        $this->assertEquals('', $response);
    }

    /**
     * Test of json Save
     */
    public function testJsonSaveStartAfterEndTime()
    {
        // Try to INSERT a period with start time after end time. Returns nothing here
        $this->setRequestUrl('Timecard/index/jsonSave/');
        $this->request->setParam('date', '2009-05-21');
        $this->request->setParam('startTime', '17:00');
        $this->request->setParam('endTime', '08:00');
        $response = $this->getResponse();
        $this->assertEquals('', $response);
    }

    /**
     * Test of json Save
     */
    public function testJsonSaveEndTimeInvalid()
    {
        // Try to INSERT a period with wrong end time. Returns nothing here
        $this->setRequestUrl('Timecard/index/jsonSave/');
        $this->request->setParam('date', '2009-05-21');
        $this->request->setParam('startTime', '17:00');
        $this->request->setParam('endTime', '18:60');
        $response = $this->getResponse();
        $this->assertEquals('', $response);
    }

    /**
     * Test of json Delete -in fact default jsonDelete
     */
    public function testJsonDelete()
    {
        $this->setRequestUrl('Timecard/index/jsonDelete');
        $this->request->setParam('id', '9');
        $response = $this->getResponse();
        $this->assertContains(Calendar_IndexController::DELETE_TRUE_TEXT, $response);
    }

    /**
     * Test of json Booking Save
     */
    public function testJsonBookingSaveInsert()
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
        $expected = '"data":{"timecard":[],"timeproj":[{"id":1,"date":"2009-05-17","rights":[],"projectId":1,'
            . '"notes":"My note","amount":"02:00:00"}]},"numRows":1}';
        $this->assertContains($expected, $response);
    }

    /**
     * Test of json Booking Save
     */
    public function testJsonBookingSaveModify()
    {
        // INSERT
        $this->setRequestUrl('Timecard/index/jsonBookingSave/');
        $this->request->setParam('id', '1');
        $this->request->setParam('date', '2009-05-17');
        $this->request->setParam('amount', '01:00');
        $this->request->setParam('notes', 'My note 2');
        $this->request->setParam('projectId', 1);
        $response = $this->getResponse();
        $this->assertContains(Timecard_IndexController::EDIT_TRUE_TEXT, $response);

        // Check that the period has been modified
        $this->setRequestUrl('Timecard/index/jsonBookingDetail/');
        $this->request->setParam('date', '2009-05-17');
        $response = $this->getResponse();
        $expected = '"data":{"timecard":[],"timeproj":[{"id":1,"date":"2009-05-17","rights":[],"projectId":1,'
            . '"notes":"My note 2","amount":"01:00:00"}]},"numRows":1}';
        $this->assertContains($expected, $response);
    }

    /**
     * Test of json Booking Delete
     */
    public function testJsonBookingDelete()
    {
        $this->setRequestUrl('Timecard/index/jsonBookingDelete/');
        $this->request->setParam('id', '1');
        $response = $this->getResponse();
        $this->assertContains(Timecard_IndexController::DELETE_TRUE_TEXT, $response);

        // Check that the period has been deleted
        $this->setRequestUrl('Timecard/index/jsonBookingDetail/');
        $this->request->setParam('date', '2009-05-17');
        $response = $this->getResponse();
        $expected = '"data":{"timecard":[],"timeproj":[]},"numRows":0}';
        $this->assertContains($expected, $response);
    }

    /**
     * Test of json Booking Delete
     */
    public function testJsonBookingDeleteEmptyId()
    {
        $this->setRequestUrl('Timecard/index/jsonBookingDelete/');
        $this->getResponse();

        $this->assertTrue($this->error);
        $this->assertContains(Timecard_IndexController::ID_REQUIRED_TEXT, $this->errormessage);
    }

    /**
     * Test of json Booking Delete
     */
    public function testJsonBookingDeleteWrongId()
    {
        $this->setRequestUrl('Timecard/index/jsonBookingDelete/');
        $this->request->setParam('id', '50');
        $this->getResponse();

        $this->assertTrue($this->error);
        $this->assertContains(Timecard_IndexController::NOT_FOUND, $this->errormessage);
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
        $this->assertContains('"numRows":' . date("t") . '}', $response);
    }

    /**
     * Test of json Start
     */
    public function testJsonStartAction()
    {
        $this->setRequestUrl('Timecard/index/jsonStart');
        $response = $this->getResponse();
        $hour     = date("G");
        if ($hour < 21 && $hour >= 8) {
            $this->assertContains(Timecard_IndexController::ADD_TRUE_TEXT, $response);

            // Check that the period has been added
            $this->setRequestUrl('Timecard/index/jsonDetail/');
            $this->request->setParam('date', date('Y-m-d'));
            $response = $this->getResponse();
            // We don't expect the complete string containing date('Y-m-d') because the seconds usually change
            $expected = '"data":[{"id":11,"startTime":"';
            $this->assertContains($expected, $response);

            $expected = '","rights":[],"endTime":""}],"numRows":1}';
            $this->assertContains($expected, $response);
        } else {
            $this->assertTrue($this->error, "Based on server time ($hour h UTC), starting work should have failed.");
            $this->assertContains('Start time has to be between 8:00 and 21:00', $this->errormessage);
        }
    }

    /**
     * Test of json Stop
     */
    public function testJsonStopAction()
    {
        $this->setRequestUrl('Timecard/index/jsonStop');
        $response = $this->getResponse();
        $hour     = date("G");
        if ($hour < 21 && $hour >= 8) {
            $this->assertContains(Timecard_IndexController::ADD_TRUE_TEXT, $response);
        } else {
            $this->assertContains('The Item was not found', $response,
                "Based on server time ($hour h UTC), stopping work should have failed.");
        }
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

    /**
     * Test of json Favorites Get
     */
    public function testJsonGetFavoritesProjectsEmpty()
    {
        // Will return empty data
        $this->setRequestUrl('Timecard/index/jsonGetFavoritesProjects/');
        $response = $this->getResponse();
        $this->assertContains('{}&&({"metadata":[]})', $response);
    }

    /**
     * Test of json Favorites Save
     */
    public function testJsonFavoritesSave()
    {
        // INSERT
        $this->setRequestUrl('Timecard/index/jsonFavoritesSave/');
        $favorites = array(0 => 1,
                           1 => 2);
        $this->request->setParam('favorites', $favorites);
        $response = $this->getResponse();
        $this->assertContains(Timecard_IndexController::EDIT_TRUE_TEXT, $response);
    }

    /**
     * Test of json Favorites Get
     */
    public function testJsonGetFavoritesProjects()
    {
        // INSERT
        $this->setRequestUrl('Timecard/index/jsonGetFavoritesProjects/');
        $response = $this->getResponse();
        $this->assertContains('{}&&([1,2])', $response);
    }

}
