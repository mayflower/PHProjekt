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
 * @subpackage Calendar
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 */


/**
 * Tests Calendar Model class
 *
 * @category   PHProjekt
 * @package    UnitTests
 * @subpackage Calendar
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 * @group      calendar
 * @group      model
 * @group      calendar-model
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
        $calendarModel->find(27);
        $return = $calendarModel->getRootEventId($calendarModel);
        $this->assertEquals(26, $return);
    }

    /**
     * Test recordValidate method
     */
    public function testRecordValidate()
    {
        $calendarModel = clone($this->_model);
        $calendarModel->find(27);
        $return = $calendarModel->recordValidate();
        $this->assertEquals(true, $return);

        $calendarModel->startDatetime = '2009-10-10';
        $return = $calendarModel->recordValidate();
        $this->assertEquals(false, $return);
    }

    /**
     * Test recordValidate method
     */
    public function testRecordValidateWrong()
    {
        // Wrong data: Start date after end date
        $calendarModel = clone($this->_model);
        $calendarModel->startDatetime = '2009-10-10 12:00:00';
        $calendarModel->endDatetime   = '2009-09-10 12:00:00';

        $return = $calendarModel->recordValidate();
        $this->assertEquals(false, $return);

        // Wrong data: Start time after end time
        $calendarModel = clone($this->_model);
        $calendarModel->startDatetime = '2009-10-10 15:00:00';
        $calendarModel->endDatetime   = '2009-10-10 14:00:00';

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
        $expected = '';
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
     * Test getRelatedEvents method
     */
    public function testGetRelatedEvents()
    {
        $calendarModel = clone($this->_model);
        $calendarModel->find(18);
        $return = $calendarModel->getRelatedEvents();
        $this->assertEquals(array(19,20,21), $return);

        // No existing id
        $calendarModel = clone($this->_model);
        $calendarModel->find(50);
        $return = $calendarModel->getRelatedEvents();
        $this->assertEquals(array(), $return);
    }

    /**
     * Test deleteEvents method
     */
    public function testDeleteEvents()
    {
        $calendarModel = clone($this->_model);
        $before        = count($calendarModel->fetchAll());
        $calendarModel->find(18);
        $calendarModel->deleteEvents(true, true);
        $after = count($calendarModel->fetchAll());
        $this->assertEquals($before - 8, $after);

        $calendarModel = clone($this->_model);
        $before        = count($calendarModel->fetchAll());
        $calendarModel->find(27);
        $calendarModel->deleteEvents(false, false);
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
        $calendarModel->deleteEvents(true, true);
        $after = count($calendarModel->fetchAll());
        $this->assertEquals($before, $after);
    }
}
