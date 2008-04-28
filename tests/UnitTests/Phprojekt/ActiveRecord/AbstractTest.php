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
require_once 'PHPUnit/Framework/TestCase.php';

class Phprojekt_Project extends Phprojekt_Item_Abstract
{
    public $hasMany = array('instances' => array('classname' => 'Phprojekt_ModuleInstance'));
}

class Phprojekt_ModuleInstance extends Phprojekt_ActiveRecord_Abstract
{
    public $belongsTo = array('project' => array('classname' => 'Phprojekt_Project'));
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
class Phprojekt_ActiveRecord_AbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     *
     */
    public function testFetchAllWithJoins()
    {
        try {
            $project  = new Phprojekt_Project(array('db' => $this->sharedFixture));
            $project->fetchAll();
            $this->assertEquals(7,$project->count());

            $project->find(3);
            $this->assertNull($project->title);

            $projects = $project->fetchAll(null, null, null, null,
                            'RIGHT JOIN projectuserrolerelation ON projectuserrolerelation.projectId = Project.id');
            $this->assertEquals(1,count($projects));

            $projects = $project->fetchAll(null, null, null, null,
                            'LEFT JOIN projectuserrolerelation ON projectuserrolerelation.projectId = Project.id');
            $this->assertEquals(6,count($projects));
        } catch (Exception $e) {
            $this->fail($e->getMessage().$e->getTraceAsString());
        }
    }

    /**
     *
     */
    public function testCreateHasManyAndBelongsToMany()
    {
        try {
            $user = new User_Models_User(array('db' => $this->sharedFixture));
            $users = $user->fetchAll($this->sharedFixture->quoteInto('username = ?', 'david'));

            if ($users == NULL) {
                $this->fail ('No user found');
            }

            $david        = $users[0];
            $group        = $david->groups->create();
            $group->name  = 'TEST GROUP';
            $this->assertTrue($group->save());

            $this->assertNotNull($group->id);
        } catch (Exception $e) {
            $this->fail($e->getMessage().$e->getTraceAsString());
        }
    }

    /**
	 *
	 */
	public function testDeleteProject ()
	{
		try {
			$project = new Phprojekt_Project(array('db' => $this->sharedFixture));
			$project->title = 'Hello World Project to delete';
			$project->startDate = '1981-05-12';
			$project->endDate = '1981-05-12';
			$project->priority = 1;
			$project->path = '/';
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
	    $authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');
        $keepUser = $authNamespace->userId;

		try {
            $authNamespace->userId = 0;

			$user = new User_Models_User(array('db' => $this->sharedFixture));
			$user->username = 'Foo';
			$user->password = md5('Bar');
			$user->language = 'en_GB';
			$user->save();

			$group       = $user->groups->create();
			$group->name = 'Test';
			$this->assertTrue($group->save());

			$this->assertNotNull($user->id);
			$this->assertEquals(1, $user->groups->count());

			$user->delete();

			$this->assertEquals(0, $user->groups->count());
			$this->assertNull($user->id);
        } catch (Exception $e) {
            $authNamespace->userId = $keepUser;
            $this->fail($e->getMessage());
        }
        $authNamespace->userId = $keepUser;
	}

    /**
     *
     */
    public function testCreateProjectWithHasMany()
    {
        try {
            $project = new Phprojekt_Project(array('db' => $this->sharedFixture));
            $project->find(1);
            $module_instance = $project->instances->create();
            $module_instance->name = 'My TestModule';
            $module_instance->module = 'TestModule';
            $this->assertTrue($module_instance->save());
        } catch (Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     *
     */
    public function testCreateUser()
    {
        $authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');
        $keepUser = $authNamespace->userId;
        try {
            $authNamespace->userId = 0;
            $user = new User_Models_User(array('db'=>$this->sharedFixture));
            $user->username  = 'gustavo';
            $user->password  = md5('gustavo');
            $user->firstname = 'Gustavo';
            $user->lastname  = 'Solt';
            $user->language  = 'es_AR';

            $this->assertTrue($user->save());

            $gustavo = new User_Models_User(array('db' => $this->sharedFixture));
            $gustavo->find($user->id);
            $this->assertEquals('gustavo', $gustavo->username);
        } catch (Exception $e) {
            $authNamespace->userId = $keepUser;
            $this->fail($e->getMessage());
        }
        $authNamespace->userId = $keepUser;
    }

    /**
     * Has Many and belongs to many test
     *
     * @return void
     */
    public function testHasManyAndBelongsToMany()
    {
        $user = new User_Models_User(array('db' => $this->sharedFixture));
        $user->find(1);
        $group = $user->groups->fetchAll();

        $this->assertEquals('default', $user->groups->find(1)->name);
        $this->assertEquals('ninasgruppe', $group[1]->name);
        $this->assertEquals('TEST GROUP', $group[2]->name);
        $this->assertEquals(3, $user->groups->count());

        $group = new Groups_Models_Groups(array('db' => $this->sharedFixture));
        $group->find(1);
        $users = $group->users->fetchAll();
        $this->assertEquals('david', $users[0]->username);
        $this->assertEquals(1, $group->users->count());
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
        $project->find(5);
        $this->assertEquals(5, $project->id);
        $this->assertEquals('Developer Tasks', $project->instances->find(1)->name);
        $this->assertEquals('Project Tasks', $project->instances->find(2)->name);

        $this->assertEquals(3, $project->instances->count());
        $this->assertEquals(7, $project->count());

        // same but with fetch all
        $rows = $project->fetchAll();
        $this->assertEquals(5, $rows[3]->id);
        $this->assertEquals('Developer Tasks', $rows[3]->instances->find(1)->name);
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
        $this->assertEquals(3, $instance->count());
    }

    /**
     * Update hasMany relations
     */
    public function testUpdateHasMany()
    {
        $instance = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $instance->find(5);
        $instance->id = 10;
        $instance->notes = '';
        $this->assertTrue($instance->save());

        $instance->find(10);
        $this->assertEquals('Developer Tasks', $instance->instances->find(1)->name);
        $this->assertEquals(10, $instance->instances->find(1)->projectId);

        $instance->id = 5;
        $instance->save();
    }

    /**
     * Update hasMany relations
     */
    public function testUpdateHasManyAndBelongsToMany()
    {
        try {
            $user = new User_Models_User(array('db' => $this->sharedFixture));
            $user->find(1);
            $user->id = 2;
            $user->username = 'dsp';
            $user->save();

            $groups = $user->groups->fetchAll();
            $this->assertEquals(2, $groups[0]->userId);
            $this->assertEquals('ninatest', $groups[1]->name);

            $user->find(2);
            $user->id = 1;
            $user->username = 'david';
            $user->save();
        }
        catch(Exception $e) {
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
        $instance->find(5);

        $this->assertEquals('Test Project', $instance->title);

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