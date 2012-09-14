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
 * @copyright  Copyright (c) 2012 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */


/**
 * Tests for Tags
 *
 * @group      phprojekt
 * @group      tags
 * @group      phprojekt-tags
 */
class Phprojekt_TagsTest extends DatabaseTest
{

    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/data.xml');
    }

    public function testTagsByModule() {
        $tag  = new Phprojekt_Tags();
        $tags = $tag->getTagsByModule(1, 1);
        $this->assertEquals(array("this"), $tags);
    }

    public function testTagsByModuleLimit() {
        $tag  = new Phprojekt_Tags();
        $tags = $tag->getTagsByModule(1, 6);
        $this->assertEquals(count($tags), 2);

        $tags = $tag->getTagsByModule(1, 6, 1);
        $this->assertEquals(count($tags), 1);
    }

    public function testSearch() {
        $tag  = new Phprojekt_Tags();
        $tags = $tag->search("this");
        $this->assertEquals(array(
            array(
                'id'            => 1,
                'moduleId'      => 1,
                'moduleName'    => 'Project',
                'moduleLabel'   => 'Project',
                'firstDisplay'  => 'Hallo Welt',
                'secondDisplay' => null,
                'projectId'     => 1
            )
        ), $tags);
    }

    public function testEmptySearch() {
        $tag  = new Phprojekt_Tags();
        $tags = $tag->search("");
        $this->assertTrue(empty($tags));
    }

    public function testInvalidSearch() {
        $tag  = new Phprojekt_Tags();
        $tags = $tag->search("tag");
        $this->assertTrue(empty($tags));
    }

    public function testLimitSearch() {
        $tag  = new Phprojekt_Tags();

        $tags = $tag->search("blafoo");
        $this->assertEquals(count($tags), 2);

        $tags = $tag->search("blafoo", 1);
        $this->assertEquals(count($tags), 1);
    }

    public function testDeleteTagsByItem() {
        $tag  = new Phprojekt_Tags();
        $tag->deleteTagsByItem(1, 1);
        $tags = $tag->getTagsByModule(1, 1);
        $this->assertTrue(empty($tags));
    }

    public function testSaveTags() {
        $tag  = new Phprojekt_Tags();
        $tag->saveTags(1, 1, "love");

        $tags = $tag->search("love");
        $this->assertEquals(array(
            array(
                'id'            => 1,
                'moduleId'      => 1,
                'moduleName'    => 'Project',
                'moduleLabel'   => 'Project',
                'firstDisplay'  => 'Hallo Welt',
                'secondDisplay' => null,
                'projectId'     => 1
            )
        ), $tags);
    }

    public function testSaveMultipleTags() {
        $tag  = new Phprojekt_Tags();
        $tag->saveTags(1, 1, "love phprojekt");
        $tag->saveTags(1, 2, "admire phprojekt");

        $tags = $tag->search("love");
        $this->assertEquals(array(
            array(
                'id'            => 1,
                'moduleId'      => 1,
                'moduleName'    => 'Project',
                'moduleLabel'   => 'Project',
                'firstDisplay'  => 'Hallo Welt',
                'secondDisplay' => null,
                'projectId'     => 1
            )
        ), $tags);

        $tags = $tag->search("phprojekt");
        $this->assertEquals(array(
            array(
                'id'            => 1,
                'moduleId'      => 1,
                'moduleName'    => 'Project',
                'moduleLabel'   => 'Project',
                'firstDisplay'  => 'Hallo Welt',
                'secondDisplay' => null,
                'projectId'     => 1
            ),
            array(
                'id'            => 2,
                'moduleId'      => 1,
                'moduleName'    => 'Project',
                'moduleLabel'   => 'Project',
                'firstDisplay'  => '',
                'secondDisplay' => '',
                'projectId'     => 1
            )
        ), $tags);

        $tags = $tag->search("admire phprojekt");
        $this->assertEquals(array(
            array(
                'id'            => 2,
                'moduleId'      => 1,
                'moduleName'    => 'Project',
                'moduleLabel'   => 'Project',
                'firstDisplay'  => '',
                'secondDisplay' => '',
                'projectId'     => 1
            )
        ), $tags);
    }

    public function testGetFieldDefinition() {
        $tag  = new Phprojekt_Tags();
        $fdef = $tag->getFieldDefinition();
        $this->assertEquals(array(
            array(
                'key'   => 'string',
                'label' => 'Tags'
            ),
            array (
                'key'   => 'count',
                'label' => 'Count'
            )
        ), $fdef);
    }
}
