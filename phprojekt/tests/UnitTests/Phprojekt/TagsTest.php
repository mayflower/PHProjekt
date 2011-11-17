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


/**
 * Tests for Tags
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
 * @group      tags
 * @group      phprojekt-tags
 */
class Phprojekt_TagsTest extends DatabaseTest
{

    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/data.xml');
    }

    /**
     * Test save
     *
     * @return void
     */
    public function testSaveTags()
    {
        // Test add
        $tag = Phprojekt_Tags::getInstance();
        $tag->saveTags(1, 1, 'This is a tag test');
        $result = array(array('string' => 'test',
                              'count'  => 1));

        $this->assertContains(array('string' => 'test', 'count' => 1),
            $tag->getTagsByModule(1, 1));
        $this->assertContains(array('string' => 'tag', 'count' => 1),
            $tag->getTagsByModule(1, 1));
        $this->assertContains(array('string' => 'this', 'count' => 1),
            $tag->getTagsByModule(1, 1));

        // Test update
        $tag->saveTags(1, 1, 'This is a tag');
        $result = array(array('string' => 'this',
                              'count'  => 1),
                        array('string' => 'tag',
                              'count'  => 1));
        $this->assertContains(array('string' => 'this', 'count' => 1),
            $tag->getTagsByModule(1, 1));
        $this->assertContains(array('string' => 'tag', 'count' => 1),
            $tag->getTagsByModule(1, 1));
    }

    /**
     * Test get
     *
     * @return void
     */
    public function testGetTags()
    {
        $tag    = Phprojekt_Tags::getInstance();
        $result = array('0' => array('string' => 'this',
                                     'count'  => 2),
                        '1' => array('string' => 'tag',
                                     'count'  => 1)
                        );
        $this->assertEquals($result, $tag->getTags(3));

        $tag->saveTags(1, 2, 'This is other test');
        $tag->saveTags(2, 1, 'This is other test for todo');
        $result = array('0' => array('string' => 'this',
                                     'count'  => 3),
                        '1' => array('string' => 'tag',
                                     'count'  => 1),
                        '2' => array('string' => 'other',
                                     'count'  => 1),
                        '3' => array('string' => 'test',
                                     'count'  => 1));
        $this->assertEquals($result, $tag->getTags(6));
    }

    /**
     * Test get Modules
     *
     * @return void
     */
    public function testGetModulesByTag()
    {
        $tag    = Phprojekt_Tags::getInstance();
        $result = array(
                        '0' => array('id'            => 1,
                                     'moduleId'      => 1,
                                     'moduleName'    => 'Project',
                                     'moduleLabel'   => 'Project',
                                     'firstDisplay'  => 'Hallo Welt',
                                     'secondDisplay' => '',
                                     'projectId'     => 1),
                        '1' => array('id'            => 6,
                                     'moduleId'      => 1,
                                     'moduleName'    => 'Project',
                                     'moduleLabel'   => 'Project',
                                     'firstDisplay'  => 'BWV 810 - II. Allemande',
                                     'secondDisplay' => '',
                                     'projectId'     => 5));
        $this->assertEquals($result, $tag->getModulesByTag('this'));

        // limit
        $result = array(
                        '0' => array('id'            => 1,
                                     'moduleId'      => 1,
                                     'moduleName'    => 'Project',
                                     'moduleLabel'   => 'Project',
                                     'firstDisplay'  => 'Hallo Welt',
                                     'secondDisplay' => '',
                                     'projectId'     => 1));
        $this->assertEquals($result, $tag->getModulesByTag('this', 1));

        // None
        $this->assertEquals(array(), $tag->getModulesByTag('', 2));
        $this->assertEquals(array(), $tag->getModulesByTag('wordthatnotsaved', 2));
    }

    /**
     * Test get tags for a module
     *
     * @return void
     */
    public function testGetTagsByModule()
    {
        $tag    = Phprojekt_Tags::getInstance();
        $result = array('0' => array('string' => 'this',
                                     'count'  => 1),
                        '1' => array('string' => 'tag',
                                     'count'  => 1));
        $this->assertEquals($result, $tag->getTagsByModule(1, 1));

        // No  ID
        $this->assertEquals(array(), $tag->getTagsByModule(1, 4));

        // NO Module
        $this->assertEquals(array(), $tag->getTagsByModule(200, 4));

        // Limit
        $result = array('0' => array('string' => 'this',
                                     'count'  => 1));
        $this->assertEquals($result, $tag->getTagsByModule(1, 1, 1));
    }

    /**
     * Test get relations
     *
     * @return void
     */
    public function testGetRelationIdByModule()
    {
        $tag    = Phprojekt_Tags::getInstance();
        $result = array('1', '3');
        $this->assertEquals($result, $tag->getRelationIdByModule(1, 1));
    }
}
