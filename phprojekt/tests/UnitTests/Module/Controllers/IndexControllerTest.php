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
 * Tests for Index Controller
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Module_IndexController_Test extends FrontInit
{
    /**
     * Test of json save Module -in fact, default json save
     */
    public function testJsonSave()
    {
        $designerData = '{"0":{"id":0,"tableName":"Aa","formPosition":1,"formTab":1,"formColumns":1,"formRegexp":null,'
            . '"listAlign":"center","listUseFilter":1,"altPosition":0,"isInteger":0,"isUnique":0,"tableField":'
            . '"project_id","selectType":"project","tableType":"int","tableLength":11,"formLabel":"Project","formType"'
            . ':"selectValues","formRange":"Project # id # title","defaultValue":1,"listPosition":0,"status":1,'
            . '"isRequired":1}}';
        $this->setRequestUrl('Core/moduledesigner/jsonSave');
        $this->request->setParam('id', null);
        $this->request->setParam('designerData', $designerData);
        $this->request->setParam('name', 'test');
        $this->request->setParam('label', 'test');
        $response = $this->getResponse();
        $this->assertContains('The table module was created correctly', $response);

        $this->setRequestUrl('Core/module/jsonSave');
        $this->request->setParam('id', null);
        $this->request->setParam('name', 'test');
        $this->request->setParam('label', 'test');
        $this->request->setParam('saveType', 0);
        $this->request->setParam('active', 1);
        $response = $this->getResponse();
        $this->assertContains('The module was added correctly', $response);
    }

    /**
     * Test of json delete Module
     */
    public function testJsonDelete()
    {
        $this->setRequestUrl('Core/module/jsonDelete');
        $this->request->setParam('id', 12);
        $response = $this->getResponse();
        $this->assertContains('The module was deleted correctly', $response);
    }

    /**
     * Test of json detail Module
     */
    public function testJsonDetail()
    {
        $this->setRequestUrl('Core/module/jsonDetail');
        $this->request->setParam('id', 1);
        $response = $this->getResponse();
        $this->assertContains('"name":"Project"', $response);
    }
}
