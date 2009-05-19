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
 * Tests Calendar Model class
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 */
class Calendar_Models_Calendar_Test extends PHPUnit_Framework_TestCase
{
    private $_model = null;

    /**
     * setUp method for PHPUnit
     */
    public function setUp()
    {
        $this->_model = new Calendar_Models_Calendar();
    }

    /**
     * Test getRootEvent method
     */
    public function testGetRootEventId()
    {
        $calendarModel = clone($this->_model);
        $calendarModel->find(7);
        $return = $calendarModel->getRootEventId($calendarModel);
        $this->assertEquals(6, $return);
    }

    /**
     * Test recordValidate method
     */
    public function testRecordValidate()
    {
        $calendarModel = clone($this->_model);
        $calendarModel->find(7);
        $return = $calendarModel->recordValidate();
        $this->assertEquals(true, $return);

        $calendarModel->startDate = 'hello';
        $return = $calendarModel->recordValidate();
        $this->assertEquals(false, $return);
    }

    /**
     * Test getAllParticipants method
     */
    public function testGetAllParticipants()
    {
        $calendarModel = clone($this->_model);
        $calendarModel->find(18);
        $return = $calendarModel->getAllParticipants();
        $this->assertEquals('1,2', $return);

        // No existing id
        $calendarModel = clone($this->_model);
        $calendarModel->find(50);
        $return   = $calendarModel->getAllParticipants();
        $expected = array();
        $this->assertEquals($expected, $return);
    }

    /**
     * Test getRecursionStartDate method
     */
    public function testGetRecursionStartDate()
    {
        $calendarModel = clone($this->_model);
        $return        = $calendarModel->getRecursionStartDate(18, '2009-09-01');
        $this->assertEquals('2009-03-01', $return);
    }

    /**
     * Test deleteEvents method
     */
    public function testDeleteEvents()
    {
        $calendarModel = clone($this->_model);
        $before        = count($calendarModel->fetchAll());
        $calendarModel->find(18);
        $calendarModel->deleteEvents(true);
        $after = count($calendarModel->fetchAll());
        $this->assertEquals($before - 8, $after);

        $calendarModel = clone($this->_model);
        $before        = count($calendarModel->fetchAll());
        $calendarModel->find(7);
        $calendarModel->deleteEvents(false);
        $after = count($calendarModel->fetchAll());
        $this->assertEquals($before - 1, $after);
    }

    /**
     * Test deleteEvents method with no existing id
     */
    public function testDeleteEventsWrongId()
    {
        $calendarModel = clone($this->_model);
        $before        = count($calendarModel->fetchAll());
        $calendarModel->find(50);
        $calendarModel->deleteEvents(true);
        $after = count($calendarModel->fetchAll());
        $this->assertEquals($before, $after);
    }
}
