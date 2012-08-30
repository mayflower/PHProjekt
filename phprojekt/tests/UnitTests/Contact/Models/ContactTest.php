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
 * @version    Release: 6.1.0
 */


/**
 * Tests Contact Model class
 *
 * @version    Release: 6.1.0
 * @group      contact
 * @group      model
 * @group      contact-model
 */
class Contact_Models_Contact_Test extends PHPUnit_Framework_TestCase
{
    private $_model = null;

    /**
     * setUp method for PHPUnit
     */
    public function setUp()
    {
        $this->_model = new Contact_Models_Contact();
    }

    /**
     * Test save method
     */
    public function testSaveInsert()
    {
        // INSERT
        $contactModel              = clone($this->_model);
        $contactModel->ownerId     = 1;
        $contactModel->projectId   = 1;
        $contactModel->name        = 'Mariano7';
        $contactModel->email       = 'mariano.lapenna@mayflower.de';
        $contactModel->company     = 'Mayflower';
        $contactModel->firstphone  = '12341234';
        $contactModel->secondphone = '23452345';
        $contactModel->mobilephone = '34563456';
        $contactModel->street      = 'Edison 1234';
        $contactModel->city        = 'Buenos Aires';
        $contactModel->zipcode     = '1234AAA';
        $contactModel->country     = 'Argentina';
        $contactModel->comment     = 'Very intelligent';
        $contactModel->private     = 0;
        $response                  = $contactModel->save();
        $this->assertEquals(true, $response);

        // INSERT
        unset($contactModel);
        $contactModel              = clone($this->_model);
        $contactModel->ownerId     = 1;
        $contactModel->projectId   = 1;
        $contactModel->name        = 'Mariano8';
        $contactModel->email       = 'mariano.lapenna@mayflower.de2';
        $contactModel->company     = 'Mayflower2';
        $contactModel->firstphone  = '12341234b';
        $contactModel->secondphone = '23452345b';
        $contactModel->mobilephone = '34563456b';
        $contactModel->street      = 'Edison 1234b';
        $contactModel->city        = 'Buenos Aires2';
        $contactModel->zipcode     = '1234AAA2';
        $contactModel->country     = 'Argentinab';
        $contactModel->comment     = 'Very intelligent2';
        $contactModel->private     = 1;
        $response                  = $contactModel->save();
        $this->assertEquals(true, $response);
    }

    /**
     * Test save method
     */
    public function testSaveEdit() {
        // EDIT
        $contactModel = clone($this->_model);
        $contactModel->find(2);
        $contactModel->name = 'Mariano10';
        $response           = $contactModel->save();
        $this->assertEquals(true, $response);
    }

    /**
     * Test fetchAll method
     */
    public function testFetchAll()
    {
        // Check that the previous inserts were well done
        $contactModel  = clone($this->_model);
        $response      = $contactModel->fetchAll();
        $responseModel = $response[0];
        $this->assertEquals(2, $responseModel->id);
        $this->assertEquals('Mariano10', $responseModel->name);
        $this->assertEquals('mariano.lapenna@mayflower.de', $responseModel->email);
        $this->assertEquals('Mayflower', $responseModel->company);
        $this->assertEquals('12341234', $responseModel->firstphone);
        $this->assertEquals('23452345', $responseModel->secondphone);
        $this->assertEquals('34563456', $responseModel->mobilephone);
        $this->assertEquals('Edison 1234', $responseModel->street);
        $this->assertEquals('Buenos Aires', $responseModel->city);
        $this->assertEquals('1234AAA', $responseModel->zipcode);
        $this->assertEquals('Argentina', $responseModel->country);
        $this->assertEquals('Very intelligent', $responseModel->comment);
        $this->assertEquals(0, $responseModel->private);

        $responseModel = $response[1];
        $this->assertEquals(3, $responseModel->id);
    }

    /**
     * Test getRangeFromModel method
     */
    public function testGetRangeFromModel()
    {
        $contactModel = clone($this->_model);
        $projectModel = new Project_Models_Project();
        $projectModel->find(1);
        $field    = new Phprojekt_DatabaseManager_Field($projectModel->getInformation(), 'contactId');
        $response = $contactModel->getRangeFromModel($field);
        $expected = array(array('id' => 0,
                                'name' => ''),
                          array('id' => '2',
                                'name' => 'Mariano10'));
        $this->assertEquals($expected, $response);
    }

    /**
     * Test save rights
     */
    public function testSaveRights()
    {
        $contactModel = clone($this->_model);
        $contactModel->saveRights(null);
    }

    /**
     * Test recordValidate
     */
    public function testRecordValidate()
    {
        $contactModel = clone($this->_model);
        $contactModel->find(1);
        $response = $contactModel->recordValidate();
        $this->assertEquals(true, $response);
    }

    /**
     * Test delete method
     */
    public function testDelete()
    {
        // Delete
        $contactModel = clone($this->_model);
        $contactModel->find(2);
        $contactModel->delete();

        // Check that the item was deleted
        unset($contactModel);
        $contactModel = clone($this->_model);
        $response     = $contactModel->find(2);
        $this->assertEquals(array(), $response);
    }
}
