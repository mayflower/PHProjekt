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
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */


/**
 * Tests Timecard Model Timecard class
 *
 * @group      timecard
 * @group      model
 * @group      timecard-model
 */
class Timecard_Models_Timecard_Test extends DatabaseTest
{
    protected function getDataSet()
    {
        return new PHPUnit_Extensions_Database_DataSet_CompositeDataSet(
            array(
                $this->createFlatXMLDataSet(dirname(__FILE__) . '/../../common.xml'),
                $this->createFlatXMLDataSet(dirname(__FILE__) . '/../data.xml')
            )
        );
    }

    /**
     * setUp method for PHPUnit
     */
    public function setUp()
    {
        parent::setUp();
        $this->_model = new Timecard_Models_Timecard();
    }

    /**
     * Test simple finding
     */
    public function testSimpleFind()
    {
        $timecardModel = clone($this->_model);
        $timecardModel->find(7);
        $this->assertEquals("2009-05-17 09:00:00", $timecardModel->startDatetime);
        $this->assertEquals("13:00:00", $timecardModel->endTime);
        $this->assertEquals("240", $timecardModel->minutes);
        $this->assertEquals("1", $timecardModel->projectId);
        $this->assertEquals("My note", $timecardModel->notes);
    }

    /**
     * Test record validate
     */
    public function testRecordValidate()
    {
        // Right data
        $timecardModel                = clone($this->_model);
        $timecardModel->startDatetime = '2009-05-01 10:00:00';
        $timecardModel->endTime       = '18:00:00';
        $timecardModel->projectId     = 1;
        $timecardModel->notes         = 'TEST';
        $response                     = $timecardModel->recordValidate();
        $this->assertEquals(true, $response);

        // Wrong data - Start time invalid
        $timecardModel->startDatetime = '2009-05-17 09:60:00';
        $timecardModel->endTime       = '18:00:00';
        $response                     = $timecardModel->recordValidate();
        $this->assertEquals(false, $response);
        $error = $timecardModel->getError();
        $this->assertEquals('The start time is invalid', $error[0]['message']);

        // Wrong data, only start time but overlapping existing period
        $timecardModel->startDatetime = '2009-05-17 11:00:00';
        $timecardModel->endTime       = null;
        $response                     = $timecardModel->recordValidate();
        $this->assertEquals(false, $response);
        $error           = $timecardModel->getError();
        $expectedMessage = 'The entry overlaps with an existing one';
        $this->assertEquals($expectedMessage, $error[0]['message']);

        // Wrong data start time after end time
        $timecardModel->startDatetime = '2009-05-18 18:00:00';
        $timecardModel->endTime       = '11:00:00';
        $response = $timecardModel->recordValidate();
        $this->assertEquals(false, $response);
        $error           = $timecardModel->getError();
        $expectedMessage = 'The end time must be after the start time';
        $this->assertEquals($expectedMessage, $error[0]['message']);

        // Wrong data - End time too late
        $timecardModel->startDatetime = '2009-05-18 10:00:00';
        $timecardModel->endTime       = '25:00:00';
        $response                     = $timecardModel->recordValidate();
        $this->assertEquals(false, $response);
        $error           = $timecardModel->getError();
        $expectedMessage = 'End time has to be between 0:00 and 24:00';
        $this->assertEquals($expectedMessage, $error[0]['message']);

        // Wrong data - Invalid end time
        $timecardModel->startDatetime = '2009-05-18 10:00:00';
        $timecardModel->endTime       = '12:60:00';
        $response                     = $timecardModel->recordValidate();
        $this->assertEquals(false, $response);
        $error           = $timecardModel->getError();
        $expectedMessage = 'The end time is invalid';
        $this->assertEquals($expectedMessage, $error[0]['message']);

        // Wrong data - Invalid start time
        $timecardModel->startDatetime = '2009-05-18 10:60:00';
        $timecardModel->endTime       = '12:00:00';
        $response                     = $timecardModel->recordValidate();
        $this->assertEquals(false, $response);
        $error           = $timecardModel->getError();
        $expectedMessage = 'The start time is invalid';
        $this->assertEquals($expectedMessage, $error[0]['message']);

        // Wrong data - Invalid start time
        $timecardModel->startDatetime = '2009-05-18';
        $timecardModel->endTime       = '12:00:00';
        $response                     = $timecardModel->recordValidate();
        $this->assertEquals(false, $response);
        $error           = $timecardModel->getError();
        $expectedMessage = 'Invalid Format';
        $this->assertEquals($expectedMessage, $error[0]['message']);

        // Wrong data - Invalid start time
        $timecardModel->startDatetime = '2009-05-18 10:60:00';
        $timecardModel->endTime       = '12:00:00';
        $response                     = $timecardModel->recordValidate();
        $this->assertEquals(false, $response);
        $error           = $timecardModel->getError();
        $expectedMessage = 'The start time is invalid';
        $this->assertEquals($expectedMessage, $error[0]['message']);
    }

    /**
     * Test record validate and Saving
     */
    public function testRecordValidateAndSave()
    {
        // Will be inserted a open period and then tried to close it in an overlapping end time, then close it right
        // Part 1 - Insert common period
        $timecardModel                = clone($this->_model);
        $timecardModel->ownerId       = 1;
        $timecardModel->startDatetime = '2009-05-18 14:00:00';
        $timecardModel->endTime       = '18:00:00';
        $timecardModel->projectId     = 1;
        $timecardModel->notes         = 'TEST';
        $response                     = $timecardModel->recordValidate();
        $this->assertEquals(true, $response);
        $timecardModel->save();

        // Part 2 - Check it was well inserted
        $lastId = $timecardModel->id;
        unset($timecardModel);
        $timecardModel = clone($this->_model);
        $timecardModel->find($lastId);
        $this->assertEquals('2009-05-18 14:00:00', $timecardModel->startDatetime);
        $this->assertEquals('18:00:00', $timecardModel->endTime);

        // Part 3 - Insert open period
        unset($timecardModel);
        $timecardModel                = clone($this->_model);
        $timecardModel->ownerId       = 1;
        $timecardModel->startDatetime = '2009-05-17 13:00:00';
        $timecardModel->projectId     = 1;
        $timecardModel->notes         = 'TEST';
        $response                     = $timecardModel->recordValidate();
        $this->assertEquals(true, $response);
        $timecardModel->save();

        // Part 4 - Check it was well inserted
        $lastId = $timecardModel->id;
        unset($timecardModel);
        $timecardModel = clone($this->_model);
        $timecardModel->find($lastId);
        $timecardModel->ownerId = 1;
        $this->assertEquals('2009-05-17 13:00:00', $timecardModel->startDatetime);
        $this->assertEquals(null, $timecardModel->endTime);

        // Part 5 - Try to close previous period overlapping another
        $timecardModel->endTime = '15:00:00';
        $response               = $timecardModel->recordValidate();
        $this->assertEquals(false, $response);
        $error           = $timecardModel->getError();
        $expectedMessage = 'Can not End Working Time because this moment is occupied by an existing period';
        $this->assertEquals($expectedMessage, $error[0]['message']);

        // Part 6 - Close previous period not overlapping another
        $timecardModel->endTime = '13:30:00';
        $response               = $timecardModel->save();
        $this->assertEquals(true, $response);
    }
}
