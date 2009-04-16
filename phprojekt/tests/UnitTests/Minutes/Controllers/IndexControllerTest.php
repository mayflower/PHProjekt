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
        
        $this->assertTrue(strpos($response, '{"metadata":[]}') > 0);
    }
    
    /**
     * Request empty form
     */
    public function testJsonDetailActionGetEmptyForm()
    {
        $this->setRequestUrl('Minutes/index/jsonDetail/id/0');
        $this->request->setParam('start', 0);
        $response = $this->getResponse();
        
        $this->assertTrue(strpos($response, '{"metadata":[{"key":"projectId","label":"Select","type":"hidden",') > 0,
            "Response was: '$response'");
        $this->assertTrue(strpos($response, ',"data":[{"id":null,"projectId":"","rights":' 
            . '{"currentUser":{"moduleId":"11","itemId":null') > 0, "Response was: '$response'");
    }
    
    /*
     * Test getting the user list from nonexistant minutes
     */
    public function testJsonListUserActionFromNonExistingMinutes() {
        $this->setRequestUrl('Minutes/index/jsonListUser/id/1');
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, Minutes_IndexController::NOT_FOUND) > 0, "Response was: '$response'");
        // This action should return the list of users selected as participantsInvited only from existing minutes.
    }
    
    /**
     * Test of json save Minutes
     */
    public function testJsonSaveActionSaveFirstMinutes()
    {
        
        $this->setRequestUrl('Minutes/index/jsonSave/id/0');
        //$this->request->setParam('id', 0);

/*        $this->request->setParam('checkAccessAccess[1]', 1);
        $this->request->setParam('checkAccessAccess[1]', 1);
        $this->request->setParam('checkAdminAccess[1]', 1);
        $this->request->setParam('checkCopyAccess[1]', 1);
        $this->request->setParam('checkCreateAccess[1]', 1);
        $this->request->setParam('checkDeleteAccess[1]', 1);
        $this->request->setParam('checkDownloadAccess[1]', 1);
        $this->request->setParam('checkReadAccess[1]', 1);
        $this->request->setParam('checkWriteAccess[1]', 1);
        $this->request->setParam('dataAccessAdd', 2);
        $this->request->setParam('dataAccess[1]', 'admin');
        */
        $this->request->setParam('projectId', 1);
        $this->request->setParam('title', 'TestTitle');
        $this->request->setParam('description', 'TestDescription');
        $this->request->setParam('meetingDate', 'Thu Apr 09 2009 00:00:00 GMT+0200');
        $this->request->setParam('startTime', 'Thu Jan 01 1970 03:00:00 GMT+0100');
        $this->request->setParam('endTime', 'Thu Jan 01 1970 03:00:00 GMT+0100');
        $this->request->setParam('place', 'TestPlace');
        $this->request->setParam('moderator', 'TestModerator');
        $this->request->setParam('participantsInvited[]', 0);
        $this->request->setParam('participantsAttending[]', 0);
        $this->request->setParam('participantsExcused[]', 0);
        $this->request->setParam('recipients[]', 0);
        $this->request->setParam('itemStatus', 1);
        $this->request->setParam('string', '');
        $this->request->setParam('requiredField1', '(*) Required Field');
        
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, Minutes_IndexController::ADD_TRUE_TEXT) > 0, "Response was: '$response'");
    }

    /*
     * Test getting the user list
     */
    public function testJsonListUserActionFromExistingMinutes() {
        $this->markTestIncomplete('Not yet implemented');
        // This action should return the list of users selected as participantsInvited from existing minutes.
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
/*        $this->setRequestUrl('Minutes/index/jsonList/');
        $this->request->setParam('id', 1);
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, '"numRows":0}') > 0);*/
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
