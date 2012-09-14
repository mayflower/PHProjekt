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
 * Tests for Configurations
 *
 * @group      phprojekt
 * @group      main
 * @group      phprojekt-main
 */
class Phprojekt_ConfigurationTest extends DatabaseTest
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
        $configuration = new Phprojekt_Configuration();
        $data          = array(array('name'  => 'General',
                                     'label' => Phprojekt::getInstance()->translate('General')));

        $this->assertEquals($data, $configuration->getModules());
    }

    /**
     * Test getModel and setModule
     */
    public function testGetModel()
    {
        $configuration = new Phprojekt_Configuration();
        $configuration->setModule('General');

        $this->assertTrue(get_class($configuration->getModel()) == 'Core_Models_General_Configuration');
    }

    /**
     * Test setConfigurations, validateConfigurations and getConfiguration
     */
    public function testSaveAndGetConfiguration()
    {
        $configuration = new Phprojekt_Configuration();
        $configuration->setModule('General');

        $this->assertEquals('', $configuration->getConfiguration('companyName'));

        $message = $configuration->validateConfigurations(array('companyName' => 'TEST'));
        $this->assertNull($message);

        $configuration->setConfigurations(array('companyName' => 'TEST'));
        $this->assertEquals('TEST', $configuration->getConfiguration('companyName'));

        $configuration->setConfigurations(array('companyName' => 'Invisible Root'));
    }

    /**
     * Test getList
     */
    public function testGetList()
    {
        $configuration = new Phprojekt_Configuration();
        $configuration->setModule('General');
        $metadata = $configuration->getModel()->getFieldDefinition(Phprojekt_ModelInformation_Default::ORDERING_FORM);
        $records  = $configuration->getList(0, $metadata);

        $data = array('id' => 0, 'companyName' => '');
        $this->assertEquals(array($data), $records);
    }
}
