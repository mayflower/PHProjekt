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
 * Tests for Index Controller
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Timecard_IndexController_Test extends FrontInit
{
    /**
     * Test if the limits work
     */
    public function testJsonListAction()
    {
        $this->request->setParams(array('action' => 'jsonList', 'controller' => 'index', 'module' => 'Timecard'));
        $this->request->setBaseUrl($this->config->webpath
            . 'index.php/Timecard/index/jsonList/year/2008/month/04/view/month');
        $this->request->setPathInfo('/Timecard/index/jsonList/year/2008/month/04/view/month');
        $this->request->setRequestUri('/Timecard/index/jsonList/year/2008/month/04/view/month');
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, '"numRows":'.date("t").'}') > 0);
    }

    public function testJsonStartAction()
    {
        $this->request->setParams(array('action' => 'jsonStart', 'controller' => 'index', 'module' => 'Timecard'));
        $this->request->setBaseUrl($this->config->webpath . 'index.php/Timecard/index/jsonStart');
        $this->request->setPathInfo('/Timecard/index/jsonStart');
        $this->request->setRequestUri('/Timecard/index/jsonStart');
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, 'The Item was added correctly') > 0);
    }

    public function testJsonStopAction()
    {
        $this->request->setParams(array('action' => 'jsonStart', 'controller' => 'index', 'module' => 'Timecard'));
        $this->request->setBaseUrl($this->config->webpath . 'index.php/Timecard/index/jsonStop');
        $this->request->setPathInfo('/Timecard/index/jsonStop');
        $this->request->setRequestUri('/Timecard/index/jsonStop');
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, 'The Item was added correctly') > 0);
    }

    public function testJsonStopActionNoRecordOpen()
    {
        $this->request->setParams(array('action' => 'jsonStart', 'controller' => 'index', 'module' => 'Timecard'));
        $this->request->setBaseUrl($this->config->webpath . 'index.php/Timecard/index/jsonStop');
        $this->request->setPathInfo('/Timecard/index/jsonStop');
        $this->request->setRequestUri('/Timecard/index/jsonStop');
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, 'The Item was not found') > 0);
    }
}
