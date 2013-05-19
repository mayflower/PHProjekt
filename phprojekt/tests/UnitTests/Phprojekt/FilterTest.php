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
 * Tests for Filters
 *
 * @group      phprojekt
 * @group      main
 * @group      phprojekt-main
 */
class Phprojekt_FilterTest extends DatabaseTest
{
    public function setUp()
    {
        parent::setUp();
        $this->sharedFixture = Phprojekt::getInstance()->getDb();
    }

    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/data.xml');
    }

    /**
     * Test addFilter and getWhere
     */
    public function testAddFilterPart1()
    {
        $item   = new Project_Models_Project();
        $filter = new Phprojekt_Filter($item);

        $filter->addFilter('title', 'like', 'root', 'AND');
        $this->assertEquals("( (`project`.`title` LIKE '%root%' )  )", $filter->getWhere());

        $filter->addFilter('title', 'like', 'root1', 'AND');
        $this->assertEquals("( (`project`.`title` LIKE '%root%' ) AND (`project`.`title` LIKE '%root1%' )  )",
            $filter->getWhere());

        $filter->addFilter('title', 'like', 'root2', 'OR');
        $this->assertEquals("( (`project`.`title` LIKE '%root%' ) AND (`project`.`title` LIKE '%root1%' ) OR "
            . "(`project`.`title` LIKE '%root2%' )  )", $filter->getWhere());
    }

    /**
     * Test addFilter and getWhere
     */
    public function testAddFilterPart2()
    {
        $item   = new Project_Models_Project();
        $filter = new Phprojekt_Filter($item, 'projectId = 1');

        $filter->addFilter('title', 'like', 'root', 'AND');
        $this->assertEquals("(projectId = 1) AND ( (`project`.`title` LIKE '%root%' )  )", $filter->getWhere());

        $filter->addFilter('title', 'like', 'root1', 'AND');
        $this->assertEquals("(projectId = 1) AND ( (`project`.`title` LIKE '%root%' ) AND "
            . "(`project`.`title` LIKE '%root1%' )  )", $filter->getWhere());

        $filter->addFilter('title', 'like', 'root2', 'OR');
        $this->assertEquals("(projectId = 1) AND ( (`project`.`title` LIKE '%root%' ) AND "
            . "(`project`.`title` LIKE '%root1%' ) OR (`project`.`title` LIKE '%root2%' )  )", $filter->getWhere());
    }
}
