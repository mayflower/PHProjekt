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
 * Tests Phprojekt Model Information Default class
 *
 * @group      phprojekt
 * @group      modelinformation
 * @group      phprojekt-modelinformation
 * @group      model
 * @group      activerecord
 * @group      databasemanager
 */
class Phprojekt_ModelInformation_DefaultTest extends PHPUnit_Framework_TestCase
{
    private $_model    = null;
    private $_testData = array();

    /**
     * setUp method for PHPUnit
     */
    public function setUp()
    {
        $this->_model      = new Phprojekt_ModelInformation_Default();
        $this->_testData[] = array(
                'key'           => 'startDatetime',
                'label'         => Phprojekt::getInstance()->translate('Start'),
                'originalLabel' => 'Start',
                'type'          => 'datetime',
                'hint'          => Phprojekt::getInstance()->getTooltip('startDatetime'),
                'listPosition'  => 1,
                'formPosition'  => 1,
                'fieldset'      => '',
                'range'         => array('id'   => '',
                                         'name' => ''),
                'required' => true,
                'readOnly' => false,
                'tab'      => 1,
                'integer'  => false,
                'length'   => 0,
                'default'  => null);
        $this->_testData[] = array(
                'key'           => 'notes',
                'label'         => Phprojekt::getInstance()->translate('Notes'),
                'originalLabel' => 'Notes',
                'type'          => 'textarea',
                'hint'          => Phprojekt::getInstance()->getTooltip('notes'),
                'listPosition'  => 0,
                'formPosition'  => 2,
                'fieldset'      => '',
                'range'         => array('id'   => '',
                                         'name' => ''),
                'required' => false,
                'readOnly' => false,
                'tab'      => 1,
                'integer'  => false,
                'length'   => 255,
                'default'  => '--');
    }

    /**
     * Test fillField
     */
    public function testFillField()
    {
        $this->_model->fillField('startDatetime', 'Start', 'datetime', 1, 1, array(
            'required' => true));
        $this->_model->fillField('notes', 'Notes', 'textarea', 0, 2, array(
            'length'  => 255,
            'default' => '--'));

        $order = Phprojekt_ModelInformation_Default::ORDERING_FORM;
        $this->assertEquals($this->_testData, $this->_model->getFieldDefinition($order));
    }

    /**
     * Test fieldDefinition
     */
    public function testGetFieldDefinition()
    {
        $model = new Phprojekt_ModelInformation_Default();
        $order = Phprojekt_ModelInformation_Default::ORDERING_FORM;
        $this->assertEquals(array(), $model->getFieldDefinition($order));

        $model->fillField('startDatetime', 'Start', 'datetime', 1, 1, array(
            'required' => true));
        $model->fillField('notes', 'Notes', 'textarea', 0, 2, array(
            'length'  => 255,
            'default' => '--'));

        $order = Phprojekt_ModelInformation_Default::ORDERING_FORM;
        $this->assertEquals($this->_testData, $model->getFieldDefinition($order));

        $order  = Phprojekt_ModelInformation_Default::ORDERING_LIST;
        $result = $model->getFieldDefinition($order);
        $this->assertEquals($this->_testData[0], $result[0]);
    }

    /**
     * Test testGetFullRangeValues
     */
    public function testGetFullRangeValues()
    {
        $records = $this->_model->getFullRangeValues('8', 'Title');
        $this->assertEquals($records, array('id'           => 8,
                                            'name'         => 'Title',
                                            'originalName' => 'Title'));
    }

    /**
     * Test getRangeValues
     */
    public function testGetRangeValues()
    {
        $records = $this->_model->getRangeValues('8', 'Title');
        $this->assertEquals($records, array('id'   => 8,
                                            'name' => 'Title'));
    }

    /**
     * Test getProjectRange
     */
    public function testGetProjectRange()
    {
        $records = $this->_model->getProjectRange();
        $this->assertEquals($records[0]['id'], 1);
        $this->assertEquals($records[0]['name'], "PHProjekt");
    }
}
