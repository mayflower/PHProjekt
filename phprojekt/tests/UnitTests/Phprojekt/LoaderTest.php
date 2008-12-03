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
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    CVS: $Id$
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */
require_once 'PHPUnit/Framework.php';

/**
 * Tests for active records
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class Phprojekt_LoaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * setUp method for PHPUnit. We use a shared db connection
     *
     */
    public function setUp()
    {
    }

    /**
     * Test the loadClass functionality
     *
     */
    public function testLoadClass()
    {
        Phprojekt_Loader::loadClass('IndexController');
        $this->assertTrue(class_exists('IndexController'));
    }

    /**
     * Test the getModel functionality
     *
     */
    public function testGetModel()
    {
        $name = Phprojekt_Loader::getModel('Project', 'Project');
        $this->assertEquals('Project_Models_Project', get_class($name));
        $instance = new $name(array('db'=>$this->sharedFixture));
        $this->assertNotNull($instance);
    }

    /**
     * Test GetViewClassname
     *
     */
    public function testGetViewClassname()
    {
        $this->assertEquals('Project_Views_Project', Phprojekt_Loader::getViewClassname('Project', 'Project'));
    }

    /**
     * Test getModelFromObject
     *
     */
    public function testGetModelFromObject()
    {
        $object = Phprojekt_Loader::getModel('Todo', 'Todo');
        $this->assertEquals('Todo', Phprojekt_Loader::getModelFromObject($object));
    }
}
