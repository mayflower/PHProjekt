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


/**
 * Tests for Filter
 *
 * @group      phprojekt
 * @group      filter
 * @group      user
 * @group      phprojekt-filter
 * @group      phprojekt-filter-user
 */
class Phprojekt_Filter_UserFilterTest extends DatabaseTest
{
    public function setUp()
    {
        parent::setUp();
        $this->sharedFixture = Phprojekt::getInstance()->getDb();
    }

    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/../data.xml');
    }

    /**
     * Test the filtering
     */
    public function testFilter()
    {
        $record = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $filter = new Phprojekt_Filter_UserFilter($record, 'title', 'PHProjekt');
        $tree   = new Phprojekt_Tree_Node_Database($record, 1);
        $tree   = $tree->setup($filter);
        $this->assertEquals(1, $tree->getRootNode()->id);

        $this->setExpectedException('InvalidArgumentException');
        $filter = new Phprojekt_Filter_UserFilter($record, 'NONE', 'Invisible Root');
    }

    /**
     * Test setValue function
     */
    public function testSetValue()
    {
        $record = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $filter = new Phprojekt_Filter_UserFilter($record, 'title', 'NONE');

        $filter->setValue('PHProjekt');
        $tree = new Phprojekt_Tree_Node_Database($record, 1);
        $tree = $tree->setup($filter);
        $this->assertEquals(1, $tree->getRootNode()->id);
    }

    /**
     * Test saveToBackingStore function
     */
    public function testSaveToFilter()
    {
        $user = new Phprojekt_User_User(array('db' => $this->sharedFixture));
        $user->find(1);

        $record = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $filter = new Phprojekt_Filter_UserFilter($record, 'title', 'PHProjekt');
        $tree   = new Phprojekt_Tree_Node_Database($record, 1);
        $tree   = $tree->setup($filter);

        $filter->saveToBackingStore($user);
    }
}
