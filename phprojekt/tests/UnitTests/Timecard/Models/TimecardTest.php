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
 * Tests Timecard Model Timecard class
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 * @group      timecard
 * @group      model
 * @group      timecard-model
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
        $this->assertEquals("120", $timecardModel->minutes);
        $this->assertEquals("1", $timecardModel->projectId);
        $this->assertEquals("My note", $timecardModel->notes);
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
        $timecardModel->projectId = 1;
        $timecardModel->notes     = 'TEST';
        $response                 = $timecardModel->recordValidate();
        $this->assertEquals(true, $response);

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
        $expectedMessage = 'Can not Start Working Time because this moment is occupied by an existing period or a '
            . 'open one';
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
        $timecardModel->endTime   = '25:00:00';
        $response                 = $timecardModel->recordValidate();
        $this->assertEquals(false, $response);
        $error           = $timecardModel->getError();
        $expectedMessage = 'End time has to be between 0:00 and 24:00';
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

        // Wrong data - Invalid start time
        $timecardModel->date      = '2009-05-17';
        $timecardModel->startTime = '10:60:00';
        $timecardModel->endTime   = '12:00:00';
        $response                 = $timecardModel->recordValidate();
        $this->assertEquals(false, $response);
        $error           = $timecardModel->getError();
        $expectedMessage = 'The start time is invalid';
        $this->assertEquals($expectedMessage, $error[0]['message']);

        // Wrong data - Invalid start time
        $timecardModel->date      = '2009-05-17';
        $timecardModel->startTime = null;
        $timecardModel->endTime   = '12:00:00';
        $response                 = $timecardModel->recordValidate();
        $this->assertEquals(false, $response);
        $error           = $timecardModel->getError();
        $expectedMessage = 'The start time is invalid';
        $this->assertEquals($expectedMessage, $error[0]['message']);

        // Wrong data - Invalid start time
        $timecardModel->date      = '2009-05-17';
        $timecardModel->startTime = '10:60:00';
        $timecardModel->endTime   = '12:00:00';
        $response                 = $timecardModel->recordValidate();
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
        $timecardModel            = clone($this->_model);
        $timecardModel->ownerId   = 1;
        $timecardModel->date      = '2009-05-17';
        $timecardModel->startTime = '14:00:00';
        $timecardModel->endTime   = '18:00:00';
        $timecardModel->projectId = 1;
        $timecardModel->notes     = 'TEST';
        $response                 = $timecardModel->recordValidate();
        $this->assertEquals(true, $response);
        $timecardModel->save();

        // Part 2 - Check it was well inserted
        $lastId = $timecardModel->id;
        unset($timecardModel);
        $timecardModel = clone($this->_model);
        $timecardModel->find($lastId);
        $this->assertEquals('2009-05-17', $timecardModel->date);
        $this->assertEquals('14:00:00', $timecardModel->startTime);
        $this->assertEquals('18:00:00', $timecardModel->endTime);

        // Part 3 - Insert open period
        unset($timecardModel);
        $timecardModel            = clone($this->_model);
        $timecardModel->ownerId   = 1;
        $timecardModel->date      = '2009-05-17';
        $timecardModel->startTime = '13:00:00';
        $timecardModel->projectId = 1;
        $timecardModel->notes     = 'TEST';
        $response                 = $timecardModel->recordValidate();
        $this->assertEquals(true, $response);
        $timecardModel->save();

        // Part 4 - Check it was well inserted
        $lastId = $timecardModel->id;
        unset($timecardModel);
        $timecardModel = clone($this->_model);
        $timecardModel->find($lastId);
        $timecardModel->ownerId = 1;
        $this->assertEquals('2009-05-17', $timecardModel->date);
        $this->assertEquals('13:00:00', $timecardModel->startTime);
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

    /**
     * Test for mock function
     */
    public function testMocks()
    {
        $timecardModel = clone($this->_model);
        $this->assertEquals(array(), $timecardModel->getRights());
    }

    public function testGetFieldDefinition()
    {
        // date
        $data1 = array();
        $data1['key']      = 'date';
        $data1['label']    = Phprojekt::getInstance()->translate('Date');
        $data1['type']     = 'date';
        $data1['hint']     = Phprojekt::getInstance()->getTooltip('date');
        $data1['order']    = 0;
        $data1['position'] = 1;
        $data1['fieldset'] = '';
        $data1['range']    = array('id'   => '',
                                   'name' => '');
        $data1['required'] = true;
        $data1['readOnly'] = false;
        $data1['tab']      = 1;
        $data1['integer']  = false;
        $data1['length']   = 0;

        // startDate
        $data2 = array();
        $data2['key']      = 'startTime';
        $data2['label']    = Phprojekt::getInstance()->translate('Start Time');
        $data2['type']     = 'time';
        $data2['hint']     = Phprojekt::getInstance()->getTooltip('startTime');
        $data2['order']    = 0;
        $data2['position'] = 2;
        $data2['fieldset'] = '';
        $data2['range']    = array('id'   => '',
                                   'name' => '');
        $data2['required'] = true;
        $data2['readOnly'] = false;
        $data2['tab']      = 1;
        $data2['integer']  = false;
        $data2['length']   = 0;

        // endDate
        $data3 = array();
        $data3['key']      = 'endTime';
        $data3['label']    = Phprojekt::getInstance()->translate('End Time');
        $data3['type']     = 'time';
        $data3['hint']     = Phprojekt::getInstance()->getTooltip('endTime');
        $data3['order']    = 0;
        $data3['position'] = 3;
        $data3['fieldset'] = '';
        $data3['range']    = array('id'   => '',
                                   'name' => '');
        $data3['required'] = false;
        $data3['readOnly'] = false;
        $data3['tab']      = 1;
        $data3['integer']  = false;
        $data3['length']   = 0;

        $data4 = array();
        $data4['key']      = 'minutes';
        $data4['label']    = Phprojekt::getInstance()->translate('Minutes');
        $data4['type']     = 'text';
        $data4['hint']     = Phprojekt::getInstance()->getTooltip('minutes');
        $data4['order']    = 0;
        $data4['position'] = 4;
        $data4['fieldset'] = '';
        $data4['range']    = array('id'   => '',
                                   'name' => '');
        $data4['required'] = false;
        $data4['readOnly'] = false;
        $data4['tab']      = 1;
        $data4['integer']  = true;
        $data4['length']   = 0;

        $data5 = array();
        $data5['key']      = 'projectId';
        $data5['label']    = Phprojekt::getInstance()->translate('Project');
        $data5['type']     = 'time';
        $data5['hint']     = Phprojekt::getInstance()->getTooltip('projectId');
        $data5['order']    = 0;
        $data5['position'] = 5;
        $data5['fieldset'] = '';
        $data5['range']    = array();
        $data5['type']     = 'selectbox';
        $activeRecord = Phprojekt_Loader::getModel('Project', 'Project');
        $tree = new Phprojekt_Tree_Node_Database($activeRecord, 1);
        $tree = $tree->setup();
        foreach ($tree as $node) {
            $data5['range'][] = array('id'   => (int) $node->id,
                                      'name' => $node->getDepthDisplay('title'));
        }
        $data5['required'] = true;
        $data5['readOnly'] = false;
        $data5['tab']      = 1;
        $data5['integer']  = true;
        $data5['length']   = 0;

        $data6 = array();
        $data6['key']      = 'notes';
        $data6['label']    = Phprojekt::getInstance()->translate('Notes');
        $data6['type']     = 'textarea';
        $data6['hint']     = Phprojekt::getInstance()->getTooltip('notes');
        $data6['order']    = 0;
        $data6['position'] = 6;
        $data6['fieldset'] = '';
        $data6['range']    = array('id'   => '',
                                   'name' => '');
        $data6['required'] = true;
        $data6['readOnly'] = false;
        $data6['tab']      = 1;
        $data6['integer']  = false;
        $data6['length']   = 0;

        $timecardModel = clone($this->_model);
        $expected      = array($data1, $data2, $data3, $data4, $data5, $data6);
        $order         = Phprojekt_ModelInformation_Default::ORDERING_FORM;
        $this->assertEquals($expected, $timecardModel->getInformation()->getFieldDefinition($order));
    }
}
