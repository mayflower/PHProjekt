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
 * Tests for Groups
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
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

        $group = new Phprojekt_Groups_Groups($this->sharedFixture);
        $groupUser = $group->getUserId();
        $this->assertEquals($user, $groupUser);
    }

    /**
     * is user in a group
     */
    public function testIsUserInGroup()
    {
        $group = new Phprojekt_Groups_Groups($this->sharedFixture);
        $this->assertTrue($group->isUserInGroup(1));
        $this->assertFalse($group->isUserInGroup(4));
    }

    /**
     * groups for one user
     */
    public function testGetUserGroups()
    {
        $group = new Phprojekt_Groups_Groups($this->sharedFixture);
        $this->assertEquals(2, count($group->getUserGroups()));

        $authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');
        $keepUser = $authNamespace->userId;

        $authNamespace->userId = 3;
        $group = new Phprojekt_Groups_Groups($this->sharedFixture);
        $this->assertEquals(0, count($group->getUserGroups()));
        $authNamespace->userId = $keepUser;
    }
}
