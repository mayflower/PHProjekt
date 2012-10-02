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

    public function testGetFieldDefinition()
    {
        // startDatetime
        $data1                  = array();
        $data1['key']           = 'startDatetime';
        $data1['label']         = Phprojekt::getInstance()->translate('Start');
        $data1['originalLabel'] = 'Start';
        $data1['type']          = 'datetime';
        $data1['hint']          = Phprojekt::getInstance()->getTooltip('startDatetime');
        $data1['listPosition']  = 1;
        $data1['formPosition']  = 1;
        $data1['fieldset']      = '';
        $data1['range']         = array('id'   => '',
                                       'name' => '');
        $data1['required'] = true;
        $data1['readOnly'] = false;
        $data1['tab']      = 1;
        $data1['integer']  = false;
        $data1['length']   = 0;
        $data1['default']  = null;

        // endTtime
        $data2                  = array();
        $data2['key']           = 'endTime';
        $data2['label']         = Phprojekt::getInstance()->translate('End');
        $data2['originalLabel'] = 'End';
        $data2['type']          = 'time';
        $data2['hint']          = Phprojekt::getInstance()->getTooltip('endTime');
        $data2['listPosition']  = 2;
        $data2['formPosition']  = 2;
        $data2['fieldset']      = '';
        $data2['range']         = array('id'   => '',
                                        'name' => '');
        $data2['required'] = false;
        $data2['readOnly'] = false;
        $data2['tab']      = 1;
        $data2['integer']  = false;
        $data2['length']   = 0;
        $data2['default']  = null;

        $data3                  = array();
        $data3['key']           = 'minutes';
        $data3['label']         = Phprojekt::getInstance()->translate('Minutes');
        $data3['originalLabel'] = 'Minutes';
        $data3['type']          = 'text';
        $data3['hint']          = Phprojekt::getInstance()->getTooltip('minutes');
        $data3['listPosition']  = 3;
        $data3['formPosition']  = 3;
        $data3['fieldset']      = '';
        $data3['range']         = array('id'   => '',
                                        'name' => '');
        $data3['required'] = false;
        $data3['readOnly'] = false;
        $data3['tab']      = 1;
        $data3['integer']  = true;
        $data3['length']   = 0;
        $data3['default']  = null;

        $data4                  = array();
        $data4['key']           = 'projectId';
        $data4['label']         = Phprojekt::getInstance()->translate('Project');
        $data4['originalLabel'] = 'Project';
        $data4['type']          = 'selectbox';
        $data4['hint']          = Phprojekt::getInstance()->getTooltip('projectId');
        $data4['listPosition']  = 4;
        $data4['formPosition']  = 4;
        $data4['fieldset']      = '';
        $data4['range']         = array();
        $activeRecord = new Project_Models_Project();
        $tree = new Phprojekt_Tree_Node_Database($activeRecord, 1);
        $tree = $tree->setup();
        foreach ($tree as $node) {
            $data4['range'][] = array('id'   => (int) $node->id,
                                      'name' => $node->getDepthDisplay('title'));
        }
        $data4['required'] = true;
        $data4['readOnly'] = false;
        $data4['tab']      = 1;
        $data4['integer']  = true;
        $data4['length']   = 0;
        $data4['default']  = null;

        $data5                  = array();
        $data5['key']           = 'notes';
        $data5['label']         = Phprojekt::getInstance()->translate('Notes');
        $data5['originalLabel'] = 'Notes';
        $data5['type']          = 'textarea';
        $data5['hint']          = Phprojekt::getInstance()->getTooltip('notes');
        $data5['listPosition']  = 5;
        $data5['formPosition']  = 5;
        $data5['fieldset']      = '';
        $data5['range']         = array('id'   => '',
                                        'name' => '');
        $data5['required'] = false;
        $data5['readOnly'] = false;
        $data5['tab']      = 1;
        $data5['integer']  = false;
        $data5['length']   = 0;
        $data5['default']  = null;

        $timecardModel = clone($this->_model);
        $expected      = array($data1, $data2, $data3, $data4, $data5);
        $order         = Phprojekt_ModelInformation_Default::ORDERING_FORM;
        $this->assertEquals($expected, $timecardModel->getInformation()->getFieldDefinition($order));
    }

}
