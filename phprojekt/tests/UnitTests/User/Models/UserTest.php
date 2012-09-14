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
 * Tests User Model class
 *
 * @group      user
 * @group      model
 * @group      user-model
 */
class User_User_Test extends DatabaseTest
{
    protected function getDataSet() {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/../../common.xml');
    }

    /**
     * Test valid method
     */
    public function testSave()
    {
        $user = new Phprojekt_User_User();
        $user->find(2);
        $this->assertEquals($user->saveRights(), null);
        $this->assertEquals($user->recordValidate(), false);
        $error = $user->getError();
        $this->assertEquals('lastname', $error[0]['field']);
        $this->assertEquals('Is a required field', $error[0]['message']);
    }

    /**
     * Test save function
     */
    public function testUserNamecheck()
    {
        $user            = new Phprojekt_User_User();
        $user->username  = 'Test';
        $user->firstname = 'testuser';
        $user->lastname  = 'testuser';
        $user->status    = 'A';
        $this->assertEquals(false, $user->recordValidate());
        $error = $user->getError();
        $this->assertEquals('Already exists, choose another one please', $error[0]['message']);
    }

    /**
     * Test save function
     */
    public function testUpdate()
    {
        $user = new Phprojekt_User_User();
        $user->username  = 'testuser';
        $user->username  = 'testuser';
        $user->firstname = 'testuser';
        $user->lastname  = 'testuser';
        $user->status    = 'A';
        $user->save();

        $user->username = 'testuserchanged';
        $user->save();
        $this->assertEquals('testuserchanged', $user->username);
    }

    /**
     * Test delete
     */
    public function testDelete()
    {
        $user = new Phprojekt_User_User();
        $user->find(1);
        $this->setExpectedException('Phprojekt_User_Exception');
        $user->delete();
    }

    /**
     * Test the display
     */
    public function testdisplay()
    {
        $user = new Phprojekt_User_User();
        $this->assertEquals(array('lastname', 'firstname'), $user->getDisplay());

        $user->find(1);
        $this->assertEquals('Mustermann, Max', $user->applyDisplay(array('lastname', 'firstname'), $user));
        $this->assertEquals('Test, Mustermann, Max', $user->applyDisplay(array('username', 'lastname', 'firstname'),
            $user));
        $this->assertEquals('Test', $user->applyDisplay(array('username'), $user));
    }
}
