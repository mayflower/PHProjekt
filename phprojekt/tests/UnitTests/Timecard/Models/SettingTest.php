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
 * Tests Timecard Model TimecardSetting class
 *
 * @group      timecard
 * @group      model
 * @group      setting
 * @group      timecard-model
 * @group      timecard-model-setting
 */
class Timecard_Models_Setting_Test extends DatabaseTest
{
    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/../../common.xml');
    }

    /**
     * setUp method for PHPUnit
     */
    public function setUp()
    {
        $this->_model = new Timecard_Models_Setting();
    }

    /**
     * Test getFieldDefinition
     */
    public function testGetFieldDefinition()
    {
        $timecardSetting = clone($this->_model);
        $response        = $timecardSetting->getFieldDefinition(Phprojekt_ModelInformation_Default::ORDERING_FORM);
        $key             = $response[0]['key'];
        $total           = count($response[0]['range']);
        $this->assertEquals("favorites", $key);
        $this->assertEquals(5, $total);
    }

    /**
     * Test getFavorites
     */
    public function testGetFavorites()
    {
        $timecardSetting = clone($this->_model);
        $favorites       = array(0 => 1, 1 => 2);
        $favoritesS      = serialize($favorites);
        $response = $timecardSetting->getFavorites($favoritesS);
        $this->assertEquals($favorites , $response);
    }

    /**
     * Test setSettings
     */
    public function testSetSettings()
    {
        // Save favorites setting
        $timecardSetting = clone($this->_model);
        $favorites       = array(0 => 1,
                                 1 => 2);
        $params          = array('favorites' => $favorites);
        $timecardSetting->setSettings($params);

        // Check it was well saved
        $settingsModel = new Phprojekt_Setting();
        $settingsModel->setModule('Timecard');
        $response      = $settingsModel->getSetting('favorites');
        $response      = unserialize($response);
        $this->assertEquals($favorites, $response);
    }
}
