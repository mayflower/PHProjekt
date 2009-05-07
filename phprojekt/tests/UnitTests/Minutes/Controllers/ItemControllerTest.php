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
 */
class Minutes_ItemController_Test extends FrontInit
{
    /**
     * Test the Minutes list
     */
    public function testJsonListActionBeforeAll()
    {
        $this->setRequestUrl('Minutes/item/jsonList/minutesId/1');
        $response = $this->getResponse();
        $this->assertContains('{"metadata":[]}', $response);
    }

    /**
     * Test of json save Minutes
     */
    public function testJsonSaveMinutesDoNotExist()
    {
        $this->setRequestUrl('Minutes/item/jsonSave/minutesId/1/id/0');
        $response = $this->getResponse();
        
        $this->assertTrue($this->error);
        $this->assertContains(Minutes_ItemController::NOT_FOUND, $this->errormessage);
    }

    /*
     * Create one meeting minutes as a parent for the item tests
     */
    public function testCreateOneMeetingMinutes ()
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
        $this->assertContains(Minutes_IndexController::ADD_TRUE_TEXT, $response);
    }

    /**
     * Test the Minutes event detail
     */
    public function testJsonCheckMinutesCreated()
    {
        $this->setRequestUrl('Minutes/index/jsonDetail/id/3');
        $response = $this->getResponse();
        $this->assertContains('SecondTestTitle', $response, "Response was: '$response'");
    }
    
    /**
     * Test the Minutes item list, should be empty
     */
    public function testJsonListActionAfterAll()
    {
        $this->setRequestUrl('Minutes/item/jsonList/minutesId/3');
        $response = $this->getResponse();
        $this->assertContains('{"metadata":[]}', $response);
    }

    /**
     * Add one minutes item
     */
    public function testJsonSaveFirstItem()
    {
        $this->setRequestUrl('Minutes/item/jsonSave/');
        $this->request->setParam('id', '');
        $this->request->setParam('minutesId', '3');
        $this->request->setParam('projectId', '');
        $this->request->setParam('sortOrder', '1');
        $this->request->setParam('title', 'DerTitel');
        $this->request->setParam('topicType', '1');
        $this->request->setParam('userId', '');
        $this->request->setParam('topicId', '');
        
        $response = $this->getResponse();
        $this->assertContains(Minutes_ItemController::ADD_TRUE_TEXT, $response);
    }
    /**
     * Test the Minutes event detail
     */
    public function testJsonDetailAction()
    {
        $this->markTestIncomplete('Not yet implemented');
    }

    public function testJsonListAction()
    {
        $this->markTestIncomplete('Not yet implemented');
    }

    /**
     * Test the Minutes deletion
     */
    public function testJsonDeleteAction()
    {
        $this->markTestIncomplete('Not yet implemented');
    }

    /**
     * Test the Minutes deletion with errors
     */
    public function testJsonDeleteActionWrongId()
    {
        $this->markTestIncomplete('Not yet implemented');
    }

    /**
     * Test the Minutes deletion with errors
     */
    public function testJsonDeleteActionNoId()
    {
        $this->markTestIncomplete('Not yet implemented');
    }
}
