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
        $return    = $roleModel->getRoleModulePermissionsById(1);
        $expected  = array(
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

        $this->assertEquals(ksort($return), ksort($expected));
    }
}
