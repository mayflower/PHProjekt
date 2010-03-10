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
 * @subpackage Minutes
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Sven Rautenberg <sven.rautenberg@mayflower.de>
 */

require_once 'PHPUnit/Framework.php';

/**
 * Tests for Minutes Index Controller
 *
 * @category   PHProjekt
 * @package    UnitTests
 * @subpackage Minutes
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Sven Rautenberg <sven.rautenberg@mayflower.de>
 * @group      minutes
 * @group      controller
 * @group      minutes-controller
 */
class Minutes_ItemController_Test extends FrontInit
{
    /**
     * Test the Minutes list
     */
    public function testJsonListActionBeforeAll()
    {
        $this->setRequestUrl('MinutesItem/index/jsonList/');
        $this->request->setParam('minutesId', 1);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();

        $this->assertContains('{"metadata":[{"key":', $response);
        $this->assertContains('"Type","originalLabel":"Type","type":"selectbox"', $response);

        $expected = '"range":[{"id":1,"name":"Topic","originalName":"Topic"},'
            . '{"id":2,"name":"Statement","originalName":"Statement"},'
            . '{"id":3,"name":"Todo","originalName":"Todo"},'
            . '{"id":4,"name":"Decision","originalName":"Decision"},'
            . '{"id":5,"name":"Date","originalName":"Date"}]';
        $this->assertContains($expected, $response);
        $this->assertContains('"numRows":0}', $response);
    }

    /**
     * Test of json save Minutes
     */
    public function testJsonSaveMinutesDoNotExist()
    {
        $this->setRequestUrl('MinutesItem/index/jsonSave/');
        $this->request->setParam('id', 0);
        $this->request->setParam('nodeId', 1);
        $this->request->setParam('minutesId', 1);
        $this->getResponse();

        $this->assertTrue($this->error);
        $this->assertContains(MinutesItem_IndexController::NOT_FOUND, $this->errormessage);
    }

    /*
     * Create one meeting minutes as a parent for the item tests
     */
    public function testCreateOneMeetingMinutes ()
    {
        $this->setRequestUrl('Minutes/index/jsonSave/');
        $this->request->setParam('id', 0);
        $this->request->setParam('projectId', 1);
        $this->request->setParam('title', 'SecondTestTitle');
        $this->request->setParam('description', 'SecondTestDescription');
        $this->request->setParam('meetingDatetime', date('Y-m-d H:i:s'));
        $this->request->setParam('endTime', strtotime('03:00'));
        $this->request->setParam('place', 'SecondTestPlace');
        $this->request->setParam('moderator', 'SecondTestModerator');
        $this->request->setParam('participantsInvited', array(1, 2));
        $this->request->setParam('participantsAttending', array());
        $this->request->setParam('participantsExcused', array());
        $this->request->setParam('itemStatus', 1);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();

        $this->assertContains(Minutes_IndexController::ADD_TRUE_TEXT, $response);
        $this->assertContains('"id":"3"', $response, "ID created was not numbered 3.");
    }

    /**
     * Test the Minutes event detail
     */
    public function testJsonCheckMinutesCreated()
    {
        $this->setRequestUrl('Minutes/index/jsonDetail/');
        $this->request->setParam('id', 3);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();

        $this->assertContains('SecondTestTitle', $response, "Response was: '$response'");
    }

    /**
     * Add one minutes item
     */
    public function testJsonSaveFirstItem()
    {
        $this->setRequestUrl('MinutesItem/index/jsonSave/');
        $this->request->setParam('id', null);
        $this->request->setParam('minutesId', 3);
        $this->request->setParam('title', 'DerTitel');
        $this->request->setParam('topicType', 1);
        $this->request->setParam('comment', '');
        $this->request->setParam('nodeId', 1);

        $response = $this->getResponse();
        $this->assertContains(MinutesItem_IndexController::ADD_TRUE_TEXT, $response);
    }

    /**
     * Test the Minutes item list
     */
    public function testJsonListActionAfterFirstItem()
    {
        $this->setRequestUrl('MinutesItem/index/jsonList/');
        $this->request->setParam('minutesId', 3);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();

        $this->assertContains('DerTitel', $response);
        $this->assertContains(',"numRows":1}', $response);
    }

    /**
     * Test the Minutes item detail
     */
    public function testJsonDetailsWithNoItem()
    {
        $this->setRequestUrl('MinutesItem/index/jsonDetail/');
        $this->request->setParam('minutesId', 3);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();

        $this->assertContains('"data":[{"id":0,"sortOrder":0,"title":"","topicType":0,'
            . '"comment":"","topicDate":"","userId":"","rights":{"currentUser":{"moduleId":11,"'
            . 'itemId":0,"userId":1,"none":false,"read":true,"write":true,"access":true,"create":true,'
            . '"copy":true,"delete":true,"download":true,"admin":true}}}]', $response);
    }

    /**
     * Add second minutes item
     */
    public function testJsonSaveSecondItem()
    {
        $this->setRequestUrl('MinutesItem/index/jsonSave/');
        $this->request->setParam('id', '');
        $this->request->setParam('minutesId', 3);
        $this->request->setParam('title', 'SecondTitle');
        $this->request->setParam('comment', "Some lines of comment\nSome lines of comment");
        $this->request->setParam('topicType', 3);
        $this->request->setParam('topicDate', '2009-05-01');
        $this->request->setParam('userId', 1);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();

        $this->assertContains(MinutesItem_IndexController::ADD_TRUE_TEXT, $response);
    }

    /**
     * Test the Minutes item list
     */
    public function testJsonListActionAfterSecondItem()
    {
        $this->setRequestUrl('MinutesItem/index/jsonList/');
        $this->request->setParam('minutesId', 3);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();

        $this->assertContains('"DerTitel"', $response);
        $this->assertContains('"SecondTitle"', $response);
        $this->assertContains('"Some lines of comment\\nSome lines of comment"', $response);
        $this->assertContains(',"numRows":2}', $response);
    }

    /**
     * Test the Minutes item list
     */
    public function testJsonListItemSortOrder()
    {
        $this->setRequestUrl('MinutesItem/index/jsonListItemSortOrder/');
        $this->request->setParam('minutesId', 3);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $expected = '[{"id":0,"name":""},{"id":1,"name":"DerTitel"},{"id":2,"name":"SecondTitle"}]';
        $this->assertContains($expected, $response);
    }

    /**
     * Test editing one item
     */
    public function testJsonSaveActionWithEditedData()
    {
        $this->setRequestUrl('MinutesItem/index/jsonSave/');
        $this->request->setParam('id', '2');
        $this->request->setParam('minutesId', 3);
        $this->request->setParam('sortOrder', 2);
        $this->request->setParam('title', 'SecondTitleSecondSave');
        $this->request->setParam('comment', "Some lines of new comment\nSome lines of new comment");
        $this->request->setParam('topicType', 3);
        $this->request->setParam('topicDate', '2009-05-01');
        $this->request->setParam('userId', 1);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();

        $this->assertContains(MinutesItem_IndexController::EDIT_TRUE_TEXT, $response);
    }

    /**
     * Test detail
     */
    public function testJsonDetailActionWithSecondItem()
    {
        $this->setRequestUrl('MinutesItem/index/jsonDetail/');
        $this->request->setParam('id', 2);
        $this->request->setParam('minutesId', 3);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();

        $this->assertContains(',"numRows":1}', $response);
        $this->assertContains('"SecondTitleSecondSave"', $response);
        $this->assertContains('"Some lines of new comment\\nSome lines of new comment"', $response);
    }

    /**
     * Add one minutes item
     */
    public function testJsonSaveThirdItem()
    {
        $this->setRequestUrl('MinutesItem/index/jsonSave/');
        $this->request->setParam('id', null);
        $this->request->setParam('minutesId', 3);
        $this->request->setParam('title', 'StatementTitle');
        $this->request->setParam('topicType', 2);
        $this->request->setParam('comment', 'StatementComment');
        $this->request->setParam('nodeId', 1);

        $response = $this->getResponse();
        $this->assertContains(MinutesItem_IndexController::ADD_TRUE_TEXT, $response);
    }

    /**
     * Add one minutes item
     */
    public function testJsonSaveFourthItem()
    {
        $this->setRequestUrl('MinutesItem/index/jsonSave/');
        $this->request->setParam('id', null);
        $this->request->setParam('minutesId', 3);
        $this->request->setParam('title', 'DecisionTitle');
        $this->request->setParam('topicType', 4);
        $this->request->setParam('comment', 'DecisionComment');
        $this->request->setParam('nodeId', 1);

        $response = $this->getResponse();
        $this->assertContains(MinutesItem_IndexController::ADD_TRUE_TEXT, $response);
    }

    /**
     * Add one minutes item
     */
    public function testJsonSaveFifthItem()
    {
        $this->setRequestUrl('MinutesItem/index/jsonSave/');
        $this->request->setParam('id', null);
        $this->request->setParam('minutesId', 3);
        $this->request->setParam('title', 'DateTitle');
        $this->request->setParam('topicDate', '2009-05-01');
        $this->request->setParam('topicType', 5);
        $this->request->setParam('comment', 'DateComment');
        $this->request->setParam('nodeId', 1);

        $response = $this->getResponse();
        $this->assertContains(MinutesItem_IndexController::ADD_TRUE_TEXT, $response);
    }

    /**
     * Test edit
     */
    public function testJsonSaveActionThirdItemEdit()
    {
        $this->setRequestUrl('MinutesItem/index/jsonSave/');
        $this->request->setParam('id', '3');
        $this->request->setParam('minutesId', 3);
        $this->request->setParam('sortOrder', 2);
        $this->request->setParam('title', 'StatementTitle');
        $this->request->setParam('topicType', 2);
        $this->request->setParam('comment', 'StatementComment');
        $this->request->setParam('nodeId', 1);

        $response = $this->getResponse();

        $this->assertContains(MinutesItem_IndexController::EDIT_TRUE_TEXT, $response);
    }

    /**
     * Test list
     */
    public function testJsonListItemSortOrder2()
    {
        $this->setRequestUrl('MinutesItem/index/jsonListItemSortOrder/');
        $this->request->setParam('minutesId', 3);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();

        $this->assertContains('[{"id":0,"name":""},{"id":1,"name":"DerTitel"},'
            . '{"id":2,"name":"StatementTitle"},{"id":3,"name":"SecondTitleSecondSave"}',
            $response);
    }

    /**
     * Test various methods to get a PDF
     */
    public function testPdfNoId()
    {
        $this->setRequestUrl('Minutes/index/pdf');
        $this->request->setParam('nodeId', 1);
        $this->getResponse();

        $this->assertTrue($this->error);
        $this->assertContains(Minutes_IndexController::ID_REQUIRED_TEXT, $this->errormessage);
    }

    /**
     * Test various methods to get a PDF
     */
    public function testPdfWrongId()
    {
        $this->setRequestUrl('Minutes/index/pdf/');
        $this->request->setParam('nodeId', 1);
        $this->request->setParam('id', 1);
        $this->getResponse();

        $this->assertTrue($this->error);
        $this->assertContains(Minutes_IndexController::NOT_FOUND, $this->errormessage);
    }

    /**
     * Test sending of a minutes mail
     */
    public function testJsonSendMailNoId()
    {
        $this->setRequestUrl('Minutes/index/jsonSendMail');
        $this->request->setParam('nodeId', 1);
        $this->getResponse();

        $this->assertTrue($this->error);
        $this->assertContains(Minutes_IndexController::ID_REQUIRED_TEXT, $this->errormessage);
    }

    /**
     * Test sending of a minutes mail
     */
    public function testJsonSendMailWrongId()
    {
        $this->setRequestUrl('Minutes/index/jsonSendMail');
        $this->request->setParam('nodeId', 1);
        $this->request->setParam('id', 1);
        $this->getResponse();

        $this->assertTrue($this->error);
        $this->assertContains(Minutes_IndexController::ID_REQUIRED_TEXT, $this->errormessage);
    }

    /**
     * Test sending of a minutes mail
     */
    public function testJsonSendMailNoRecipients()
    {
        $this->setRequestUrl('Minutes/index/jsonSendMail');
        $this->request->setParam('nodeId', 1);
        $this->request->setParam('id', 3);
        $response = $this->getResponse();

        $this->assertFalse($this->error, 'Response was: '. $response);
        $this->assertContains(Minutes_IndexController::MAIL_FAIL_TEXT, $response);
        $this->assertContains(Minutes_IndexController::MISSING_MAIL_RECIPIENTS, $response);
    }

    /**
     * Test sending of a minutes mail
     */
    public function testJsonSendMailWithRecipientsIds()
    {
        $this->setRequestUrl('Minutes/index/jsonSendMail');
        $this->request->setParam('nodeId', 1);
        $this->request->setParam('id', 3);
        $this->request->setParam('recipients', array(1, 2));
        $response = $this->getResponse();

        $this->assertFalse($this->error, 'Response was: '. $response);
        $this->assertContains(Minutes_IndexController::MAIL_SUCCESS_TEXT, $response);
    }

    /**
     * Test sending of a minutes mail
     */
    public function testJsonSendMailWithRecipientsIdsAndAdditional()
    {
        $this->setRequestUrl('Minutes/index/jsonSendMail');
        $this->request->setParam('nodeId', 1);
        $this->request->setParam('id', 3);
        $this->request->setParam('recipients', array(1, 2));
        $this->request->setParam('additional', 'test@example.com, foobar@example.com');
        $response = $this->getResponse();

        $this->assertFalse($this->error, 'Response was: '. $response);
        $this->assertContains(Minutes_IndexController::MAIL_SUCCESS_TEXT, $response);
    }

    /**
     * Test sending of a minutes mail
     */
    public function testJsonSendMailWithRecipientsIdsAndAdditionalWithPdfAttached()
    {
        $this->setRequestUrl('Minutes/index/jsonSendMail');
        $this->request->setParam('nodeId', 1);
        $this->request->setParam('id', 3);
        $this->request->setParam('recipients', array(1, 2));
        $this->request->setParam('additional', 'test@example.com, foobar@example.com');
        $this->request->setParam('options', array('pdf'));
        $response = $this->getResponse();

        $this->assertFalse($this->error, 'Response was: '. $response);
        $this->assertContains(Minutes_IndexController::MAIL_SUCCESS_TEXT, $response);
    }

    /**
     * Test sending of a minutes mail
     */
    public function testJsonSendMailWithRecipientsIdsAndAdditionalWrongFormat()
    {
        $this->setRequestUrl('Minutes/index/jsonSendMail');
        $this->request->setParam('nodeId', 1);
        $this->request->setParam('id', 3);
        $this->request->setParam('recipients', array(1, 2));
        $this->request->setParam('additional', 'Test User <test@example.com>, '
            . 'Anotha Usa <foobar@example.com>; third@example.com');
        $response = $this->getResponse();

        $this->assertFalse($this->error, 'Response was: '. $response);
        $this->assertContains(Minutes_IndexController::MAIL_FAIL_TEXT, $response);
        $this->assertContains('Invalid email address detected: Test User <test@example.com>', $response);
        $this->assertContains('Invalid email address detected: Anotha Usa <foobar@example.com>; '
            . 'third@example.com', $response);
    }

    /**
     * Test the Minutes deletion
     */
    public function testJsonDeleteAction()
    {
        $this->setRequestUrl('MinutesItem/index/jsonDelete/');
        $this->request->setParam('id', 1);
        $this->request->setParam('minutesId', 3);
        $response = $this->getResponse();

        $this->assertContains(MinutesItem_IndexController::DELETE_TRUE_TEXT, $response);
    }

    /**
     * Test the Minutes item list
     */
    public function testJsonListActionAfterDeleteItem()
    {
        $this->setRequestUrl('MinutesItem/index/jsonList/');
        $this->request->setParam('minutesId', 3);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();

        $this->assertNotContains('"DerTitel"', $response);
        $this->assertContains('"SecondTitleSecondSave"', $response);
        $this->assertContains('"Some lines of new comment\\nSome lines of new comment"', $response);
        $this->assertContains(',"numRows":4}', $response);
    }

    /**
     * Test sending forms with errors
     */
    public function testJsonSaveWithNoTitle()
    {
        $this->setRequestUrl('MinutesItem/index/jsonSave/');
        $this->request->setParam('id', 0);
        $this->request->setParam('minutesId', 3);
        $this->request->setParam('sortOrder', 1);
        // missing title leads to error
        $this->request->setParam('title', '');
        $this->request->setParam('comment', "Some lines of new comment\nSome lines of new comment");
        $this->request->setParam('topicType', 2);
        $this->request->setParam('topicDate', '2009-05-01');
        $this->request->setParam('userId', 1);
        $this->request->setParam('nodeId', 1);
        $this->getResponse();

        $this->assertTrue($this->error);
        $this->assertContains('Title: Is a required field', $this->errormessage);
    }

    /**
     * Test sending forms with errors
     */
    public function testJsonSaveWithNotopicType()
    {
        $this->setRequestUrl('MinutesItem/index/jsonSave/');
        $this->request->setParam('id', 0);
        $this->request->setParam('minutesId', 3);
        $this->request->setParam('sortOrder', 1);
        $this->request->setParam('title', 'Title');
        $this->request->setParam('comment', "Some lines of new comment\nSome lines of new comment");
        // missing title leads to error
        $this->request->setParam('topicType', '');
        $this->request->setParam('topicDate', '2009-05-01');
        $this->request->setParam('userId', 1);
        $this->request->setParam('nodeId', 1);
        $this->getResponse();

        $this->assertTrue($this->error);
        $this->assertContains('Type: Is a required field', $this->errormessage);
    }

    /**
     * Minutes with itemStatus == 4 are read-only with exeption of writes to itemStatus itself.
     */
    public function testFinalizeMinutes()
    {
        $this->setRequestUrl('Minutes/index/jsonSave/');
        $this->request->setParam('id', 3);
        $this->request->setParam('itemStatus', 4);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();

        $this->assertFalse($this->error);
        $this->assertContains(Minutes_IndexController::EDIT_TRUE_TEXT, $response);
    }

    /**
     * Minutes with itemStatus == 4 are read-only with exeption of writes to itemStatus itself.
     */
    public function testItemsNowReadonly()
    {
        $this->setRequestUrl('MinutesItem/index/jsonSave/');
        $this->request->setParam('id', 2);
        $this->request->setParam('minutesId', 3);
        $this->request->setParam('parentOrder', 1);
        $this->request->setParam('sortOrder', 2);
        $this->request->setParam('title', 'ReadonlySecondTitleSecondSave');
        $this->request->setParam('comment', "ReadonlySome lines of new comment\nSome lines of new comment");
        $this->request->setParam('topicType', 2);
        $this->request->setParam('topicDate', '2009-05-01');
        $this->request->setParam('userId', 1);
        $this->request->setParam('nodeId', 1);
        $this->getResponse();

        $this->assertTrue($this->error);
        $this->assertContains(MinutesItem_IndexController::MINUTES_READ_ONLY, $this->errormessage);
    }

    /**
     * Minutes with itemStatus == 4 are read-only with exeption of writes to itemStatus itself.
     */
    public function testItemsUndeletable()
    {
        $this->setRequestUrl('MinutesItem/index/jsonDelete/');
        $this->request->setParam('id', 1);
        $this->request->setParam('minutesId', 3);
        $this->getResponse();

        $this->assertTrue($this->error);
        $this->assertContains(MinutesItem_IndexController::MINUTES_READ_ONLY, $this->errormessage);
    }

    /**
     * Minutes with itemStatus == 4 are read-only with exeption of writes to itemStatus itself.
     */
    public function testMinutesReadonly()
    {
        $this->setRequestUrl('Minutes/index/jsonSave/');
        $this->request->setParam('id', 3);
        $this->request->setParam('projectId', 1);
        $this->request->setParam('title', 'ReadOnly');
        $this->request->setParam('description', 'ReadOnly');
        $this->request->setParam('meetingDatetime', '2009-06-09 03:00:00');
        $this->request->setParam('endTime', strtotime('03:00'));
        $this->request->setParam('place', 'ReadOnly');
        $this->request->setParam('moderator', 'ReadOnly');
        $this->request->setParam('participantsInvited', array());
        $this->request->setParam('participantsAttending', array());
        $this->request->setParam('participantsExcused', array());
        $this->request->setParam('itemStatus', 1);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();

        $this->assertContains(Minutes_IndexController::EDIT_TRUE_TEXT, $response, "Response was: '$response'");
        $this->assertContains('"id":"3"', $response, "ID edited was not numbered 3.");
    }

    /**
     * Minutes with itemStatus == 4 are read-only with exeption of writes to itemStatus itself.
     */
    public function testEditWasNotDone()
    {
        $this->setRequestUrl('Minutes/index/jsonList/');
        $this->request->setParam('id', 3);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();

        $this->assertContains('"itemStatus":3', $response);
        $this->assertNotContains('ReadOnly', $response, "Response was edited: '$response'");
    }

    /**
     * Test the Minutes deletion with errors
     */
    public function testJsonDeleteActionWrongItemId()
    {
        $this->setRequestUrl('MinutesItem/index/jsonDelete/');
        $this->request->setParam('id', 12);
        $this->request->setParam('minutesId', 3);
        $this->getResponse();

        $this->assertTrue($this->error);
        $this->assertContains(MinutesItem_IndexController::NOT_FOUND, $this->errormessage);
    }

    /**
     * Test the Minutes deletion with errors
     */
    public function testJsonDeleteActionNoItemId()
    {
        $this->setRequestUrl('MinutesItem/index/jsonDelete/');
        $this->request->setParam('minutesId', 3);
        $this->getResponse();

        $this->assertTrue($this->error);
        $this->assertContains(MinutesItem_IndexController::ID_REQUIRED_TEXT, $this->errormessage);
    }

    /**
     * Test the Minutes deletion with errors
     */
    public function testJsonDeleteActionNoMinutesId()
    {
        $this->setRequestUrl('MinutesItem/index/jsonDelete/');
        $this->request->setParam('id', 1);
        $this->getResponse();

        $this->assertTrue($this->error);
        $this->assertContains(MinutesItem_IndexController::NOT_FOUND, $this->errormessage);
    }

    /**
     * Test the Minutes deletion with errors
     */
    public function testJsonDeleteWholeMinutes()
    {
        $this->setRequestUrl('Minutes/index/jsonDelete/');
        $this->request->setParam('id', 3);
        $response = $this->getResponse();

        $this->assertContains(Minutes_IndexController::DELETE_TRUE_TEXT, $response);
    }

    /**
     * Test the Minutes deletion with errors
     */
    public function testJsonDetailActionDeletedItem()
    {
        $this->setRequestUrl('MinutesItem/index/jsonDetail/');
        $this->request->setParam('id', 2);
        $this->request->setParam('minutesId', 3);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();

        $this->assertContains('{"metadata":[]}', $response, "Response was: '$response'");
    }
}
