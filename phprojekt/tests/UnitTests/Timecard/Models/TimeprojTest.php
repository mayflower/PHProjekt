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
 * Tests Timecard Model Timeproj class
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 * @group      timecard
 * @group      model
 * @group      timeproj
 * @group      timecard-model
 * @group      timecard-model-timeproj
 */
class Timecard_Models_Timeproj_Test extends PHPUnit_Framework_TestCase
{
    /**
     * setUp method for PHPUnit
     */
    public function setUp()
    {
        $this->_model = new Timecard_Models_Timeproj();
    }

    /**
     * Test for mock function
     */
    public function testMocks()
    {
        $timeprojModel = clone($this->_model);
        $this->assertEquals(array(), $timeprojModel->getRights());
        $this->assertEquals(array(), $timeprojModel->getInformation()->getTitles());
    }

    /**
     * Test record validate
     */
    public function testRecordValidate()
    {
        // Right data
        $timeprojModel            = clone($this->_model);
        $timeprojModel->projectId = 1;
        $timeprojModel->date      = '2009-05-22';
        $timeprojModel->amount    = '02:00';
        $timeprojModel->notes     = 'My booked project';
        $response                 = $timeprojModel->recordValidate();
        $this->assertEquals(true, $response);

        // Right data
        $timeprojModel            = clone($this->_model);
        $timeprojModel->projectId = 1;
        $timeprojModel->date      = '2009-05-22';
        $timeprojModel->amount    = 100;
        $timeprojModel->notes     = 'My booked project';
        $response                 = $timeprojModel->recordValidate();
        $this->assertEquals(true, $response);

        // Wrong data
        $timeprojModel            = clone($this->_model);
        $timeprojModel->amount    = 29;
        $response                 = $timeprojModel->recordValidate();
        $this->assertEquals(false, $response);
        $error = $timeprojModel->getError();
        $this->assertEquals('The amount is invalid (from 30 to 1300)', $error[0]['message']);

        // Wrong data
        $timeprojModel->amount    = '13:01';
        $response                 = $timeprojModel->recordValidate();
        $this->assertEquals(false, $response);
        $error = $timeprojModel->getError();
        $this->assertEquals('The amount is invalid (from 30 to 1300)', $error[0]['message']);
    }

    /**
     * Test saving -actually default save
     */
    public function testSave()
    {
        // Right data
        $timeprojModel            = clone($this->_model);
        $timeprojModel->ownerId   = 1;
        $timeprojModel->projectId = 1;
        $timeprojModel->date      = '2009-05-22';
        $timeprojModel->amount    = '02:00';
        $timeprojModel->notes     = 'My booked project';
        $response                 = $timeprojModel->save();
        $this->assertEquals(true, $response);
    }

    /**
     * Test getRecords
     */
    public function testGetRecords()
    {
        $timeprojModel = clone($this->_model);
        $response      = $timeprojModel->getRecords('2009-05-17');
        $this->assertEquals(2, $response['numRows']);
        $this->assertEquals(2, count($response['data']));

        $firstRecord = $response['data']['timecard'][0];
        $this->assertEquals('13:00:00', $firstRecord['startTime']);
        $this->assertEquals('13:30:00', $firstRecord['endTime']);

        $secondRecord = $response['data']['timecard'][1];
        $this->assertEquals('14:00:00', $secondRecord['startTime']);
        $this->assertEquals('18:00:00', $secondRecord['endTime']);
    }
}
