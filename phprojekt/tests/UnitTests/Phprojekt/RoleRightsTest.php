<?php
/**
 * Unit test
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */
require_once 'PHPUnit/Framework.php';

/**
 * Tests for RoleRights
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Nina Schmitt <nina.schmitt@mayflower.de>
 */

class Phprojekt_RoleRightsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var    Phprojekt_RoleRights
     * @access protected
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp()
    {
        $this->object = new Phprojekt_RoleRights(1, 2, 0, 1);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @access protected
     */
    protected function tearDown()
    {
    }

    /**
     * test hasRight
     */
    public function testHasRight()
    {
        $this->assertEquals(true, $this->object->hasRight('write',1));
    }

    /**
     * testGetId() tests the GetId function.
     */
    public function testGetId()
    {
        $this->assertEquals(0, $this->object->getId());
    }

    /**
     * testGetProject() tests the getProject function.
     */
    public function testGetProject()
    {
        // Remove the following lines when you implement this test.
        $this->assertEquals(1, $this->object->getProject());
    }

    /**
     * testGetModule().
     */
    public function testGetModule()
    {
        $this->assertEquals(2, $this->object->getModule());
    }

    /**
     * test whether right user is found.
     *
     */
    public function testGetUser()
    {
       $this->assertEquals(1, $this->object->getUser());
    }

    /**
     *  testGetAcl().
     */
    public function testGetAcl()
    {
        $this->assertSame(Phprojekt_Acl::getInstance(), $this->object->getAcl());
    }

    /**
     *  testGetUserRole().
     */
    public function testGetUserRole()
    {
        $this->assertEquals(1, $this->object->getUserRole());
    }
}