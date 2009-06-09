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
 * Tests Timecard Model TimecardSetting class
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 */
class Timecard_Models_TimecardSetting_Test extends PHPUnit_Framework_TestCase
{
    /**
     * setUp method for PHPUnit
     */
    public function setUp()
    {
        $this->_model = new Timecard_Models_TimecardSetting();
    }

    /**
     * Test getFieldDefinition
     */
    public function testGetFieldDefinition()
    {
        $timecardSetting = clone($this->_model);
        $response        = $timecardSetting->getFieldDefinition();
        $key             = $response[0]['key'];
        $total           = count($response[0]['range']);
        $this->assertEquals("favorites", $key);
        $this->assertEquals(6, $total);
    }

    /**
     * Test getFavorites
     */
    public function testGetFavorites()
    {
        $timecardSetting = clone($this->_model);
        $favorites       = serialize(array(0 => 1,
                                           1 => 2));
        $response = $timecardSetting->getFavorites($favorites);
        $this->assertEquals("1,2", $response);
    }

    /**
     * Test setSettings
     */
    public function testSetSettingsPart1()
    {
        // Save favorites setting
        $timecardSetting = clone($this->_model);
        $params          = array('favorites' => "");
        $timecardSetting->setSettings($params);

        // Check it was well saved
        $settingsModel = new Setting_Models_Setting();
        $settingsModel->setModule('Timecard');
        $response      = $settingsModel->getSetting('favorites');
        $response      = unserialize($response);
        $this->assertEquals(null, $response);
    }

    /**
     * Test setSettings
     */
    public function testSetSettingsPart2()
    {
        // Save favorites setting
        $timecardSetting = clone($this->_model);
        $favorites       = array(0 => 1,
                                 1 => 2);
        $params          = array('favorites' => $favorites);
        $timecardSetting->setSettings($params);

        // Check it was well saved
        $settingsModel = new Setting_Models_Setting();
        $settingsModel->setModule('Timecard');
        $response      = $settingsModel->getSetting('favorites');
        $response      = unserialize($response);
        $this->assertEquals($favorites, $response);
    }
}
