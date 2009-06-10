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
 * Tests Converter text class
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 * @group      phprojekt
 * @group      converter
 * @group      text
 * @group      phprojekt-converter
 * @group      phprojekt-converter-text
 */
class Phprojekt_Converter_TextTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test text converter
     */
    public function testConvertPart1()
    {
        $model           = Phprojekt_Loader::getModel('Project', 'Project');
        $order           = Phprojekt_ModelInformation_Default::ORDERING_FORM;
        $fieldDefinition = $model->getInformation()->getFieldDefinition($order);
        $object          = $model->find(1);

        foreach ($fieldDefinition as $info) {
            // Selectbox
            if ($info['key'] == 'currentStatus') {
                $value = Phprojekt_Converter_Text::convert($object, $info);
                $this->assertEquals('Offered', $value);
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
        $model           = Phprojekt_Loader::getModel('Calendar', 'Calendar');
        $order           = Phprojekt_ModelInformation_Default::ORDERING_FORM;
        $fieldDefinition = $model->getInformation()->getFieldDefinition($order);

        $model->title         = 'test';
        $model->projectId     = 1;
        $model->startDate     = '2009-05-12';
        $model->endDate       = '2009-05-12';
        $model->startTime     = '12:00:00';
        $model->endTime       = '13:00:00';
        $model->uid           = '2342342342342323';
        $model->participantId = 1;
        foreach ($fieldDefinition as $info) {
            // Time
            if ($info['key'] == 'startTime') {
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
        $model           = Phprojekt_Loader::getModel('Helpdesk', 'Helpdesk');
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
