<?php
/**
 * Unit test
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
*/
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Extensions/ExceptionTestCase.php';

class Phprojekt_Project extends Phprojekt_Item_Abstract
{
    public $hasMany = array('instances' => array('classname' => 'Phprojekt_ModuleInstance'));
}

class Phprojekt_ModuleInstance extends Phprojekt_ActiveRecord_Abstract
{
    public $belongsTo = array('project' => array('classname' => 'Phprojekt_Project'));
}

class Phprojekt_User extends Phprojekt_ActiveRecord_Abstract
{
    public $hasManyAndBelongsToMany = array('roles' => array('classname'=> 'Phprojekt_Role'));
}

class Phprojekt_Role extends Phprojekt_ActiveRecord_Abstract
{
    public $hasManyAndBelongsToMany = array('users' => array('classname'=> 'Phprojekt_User'));
}

/**
 * Tests for active records
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class Phprojekt_ActiveRecord_AbstractTest extends PHPUnit_Extensions_ExceptionTestCase
{

    /**
     *
     */
    public function testCreateHasManyAndBelongsToMany()
    {
        $this->sharedFixture->beginTransaction();
        try {
            $user = new Phprojekt_User(array('db' => $this->sharedFixture));
            $users = $user->fetchAll($this->sharedFixture->quoteInto('username = ?', 'david'));

            $david = $users[0];
            $role  = $david->roles->create();
            $role->name       = 'Project Admin';
            $role->module     = 'Test Module';
            $role->permission = 'Write';
            $role->save();

            $this->assertNotNull($role->id);
        } catch (Exception $e) {
            $this->sharedFixture->rollBack();
            $this->fail($e->getMessage().$e->getTraceAsString());
        }
        $this->sharedFixture->rollBack();
    }

    /**
	 *
	 */
	public function testDeleteProject ()
	{
		$this->sharedFixture->beginTransaction();
		try {
			$project = new Phprojekt_Project(array('db' => $this->sharedFixture));
			$project->title = 'Hello World Project to delete';
			$project->path = '/';
			$project->save();

			$this->assertNotNull($project->id);
			$project->delete();

			$this->assertNull($project->id);
        } catch (Exception $e) {
            $this->sharedFixture->rollBack();
            $this->fail($e->getMessage());
        }
        $this->sharedFixture->rollBack();
	}

	public function testGetTableName()
	{
	   $instance = new Phprojekt_ModuleInstance(array('db' => $this->sharedFixture));
	   $this->assertEquals('ModuleInstance', $instance->getTableName());
	}

	/*
	 *
	 */
	public function testDeleteHasManyAndBelongsToMany()
	{
		$this->sharedFixture->beginTransaction();
		try {
			$user = new Phprojekt_User(array('db' => $this->sharedFixture));
			$user->username = 'Foo';
			$user->password = md5('Bar');
			$user->language = 'en_GB';
			$user->save();

			$role = $user->roles->create();
			$role->name       = 'Test';
			$role->module     = 'Tasks';
			$role->permission = 'Write';

			$role->save();

			$this->assertNotNull($user->id);
			$this->assertEquals(1, $user->roles->count());

			$user->delete();

			$this->assertEquals(0, $user->roles->count());
			$this->assertNull($user->id);
        } catch (Exception $e) {
            $this->sharedFixture->rollBack();
            $this->fail($e->getMessage());
        }
        $this->sharedFixture->rollBack();
	}

    /**
     *
     */
    public function testCreateProjectWithHasMany()
    {
        $this->sharedFixture->beginTransaction();
        try {
            $project = new Phprojekt_Project(array('db' => $this->sharedFixture));
            $project->find(1);
            $module_instance = $project->instances->create();
            $module_instance->name = 'My TestModule';
            $module_instance->module = 'TestModule';
            $module_instance->save();
        } catch (Exception $e) {
            $this->sharedFixture->rollBack();
            $this->fail($e->getMessage());
        }
        $this->sharedFixture->rollBack();
    }

    /**
     *
     */
    public function testCreateUser()
    {
        $this->sharedFixture->beginTransaction();
        try {
            $user = new Phprojekt_User(array('db'=>$this->sharedFixture));
            $user->username  = 'gustavo';
            $user->password  = md5('gustavo');
            $user->firstname = 'Gustavo';
            $user->lastname  = 'Solt';
            $user->language  = 'es_AR';
            $user->save();

            $gustavo = new Phprojekt_User(array('db' => $this->sharedFixture));
            $gustavo->find($user->id);
            $this->assertEquals('gustavo', $gustavo->username);
        } catch (Exception $e) {
            $this->sharedFixture->rollBack();
            $this->fail($e->getMessage());
        }
        $this->sharedFixture->rollBack();
    }

    /**
     * Has Many and belongs to many test
     *
     * @return void
     */
    public function testHasManyAndBelongsToMany()
    {
        $user = new Phprojekt_User(array('db' => $this->sharedFixture));
        $user->find(1);
        $roles = $user->roles->fetchAll();
        $this->assertEquals('Senior Developer', $user->roles->find(2)->name);
        $this->assertEquals('Developer', $roles[0]->name);
        $this->assertEquals('Senior Developer', $roles[1]->name);
        $this->assertEquals(2, $user->roles->count());

        $role = new Phprojekt_Role(array('db' => $this->sharedFixture));
        $role->find(1);
        $users = $role->users->fetchAll();
        $this->assertEquals('david', $users[0]->username);
        $this->assertEquals(1, $role->users->count());

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
        $project->find(1);
        $this->assertEquals(1, $project->id);
        $this->assertEquals('Developer Tasks', $project->instances->find(1)->name);
        $this->assertEquals('Project Tasks', $project->instances->find(2)->name);
        $this->assertEquals(2, $project->instances->count());
        $this->assertEquals(1, $project->count());

        // same but with fetch all
        $rows = $project->fetchAll();
        $this->assertEquals(1, $rows[0]->id);
        $this->assertEquals('Developer Tasks', $rows[0]->instances->find(1)->name);
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
        $instance = new Phprojekt_ModuleInstance(array('db' => $this->sharedFixture));

        $instance->find(1);
        $this->assertEquals('Test Project', $instance->project->title);
    }

    /**
     * Update hasMany relations
     */
    public function testUpdateHasMany()
    {
        $instance = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $instance->find(1);
        $instance->id = 2;
        $instance->save();

        $instance->find(2);
        $this->assertEquals('Developer Tasks', $instance->instances->find(1)->name);
        $this->assertEquals(2, $instance->instances->find(1)->projectId);

        $instance->find(2);
        $instance->id = 1;
        $instance->save();
    }

    /**
     * Update hasMany relations
     */
    public function testUpdateHasManyAndBelongsToMany()
    {
        $this->sharedFixture->beginTransaction();
        try {
            $user = new Phprojekt_User(array('db' => $this->sharedFixture));
            $user->find(1);
            $user->id = 2;
            $user->username = 'dsp';
            $user->save();

            $roles = $user->roles->fetchAll();
            $this->assertEquals(2, $roles[0]->userId);
            $this->assertEquals('Senior Developer', $roles[1]->name);

            $user->find(2);
            $user->id = 1;
            $user->username = 'david';
            $user->save();
        }
        catch(Exception $e) {
            $this->sharedFixture->rollBack();
            $this->fail($e->getMessage());
        }
    }

    /**
     * Save Test
     *
     * @return void
     */
    public function testSave()
    {
        $instance = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $instance->find(1);

        $this->assertEquals('Test Project', $instance->title);

        $instance->title = 'PHPUnit Test Project';
        $this->assertEquals('PHPUnit Test Project', $instance->title);

        $instance->save();

        $instance->find(1);
        $this->assertEquals('PHPUnit Test Project', $instance->title);

        unset ($instance);

        $instance = new PHprojekt_Project(array('db' => $this->sharedFixture));
        $instance->find(1);
        $this->assertEquals('PHPUnit Test Project', $instance->title);

        $instance->title = 'Test Project';
        $instance->save();
    }

    /**
     * Test setting a nonexisting attribute.
     * Should throw an exception
     *
     * @return void
     */
    public function testWrongSet()
    {
        $this->setExpectedException('Exception');

        $instance = new PHprojekt_Project(array('db' => $this->sharedFixture));
        $instance->wrongAttribute = 'Hello World';
    }

    /**
     * Test getting a nonexisting attribute.
     * Should throw an exceptio n
     *
     * @return void
     */
    public function testWrongGet()
    {
        $this->setExpectedException('Exception');

        $instance = new PHprojekt_Project(array('db' => $this->sharedFixture));
        $get      = $instance->wrongAttribute;
    }

    /**
     * Test, db not given
     *
     * @return void
     */
    public function testDbNotGiven()
    {
        try {
            $instance = new Phprojekt_Project(array());
        } catch (Phprojekt_ActiveRecord_Exception $pare) {
            return;
        }
        $this->fail('Phprojekt_ActiveRecord_Exception expected');
    }
}