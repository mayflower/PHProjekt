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
 * Tests Converter csv class
 *
 * @group      phprojekt
 * @group      converter
 * @group      csv
 * @group      phprojekt-converter
 * @group      phprojekt-converter-csv
 */
class Phprojekt_Converter_CsvTest extends DatabaseTest
{
    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/../data.xml');
    }

    /**
     * Test csv converter
     */
    public function testConvert()
    {
        $convertedFields = '"Title","Start date","End date","Priority","Current status","Complete percent"';
        $convertedValues = '"PHProjekt","2007-12-01","","1","Working","0.00"';
        $object          = new Project_Models_Project();
        $records         = $object->fetchAll();
        $result          = Phprojekt_Converter_Csv::convert($records);
        $this->assertContains($convertedFields, $result);
        $this->assertContains($convertedValues, $result);

        $result = Phprojekt_Converter_Csv::convert($object->find(1));
        $this->assertEquals($result, "");
    }

    /**
     * Test csv convertion of array
     */
    public function testConvertArray()
    {
        $data      = array('first entry', 'second entry');
        $converted = "\"\n\"\n";
        $result    = Phprojekt_Converter_Csv::convert($data);;
        $this->assertEquals($converted, $result);

        $data            = array();
        $data[0][]       = 'Title 1';
        $data[0][]       = 'Title 2';
        $data[1][]       = 'Data 1';
        $data[1][]       = 'Data 2';
        $convertedFields = '"Title 1","Title 2"';
        $convertedValues = '"Data 1","Data 2"';
        $result          = Phprojekt_Converter_Csv::convert($data);
        $this->assertContains($convertedFields, $result);
        $this->assertContains($convertedValues, $result);
    }
}
