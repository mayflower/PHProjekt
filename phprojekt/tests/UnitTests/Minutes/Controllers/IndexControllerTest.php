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
 * Tests for Minutes Index Controller
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Sven Rautenberg <sven.rautenberg@mayflower.de>
 * @group      minutes, controller, minutes-controller
 */
class Minutes_IndexController_Test extends FrontInit
{
    /**
     * Test the empty Minutes list
     */
    public function testJsonListActionWithEmptyList()
    {
        $this->setRequestUrl('Minutes/index/jsonList/nodeId/1');
        $this->request->setParam('start', 0);
        $response = $this->getResponse();

        $this->assertContains('{"metadata":[]}', $response, "Response was: '$response'");
    }

    /**
     * Request empty form
     */
    public function testJsonDetailActionGetEmptyForm()
    {
        $this->setRequestUrl('Minutes/index/jsonDetail/id/0');
        $this->request->setParam('start', 0);
        $response = $this->getResponse();

        $this->assertContains('{"metadata":[{"key":"projectId","label":"Select","type":"hidden",', $response,
            "Response was: '$response'");
        $this->assertContains(',"data":[{"id":null,"projectId":"","rights":'
            . '{"currentUser":{"moduleId":"11","itemId":null', $response, "Response was: '$response'");
    }

    /*
     * Test getting the user list from nonexistant minutes
     */
    public function testJsonListUserActionFromNonExistingMinutes() {
        $this->setRequestUrl('Minutes/index/jsonListUser/id/1');
        $response = $this->getResponse();

        $this->assertContains(Minutes_IndexController::NOT_FOUND, $this->errormessage, "Response was: '$response'");
        // This action should return the list of users selected as participantsInvited only from existing minutes.
    }

    /**
     * Test of json save Minutes
     */
    public function testJsonSaveActionSaveFirstMinutes()
    {
        $locale = new Zend_Locale('en');
        $meetDate = new Zend_Date($locale);
        $meetDate->sub(1, Zend_Date::DAY);
        $meetDateString = $meetDate->toString("EEE MMM dd yyyy '00:00:00 GMT'ZZZ");

        $this->setRequestUrl('Minutes/index/jsonSave/id/0');
        $this->request->setParam('projectId', 1);
        $this->request->setParam('title', 'TestTitle');
        $this->request->setParam('description', 'TestDescription');
        $this->request->setParam('meetingDate', $meetDateString);
        $this->request->setParam('startTime', 'Thu Jan 01 1970 03:00:00 GMT+0100');
        $this->request->setParam('endTime', 'Thu Jan 01 1970 03:00:00 GMT+0100');
        $this->request->setParam('place', 'TestPlace');
        $this->request->setParam('moderator', 'TestModerator');
        $this->request->setParam('participantsInvited', array(2, 1));
        $this->request->setParam('participantsAttending', array());
        $this->request->setParam('participantsExcused', array());
        $this->request->setParam('recipients', array());
        $this->request->setParam('itemStatus', 4);
        $this->request->setParam('string', '');
        $this->request->setParam('requiredField1', '(*) Required Field');
        $response = $this->getResponse();

        $this->assertFalse($this->error, "Yesterdays date used: '$meetDateString', Exception is: ".$this->errormessage);
        $this->assertContains(Minutes_IndexController::ADD_TRUE_TEXT, $response, "Response was: '$response'");
        $this->assertContains('"id":"1"', $response, "ID created was not numbered 1.");
    }

    public function testJsonListActionAfterFirstInsertion()
    {
        $this->setRequestUrl('Minutes/index/jsonList/nodeId/1');
        $this->request->setParam('start', 0);
        $response = $this->getResponse();

        $this->assertContains('"numRows":1}', $response, "Response was: '$response'");
    }

    /*
     * Create one meeting minutes as a parent for the item tests
     */
    public function testCreateOneMeetingMinutesForItemTest ()
    {
        $this->setRequestUrl('Minutes/index/jsonSave/id/0');
        $this->request->setParam('projectId', 1);
        $this->request->setParam('title', 'SecondTestTitle');
        $this->request->setParam('description', 'SecondTestDescription');
        $this->request->setParam('meetingDate', 'Thu Apr 09 2009 00:00:00 GMT+0200');
        $this->request->setParam('startTime', 'Thu Jan 01 1970 03:00:00 GMT+0100');
        $this->request->setParam('endTime', 'Thu Jan 01 1970 03:00:00 GMT+0100');
        $this->request->setParam('place', 'SecondTestPlace');
        $this->request->setParam('moderator', 'SecondTestModerator');
        $this->request->setParam('participantsInvited', array(1, 2));
        $this->request->setParam('participantsAttending', array());
        $this->request->setParam('participantsExcused', array());
        $this->request->setParam('recipients', array());
        $this->request->setParam('itemStatus', 1);
        $this->request->setParam('string', '');
        $this->request->setParam('requiredField1', '(*) Required Field');
        $response = $this->getResponse();

        $this->assertContains(Minutes_IndexController::ADD_TRUE_TEXT, $response, "Response was: '$response'");
        $this->assertContains('"id":"2"', $response, "ID created was not numbered 2.");
    }

    public function testJsonListActionAfterSecondInsertion()
    {
        $this->setRequestUrl('Minutes/index/jsonList/nodeId/1');
        $this->request->setParam('start', 0);
        $response = $this->getResponse();

        $this->assertContains('"numRows":2}', $response, "Response was: '$response'");
    }

    /*
     * Test getting the user list
     */
    public function testJsonListUserActionFromExistingMinutes()
    {
        $this->setRequestUrl('Minutes/index/jsonListUser/id/1');
        $response = $this->getResponse();

        $this->assertContains('{"id":"1","display":', $response);
        $this->assertContains('{"id":"2","display":', $response);
        $this->assertContains('"numRows":2})', $response, "Response was: '$response'");
        // This action should return the list of users selected as participantsInvited only from existing minutes.
    }
    /**
     * Test the Minutes event detail
     */
    public function testJsonDetailAction()
    {
        $this->setRequestUrl('Minutes/index/jsonDetail/id/1');
        $response = $this->getResponse();

        $this->assertContains('TestTitle', $response, "Response was: '$response'");
    }

    public function testJsonListAction()
    {
        $this->setRequestUrl('Minutes/index/jsonList/');
        $this->request->setParam('id', 1);
        $response = $this->getResponse();

        $this->assertContains('"numRows":1}', $response, "Response was: '$response'");
    }

    /**
     * Test the Minutes deletion with errors
     */
    public function testJsonDeleteActionWrongId()
    {
        $this->setRequestUrl('Minutes/index/jsonDelete/id/11');
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
        $this->setRequestUrl('Minutes/index/jsonDelete/id/1');
        $response = $this->getResponse();

        $this->assertContains(Minutes_IndexController::DELETE_TRUE_TEXT, $response, "Response was: '$response'");
    }

    public function testJsonDeleteId2Action()
    {
        $this->setRequestUrl('Minutes/index/jsonDelete/id/2');
        $response = $this->getResponse();

        $this->assertContains(Minutes_IndexController::DELETE_TRUE_TEXT, $response, "Response was: '$response'");
    }


    /**
     * Test the empty Minutes list
     */
    public function testJsonListActionAfterDeletion()
    {
        $this->setRequestUrl('Minutes/index/jsonList/nodeId/1');
        $this->request->setParam('start', 0);
        $response = $this->getResponse();

        $this->assertContains('{"metadata":[]}', $response, "Response was: '$response'");
    }
}
