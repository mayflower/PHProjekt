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
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    CVS: $Id:
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */
require_once 'PHPUnit/Framework.php';

/**
 * Tests for Calendar Index Controller
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Calendar_IndexController_Test extends FrontInit
{
    /**
     * Test of json save calendar
     */
    public function testJsonSave()
    {
        $this->request->setParams(array('action' => 'jsonSave', 'controller' => 'index', 'module' => 'Project'));
        $this->request->setBaseUrl($this->config->webpath
            . 'index.php/Calendar/index/jsonSave/'
            . 'endTime/Thu Jan 01 1970 10:00:00 GMT-0300 (SA Eastern Standard Time)/notes/test note/projectId/1/'
            . 'startDate/Mon Jun 02 2008 00:00:00 GMT-0300 (SA Eastern Standard Time)/'
            . 'startTime/Thu Jan 01 1970 09:00:00 GMT-0300 (SA Eastern Standard Time)/title/test/participantId/1');
        $this->request->setPathInfo('/Calendar/index/jsonSave/'
            . 'endTime/Thu Jan 01 1970 10:00:00 GMT-0300 (SA Eastern Standard Time)/notes/test note/projectId/1/'
            . 'startDate/Mon Jun 02 2008 00:00:00 GMT-0300 (SA Eastern Standard Time)/'
            . 'startTime/Thu Jan 01 1970 09:00:00 GMT-0300 (SA Eastern Standard Time)/title/test/participantId/1');
        $this->request->setRequestUri('/Calendar/index/jsonSave/'
            . 'endTime/Thu Jan 01 1970 10:00:00 GMT-0300 (SA Eastern Standard Time)/notes/test note/projectId/1/'
            . 'startDate/Mon Jun 02 2008 00:00:00 GMT-0300 (SA Eastern Standard Time)/'
            . 'startTime/Thu Jan 01 1970 09:00:00 GMT-0300 (SA Eastern Standard Time)/title/test/participantId/1');
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, 'The Item was added correctly') > 0);
    }

    /**
     * Test the calendar event detail
     */

    public function testJsonDetailAction()
    {
        $this->request->setParams(array('action' => 'jsonList', 'controller' => 'index', 'module' => 'Calendar'));
        $this->request->setBaseUrl($this->config->webpath . 'index.php/Calendar/index/jsonDetail/id/1');
        $this->request->setPathInfo('/Calendar/index/jsonDetail/id/1');
        $this->request->setRequestUri('/Calendar/index/jsonDetail/id/1');
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, '"numRows":1') > 0);
    }

    /**
     * Test the calendar list
     */

    public function testJsonListAction()
    {
        $this->request->setParams(array('action' => 'jsonList', 'controller' => 'index', 'module' => 'Calendar'));
        $this->request->setBaseUrl($this->config->webpath . 'index.php/Calendar/index/jsonList');
        $this->request->setPathInfo('/Calendar/index/jsonList');
        $this->request->setRequestUri('/Calendar/index/jsonList');
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, '"numRows":1}') > 0);
    }

    /**
     * Test the calendar deletion
     */
    public function testJsonDeleteAction()
    {
        $this->request->setParams(array('action' => 'jsonList', 'controller' => 'index', 'module' => 'Calendar'));
        $this->request->setBaseUrl($this->config->webpath . 'index.php/Calendar/index/jsonDelete/id/1');
        $this->request->setPathInfo('/Calendar/index/jsonDelete/id/1');
        $this->request->setRequestUri('/Calendar/index/jsonDelete/id/1');
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, 'The Item was deleted correctly') > 0);
    }
}
