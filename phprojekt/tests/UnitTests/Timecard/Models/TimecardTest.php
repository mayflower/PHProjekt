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
 * Tests Timecard Model class
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 */
class Timecard_Models_Timecard_Test extends PHPUnit_Framework_TestCase
{
    /**
     * setUp method for PHPUnit
     */
    public function setUp()
    {
        $this->_model = new Timecard_Models_Timecard();
    }

    /**
     * Test simple finding
     */
    public function testSimpleFind()
    {
        $timecardModel = clone($this->_model);
        $timecardModel->find(7);
        $this->assertEquals("2009-05-16", $timecardModel->date);
        $this->assertEquals("10:30:00", $timecardModel->startTime);
        $this->assertEquals("12:30:00", $timecardModel->endTime);
    }

    /**
     * Test record validate
     */
    public function testRecordValidate()
    {
        // Right data
        $timecardModel            = clone($this->_model);
        $timecardModel->date      = '2009-05-17';
        $timecardModel->startTime = '10:00:00';
        $timecardModel->endTime   = '18:00:00';
        $response                 = $timecardModel->recordValidate();
        $this->assertEquals(true, $response);

        // Wrong data - Start time too early
        $timecardModel->date      = '2009-05-17';
        $timecardModel->startTime = '07:00:00';
        $timecardModel->endTime   = '18:00:00';
        $response                 = $timecardModel->recordValidate();
        $this->assertEquals(false, $response);
        $error = $timecardModel->getError();
        $this->assertEquals('Start time has to be between 8:00 and 21:00', $error[0]['message']);

        // Wrong data - Start time invalid
        $timecardModel->date      = '2009-05-17';
        $timecardModel->startTime = '09:60:00';
        $timecardModel->endTime   = '18:00:00';
        $response                 = $timecardModel->recordValidate();
        $this->assertEquals(false, $response);
        $error = $timecardModel->getError();
        $this->assertEquals('The start time is invalid', $error[0]['message']);

        // Wrong data, only start time but overlapping existing period
        $timecardModel->date      = '2009-05-16';
        $timecardModel->startTime = '11:00:00';
        $timecardModel->endTime   = null;
        $response                 = $timecardModel->recordValidate();
        $this->assertEquals(false, $response);
        $error           = $timecardModel->getError();
        $expectedMessage = 'Can not Start Working Time because this moment is occupied by an existing period.';
        $this->assertEquals($expectedMessage, $error[0]['message']);

        // Wrong data start time after end time
        $timecardModel->date      = '2009-05-17';
        $timecardModel->startTime = '18:00:00';
        $timecardModel->endTime   = '11:00:00';
        $response = $timecardModel->recordValidate();
        $this->assertEquals(false, $response);
        $error           = $timecardModel->getError();
        $expectedMessage = 'The end time must be after the start time';
        $this->assertEquals($expectedMessage, $error[0]['message']);

        // Wrong data - End time too late
        $timecardModel->date      = '2009-05-17';
        $timecardModel->startTime = '10:00:00';
        $timecardModel->endTime   = '22:00:00';
        $response                 = $timecardModel->recordValidate();
        $this->assertEquals(false, $response);
        $error           = $timecardModel->getError();
        $expectedMessage = 'End time has to be between 8:00 and 21:00';
        $this->assertEquals($expectedMessage, $error[0]['message']);

        // Wrong data - Invalid end time
        $timecardModel->date      = '2009-05-17';
        $timecardModel->startTime = '10:00:00';
        $timecardModel->endTime   = '12:60:00';
        $response                 = $timecardModel->recordValidate();
        $this->assertEquals(false, $response);
        $error           = $timecardModel->getError();
        $expectedMessage = 'The end time is invalid';
        $this->assertEquals($expectedMessage, $error[0]['message']);
    }

    /**
     * Test record validate and Saving
     */
    public function testRecordValidateAndSave()
    {
        // Will be inserted a open period and then tried to close it in an overlapping end time, then close it right
        // Part 1 - Insert common period
        $timecardModel            = clone($this->_model);
        $timecardModel->ownerId   = 1;
        $timecardModel->date      = '2009-05-17';
        $timecardModel->startTime = '14:00:00';
        $timecardModel->endTime   = '18:00:00';
        $response                 = $timecardModel->recordValidate();
        $this->assertEquals(true, $response);
        $timecardModel->save();

        // Part 2 - Check it was well inserted
        unset($timecardModel);
        $timecardModel = clone($this->_model);
        $timecardModel->find(12);
        $this->assertEquals('2009-05-17', $timecardModel->date);
        $this->assertEquals('14:00:00', $timecardModel->startTime);
        $this->assertEquals('18:00:00', $timecardModel->endTime);

        // Part 3 - Insert open period
        unset($timecardModel);
        $timecardModel            = clone($this->_model);
        $timecardModel->ownerId   = 1;
        $timecardModel->date      = '2009-05-17';
        $timecardModel->startTime = '13:00:00';
        $response                 = $timecardModel->recordValidate();
        $this->assertEquals(true, $response);
        $timecardModel->save();

        // Part 4 - Check it was well inserted
        unset($timecardModel);
        $timecardModel = clone($this->_model);
        $timecardModel->find(13);
        $timecardModel->ownerId = 1;
        $this->assertEquals('2009-05-17', $timecardModel->date);
        $this->assertEquals('13:00:00', $timecardModel->startTime);
        $this->assertEquals(null, $timecardModel->endTime);

        // Part 5 - Try to close previous period overlapping another
        $timecardModel->endTime = '15:00:00';
        $response               = $timecardModel->recordValidate();
        $this->assertEquals(false, $response);
        $error           = $timecardModel->getError();
        $expectedMessage = 'Can not End Working Time because this moment is occupied by an existing period.';
        $this->assertEquals($expectedMessage, $error[0]['message']);

        // Part 6 - Close previous period not overlapping another
        $timecardModel->endTime = '13:30:00';
        $response               = $timecardModel->save();
        $this->assertEquals(true, $response);
    }

    /**
     * Test for mock function
     */
    public function testMocks()
    {
        $timecardModel = clone($this->_model);
        $this->assertEquals(array(), $timecardModel->getRights());
        $this->assertEquals(array(), $timecardModel->getInformation()->getTitles());
    }
}
