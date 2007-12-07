<?php
/**
 * Unit test
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
*/
require_once 'PHPUnit/Framework.php';

/**
 * Tests for Groups
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Eduardo Polidor <solt@mayflower.de>
 */
class Phprojekt_GroupTest extends PHPUnit_Framework_TestCase
{
    /**
     * set user
     */
    public function testSetUser()
    {
        $authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');
        $user = $authNamespace->userId;

        $group = new Groups_Models_Groups($this->sharedFixture);
        $groupUser = $group->getUser();
        $this->assertEquals($user, $groupUser);

        $group = new Groups_Models_Groups($this->sharedFixture, 3);
        $groupUser = $group->getUser();
        $this->assertEquals(3, $groupUser);
    }

    /**
     * is user in a group
     */
    public function testIsUserInGroup()
    {
        $group = new Groups_Models_Groups($this->sharedFixture);
        $this->assertTrue($group->isUserInGroup(1));
        $this->assertFalse($group->isUserInGroup(4));
    }

    /**
     * groups for one user
     */
    public function testGetUserGroups()
    {
        $group = new Groups_Models_Groups($this->sharedFixture);
        $this->assertEquals(2, count($group->getUserGroups()));

        $group = new Groups_Models_Groups($this->sharedFixture, 3);
        $this->assertEquals(0, count($group->getUserGroups()));
    }
}
