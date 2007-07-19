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

class Phprojekt_Project extends Phprojekt_ActiveRecord
{
    public $hasMany = array('instances' => array('classname' => 'Phprojekt_ModuleInstance'));
}

class Phprojekt_ModuleInstance extends Phprojekt_ActiveRecord
{
    public $belongsTo = array('project' => array('classname' => 'Phprojekt_Project'));
}

class Phprojekt_User extends Phprojekt_ActiveRecord
{
    public $hasManyAndBelongsToMany = array('roles' => array('classname'=> 'Phprojekt_Role'));
}

class Phprojekt_Role extends Phprojekt_ActiveRecord
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
class Phprojekt_ActiveRecordTest extends PHPUnit_Extensions_ExceptionTestCase
{

    /**
     * setUp method for PHPUnit. We use a shared db connection
     *
     */
    public function setUp()
    {
        $this->sharedFixture = Zend_Db::factory('PDO_MYSQL', array(
                                          'username' => 'phprojekt',
                                          'password' => 'phprojekt',
                                          'dbname' => 'phprojekt-mvc',
                                          'host' => 'localhost'));
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