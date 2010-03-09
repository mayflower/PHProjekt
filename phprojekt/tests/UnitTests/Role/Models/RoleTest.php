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
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */
require_once 'PHPUnit/Framework.php';

/**
 * Tests Role class
 *
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 * @group      role
 * @group      model
 * @group      role-model
 */
class Phprojekt_Role_Test extends PHPUnit_Framework_TestCase
{
    /**
     * Test save
     */
    public function testSave()
    {
        $role       = new Phprojekt_Role_Role();
        $role->name = '';
        $this->assertFalse($role->recordValidate());
        $this->assertNotEquals(array(), $role->getError());

        $role->name = 'New Role';
        $role->save();
        $role->saveRights(array('1' => 139));

        $this->assertEquals(1, count($role->modulePermissions->fetchAll()));

        $role->saveRights(array(1 => 139, 2 => 139));
        $this->assertEquals(2, count($role->modulePermissions->fetchAll()));

        $role->delete();
    }

    /**
     * Test for mock function
     */
    public function testMocks()
    {
        $role = new Phprojekt_Role_Role();
        $this->assertEquals(array(), $role->getRights());
    }
}
