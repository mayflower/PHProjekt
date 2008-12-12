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
 * @version    CVS: $Id:
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */
require_once 'PHPUnit/Framework.php';

/**
 * Tests Default Model class
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */
class Phprojekt_RoleModelsRoleModulePermissions_Test extends PHPUnit_Framework_TestCase
{
    /**
     * Test valid method
     *
     */
    public function testRoleModelsRole()
    {
        $roleModel = new Phprojekt_Role_RoleModulePermissions();
        $return    = $roleModel->getRoleModulePermissionsById(2);
        $expected  = array(
            "data" => array(
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
                "4" => array (
                    "id"       => "4",
                    "name"     => "Timecard",
                    "label"    => "Timecard",
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
        $this->assertEquals($return, $expected);
    }
}
