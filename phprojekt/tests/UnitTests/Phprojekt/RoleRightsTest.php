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
 * Tests for RoleRights
 *
 * @group      phprojekt
 * @group      role
 * @group      rights
 * @group      phprojekt-role-rights
 */

class Phprojekt_RoleRightsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var    Phprojekt_RoleRights
     * @access protected
     */
    protected $_object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp()
    {
        $this->_object = new Phprojekt_RoleRights(1, 2, 0, 1);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown()
    {
    }

    /**
     * test hasRight
     */
    public function testHasRight()
    {
        $this->assertEquals(true, $this->_object->hasRight('write', 1));
    }

    /**
     * testGetId() tests the GetId function.
     */
    public function testGetId()
    {
        $this->assertEquals(0, $this->_object->getId());
    }

    /**
     * testGetProject() tests the getProject function.
     */
    public function testGetProject()
    {
        // Remove the following lines when you implement this test.
        $this->assertEquals(1, $this->_object->getProject());
    }

    /**
     * testGetModule().
     */
    public function testGetModule()
    {
        $this->assertEquals(2, $this->_object->getModule());
    }

    /**
     * test whether right user is found.
     */
    public function testGetUser()
    {
       $this->assertEquals(1, $this->_object->getUser());
    }

    /**
     *  testGetAcl().
     */
    public function testGetAcl()
    {
        $this->assertSame(Phprojekt_Acl::getInstance(), $this->_object->getAcl());
    }

    /**
     *  testGetUserRole().
     */
    public function testGetUserRole()
    {
        $this->assertEquals(1, $this->_object->getUserRole());
    }
}
