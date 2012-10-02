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
 * Tests for Settings
 *
 * @group      phprojekt
 * @group      main
 * @group      phprojekt-main
 */
class Phprojekt_SettingTest extends DatabaseTest
{
    public function setUp()
    {
        parent::setUp();
        $this->sharedFixture = Phprojekt::getInstance()->getDb();
    }

    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/data.xml');
    }

    /**
     * Test getModules
     */
    public function testGetModules()
    {
        $setting = new Phprojekt_Setting();
        $data    = array();
        $data[]  = array('name'  => 'User',
                         'label' => Phprojekt::getInstance()->translate('User'));
        $data[]  = array('name'  => 'Notification',
                         'label' => Phprojekt::getInstance()->translate('Notification'));
        $data[]  = array('name'  => 'Timecard',
                         'label' => Phprojekt::getInstance()->translate('Timecard'));

        $this->assertEquals($data, $setting->getModules());
    }

    /**
     * Test getModel and setModule
     */
    public function testGetModel()
    {
        $setting = new Phprojekt_Setting();
        $setting->setModule('User');
        $this->assertTrue(get_class($setting->getModel()) == 'Core_Models_User_Setting');

        $setting = new Phprojekt_Setting();
        $setting->setModule('Timecard');
        $this->assertTrue(get_class($setting->getModel()) == 'Timecard_Models_Setting');
    }

    /**
     * Test setSettings, validateSettings and getSetting
     */
    public function testSaveAndGetSetting()
    {
        $setting = new Phprojekt_Setting();
        $setting->setModule('Timecard');

        $message = $setting->validateSettings(array('favorites' => array(1, 2)));
        $this->assertNull($message);

        $setting->setSettings(array('favorites' => array(1, 2)));
        $this->assertEquals(array(1, 2), unserialize($setting->getSetting('favorites')));

        $setting = new Phprojekt_Setting();
        $setting->setModule('User');
        $this->assertEquals('156c3239dbfa5c5222b51514e9d12948', $setting->getSetting('password'));

        $message = $setting->validateSettings(array('password' => 'test', 'language' => 'en', 'timeZone' => 2,
            'confirmValue' => 'test', 'oldValue' => 'test'));
        $this->assertNull($message);

        $setting->setSettings(array('password' => 'test', 'language' => 'en', 'timeZone' => 2,
            'confirmValue' => 'test', 'oldValue' => 'test'));
        $this->assertEquals('156c3239dbfa5c5222b51514e9d12948', $setting->getSetting('password'));

        $setting = new Phprojekt_Setting();
        $setting->setModule('Notification');

        $message = $setting->validateSettings(array('loginlogout' => 0));
        $this->assertNull($message);

        $setting->setSettings(array('loginlogout' => 0));
        $this->assertEquals(0, $setting->getSetting('loginlogout'));
    }

    /**
     * Test getList
     */
    public function testGetList()
    {
        $setting = new Phprojekt_Setting();
        $setting->setModule('Timecard');
        $metadata = $setting->getModel()->getFieldDefinition(Phprojekt_ModelInformation_Default::ORDERING_FORM);
        $records  = $setting->getList(4, $metadata);

        $data = array('id' => 0, 'favorites' => array (0 => 1, 1 => 2));
        $this->assertEquals(array($data), $records);

        $setting = new Phprojekt_Setting();
        $setting->setModule('Notification');
        $metadata = $setting->getModel()->getFieldDefinition(Phprojekt_ModelInformation_Default::ORDERING_FORM);
        $records  = $setting->getList(0, $metadata);

        $data = array('id' => 0, 'loginlogout' => 0, 'datarecords' => 1, 'alerts' => 1);
        $this->assertEquals(array($data), $records);
    }
}
