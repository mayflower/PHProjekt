<?php
/**
 * Unit test
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @category   PHProjekt
 * @package    UnitTests
 * @subpackage Contact
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 */


/**
 * Tests for Contact Index Controller
 *
 * @category   PHProjekt
 * @package    UnitTests
 * @subpackage Contact
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 * @group      contact
 * @group      controller
 * @group      contact-controller
 */
class Contact_IndexController_Test extends FrontInit
{
    protected function getDataSet() {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/../../common.xml');
    }

    /**
     * Test of json save
     */
    public function testJsonSaveAdd()
    {
        // INSERT
        $this->setRequestUrl('Contact/index/jsonSave/');
        $this->request->setParam('name', 'Mariano');
        $this->request->setParam('email', 'mariano.lapenna@mayflower.de');
        $this->request->setParam('company', 'Mayflower');
        $this->request->setParam('firstphone', '004912341234');
        $this->request->setParam('secondphone', '004923452345');
        $this->request->setParam('mobilephone', '004934563456');
        $this->request->setParam('street', 'Edison 1234');
        $this->request->setParam('city', 'Buenos Aires');
        $this->request->setParam('zipcode', '1234AAA');
        $this->request->setParam('country', 'Argentina');
        $this->request->setParam('comment', 'Very intelligent');
        $this->request->setParam('private', 0);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Contact_IndexController::ADD_TRUE_TEXT, $response);
    }

    /**
     * Test of json list
     */
    public function testJsonList()
    {
        // Check it
        $this->setRequestUrl('Contact/index/jsonList/');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $expected = '"data":[{"id":1,"name":"Mariano","email":"mariano.lapenna@mayflower.de",'
            . '"firstphone":"004912341234","street":"Edison 1234","private":0,"rights":{"currentUser":'
            . '{"moduleId":9,"itemId":1,"userId":1,"none":false,"read":true,"write":true,"access":true,"create":true,'
            . '"copy":true,"delete":true,"download":true,"admin":true}}}],"numRows":1})';
        $this->assertContains($expected, $response);
    }

    /**
     * Test of json detail
     */
    public function testJsonDetail()
    {
        // Check it
        $this->setRequestUrl('Contact/index/jsonDetail/');
        $this->request->setParam('id', 1);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $expected = '"data":[{"id":1,"name":"Mariano","email":"mariano.lapenna@mayflower.de",'
            . '"company":"Mayflower","firstphone":"004912341234","secondphone":"004923452345",'
            . '"mobilephone":"004934563456","street":"Edison 1234","city":"Buenos Aires","zipcode":"1234AAA",'
            . '"country":"Argentina","comment":"This is a comment","private":0,"rights":{"currentUser":{"moduleId":9,'
            . '"itemId":1,"userId":1,"none":false,"read":true,"write":true,"access":true,"create":true,"copy":true,'
            . '"delete":true,"download":true,"admin":true}}}],"numRows":1})';
        $this->assertContains($expected, $response);
    }

    /**
     * Test of json save
     */
    public function testJsonSaveEdit()
    {
        // EDIT
        $this->setRequestUrl('Contact/index/jsonSave/');
        $this->request->setParam('id', '1');
        $this->request->setParam('name', 'Mariano2');
        $this->request->setParam('email', 'mariano.lapenna@mayflower.de2');
        $this->request->setParam('company', 'Mayflower2');
        $this->request->setParam('firstphone', '12341234B');
        $this->request->setParam('secondphone', '23452345B');
        $this->request->setParam('mobilephone', '34563456B');
        $this->request->setParam('street', 'Edison 1234B');
        $this->request->setParam('city', 'Buenos Aires2');
        $this->request->setParam('zipcode', '1234AAA2');
        $this->request->setParam('country', 'Argentina2');
        $this->request->setParam('comment', 'Foo');
        $this->request->setParam('private', 1);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains(Contact_IndexController::EDIT_TRUE_TEXT, $response);
    }


    /**
     * Test of json delete - actually the Default functions
     */
    public function testJsonDelete()
    {
        // EDIT
        $this->setRequestUrl('Contact/index/jsonDelete/');
        $this->request->setParam('id', '1');
        $response = $this->getResponse();
        $this->assertContains(Contact_IndexController::DELETE_TRUE_TEXT, $response);

        $this->setRequestUrl('Contact/index/jsonList/');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains('{}&&({"metadata":[]})', $response);
    }
}
