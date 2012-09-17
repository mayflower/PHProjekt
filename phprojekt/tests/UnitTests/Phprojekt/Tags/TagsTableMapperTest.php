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
 * @copyright  Copyright (c) 2012 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1.4
 * @author     Reno Reckling <reno.reckling@mayflower.de>
 */


/**
 * Tests for Tags
 *
 * @category   PHProjekt
 * @package    UnitTests
 * @subpackage Phprojekt
 * @copyright  Copyright (c) 2012 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1.4
 * @author     Reno Reckling <reno.reckling@mayflower.de>
 * @group      phprojekt
 * @group      tags
 * @group      phprojekt-tags
 */
class Phprojekt_Tags_TagsTableMapperTest extends PHPUnit_Framework_TestCase
{

    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/data.xml');
    }

    public function testSearchForProjectsWithTags() {
        $tablemapper = new Phprojekt_Tags_TagsTableMapper();
        $tags        = $tablemapper->searchForProjectsWithTags(array("bla"));

        $this->assertEquals(array(
            1 => array(2, 5, 6)
        ), $tags);
    }

    public function testSearchForProjectsWithTagsNoSearch() {
        $tablemapper = new Phprojekt_Tags_TagsTableMapper();
        $tags        = $tablemapper->searchForProjectsWithTags(array());

        $this->assertTrue(empty($tags));
    }

    public function testSearchForProjectsWithTagsNoResults() {
        $tablemapper = new Phprojekt_Tags_TagsTableMapper();
        $tags        = $tablemapper->searchForProjectsWithTags(array("wizard"));

        $this->assertTrue(empty($tags));
    }
}
