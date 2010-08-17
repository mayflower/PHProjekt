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
 * @author     Gustavo Solt <solt@mayflower.de>
 */

require_once 'PHPUnit/Framework.php';

/**
 * Tests for Filters
 *
 * @category   PHProjekt
 * @package    UnitTests
 * @subpackage Phprojekt
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 * @group      phprojekt
 * @group      main
 * @group      phprojekt-main
 */
class Phprojekt_FilterTest extends PHPUnit_Framework_TestCase
{
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

    /**
     * Test _convertRule
     */
    public function testConvertRulePart1()
    {
        $item = new Minutes_Models_Minutes();

        $filter = new Phprojekt_Filter($item);
        $filter->addFilter('meetingDatetime', 'equal', '2010-05-12', 'AND');
        $this->assertEquals("( (DATE(`minutes`.`meeting_datetime`) = '2010-05-12' )  )", $filter->getWhere());

        $filter = new Phprojekt_Filter($item);
        $filter->addFilter('meetingDatetime', 'equal', '12:00', 'AND');
        $this->assertEquals("( (TIME(`minutes`.`meeting_datetime`) = '10:00:00' )  )", $filter->getWhere());

        $filter = new Phprojekt_Filter($item);
        $filter->addFilter('meetingDatetime', 'equal', '1273665600', 'AND');
        $this->assertEquals("( (`minutes`.`meeting_datetime` = '2010-05-12 10:00:00' )  )", $filter->getWhere());

        $filter = new Phprojekt_Filter($item);
        $filter->addFilter('endTime', 'equal', '12:00', 'AND');
        $this->assertEquals("( (`minutes`.`end_time` = '10:00:00' )  )", $filter->getWhere());
    }

    /**
     * Test _convertRule
     */
    public function testConvertRulePart2()
    {
        $item   = new Project_Models_Project();

        $filter = new Phprojekt_Filter($item);
        $filter->addFilter('title', 'equal', 'root', 'AND');
        $this->assertEquals("( (`project`.`title` = 'root' )  )", $filter->getWhere());

        $filter = new Phprojekt_Filter($item);
        $filter->addFilter('title', 'notEqual', 'root', 'OR');
        $this->assertEquals("( (`project`.`title` != 'root' )  )", $filter->getWhere());

        $filter = new Phprojekt_Filter($item);
        $filter->addFilter('id', 'major', '2', 'OR');
        $this->assertEquals("( (`project`.`id` > 2 )  )", $filter->getWhere());

        $filter = new Phprojekt_Filter($item);
        $filter->addFilter('id', 'majorEqual', '2', 'OR');
        $this->assertEquals("( (`project`.`id` >= 2 )  )", $filter->getWhere());

        $filter = new Phprojekt_Filter($item);
        $filter->addFilter('id', 'minor', '2', 'OR');
        $this->assertEquals("( (`project`.`id` < 2 )  )", $filter->getWhere());

        $filter = new Phprojekt_Filter($item);
        $filter->addFilter('id', 'minorEqual', '2', 'OR');
        $this->assertEquals("( (`project`.`id` <= 2 )  )", $filter->getWhere());

        $filter = new Phprojekt_Filter($item);
        $filter->addFilter('title', 'begins', 'root', 'OR');
        $this->assertEquals("( (`project`.`title` LIKE 'root%' )  )", $filter->getWhere());

        $filter = new Phprojekt_Filter($item);
        $filter->addFilter('title', 'ends', 'root', 'OR');
        $this->assertEquals("( (`project`.`title` LIKE '%root' )  )", $filter->getWhere());

        $filter = new Phprojekt_Filter($item);
        $filter->addFilter('title', 'notLike', 'root', 'OR');
        $this->assertEquals("( (`project`.`title` NOT LIKE '%root%' )  )", $filter->getWhere());

        $filter = new Phprojekt_Filter($item);
        $filter->addFilter('title', 'like', 'root', 'OR');
        $this->assertEquals("( (`project`.`title` LIKE '%root%' )  )", $filter->getWhere());
    }
}
