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
 * @copyright  Copyright (c) 2011 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @version    Release: 6.1.0
 */


/**
 * Tests for Index Controller
 *
 * @version    Release: 6.1.0
 * @group      calendar2
 * @group      calendar
 * @group      controller
 * @group      calendar2-controller
 * @group      calendar-controller
 */
class Calendar2_IndexController_Test extends FrontInit
{
    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/../../common.xml');
    }

    /**
     * Test creation and subsequent deletion of the same event.
     */
    public function testCreateAndDelete()
    {
        $this->_setTimezone(1);

        $this->setRequestUrl('Calendar2/index/jsonSave/nodeId/1/id/0');
        $this->request->setParam('comments', '');
        $this->request->setParam('confirmationStatus', '2');
        $this->request->setParam('description', '');
        $this->request->setParam('end', '2011-12-16 09:00');
        $this->request->setParam('location', '');
        $this->request->setParam('ownerId', '3');
        $this->request->setParam('participants', '3');
        $this->request->setParam('sendNotification', '0');
        $this->request->setParam('start', '2011-12-16 08:00');
        $this->request->setParam('summary', 'asd');
        $this->request->setParam('visibility', '1');
        $response = $this->getResponse();
        $this->assertContains(IndexController::ADD_TRUE_TEXT, $response);

        $response = Zend_Json::decode(substr($response, 5, -1));
        $this->assertArrayHasKey('id', $response);
        $id = $response['id'];

        $this->_reset();
        $this->setRequestUrl("Calendar2/index/jsonDelete/id/{$id}/occurrence/2011-12-16%2007:00:00");
        $response = $this->getResponse();
        $this->assertContains(IndexController::DELETE_TRUE_TEXT, $response);

    }

    private function _setTimezone($offset)
    {
        $this->request = new Zend_Controller_Request_Http();
        $this->setRequestUrl('Core/setting/jsonSave/nodeId/1/moduleName/User');
        $this->request->setParam('confirmValue', '');
        $this->request->setParam('email', '');
        $this->request->setParam('language', 'en');
        $this->request->setParam('oldValue', '');
        $this->request->setParam('password', '');
        $this->request->setParam('proxies[]', '');
        $this->request->setParam('timeZone', "{$offset}");
        $response = $this->getResponse();
        $this->assertContains(IndexController::EDIT_TRUE_TEXT, $response);
        $this->_reset();
    }
}
