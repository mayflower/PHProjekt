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
 * Tests for Index Controller
 *
 * @group      timecard
 * @group      controller
 * @group      timecard-controller
 */
class Timecard_IndexController_Test extends FrontInit
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
     * Test the list
     */
    public function testJsonMonthListActionPart2()
    {
        $this->markTestSkipped('Can\'t test because of errors in FrontInit');
        $this->setRequestUrl('Timecard/index/workedMinutesPerDay/');
        $this->request->setParam('start', "2009-05-01");
        $this->request->setParam('end', "2009-06-01");
        $response = $this->getResponse();
        $expected = '{"date":"2009-05-01","sumInMinutes":0}';
        $this->assertContains($expected, $response);

        $expected = '{"date":"2009-05-17","sumInMinutes":360}';
        $this->assertContains($expected, $response);

        $expected = '{"date":"2009-05-31","sumInMinutes":0}';
        $this->assertContains($expected, $response);
    }

    /**
     * Test of csv
     */
    public function testCsv()
    {
        $this->markTestSkipped('Can\'t test because of errors in FrontInit');
        $this->setRequestUrl('Timecard/Timecard/');
        $this->request->setParam('filter', '{"startDatetime":{"!ge":"2009-05-01 00:00","!lt":"2009-06-01 00:00"}}');
        $this->request->setParam('format', 'csv');
        $response = $this->getResponse();
        $this->assertContains(
            '"Start","End","Minutes","Project","Notes"'."\n"
            .'"2009-05-17 09:00:00","13:00","240","PHProjekt","My note"'."\n"
            .'"2009-05-17 14:00:00","18:00","120","PHProjekt","My note"'."\n", $response
        );
    }

    /**
     * Test of csv
     */
    public function testCsvListEmptyResult()
    {
        $this->markTestSkipped('Can\'t test because of errors in FrontInit');
        $this->setRequestUrl('Timecard/Timecard/');
        $this->request->setParam('filter', '{"startDatetime":{"!ge":"2009-09-01 00:00","!lt":"2009-10-01 00:00"}}');
        $this->request->setParam('format', 'csv');
        $response = $this->getResponse();
        $this->assertEquals(2, strlen($response));
    }
}
