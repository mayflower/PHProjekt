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
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */


/**
 * Tests for Index Controller
 *
 * @group      statistic
 * @group      controller
 * @group      statistic-controller
 */
class Statistic_IndexController_Test extends FrontInit
{
    protected function getDataSet()
    {
        return new PHPUnit_Extensions_Database_DataSet_CompositeDataSet(
            array(
                $this->createFlatXMLDataSet(dirname(__FILE__) . '/../../common.xml'),
                $this->createFlatXMLDataSet(dirname(__FILE__) . '/../../Timecard/data.xml')
            )
        );
    }

    /**
     * Test noraml call
     */
    public function testJsonGetStatistic()
    {
        $this->setRequestUrl('Statistic/index/jsonGetStatistic/');
        $this->request->setParam('startDate', '2009-05-01');
        $this->request->setParam('endDate', '2009-05-31');
        $this->request->setParam('nodeId', 1);

        $response = FrontInit::phprJsonToArray($this->getResponse());
        $expected = array (
            'data' => array (
                'users' => array (
                    1 => 'Test'
                ),
                'projects' => array (
                    1 => 'PHProjekt',
                    2 => 'Test Project',
                    5 => 'Sub Project',
                    6 => 'Sub Sub Project 1',
                    7 => 'Sub Sub Project 2'
                ),
                'rows' => array (
                    1 => array (
                        1 => 360
                    )
                )
            )
        );
        $this->assertEquals($expected, $response);
    }

    /**
     * Test wrong call
     */
    public function testJsonGetStatisticWrong()
    {
        $this->setRequestUrl('Statistic/index/jsonGetStatistic/');
        $this->request->setParam('startDate', '2009-05-31');
        $this->request->setParam('endDate', '2009-05-01');
        $this->request->setParam('nodeId', 1);

        try {
            $this->front->dispatch($this->request, $this->response);
        } catch (Zend_Controller_Action_Exception $error) {
            $this->assertEquals("Period: End time can not be before Start time", $error->getMessage());
            return;
        }
    }

    /**
     * Test of csv
     */
    public function testCsvList()
    {
        $this->setRequestUrl('Statistic/index/csvList/');
        $this->request->setParam('startDate', '2009-04-01');
        $this->request->setParam('endDate', '2009-05-31');
        $this->request->setParam('nodeId', 1);

        $response = $this->getResponse();

        $this->assertEquals(
            '"Project","Test","Total"'."\n"
            . '"PHProjekt","06:00","06:00"'."\n"
            . '"Test Project","00:00","00:00"'."\n"
            . '"Sub Project","00:00","00:00"'."\n"
            . '"Sub Sub Project 1","00:00","00:00"'."\n"
            . '"Sub Sub Project 2","00:00","00:00"'."\n"
            . '"Total","06:00","06:00"'."\n", $response
        );
    }
}
