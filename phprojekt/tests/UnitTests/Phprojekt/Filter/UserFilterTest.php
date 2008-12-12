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
 * @version    CVS: $Id$
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */
require_once 'PHPUnit/Framework.php';

/**
 * Tests for Filter
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class Phprojekt_Filter_UserFilterTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test the filtering
     *
     */
    public function testFilter ()
    {
        $authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');
        $authNamespace->userId = 1;

        $record = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $filter = new Phprojekt_Filter_UserFilter($record, 'title', 'Invisible Root');
        $tree   = new Phprojekt_Tree_Node_Database($record, 1);
        $tree->setup($filter);
        $this->assertEquals(1, count($tree->getRootNode()->getChildren()));
    }

    public function testSaveToFilter()
    {
        $user = new Phprojekt_User_User(array('db' => $this->sharedFixture));
        $user->find(1);

        $record = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $filter = new Phprojekt_Filter_UserFilter($record, 'title', 'Invisble Root');
        $tree   = new Phprojekt_Tree_Node_Database($record, 1);
        $tree->setup($filter);

        $filter->saveToBackingStore($user);
    }
}
