<?php
/**
 * Unit test
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @category   PHProjekt
 * @package    UnitTests
 * @subpackage Phprojekt
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Eduardo Polidor <solt@mayflower.de>
 */


/**
 * Tests for Groups
 *
 * @category   PHProjekt
 * @package    UnitTests
 * @subpackage Phprojekt
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Eduardo Polidor <solt@mayflower.de>
 * @group      phprojekt
 * @group      group
 * @group      phprojekt-group
 */
class Phprojekt_GroupTest extends DatabaseTest
{
    protected function getDataSet() {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/data.xml');
    }

    /**
     * Set user
     */
    public function testSetUser()
    {
        $authNamespace = new Zend_Session_Namespace('Phprojekt_Auth-login');
        $user          = $authNamespace->userId;

        $group     = new Phprojekt_Groups_Groups($this->sharedFixture);
        $groupUser = $group->getUserId();
        $this->assertEquals($user, $groupUser);
    }

    /**
     * Is user in a group
     */
    public function testIsUserInGroup()
    {
        $group = new Phprojekt_Groups_Groups($this->sharedFixture);
        $this->assertTrue($group->isUserInGroup(1));
        $this->assertFalse($group->isUserInGroup(4));
        $this->assertFalse($group->isUserInGroup(1000));
    }

    /**
     * Groups for one user
     */
    public function testGetUserGroups()
    {
        $group = new Phprojekt_Groups_Groups($this->sharedFixture);
        $this->assertEquals(2, count($group->getUserGroups()));

        $authNamespace = new Zend_Session_Namespace('Phprojekt_Auth-login');
        $keepUser      = $authNamespace->userId;

        $authNamespace->userId = 3;
        $group                 = new Phprojekt_Groups_Groups($this->sharedFixture);
        $this->assertEquals(0, count($group->getUserGroups()));
        $authNamespace->userId = $keepUser;
    }

    /**
     * Test for getInformation
     */
    public function testGetInformation()
    {
        $group     = new Phprojekt_Groups_Groups($this->sharedFixture);
        $converted = array();

        $data                  = array();
        $data['key']           = 'name';
        $data['label']         = Phprojekt::getInstance()->translate('Name');
        $data['originalLabel'] = 'Name';
        $data['type']          = 'text';
        $data['hint']          = Phprojekt::getInstance()->getTooltip('name');
        $data['listPosition']  = 1;
        $data['formPosition']  = 1;
        $data['fieldset']      = '';
        $data['range']         = array('id'   => '',
                                       'name' => '');
        $data['required'] = true;
        $data['readOnly'] = false;
        $data['tab']      = 1;
        $data['integer']  = false;
        $data['length']   = 255;
        $data['default']  = null;

        $converted[] = $data;

        $this->assertEquals($converted, $group->getInformation()->getFieldDefinition());
    }

    /**
     * Test for mock function
     */
    public function testMocks()
    {
        $group = new Phprojekt_Groups_Groups($this->sharedFixture);
        $this->assertEquals(array(), $group->getRights());

        $this->assertTrue($group->recordValidate());
    }
}
