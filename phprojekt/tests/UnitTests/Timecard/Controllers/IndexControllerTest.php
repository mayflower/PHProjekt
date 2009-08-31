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
    public function testJsonSaveCommon()
    {
        // INSERT. Defined start and end time.
        $this->setRequestUrl('Timecard/index/jsonSave/');
        $this->request->setParam('date', '2009-05-16');
        $this->request->setParam('startTime', '09:00');
        $this->request->setParam('endTime', '13:00');
        $this->request->setParam('notes', 'My note');
        $this->request->setParam('projectId', 1);
        $response = $this->getResponse();
        $this->assertContains(Timecard_IndexController::ADD_TRUE_TEXT, $response);

        $this->request->setParam('date', '2009-05-16');
        $this->request->setParam('startTime', '14:00');
        $this->request->setParam('endTime', '18:00');
        $this->request->setParam('notes', 'My note');
        $this->request->setParam('projectId', 1);
        $response = $this->getResponse();
        $this->assertContains(Timecard_IndexController::ADD_TRUE_TEXT, $response);

        // Check that the period has been added
        $this->setRequestUrl('Timecard/index/jsonDayList/');
        $this->request->setParam('date', '2009-05-16');
        $response = $this->getResponse();
        $expected = '"data":[{"id":"7","projectId":"1","startTime":"09:00:00","endTime":"13:00:00",'
            . '"display":"Invisible Root"},{"id":"8","projectId":"1","startTime":"14:00:00","endTime":"18:00:00",'
            . '"display":"Invisible Root"}]';
        $this->assertContains($expected, $response);
    }

    /**
     * Test of json Save
     */
    public function testJsonSaveJustStart()
    {
        // INSERT. Just defined start time.
        $this->setRequestUrl('Timecard/index/jsonSave/');
        $this->request->setParam('date', '2009-07-02');
        $this->request->setParam('startTime', '10:00');
        $this->request->setParam('endTime', '');
        $this->request->setParam('notes', 'My note');
        $this->request->setParam('projectId', 1);
        $response = $this->getResponse();
        $this->assertContains(Timecard_IndexController::ADD_TRUE_TEXT, $response);

        // Check that the period has been added
        $this->setRequestUrl('Timecard/index/jsonDayList/');
        $this->request->setParam('date', '2009-07-02');
        $response = $this->getResponse();
        $expected = '{"id":"9","projectId":"1","startTime":"10:00:00","endTime":null,"display":"Invisible Root"}';
        $this->assertContains($expected, $response);
    }

    /**
     * Test of json Save
     */
    public function testJsonSaveJustEnd()
    {
        // INSERT. Just defined end time.
        $this->setRequestUrl('Timecard/index/jsonSave/');
        $this->request->setParam('id', 9);
        $this->request->setParam('date', '2009-07-02');
        $this->request->setParam('startTime', '');
        $this->request->setParam('endTime', '19:00');
        $this->request->setParam('notes', 'My note');
        $this->request->setParam('projectId', 1);
        $response = $this->getResponse();
        $this->assertContains(Timecard_IndexController::EDIT_TRUE_TEXT, $response);

        // Check that the period has been modified
        $this->setRequestUrl('Timecard/index/jsonDayList/');
        $this->request->setParam('date', '2009-07-02');
        $response = $this->getResponse();
        $expected = '{"id":"9","projectId":"1","startTime":"10:00:00","endTime":"19:00:00","display":"Invisible Root"}';
        $this->assertContains($expected, $response);
    }

    /**
     * Test of json Save
     */
    public function testJsonSaveEdit()
    {
        // EDIT. Sending id
        $this->setRequestUrl('Timecard/index/jsonSave/');
        $this->request->setParam('id', 7);
        $this->request->setParam('date', '2009-05-16');
        $this->request->setParam('startTime', '10:30');
        $this->request->setParam('endTime', '12:30');
        $this->request->setParam('notes', 'My note');
        $this->request->setParam('projectId', 1);
        $response = $this->getResponse();
        $this->assertContains(Timecard_IndexController::EDIT_TRUE_TEXT, $response);

        // Check that the period has been modified
        $this->setRequestUrl('Timecard/index/jsonDayList/');
        $this->request->setParam('date', '2009-05-16');
        $response = $this->getResponse();
        $expected = '"data":[{"id":"7","projectId":"1","startTime":"10:30:00","endTime":"12:30:00",'
            . '"display":"Invisible Root"},{"id":"8","projectId":"1","startTime":"14:00:00","endTime":"18:00:00",'
            . '"display":"Invisible Root"}]';
        $this->assertContains($expected, $response);
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
        $this->request->setParam('notes', 'My note');
        $this->request->setParam('projectId', 1);
        try {
            $this->front->dispatch($this->request, $this->response);
        } catch (Phprojekt_PublishedException $error) {
            $this->assertEquals("Time period: Can not save it because it overlaps existing one", $error->getMessage());
            return;
        }
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
        $this->request->setParam('notes', 'My note');
        $this->request->setParam('projectId', 1);
        try {
            $this->front->dispatch($this->request, $this->response);
        } catch (Phprojekt_PublishedException $error) {
            $this->assertEquals("Time period: Can not Start Working Time because this moment is occupied by an "
                . "existing period or an open one", $error->getMessage());
            return;
        }
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
        $this->request->setParam('notes', 'My note');
        $this->request->setParam('projectId', 1);
        $response = $this->getResponse();
        $this->assertContains(Timecard_IndexController::ADD_TRUE_TEXT, $response);

        $this->request->setParam('date', '2009-05-16');
        $this->request->setParam('endTime', '12:00');
        try {
            $this->front->dispatch($this->request, $this->response);
        } catch (Phprojekt_PublishedException $error) {
            $this->assertEquals("Time period: Can not save it because it overlaps existing one", $error->getMessage());
            return;
        }
    }

    /**
     * Test of json Save
     */
    public function testJsonSaveWrongStartTime()
    {
        // Try to INSERT a period with wrong start time. Returns nothing here
        $this->setRequestUrl('Timecard/index/jsonSave/');
        $this->request->setParam('date', '2009-05-20');
        $this->request->setParam('startTime', '');
        $this->request->setParam('notes', 'My note');
        $this->request->setParam('projectId', 1);
        try {
            $this->front->dispatch($this->request, $this->response);
        } catch (Phprojekt_PublishedException $error) {
            $this->assertEquals("Start Time: Is a required field", $error->getMessage());
            return;
        }
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
        $this->request->setParam('notes', 'My note');
        $this->request->setParam('projectId', 1);
        try {
            $this->front->dispatch($this->request, $this->response);
        } catch (Phprojekt_PublishedException $error) {
            $this->assertEquals("Hours: The end time must be after the start time", $error->getMessage());
            return;
        }
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
        $this->request->setParam('endTime', '12:60');
        $this->request->setParam('notes', 'My note');
        $this->request->setParam('projectId', 1);
        try {
            $this->front->dispatch($this->request, $this->response);
        } catch (Phprojekt_PublishedException $error) {
            $this->assertEquals("Hours: The end time must be after the start time", $error->getMessage());
            return;
        }
    }

    /**
     * Test the list
     */
    public function testJsonMonthListActionPart1()
    {
        $this->setRequestUrl('Timecard/index/jsonMonthList/');
        $this->request->setParam('year', 2009);
        $this->request->setParam('month', '05');
        $response = $this->getResponse();
        $this->assertContains('{"date":"2009-05-01","week":"5","sumInMinutes":0,"sumInHours":0}', $response);
        $this->assertContains('{"date":"2009-05-16","week":"6","sumInMinutes":360,"sumInHours":"06:00"}', $response);
        $this->assertContains('{"date":"2009-05-31","week":"0","sumInMinutes":0,"sumInHours":0}', $response);
    }

    /**
     * Test of json Delete -in fact default jsonDelete
     */
    public function testJsonDelete()
    {
        $this->setRequestUrl('Timecard/index/jsonDelete');
        $this->request->setParam('id', '8');
        $response = $this->getResponse();
        $this->assertContains(Timecard_IndexController::DELETE_TRUE_TEXT, $response);
    }

    /**
     * Test the list
     */
    public function testJsonMonthListActionPart2()
    {
        $this->setRequestUrl('Timecard/index/jsonMonthList/');
        $this->request->setParam('year', 2009);
        $this->request->setParam('month', '05');
        $response = $this->getResponse();
        $this->assertContains('{"date":"2009-05-01","week":"5","sumInMinutes":0,"sumInHours":0}', $response);
        $this->assertContains('{"date":"2009-05-16","week":"6","sumInMinutes":120,"sumInHours":"02:00"}', $response);
        $this->assertContains('{"date":"2009-05-31","week":"0","sumInMinutes":0,"sumInHours":0}', $response);
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
        $this->assertContains('{"id":1,"display":"Invisible Root"},{"id":2,"display":"Project 1"}', $response);
    }
}
