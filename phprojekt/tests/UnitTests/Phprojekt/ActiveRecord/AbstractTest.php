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

require_once 'PHPUnit/Framework/TestCase.php';

class Phprojekt_Project extends Phprojekt_Item_Abstract
{
    public $hasMany = array('instances' => array('classname' => 'Phprojekt_ModuleInstance'));
}

class Phprojekt_ModuleInstance extends Phprojekt_ActiveRecord_Abstract
{
    public $belongsTo = array('project' => array('classname' => 'Phprojekt_Project'));
}

class Phprojekt_HmabtmTest extends Phprojekt_ActiveRecord_Abstract
{
    public $hasManyAndBelongsToMany = array('project' => array('classname' => 'Phprojekt_Project'));
}

/**
 * Tests for active records
 *
 * @group      phprojekt
 * @group      activerecord
 * @group      phprojekt-activerecord
 */
class Phprojekt_ActiveRecord_AbstractTest extends DatabaseTest
{
    public function setUp() {
        parent::setUp();
        $this->sharedFixture = Phprojekt::getInstance()->getDb();
    }

    protected function getDataSet() {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/data.xml');
    }

    public function testFetchAllWithJoins()
    {
        $project  = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $project->fetchAll();
        $this->assertEquals(5, $project->count());

        $project->find(3);
        $this->assertNull($project->title);

        $projects = $project->fetchAll(null, null, null, null, null,
            'RIGHT JOIN project_role_user_permissions ON project_role_user_permissions.project_id = project.id');
        $this->assertEquals(2, count($projects));

        $projects = $project->fetchAll(null, null, null, null, "project_role_user_permissions.role_id",
            'LEFT JOIN project_role_user_permissions ON project_role_user_permissions.project_id = project.id');

        $this->assertEquals(5, count($projects));
    }

    public function testCreateHasManyAndBelongsToMany()
    {
        $this->markTestIncomplete("functionality has to be reimplemented");
    }

    public function testGetTableName()
    {
       $instance = new Phprojekt_ModuleInstance(array('db' => $this->sharedFixture));
       $this->assertEquals('module_instance', $instance->getTableName());
    }

    public function testDeleteHasManyAndBelongsToMany()
    {
        $authNamespace = new Zend_Session_Namespace('Phprojekt_Auth-login');
        $keepUser = $authNamespace->userId;

        $role = new Phprojekt_Role_Role(array('db' => $this->sharedFixture));

        $role->name = 'deleteMe';
        $role->save();

        $modulePermissions = $role->modulePermissions->create();
        $modulePermissions->moduleId = 1;
        $modulePermissions->roleId = $role->id;
        $modulePermissions->access = 199;

        $this->assertTrue($modulePermissions->save());

        $this->assertNotNull($role->id);
        $this->assertEquals(2, $role->modulePermissions->count());

        $role->delete();

        $this->assertEquals(1, $role->modulePermissions->count());
        $this->assertNull($role->id);

        $authNamespace->userId = $keepUser;
    }

    public function testCreateProjectWithHasMany()
    {
        try {
            $project = new Phprojekt_Project(array('db' => $this->sharedFixture));
            $project->find(1);
            $moduleInstance = $project->instances->create();
            $moduleInstance->name = 'My TestModule';
            $moduleInstance->module = 'TestModule';
            $this->assertTrue($moduleInstance->save());
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Test create an user
     */
    public function testCreateUser()
    {
        try {
            $user = new Phprojekt_User_User(array('db'=>$this->sharedFixture));
            $user->username  = 'gustavo';
            $user->firstname = 'Gustavo';
            $user->lastname  = 'Solt';

            $this->assertTrue($user->save());

            $gustavo = new Phprojekt_User_User(array('db' => $this->sharedFixture));
            $gustavo->find($user->id);
            $this->assertEquals('gustavo', $gustavo->username);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Has Many and belongs to many test
     *
     * @return void
     */
    public function testHasManyAndBelongsToMany()
    {
        $this->markTestIncomplete("functionality has to be reimplemented");
    }

    /**
     * Belongs to test
     *
     * @todo Inhance
     *
     * @return void
     */
    public function testHasMany()
    {
        $project = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $project->find(2);
        $this->assertEquals(2, $project->id);
        $this->assertEquals('Developer Tasks', $project->instances->find(1)->name);
        $this->assertEquals('Project Tasks', $project->instances->find(2)->name);

        $this->assertEquals(3, $project->instances->count());
        $this->assertEquals(5, $project->count());

        // same but with fetch all
        $rows = $project->fetchAll();
        $this->assertEquals(6, $rows[3]->id);
        $this->assertEquals('Developer Tasks', $rows[1]->instances->find(1)->name);
    }

    /**
     * Belongs to test
     *
     * @todo Inhance
     *
     * @return void
     */
    public function testBelongsTo()
    {
        $project  = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $expected = $project->find(2);

        $instance = new Phprojekt_ModuleInstance(array('db' => $this->sharedFixture));
        $instance->find(2);
        $actual   = $instance->project;

        $this->assertEquals($expected->id, $actual->id);
    }

    /**
     * Update hasMany relations
     */
    public function testUpdateHasMany()
    {
        $this->markTestIncomplete('See issue  #260');
    }

    /**
     * Update hasMany relations
     */
    public function testUpdateHasManyAndBelongsToMany()
    {
        $hmabtm = new Phprojekt_HmabtmTest(array('db' => $this->sharedFixture));
        $project = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $this->markTestIncomplete("functionality not implemented");
    }

    /**
     * Save Test
     *
     * @return void
     */
    public function testSave()
    {
        $instance = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $instance->find(5);

        $this->assertEquals('Sub Project', $instance->title);

        $instance->title = 'PHPUnit Test Project';
        $this->assertEquals('PHPUnit Test Project', $instance->title);

        $this->assertTrue($instance->save());

        $instance->find(5);
        $this->assertEquals('PHPUnit Test Project', $instance->title);

        unset ($instance);

        $instance = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $instance->find(5);
        $this->assertEquals('PHPUnit Test Project', $instance->title);

        $instance->title = 'Test Project';
        $this->assertTrue($instance->save());
    }

    public function testDeleteProject ()
    {
        try {
            $project = new Phprojekt_Project(array('db' => $this->sharedFixture));
            $project->title     = 'Hello World Project to delete';
            $project->startDate = '1981-05-12';
            $project->endDate   = '1981-05-12';
            $project->priority  = 1;
            $project->projectId = 1;
            $project->path      = '/';
            $project->save();

            $this->assertNotNull($project->id);
            $id = $project->id;
            $project->delete();

            $project->find($id);
            $this->assertNull($project->title);
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Test setting a nonexisting attribute.
     * Should throw an exception
     *
     * @return void
     */
    public function testWrongSet()
    {
        $this->setExpectedException('Phprojekt_ActiveRecord_Exception');

        $instance = new PHprojekt_Project(array('db' => $this->sharedFixture));
        $instance->wrongAttribute = 'Hello World';
    }

    /**
     * Test getting a nonexisting attribute.
     * Should throw an exception
     *
     * @return void
     */
    public function testWrongGet()
    {
        $this->setExpectedException('Phprojekt_ActiveRecord_Exception');

        $instance = new PHprojekt_Project(array('db' => $this->sharedFixture));
        $instance->wrongAttribute;
    }

    /**
     * Test, db not given
     *
     * @return void
     */
    public function testDbNotGiven()
    {
        try {
            new Phprojekt_Project(array());
        } catch (Phprojekt_ActiveRecord_Exception $error) {
            return $error->getMessage();
        }
        $this->fail('Phprojekt_ActiveRecord_Exception expected');
    }

    /**
     * Test sql conversion, calls are static here to avoid Db adapter confusion
     */
    public function testVarToSql()
    {
        $this->assertEquals('lowcase_underscored', Phprojekt_ModuleInstance::convertVarToSql('lowcaseUnderscored'));
        $this->assertEquals('lowcase_underscored', Phprojekt_ModuleInstance::convertVarToSql('LowcaseUnderscored'));
        $this->assertEquals('lowcasenoscore', Phprojekt_ModuleInstance::convertVarToSql('lowcasenoscore'));
        $this->assertEquals('lowcasenoscore', Phprojekt_ModuleInstance::convertVarToSql('Lowcasenoscore'));
        $this->assertEquals('123text_text', Phprojekt_ModuleInstance::convertVarToSql('123textText'));
        $this->assertEquals('abcäöüxyz', Phprojekt_ModuleInstance::convertVarToSql('abcäöüxyz'));
        $this->assertEquals('Äöü123abc', Phprojekt_ModuleInstance::convertVarToSql('Äöü123abc'));
    }

    /**
     * Test sql conversion, calls are static here to avoid Db adapter confusion
     */
    public function testVarFromSql()
    {
        $this->assertEquals('lowcaseUnderscored', Phprojekt_ModuleInstance::convertVarFromSql('lowcase_underscored'));
        $this->assertEquals('LowcaseUnderscored', Phprojekt_ModuleInstance::convertVarFromSql('Lowcase_underscored'));
        $this->assertEquals('lowcasenoscore', Phprojekt_ModuleInstance::convertVarFromSql('lowcasenoscore'));
        $this->assertEquals('Lowcasenoscore', Phprojekt_ModuleInstance::convertVarFromSql('Lowcasenoscore'));
        $this->assertEquals('123textText', Phprojekt_ModuleInstance::convertVarFromSql('123text_text'));
        $this->assertEquals('abcäöüxyz', Phprojekt_ModuleInstance::convertVarFromSql('abcäöüxyz'));
        $this->assertEquals('Äöü123abc', Phprojekt_ModuleInstance::convertVarFromSql('Äöü123abc'));
    }

    /**
     * Test toArray
     */
    public function testToArray()
    {
        $project = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $project->find(1);
        $expected = array('id'              => 1,
                          'projectId'       => "",
                          'path'            => '/',
                          'title'           => 'PHProjekt',
                          'notes'           => "",
                          'ownerId'         => 1,
                          'startDate'       => "2007-12-01",
                          'endDate'         => "",
                          'priority'        => 1,
                          'currentStatus'   => 3,
                          'completePercent' => null,
                          'hourlyWageRate'  => "",
                          'budget'          => "",
                          'contactId'       => "");
        $this->assertEquals($expected, $project->toArray());
    }

    /**
     * Test what happens if calling find() with no argument
     */
    public function testFindWithNoArgument()
    {
        $project = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $this->setExpectedException('Phprojekt_ActiveRecord_Exception');
        // This triggers an undefined index error, should throw an exception instead
        $project->find();
    }

    /**
     * Test what happens if calling find() with NULL argument
     */
    public function testFindWithNull()
    {
        $project = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $this->setExpectedException('Phprojekt_ActiveRecord_Exception');
        // This acts like find(0), should throw an exception instead.
        $project->find(NULL);
    }

    /**
     * Test what happens if calling find() with 2 arguments
     */
    public function testFindWithTwoArgument()
    {
        $project = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $this->setExpectedException('Phprojekt_ActiveRecord_Exception');
        // Should throw an exception instead.
        $project->find(1, 2);
    }
}

