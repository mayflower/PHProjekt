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
 * Tests Converter text class
 *
 * @group      phprojekt
 * @group      converter
 * @group      text
 * @group      phprojekt-converter
 * @group      phprojekt-converter-text
 */
class Phprojekt_Converter_TextTest extends DatabaseTest
{
    public function setUp()
    {
        parent::setUp();
        $this->sharedFixture = Phprojekt::getInstance()->getDb();
    }

    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/../data.xml');
    }

    /**
     * Test text converter
     */
    public function testConvertPart1()
    {
        $model           = new Project_Models_Project();
        $order           = Phprojekt_ModelInformation_Default::ORDERING_FORM;
        $fieldDefinition = $model->getInformation()->getFieldDefinition($order);
        $object          = $model->find(1);

        foreach ($fieldDefinition as $info) {
            // Selectbox
            if ($info['key'] == 'currentStatus') {
                $value = Phprojekt_Converter_Text::convert($object, $info);
                $this->assertEquals('Working', $value);
            }
            // Percentage
            if ($info['key'] == 'completePercent') {
                $value = Phprojekt_Converter_Text::convert($object, $info);
                $this->assertEquals('0.00', $value);
            }
            // Text
            if ($info['key'] == 'title') {
                $value = Phprojekt_Converter_Text::convert($object, $info);
                $this->assertEquals($object->title, $value);
            }
            // TextArea
            if ($info['key'] == 'notes') {
                $value = Phprojekt_Converter_Text::convert($object, $info);
                $this->assertEquals($object->notes, $value);
            }
            // Date
            if ($info['key'] == 'startDate') {
                $value = Phprojekt_Converter_Text::convert($object, $info);
                $this->assertEquals($object->startDate, $value);
            }
        }
    }

    /**
     * Test text converter
     */
    public function testConvertPart2()
    {
        $this->markTestIncomplete('do not use minutes');
        $model           = new Minutes_Models_Minutes();
        $order           = Phprojekt_ModelInformation_Default::ORDERING_FORM;
        $fieldDefinition = $model->getInformation()->getFieldDefinition($order);

        $model->title           = 'test';
        $model->projectId       = 1;
        $model->meetingDatetime = '2009-05-12 11:00:00';
        $model->endTime         = '12:00:00';
        foreach ($fieldDefinition as $info) {
            // Time
            if ($info['key'] == 'endTime') {
                $value = Phprojekt_Converter_Text::convert($model, $info);
                $this->assertEquals('12:00', $value);
            }
        }
    }


    /**
     * Test text converter
     */
    public function testConvertPart3()
    {
        $this->markTestIncomplete('do not use helpdesk');
        $model           = new Helpdesk_Models_Helpdesk();
        $order           = Phprojekt_ModelInformation_Default::ORDERING_FORM;
        $fieldDefinition = $model->getInformation()->getFieldDefinition($order);

        $model->title       = 'test';
        $model->projectId   = 1;
        $model->ownerId     = 1;
        $model->attachments = '3bc3369dd33d3ab9c03bd76262cff633|LICENSE';
        $model->status      = 3;
        $model->author      = 2;
        foreach ($fieldDefinition as $info) {
            // Upload
            if ($info['key'] == 'attachments') {
                $value = Phprojekt_Converter_Text::convert($model, $info);
                $this->assertEquals('LICENSE', $value);

                $model->attachments = '6d54feaa915a99cce5850d7812ade10e|LICENSE|'.
                    '|0620dbcea94f89e8154682d21bc327b0|install.log';
                $value = Phprojekt_Converter_Text::convert($model, $info);
                $this->assertEquals('LICENSE, install.log', $value);
            }
            // Display
            if ($info['key'] == 'author') {
                $value = Phprojekt_Converter_Text::convert($model, $info);
                $this->assertEquals('Solt, Gustavo', $value);

                $model->author = 100;
                $value = Phprojekt_Converter_Text::convert($model, $info);
                $this->assertEquals('100', $value);
            }
        }
    }
}
