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
 * Tests for Minutes Index Controller
 *
 * @version    Release: 6.1.0
 * @group      minutes
 * @group      controller
 * @group      minutes-controller
 */
class Minutes_IndexController_Test extends FrontInit
{
    /**
     * Test the empty Minutes list
     */
    public function testJsonListActionWithEmptyList()
    {
        $this->setRequestUrl('Minutes/index/jsonList/');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains('{"metadata":[]}', $response, "Response was: '$response'");
    }

    /**
     * Request empty form
     */
    public function testJsonDetailActionGetEmptyForm()
    {
        $this->setRequestUrl('Minutes/index/jsonDetail/');
        $this->request->setParam('id', 0);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $expected = '{"metadata":[{"key":"title","label":"Title","originalLabel":"Title","type":"text",';
        $this->assertContains($expected, $response, "Response was: '$response'");

        $expected = ',"data":[{"id":0,"title":"","meetingDatetime":"","endTime":"","projectId":0';
        $this->assertContains($expected, $response, "Response was: '$response'");

        $expected = ',"itemStatus":1,"rights":{"currentUser":{"moduleId":11,"itemId":0';
        $this->assertContains($expected, $response, "Response was: '$response'");
    }

    /*
     * Test getting the user list from nonexistant minutes
     */
    public function testJsonListUserActionFromNonExistingMinutes() {
        $this->setRequestUrl('Minutes/index/jsonListUser/');
        $this->request->setParam('id', 1);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();

        $this->assertContains(Minutes_IndexController::NOT_FOUND, $this->errormessage, "Response was: '$response'");
        // This action should return the list of users selected as participantsInvited only from existing minutes.
    }

    /**
     * Test of json save Minutes
     */
    public function testJsonSaveActionSaveFirstMinutes()
    {
        $tomorrow = strtotime('tomorrow');
        $this->setRequestUrl('Minutes/index/jsonSave/');
        $this->request->setParam('id', 0);
        $this->request->setParam('projectId', 1);
        $this->request->setParam('title', 'TestTitle');
        $this->request->setParam('description', 'TestDescription');
        $this->request->setParam('meetingDatetime', date('Y-m-d H:i:s', $tomorrow));
        $this->request->setParam('endTime', strtotime('03:00'));
        $this->request->setParam('place', 'TestPlace');
        $this->request->setParam('moderator', 'TestModerator');
        $this->request->setParam('participantsInvited', array(2, 1));
        $this->request->setParam('participantsAttending', array());
        $this->request->setParam('participantsExcused', array());
        $this->request->setParam('itemStatus', 4);
        $this->request->setParam('string', '');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();

        $this->assertContains(Minutes_IndexController::ADD_TRUE_TEXT, $response, "Response was: '$response'");
        $this->assertContains('"id":"1"', $response, "ID created was not numbered 1.");
    }

    public function testJsonListActionAfterFirstInsertion()
    {
        $this->setRequestUrl('Minutes/index/jsonList/');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();

        $this->assertContains('"numRows":1}', $response, "Response was: '$response'");
    }

    /*
     * Create one meeting minutes as a parent for the item tests
     */
    public function testCreateOneMeetingMinutesForItemTest ()
    {
        $yesterday = strtotime('yesterday');
        $this->setRequestUrl('Minutes/index/jsonSave/');
        $this->request->setParam('id', 0);
        $this->request->setParam('projectId', 1);
        $this->request->setParam('title', 'SecondTestTitle');
        $this->request->setParam('description', 'SecondTestDescription');
        $this->request->setParam('meetingDatetime', date('Y-m-d H:i:s', $yesterday));
        $this->request->setParam('endTime', strtotime('03:00'));
        $this->request->setParam('place', 'SecondTestPlace');
        $this->request->setParam('moderator', 'SecondTestModerator');
        $this->request->setParam('participantsInvited', array(1, 2));
        $this->request->setParam('participantsAttending', array());
        $this->request->setParam('participantsExcused', array());
        $this->request->setParam('itemStatus', 1);
        $this->request->setParam('string', '');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();

        $this->assertContains(Minutes_IndexController::ADD_TRUE_TEXT, $response, "Response was: '$response'");
        $this->assertContains('"id":"2"', $response, "ID created was not numbered 2.");
    }

    public function testJsonListActionAfterSecondInsertion()
    {
        $this->setRequestUrl('Minutes/index/jsonList/');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();

        $this->assertContains('"numRows":2}', $response, "Response was: '$response'");
    }

    /*
     * Test getting the user list
     */
    public function testJsonListUserActionFromExistingMinutes()
    {
        $this->setRequestUrl('Minutes/index/jsonListUser/');
        $this->request->setParam('id', 1);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();

        $this->assertContains('{"id":1,"display":', $response);
        $this->assertContains('{"id":2,"display":', $response);
        $this->assertContains('"numRows":2})', $response, "Response was: '$response'");
        // This action should return the list of users selected as participantsInvited only from existing minutes.
    }

    /**
     * Test the Minutes event detail
     */
    public function testJsonDetailAction()
    {
        $this->setRequestUrl('Minutes/index/jsonDetail/');
        $this->request->setParam('id', 1);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();

        $this->assertContains('TestTitle', $response, "Response was: '$response'");
    }

    public function testJsonListAction()
    {
        $this->setRequestUrl('Minutes/index/jsonList/');
        $this->request->setParam('id', 1);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();

        $this->assertContains('"numRows":1}', $response, "Response was: '$response'");
    }

    /**
     * Test the Minutes deletion with errors
     */
    public function testJsonDeleteActionWrongId()
    {
        $this->setRequestUrl('Minutes/index/jsonDelete/');
        $this->request->setParam('id', 11);
        $this->getResponse();

        $this->assertTrue($this->error);
        $this->assertContains(Minutes_IndexController::NOT_FOUND, $this->errormessage);
    }

    /**
     * Test the Minutes deletion with errors
     */
    public function testJsonDeleteActionNoId()
    {
        $this->setRequestUrl('Minutes/index/jsonDelete');
        $this->getResponse();

        $this->assertTrue($this->error);
        $this->assertContains(Minutes_IndexController::ID_REQUIRED_TEXT, $this->errormessage);
    }


    /**
     * Test the Minutes deletion
     */
    public function testJsonDeleteId1Action()
    {
        $this->setRequestUrl('Minutes/index/jsonDelete/');
        $this->request->setParam('id', 1);
        $response = $this->getResponse();

        $this->assertContains(Minutes_IndexController::DELETE_TRUE_TEXT, $response, "Response was: '$response'");
    }

    public function testJsonDeleteId2Action()
    {
        $this->setRequestUrl('Minutes/index/jsonDelete/');
        $this->request->setParam('id', 2);
        $response = $this->getResponse();

        $this->assertContains(Minutes_IndexController::DELETE_TRUE_TEXT, $response, "Response was: '$response'");
    }


    /**
     * Test the empty Minutes list
     */
    public function testJsonListActionAfterDeletion()
    {
        $this->setRequestUrl('Minutes/index/jsonList/');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();

        $this->assertContains('{"metadata":[]}', $response, "Response was: '$response'");
    }
}
