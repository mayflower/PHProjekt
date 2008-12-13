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
 * @version    $Id:$
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
 */
class Timecard_IndexController_Test extends FrontInit
{
    /**
     * Test if the limits work
     */
    public function testJsonListAction()
    {
        $this->setRequestUrl('Timecard/index/jsonList/');
        $this->request->setParam('year', 2008);
        $this->request->setParam('month', '04');
        $this->request->setParam('view', 'month');
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, '"numRows":'.date("t").'}') > 0);
    }

    public function testJsonStartAction()
    {
        $this->setRequestUrl('Timecard/index/jsonStart');
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, Timecard_IndexController::ADD_TRUE_TEXT) > 0);
    }

    public function testJsonStopAction()
    {
        $this->setRequestUrl('Timecard/index/jsonStop');
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, Timecard_IndexController::ADD_TRUE_TEXT) > 0);
    }

    public function testJsonStopActionNoRecordOpen()
    {
        $this->setRequestUrl('Timecard/index/jsonStop');
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, Timecard_IndexController::NOT_FOUND) > 0);
    }
}
