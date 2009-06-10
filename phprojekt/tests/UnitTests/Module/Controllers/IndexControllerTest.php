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
 * Tests for Module and ModuleDesigner Controller
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 * @group      module
 * @group      controller
 * @group      module-controller
 */
class Module_IndexController_Test extends FrontInit
{
    /**
     * Test of json save Module -in fact, default json save
     */
    public function testJsonSave()
    {
        // Database manager, needed for create the table first
        $designerData = '{"0":{"id":0,"tableName":"Test","formPosition":1,"formTab":1,"formColumns":1,'
            . '"formRegexp":null,'
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
        $this->assertContains('The table module was created correctly', $response, 'Response was: ' . $response);

        // Save
        $this->setRequestUrl('Core/module/jsonSave');
        $this->request->setParam('id', null);
        $this->request->setParam('name', 'test');
        $this->request->setParam('label', 'test');
        $this->request->setParam('saveType', 0);
        $this->request->setParam('active', 1);
        $response = $this->getResponse();
        $this->assertContains('The module was added correctly', $response, 'Response was: ' . $response);

        // Reset cache for modules
        $moduleNamespace = new Zend_Session_Namespace('Phprojekt_Module_Module-_getCachedIds');
        $moduleNamespace->unsetAll();

        // Edit
        $module   = new Phprojekt_Module_Module();
        $where    = $this->sharedFixture->quoteInto('name = ?', 'test');
        $ids      = $module->fetchAll($where);
        $moduleId = $ids[0]->id;

        $this->setRequestUrl('Core/module/jsonSave');
        $this->request->setParam('id', $moduleId);
        $this->request->setParam('name', 'test');
        $this->request->setParam('label', 'test');
        $this->request->setParam('saveType', 0);
        $this->request->setParam('active', 0);
        $response = $this->getResponse();
        $this->assertContains('The module was edited correctly', $response, 'Response was: ' . $response);

        // Without name
        $this->setRequestUrl('Core/module/jsonSave');
        $this->request->setParam('id', $moduleId);
        $this->request->setParam('label', 'test');
        $this->request->setParam('saveType', 0);
        $this->request->setParam('active', 0);
        $response = $this->getResponse();
        $this->assertContains('The module was edited correctly', $response, 'Response was: ' . $response);
    }

    public function testEditDatabaseManager()
    {
        $project = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $db      = new Phprojekt_DatabaseManager($project, array('db' => $this->sharedFixture));
        $where   = $this->sharedFixture->quoteInto('table_name = ?', 'test');
        $ids     = $db->fetchAll($where);
        $dbId    = $ids[0]->id;

        $module   = new Phprojekt_Module_Module();
        $where    = $this->sharedFixture->quoteInto('name = ?', 'test');
        $ids      = $module->fetchAll($where);
        $moduleId = $ids[0]->id;

        // Edit
        $designerData = '{"0":{"id":' . $dbId . ',"tableName":"Test","formPosition":1,"formTab":1,"formColumns":1,'
            . '"formRegexp":null,"listAlign":"center","listUseFilter":1,"altPosition":0,"isInteger":0,"isUnique":0,'
            . '"tableField":"project_id","selectType":"project","tableType":"int","tableLength":5,'
            . '"formLabel":"Project","formType":"selectValues","formRange":"Project # id # title","defaultValue":1,'
            . '"listPosition":0,"status":1,"isRequired":1}}';
        $this->setRequestUrl('Core/moduledesigner/jsonSave');
        $this->request->setParam('designerData', $designerData);
        $this->request->setParam('id', $moduleId);
        $this->request->setParam('name', 'test');
        $this->request->setParam('label', 'test');
        $response = $this->getResponse();
        $this->assertContains('The table module was edited correctly', $response, 'Response was: ' . $response);

        // Add
        $designerData = '{"0":{"id":' . $dbId . ',"tableName":"Test","formPosition":1,"formTab":1,"formColumns":1,'
            . '"formRegexp":null,"listAlign":"center","listUseFilter":1,"altPosition":0,"isInteger":0,"isUnique":0,'
            . '"tableField":"project_id","selectType":"project","tableType":"int","tableLength":5,'
            . '"formLabel":"Project","formType":"selectValues","formRange":"Project # id # title","defaultValue":1,'
            . '"listPosition":0,"status":1,"isRequired":1},'
            . '"1":{"id":0,"tableName":"Test","formPosition":1,"formTab":1,"formColumns":1,'
            . '"formRegexp":null,"listAlign":"center","listUseFilter":1,"altPosition":0,"isInteger":0,"isUnique":0,'
            . '"tableField":"test_field","selectType":"custom","tableType":"varchar","tableLength":10,'
            . '"formLabel":"Project","formType":"text","formRange":"","defaultValue":1,'
            . '"listPosition":0,"status":1,"isRequired":1}}';
        $this->setRequestUrl('Core/moduledesigner/jsonSave');
        $this->request->setParam('designerData', $designerData);
        $this->request->setParam('id', $moduleId);
        $this->request->setParam('name', 'test');
        $this->request->setParam('label', 'test');
        $response = $this->getResponse();
        $this->assertContains('The table module was edited correctly', $response, 'Response was: ' . $response);

        // Delete
        $designerData = '{"0":{"id":' . $dbId . ',"tableName":"Test","formPosition":1,"formTab":1,"formColumns":1,'
            . '"formRegexp":null,"listAlign":"center","listUseFilter":1,"altPosition":0,"isInteger":0,"isUnique":0,'
            . '"tableField":"project_id","selectType":"project","tableType":"int","tableLength":5,'
            . '"formLabel":"Project","formType":"selectValues","formRange":"Project # id # title","defaultValue":1,'
            . '"listPosition":0,"status":1,"isRequired":1}}';
        $this->setRequestUrl('Core/moduledesigner/jsonSave');
        $this->request->setParam('designerData', $designerData);
        $this->request->setParam('id', $moduleId);
        $this->request->setParam('name', 'test');
        $this->request->setParam('label', 'test');
        $response = $this->getResponse();
        $this->assertContains('The table module was edited correctly', $response, 'Response was: ' . $response);
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

        $this->setRequestUrl('Core/module/jsonDetail');
        $response = $this->getResponse();
        $this->assertContains('"name":"Project"', $response);
    }

    /**
     * Test jsonGetGlobalModules
     */
    public function testJsonGetGlobalModules()
    {
        $this->setRequestUrl('Core/module/jsonGetGlobalModules');
        $response = $this->getResponse();
        $this->assertContains('Calendar', $response);
        $this->assertContains('Contact', $response);
        $this->assertContains('Timecard', $response);
        $this->assertNotContains('Project', $response);
    }

    /**
     * Test the databaseManager data
     */
    public function testDatabaseManagerDetail()
    {
        $this->setRequestUrl('Core/moduledesigner/jsonDetail');
        $this->request->setParam('id', 1);
        $response = $this->getResponse();
        $this->assertContains('"tableName":"Project","id":1', $response);
    }

    /**
     * Test of json delete Module
     */
    public function testJsonDelete()
    {
        $module   = new Phprojekt_Module_Module();
        $where    = $this->sharedFixture->quoteInto('name = ?', 'test');
        $ids      = $module->fetchAll($where);
        $moduleId = $ids[0]->id;

        $this->setRequestUrl('Core/module/jsonDelete');
        $this->request->setParam('id', $moduleId);
        $response = $this->getResponse();
        $this->assertContains('The module was deleted correctly', $response);
    }
}
