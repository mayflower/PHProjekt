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
 * Tests Default Model class
 *
 * @group      role
 * @group      model
 * @group      role-model
 */
class Phprojekt_RoleModelsRoleModulePermissions_Test extends PHPUnit_Framework_TestCase
{
    /**
     * Array with the current modules
     *
     * @var array
     */
    private $_expected  = array(
        "data" => array(
            "1" => Array (
                "id"       => "1",
                "name"     => "Project",
                "label"    => "Project",
                "none"     => true,
                "read"     => true,
                "write"    => true,
                "access"   => true,
                "create"   => true,
                "copy"     => true,
                "delete"   => true,
                "download" => true,
                "admin"    => true
            ),
            "2" => Array (
                "id"       => "2",
                "name"     => "Todo",
                "label"    => "Todo",
                "none"     => true,
                "read"     => true,
                "write"    => true,
                "access"   => true,
                "create"   => true,
                "copy"     => true,
                "delete"   => true,
                "download" => true,
                "admin"    => true
            ),
            "3" => Array (
                "id"       => "3",
                "name"     => "Note",
                "label"    => "Note",
                "none"     => true,
                "read"     => true,
                "write"    => true,
                "access"   => true,
                "create"   => true,
                "copy"     => true,
                "delete"   => true,
                "download" => true,
                "admin"    => true
            ),
            "5" => Array (
                "id"       => "5",
                "name"     => "Calendar",
                "label"    => "Calendar",
                "none"     => true,
                "read"     => true,
                "write"    => true,
                "access"   => true,
                "create"   => true,
                "copy"     => true,
                "delete"   => true,
                "download" => true,
                "admin"    => true
            ),
            "6" => Array (
                "id"       => "6",
                "name"     => "Gantt",
                "label"    => "Gantt",
                "none"     => true,
                "read"     => false,
                "write"    => false,
                "access"   => false,
                "create"   => false,
                "copy"     => false,
                "delete"   => false,
                "download" => false,
                "admin"    => false
            ),
            "7" => Array (
                "id"       => "7",
                "name"     => "Filemanager",
                "label"    => "Filemanager",
                "none"     => true,
                "read"     => false,
                "write"    => false,
                "access"   => false,
                "create"   => false,
                "copy"     => false,
                "delete"   => false,
                "download" => false,
                "admin"    => false
           ),
           "8" => Array (
                "id"       => "8",
                "name"     => "Statistic",
                "label"    => "Statistic",
                "none"     => true,
                "read"     => false,
                "write"    => false,
                "access"   => false,
                "create"   => false,
                "copy"     => false,
                "delete"   => false,
                "download" => false,
                "admin"    => false
           ),
           "10" => Array (
                "id"       => "10",
                "name"     => "Helpdesk",
                "label"    => "Helpdesk",
                "none"     => true,
                "read"     => false,
                "write"    => false,
                "access"   => false,
                "create"   => false,
                "copy"     => false,
                "delete"   => false,
                "download" => false,
                "admin"    => false
            ),
            "11" => Array (
                "id"       => "11",
                "name"     => "Minutes",
                "label"    => "Minutes",
                "none"     => true,
                "read"     => false,
                "write"    => false,
                "access"   => false,
                "create"   => false,
                "copy"     => false,
                "delete"   => false,
                "download" => false,
                "admin"    => false
            )
        )
    );

    /**
     * Test getRoleModulePermissionsById function
     * role exists
     */
    public function testGetRoleModulePermissionsByIdPart1()
    {
        $roleModel = new Phprojekt_Role_RoleModulePermissions();
        $return    = $roleModel->getRoleModulePermissionsById(1);
        $this->assertEquals(ksort($this->_expected), ksort($return));
    }

    /**
     * Test getRoleModulePermissionsById function
     * role don't exists
     */
    public function testGetRoleModulePermissionsByIdPart2()
    {
        $roleModel = new Phprojekt_Role_RoleModulePermissions();
        $return    = $roleModel->getRoleModulePermissionsById(100);
        $this->assertEquals(ksort($this->_expected), ksort($return));
    }

    /**
     * Test delete a relation
     */
    public function testDeleteModuleRelation()
    {
        $roleModel = new Phprojekt_Role_RoleModulePermissions();
        $roleModel->deleteModuleRelation(1);
        $return   = $roleModel->getRoleModulePermissionsById(1);
        $expected = $this->_expected;
        unset($expected['data'][1]);
        $this->assertEquals(ksort($expected), ksort($return));
    }

    /**
     * Test add a default relation
     */
    public function testAddModuleToAdminRole()
    {
        $roleModel = new Phprojekt_Role_RoleModulePermissions();
        $roleModel->addModuleToAdminRole(1);
        $return = $roleModel->getRoleModulePermissionsById(1);
        $this->assertEquals(ksort($this->_expected), ksort($return));
    }
}
