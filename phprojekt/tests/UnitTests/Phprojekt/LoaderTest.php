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
 * Tests for active records
 *
 * @group      phprojekt
 * @group      loader
 * @group      phprojekt-loader
 */
class Phprojekt_LoaderTest extends DatabaseTest
{
    protected function getDataSet() {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/data.xml');
    }

    /**
     * Test the loadClass functionality
     */
    public function testLoadClass()
    {
        Phprojekt_Loader::loadClass('IndexController');
        $this->assertTrue(class_exists('IndexController'));
    }

    /**
     * Test the getModel functionality
     */
    public function testGetModel()
    {
        $name = new Project_Models_Project();
        $this->assertEquals('Project_Models_Project', get_class($name));
        $instance = new $name(array('db' => Phprojekt::getInstance()->getDb()));
        $this->assertTrue($instance instanceof Project_Models_Project);
    }

    /**
     * Test GetViewClassname
     */
    public function testGetViewClassname()
    {
        $this->assertEquals('Project_Views_Project', Phprojekt_Loader::getViewClassname('Project', 'Project'));
    }

    /**
     * Test getModelFromObject
     */
    public function testGetModelFromObject()
    {
        $object = new Todo_Models_Todo();
        $this->assertEquals('Todo', Phprojekt_Loader::getModelFromObject($object));
    }

    /**
     * Test getModuleFromObject
     */
    public function testGetModuleFromObject()
    {
        $object = new Todo_Models_Todo();
        $this->assertEquals('Todo', Phprojekt_Loader::getModuleFromObject($object));
    }

    /**
     * Test tryToLoadClass
     */
    public function testTryToLoadClass()
    {
        $this->assertTrue(Phprojekt_Loader::tryToLoadClass('Core_Models_User_Setting'));

        $this->assertFalse(Phprojekt_Loader::tryToLoadClass('Timecard_Models_None'));
    }
}
