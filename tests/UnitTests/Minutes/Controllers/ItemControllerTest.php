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
        $this->markTestIncomplete('Not yet implemented');
        return;
        $this->setRequestUrl('Minutes/index/jsonList/nodeId/1');
        $this->request->setParam('start', 0);
        $response = $this->getResponse();
        $this->assertTrue(strpos($response, '{"metadata":[]}') > 0);
    }

    /**
     * Test of json save Minutes
     */
    public function testJsonSave()
    {
        $this->markTestIncomplete('Not yet implemented');
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
