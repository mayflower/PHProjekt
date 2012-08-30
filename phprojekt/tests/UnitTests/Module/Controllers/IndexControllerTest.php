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
 * Tests for Module and ModuleDesigner Controller
 *
 * @version    Release: 6.1.0
 * @group      module
 * @group      controller
 * @group      module-controller
 */
class Module_IndexController_Test extends FrontInit
{
    /**
     * Test of json save Module -in fact, default json save
     */
    public function testJsonSavePart1()
    {
        // Database manager, needed for create the table first
        $designerData = '{"0":{"id":0,"tableName":"Test","formPosition":1,"formTab":1,"formColumns":1,'
            . '"formRegexp":null,'
            . '"listAlign":"center","listUseFilter":1,"altPosition":0,"isInteger":0,"isUnique":0,"tableField":'
            . '"project_id","selectType":"project","tableType":"int","tableLength":11,"formLabel":"Project","formType"'
            . ':"selectValues","formRange":"Project # id # title","defaultValue":1,"listPosition":1,"status":1,'
            . '"isRequired":1}}';
        $this->setRequestUrl('Core/moduleDesigner/jsonSave');
        $this->request->setParam('id', null);
        $this->request->setParam('designerData', $designerData);
        $this->request->setParam('name', 'test');
        $this->request->setParam('label', 'test');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains('The table module was created correctly', $response, 'Response was: ' . $response);
    }

    /**
     * Test of json save Module -in fact, default json save
     */
    public function testJsonSavePart2()
    {
        // Save
        $this->setRequestUrl('Core/module/jsonSave');
        $this->request->setParam('id', null);
        $this->request->setParam('name', 'test');
        $this->request->setParam('label', 'test');
        $this->request->setParam('saveType', 0);
        $this->request->setParam('active', 1);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains('The module was added correctly', $response, 'Response was: ' . $response);

        // Reset cache for modules
        $moduleNamespace = new Zend_Session_Namespace('Phprojekt_Module_Module-_getCachedIds');
        $moduleNamespace->unsetAll();
    }

    /**
     * Test of json save Module -in fact, default json save
     */
    public function testJsonSaveEditPart1()
    {
        // Edit
        $module = new Phprojekt_Module_Module();
        $where  = $this->sharedFixture->quoteInto('name = ?', 'test');
        $ids    = $module->fetchAll($where);

        Zend_Registry::set('moduleId', $ids[0]->id);

        $this->setRequestUrl('Core/module/jsonSave');
        $this->request->setParam('id', $ids[0]->id);
        $this->request->setParam('name', 'test');
        $this->request->setParam('label', 'test');
        $this->request->setParam('saveType', 0);
        $this->request->setParam('active', 0);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains('The module was edited correctly', $response, 'Response was: ' . $response);

    }

    /**
     * Test of json save Module -in fact, default json save
     */
    public function testJsonSaveEditPart2()
    {
        // Without name
        $this->setRequestUrl('Core/module/jsonSave');
        $this->request->setParam('id', Zend_Registry::get('moduleId'));
        $this->request->setParam('label', 'test');
        $this->request->setParam('saveType', 0);
        $this->request->setParam('active', 0);
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains('The module was edited correctly', $response, 'Response was: ' . $response);
    }

    public function testEditDatabaseManager()
    {
        $project = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $db      = new Phprojekt_DatabaseManager($project, array('db' => $this->sharedFixture));
        $where   = $this->sharedFixture->quoteInto('table_name = ?', 'test');
        $ids     = $db->fetchAll($where);

        Zend_Registry::set('dbId', $ids[0]->id);

        $module = new Phprojekt_Module_Module();
        $where  = $this->sharedFixture->quoteInto('name = ?', 'test');
        $ids    = $module->fetchAll($where);

        Zend_Registry::set('moduleId', $ids[0]->id);

        // Edit
        $designerData = '{"0":{"id":' . Zend_Registry::get('dbId') . ',"tableName":"Test","formPosition":1,"formTab":1,'
            . '"formColumns":1,"formRegexp":null,"listAlign":"center","listUseFilter":1,"altPosition":0,"isInteger":0,'
            . '"isUnique":0,"tableField":"project_id","selectType":"project","tableType":"int","tableLength":5,'
            . '"formLabel":"Project","formType":"selectValues","formRange":"Project # id # title","defaultValue":1,'
            . '"listPosition":1,"status":1,"isRequired":1}}';
        $this->setRequestUrl('Core/moduleDesigner/jsonSave');
        $this->request->setParam('designerData', $designerData);
        $this->request->setParam('id', Zend_Registry::get('moduleId'));
        $this->request->setParam('name', 'test');
        $this->request->setParam('label', 'test');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains('The table module was edited correctly', $response, 'Response was: ' . $response);
    }

    public function testAddDatabaseManager()
    {
        // Add
        $designerData = '{"0":{"id":' . Zend_Registry::get('dbId') . ',"tableName":"Test","formPosition":1,"formTab":1,'
            . '"formColumns":1,"formRegexp":null,"listAlign":"center","listUseFilter":1,"altPosition":0,"isInteger":0,'
            . '"isUnique":0,"tableField":"project_id","selectType":"project","tableType":"int","tableLength":5,'
            . '"formLabel":"Project","formType":"selectValues","formRange":"Project # id # title","defaultValue":1,'
            . '"listPosition":1,"status":1,"isRequired":1},'
            . '"1":{"id":0,"tableName":"Test","formPosition":1,"formTab":1,"formColumns":1,'
            . '"formRegexp":null,"listAlign":"center","listUseFilter":1,"altPosition":0,"isInteger":0,"isUnique":0,'
            . '"tableField":"test_field","selectType":"custom","tableType":"varchar","tableLength":10,'
            . '"formLabel":"Project","formType":"text","formRange":"","defaultValue":1,'
            . '"listPosition":2,"status":1,"isRequired":1}}';
        $this->setRequestUrl('Core/moduleDesigner/jsonSave');
        $this->request->setParam('designerData', $designerData);
        $this->request->setParam('id', Zend_Registry::get('moduleId'));
        $this->request->setParam('name', 'test');
        $this->request->setParam('label', 'test');
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains('The table module was edited correctly', $response, 'Response was: ' . $response);
    }

    public function testDeleteDatabaseManager()
    {
        // Delete
        $designerData = '{"0":{"id":' . Zend_Registry::get('dbId') . ',"tableName":"Test","formPosition":1,"formTab":1,'
            . '"formColumns":1,"formRegexp":null,"listAlign":"center","listUseFilter":1,"altPosition":0,"isInteger":0,'
            . '"isUnique":0,"tableField":"project_id","selectType":"project","tableType":"int","tableLength":5,'
            . '"formLabel":"Project","formType":"selectValues","formRange":"Project # id # title","defaultValue":1,'
            . '"listPosition":1,"status":1,"isRequired":1}}';
        $this->setRequestUrl('Core/moduleDesigner/jsonSave');
        $this->request->setParam('designerData', $designerData);
        $this->request->setParam('id', Zend_Registry::get('moduleId'));
        $this->request->setParam('name', 'test');
        $this->request->setParam('label', 'test');
        $this->request->setParam('nodeId', 1);
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
        $this->request->setParam('nodeId', 1);
        $response = $this->getResponse();
        $this->assertContains('"name":"Project"', $response);
    }

    /**
     * Test of json detail Module
     */
    public function testJsonDetailNewItem()
    {
        $this->setRequestUrl('Core/module/jsonDetail');
        $this->request->setParam('nodeId', 1);
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
        $this->setRequestUrl('Core/moduleDesigner/jsonDetail');
        $this->request->setParam('id', 1);
        $this->request->setParam('nodeId', 1);
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
