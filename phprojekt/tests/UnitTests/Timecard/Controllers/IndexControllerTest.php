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
 * @subpackage Timecard
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */


/**
 * Tests for Index Controller
 *
 * @category   PHProjekt
 * @package    UnitTests
 * @subpackage Timecard
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 * @group      timecard
 * @group      controller
 * @group      timecard-controller
 */
class Timecard_IndexController_Test extends FrontInit
{
    protected function getDataSet()
    {
        return new PHPUnit_Extensions_Database_DataSet_CompositeDataSet(
            array(
                $this->createFlatXMLDataSet(dirname(__FILE__) . '/../../common.xml'),
                $this->createFlatXMLDataSet(dirname(__FILE__) . '/../data.xml')));
    }

    /**
     * Test of json Save
     */
    public function testJsonSaveCommonPart1()
    {
        // INSERT. Defined start and end time.
        $this->setRequestUrl('Timecard/index/jsonSave/');
        $this->request->setParam('startDatetime', '2009-05-16 09:00:00');
        $this->request->setParam('endTime', '13:00:00');
        $this->request->setParam('notes', 'My note');
        $this->request->setParam('projectId', 1);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Timecard_IndexController::ADD_TRUE_TEXT, $response);
    }

    /**
     * Test of json Save
     */
    public function testJsonSaveCommonPart2()
    {
        $this->setRequestUrl('Timecard/index/jsonSave/');
        $this->request->setParam('startDatetime', '2009-05-16 14:00:00');
        $this->request->setParam('endTime', '18:00');
        $this->request->setParam('notes', 'My note');
        $this->request->setParam('projectId', 1);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Timecard_IndexController::ADD_TRUE_TEXT, $response);
    }

    /**
     * Test of json Save
     */
    public function testJsonSaveCommonCheck()
    {
        // Check that the period has been added
        $this->setRequestUrl('Timecard/index/jsonDayList/');
        $this->request->setParam('date', '2009-05-17');
        $response = $this->getResponse();
        $parsed   = FrontInit::phprJsonToArray($response);
        $expected = array(
            "data" => array(
                array(
                    "id" => "7",
                    "projectId" => "1",
                    "startTime" => "09:00:00",
                    "endTime" => "13:00:00",
                    "display" => "PHProjekt",
                    "note" => "My note"
                ),
                array(
                    "id" => "8",
                    "projectId" => "1",
                    "startTime" => "14:00:00",
                    "endTime" => "18:00:00",
                    "display" => "PHProjekt",
                    "note" => "My note"
                )
            )
        );
        $this->assertEquals($expected, $parsed);
    }

    /**
     * Test of json Save
     */
    public function testJsonSaveJustStart()
    {
        // INSERT. Just defined start time.
        $this->setRequestUrl('Timecard/index/jsonSave/');
        $this->request->setParam('startDatetime', '2009-07-02 10:00:00');
        $this->request->setParam('endTime', '');
        $this->request->setParam('notes', 'My note');
        $this->request->setParam('projectId', 1);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Timecard_IndexController::ADD_TRUE_TEXT, $response);
    }

    /**
     * Test of json Save
     */
    public function testJsonSaveJustStartCheck()
    {
        // Check that the period has been added
        $this->setRequestUrl('Timecard/index/jsonDayList/');
        $this->request->setParam('date', '2009-07-03');
        $response = $this->getResponse();
        $parsed   = FrontInit::phprJsonToArray($response);
        $expected = array(
            "data" => array(
                array(
                    "id" => "9",
                    "projectId" => "1",
                    "startTime" => "10:00:00",
                    "endTime" => null,
                    "display" => "PHProjekt",
                    "note" => "My note"
                )
            )
        );
        $this->assertEquals($expected, $parsed);
    }

    /**
     * Test of json Save
     */
    public function testJsonSaveJustEnd()
    {
        // INSERT. Just defined end time.
        $this->setRequestUrl('Timecard/index/jsonSave/');
        $this->request->setParam('id', 9);
        $this->request->setParam('startDatetime', '2009-07-02');
        $this->request->setParam('endTime', '19:00');
        $this->request->setParam('notes', 'My note');
        $this->request->setParam('projectId', 1);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Timecard_IndexController::EDIT_TRUE_TEXT, $response);
    }

    /**
     * Test of json Save
     */
    public function testJsonSaveEdit()
    {
        // EDIT. Sending id
        $this->setRequestUrl('Timecard/index/jsonSave/');
        $this->request->setParam('id', 7);
        $this->request->setParam('startDatetime', '2009-05-16 10:30:00');
        $this->request->setParam('endTime', '12:30:00');
        $this->request->setParam('notes', 'My note');
        $this->request->setParam('projectId', 1);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Timecard_IndexController::EDIT_TRUE_TEXT, $response);
    }

    /**
     * Test of json Save
     *
     */
    public function testJsonSaveOverlapping()
    {
        // Try to INSERT an overlapping period. Returns nothing here
        $this->setRequestUrl('Timecard/index/jsonSave/');
        $this->request->setParam('startDatetime', '2009-05-16 10:00:00');
        $this->request->setParam('endTime', '12:00:00');
        $this->request->setParam('notes', 'My note');
        $this->request->setParam('projectId', 1);
        $this->request->setParam('nodeId', 1);
        // throws exception
        $this->getResponse();
    }

    /**
     * Test of json Save
     *
     */
    public function testJsonSaveJustStartOverlapping()
    {
        // Try to INSERT an overlapping period just with Start time. Returns nothing here
        $this->setRequestUrl('Timecard/index/jsonSave/');
        $this->request->setParam('startDatetime', '2009-05-16 11:00:00');
        $this->request->setParam('notes', 'My note');
        $this->request->setParam('projectId', 1);
        $this->request->setParam('nodeId', 1);
        // throws exception
        $this->getResponse();
    }

    /**
     * Test of json Save
     *
     */
    public function testJsonSaveJustEndOverlapping()
    {
        // Try to INSERT an overlapping period just with End time. Returns nothing here
        $this->setRequestUrl('Timecard/index/jsonSave/');
        $this->request->setParam('startDatetime', '2009-05-16 09:00:00');
        $this->request->setParam('notes', 'My note');
        $this->request->setParam('projectId', 1);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Timecard_IndexController::ADD_TRUE_TEXT, $response);

        $this->request->setParam('startDatetime', '2009-05-16');
        $this->request->setParam('endTime', '12:00:00');
        // throws exception
        $this->getResponse();
    }

    /**
     * Test of json Save
     *
     * @expectedException Phprojekt_PublishedException
     */
    public function testJsonSaveWrongStartTime()
    {
        // Try to INSERT a period with wrong start time. Returns nothing here
        $this->setRequestUrl('Timecard/index/jsonSave/');
        $this->request->setParam('startDatetime', '');
        $this->request->setParam('notes', 'My note');
        $this->request->setParam('projectId', 1);
        $this->request->setParam('nodeId', 1);
        // throws exception
        $this->getResponse();
    }

    /**
     * Test of json Save
     *
     * @expectedException Phprojekt_PublishedException
     */
    public function testJsonSaveStartAfterEndTime()
    {
        // Try to INSERT a period with start time after end time. Returns nothing here
        $this->setRequestUrl('Timecard/index/jsonSave/');
        $this->request->setParam('startDatetime', '2009-05-21 17:00:00');
        $this->request->setParam('endTime', '08:00:00');
        $this->request->setParam('notes', 'My note');
        $this->request->setParam('projectId', 1);
        $this->request->setParam('nodeId', 1);
        // throws exception
        $this->getResponse();
    }

    /**
     * Test of json Save
     *
     * @expectedException Phprojekt_PublishedException
     */
    public function testJsonSaveEndTimeInvalid()
    {
        // Try to INSERT a period with wrong end time. Returns nothing here
        $this->setRequestUrl('Timecard/index/jsonSave/');
        $this->request->setParam('startDatetime', '2009-05-21 17:00:00');
        $this->request->setParam('endTime', '12:60:00');
        $this->request->setParam('notes', 'My note');
        $this->request->setParam('projectId', 1);
        $this->request->setParam('nodeId', 1);
        // throws exception
        $this->getResponse();
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
        $this->request->setParam('month', 5);
        $response = $this->getResponse();
        $expected = '{"date":"2009-05-01","week":"5","sumInMinutes":0,"sumInHours":0,"openPeriod":0}';
        $this->assertContains($expected, $response);

        $expected = '{"date":"2009-05-17","week":"0","sumInMinutes":120,"sumInHours":"02:00","openPeriod":0}';
        $this->assertContains($expected, $response);

        $expected = '{"date":"2009-05-31","week":"0","sumInMinutes":0,"sumInHours":0,"openPeriod":0}';
        $this->assertContains($expected, $response);
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

        $this->_reset();
        $this->setRequestUrl('Timecard/index/jsonGetFavoritesProjects/');
        $response = FrontInit::phprJsonToArray($this->getResponse());

        $expected = array(
            array(
                'id'      => 1,
                'display' => 'PHProjekt',
                'name'    => 'PHProjekt'
            ),
            array(
                'id'      => 2,
                'display' => 'Test Project',
                'name'    => 'Test Project'
            )
        );

        $this->assertEquals($expected, $response);
    }

    /**
     * Test of csv
     */
    public function testCsvList()
    {
        $this->setRequestUrl('Timecard/index/csvList/');
        $this->request->setParam('year', 2009);
        $this->request->setParam('month', '05');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains('"Start","End","Minutes","Project","Notes"'."\n"
            .'"2009-05-17 09:00:00","13:00","0","PHProjekt","My note"'."\n"
            .'"2009-05-17 14:00:00","18:00","120","PHProjekt","My note"'."\n", $response);
    }

    /**
     * Test of csv
     */
    public function testCsvListEmptyResult()
    {
        $this->setRequestUrl('Timecard/index/csvList/');
        $this->request->setParam('year', 2009);
        $this->request->setParam('month', '9');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertEquals(2, strlen($response));
    }
}
