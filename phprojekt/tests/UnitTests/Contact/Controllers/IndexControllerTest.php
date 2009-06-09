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
 * Tests for Contact Index Controller
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 */
class Contact_IndexController_Test extends FrontInit
{
    /**
     * Test of json save, json list and json detail - actually the Default functions
     */
    public function testJsonSaveListDetailPart1()
    {
        // INSERT
        $this->setRequestUrl('Contact/index/jsonSave/');
        $this->request->setParam('name', 'Mariano');
        $this->request->setParam('email', 'mariano.lapenna@mayflower.de');
        $this->request->setParam('company', 'Mayflower');
        $this->request->setParam('firstphone', '12341234');
        $this->request->setParam('secondphone', '23452345');
        $this->request->setParam('mobilephone', '34563456');
        $this->request->setParam('street', 'Edison 1234');
        $this->request->setParam('city', 'Buenos Aires');
        $this->request->setParam('zipcode', '1234AAA');
        $this->request->setParam('country', 'Argentina');
        $this->request->setParam('comment', 'Very intelligent');
        $this->request->setParam('private', 0);
        $response = $this->getResponse();
        $this->assertContains(Contact_IndexController::ADD_TRUE_TEXT, $response);

        // Check it
        $this->setRequestUrl('Contact/index/jsonList/');
        $response        = $this->getResponse();
        $expectedContent = '"data":[{"id":"1","name":"Mariano","rights":{"currentUser":{"moduleId":"9","itemId":"1","us'
            . 'erId":1,"none":false,"read":true,"write":true,"access":true,"create":true,"copy":true,"delete":true,"dow'
            . 'nload":true,"admin":true}},"email":"mariano.lapenna@mayflower.de","firstphone":"12341234","street":"Edis'
            . 'on 1234","private":"0"}],"numRows":1})';
        $this->assertContains($response, $expectedContent);
        $this->assertContains($expectedContent, $response);

        // Check it
        $this->setRequestUrl('Contact/index/jsonDetail/');
        $this->request->setParam('id', 1);
        $response        = $this->getResponse();
        $expectedContent = '"data":[{"id":"1","name":"Mariano","rights":{"currentUser":{"moduleId":"9","itemId":"1","us'
            . 'erId":1,"none":false,"read":true,"write":true,"access":true,"create":true,"copy":true,"delete":true,"dow'
            . 'nload":true,"admin":true}},"email":"mariano.lapenna@mayflower.de","company":"Mayflower","firstphone":"12'
            . '341234","secondphone":"23452345","mobilephone":"34563456","street":"Edison 1234","city":"Buenos Aires","'
            . 'zipcode":"1234AAA","country":"Argentina","comment":"Very intelligent","private":"0"}],"numRows":1})';
        $this->assertContains($expectedContent, $response);
    }

    /**
     * Test of json save, json list and json detail - actually the Default functions
     */
    public function testJsonSaveListDetailPart2()
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
        $this->request->setParam('comment', 'Very intelligent2');
        $this->request->setParam('private', 1);
        $response = $this->getResponse();
        $this->assertContains(Contact_IndexController::EDIT_TRUE_TEXT, $response);

        // Check it
        $this->setRequestUrl('Contact/index/jsonList/');
        $response        = $this->getResponse();
        $expectedContent = '"data":[{"id":"1","name":"Mariano2","rights":{"currentUser":{"moduleId":"9","itemId":"1","u'
            . 'serId":1,"none":false,"read":true,"write":true,"access":true,"create":true,"copy":true,"delete":true,"do'
            . 'wnload":true,"admin":true}},"email":"mariano.lapenna@mayflower.de2","firstphone":"12341234B","street":"E'
            . 'dison 1234B","private":"1"}],"numRows":1})';
        $this->assertContains($response, $expectedContent);
        $this->assertContains($expectedContent, $response);

        // Check it
        $this->setRequestUrl('Contact/index/jsonDetail/');
        $this->request->setParam('id', 1);
        $response        = $this->getResponse();
        $expectedContent = '"data":[{"id":"1","name":"Mariano2","rights":{"currentUser":{"moduleId":"9","itemId":"1","u'
            . 'serId":1,"none":false,"read":true,"write":true,"access":true,"create":true,"copy":true,"delete":true,"do'
            . 'wnload":true,"admin":true}},"email":"mariano.lapenna@mayflower.de2","company":"Mayflower2","firstphone":'
            . '"12341234B","secondphone":"23452345B","mobilephone":"34563456B","street":"Edison 1234B","city":"Buenos A'
            . 'ires2","zipcode":"1234AAA2","country":"Argentina2","comment":"Very intelligent2","private":"1"}],"numRow'
            . 's":1})';
        $this->assertContains($expectedContent, $response);
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
    }

    /**
     * Test of json delete - actually the Default functions -> Check the item was deleted
     */
    public function testJsonDeleteCheck() {
        // Check it
        $this->setRequestUrl('Contact/index/jsonList/');
        $response        = $this->getResponse();
        $expectedContent = '{}&&({"metadata":[]})';
        $this->assertContains($expectedContent, $response);
    }
}
