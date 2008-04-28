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
 * Tests for Role
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Nina Schmitt <nina.schmitt@mayflower.de>
 */

class Phprojekt_RoleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var    Phprojekt_Role
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
        $this->object = new Role_Models_Role($this->sharedFixture);
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
     * returns the role of a user in a project
     */
    public function testFetchUserRole()
    {
        $authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');
        $authNamespace->userId = 1;

        $this->assertEquals($this->object->fetchUserRole(1, 1), 1);
        $this->assertFalse($this->object->fetchUserRole(2, 2)==1);
    }
}
