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
 * Tests Role class
 *
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
}
