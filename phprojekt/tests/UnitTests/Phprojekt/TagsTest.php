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
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */
require_once 'PHPUnit/Framework.php';

/**
 * Tests for Tags
 *
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 * @group      phprojekt
 * @group      tags
 * @group      phprojekt-tags
 */
class Phprojekt_TagsTest extends PHPUnit_Framework_TestCase
{
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
        $result = array('0' => array('string' => 'this',
                                     'count'  => 1),
                        '1' => array('string' => 'tag',
                                     'count'  => 1),
                        '2' => array('string' => 'test',
                                     'count'  => 1));

        $this->assertEquals($tag->getTagsByModule(1, 1), $result);

        // Test update
        $tag->saveTags(1, 1, 'This is a tag');
        $result = array('0' => array('string' => 'this',
                                     'count'  => 1),
                        '1' => array('string' => 'tag',
                                     'count'  => 1));
        $this->assertEquals($tag->getTagsByModule(1, 1), $result);
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
                                     'count'  => 1),
                        '1' => array('string' => 'tag',
                                     'count'  => 1)
                        );
        $this->assertEquals($tag->getTags(3), $result);

        $tag->saveTags(1, 2, 'This is other test');
        $tag->saveTags(2, 1, 'This is other test for todo');
        $result = array('0' => array('string' => 'this',
                                     'count'  => 3),
                        '1' => array('string' => 'todo',
                                     'count'  => 1),
                        '2' => array('string' => 'tag',
                                     'count'  => 1),
                        '3' => array('string' => 'test',
                                     'count'  => 2),
                        '4' => array('string' => 'other',
                                     'count'  => 2),
                        '5' => array('string' => 'for',
                                     'count'  => 1));
        $this->assertEquals($tag->getTags(6), $result);
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
                                     'firstDisplay'  => 'test',
                                     'secondDisplay' => '',
                                     'projectId'     => 1),
                        '1' => array('id'            => 2,
                                     'moduleId'      => 1,
                                     'moduleName'    => 'Project',
                                     'moduleLabel'   => 'Project',
                                     'firstDisplay'  => '',
                                     'secondDisplay' => '',
                                     'projectId'     => 1),
                        '2' => array('id'            => 1,
                                     'moduleId'      => 2,
                                     'moduleName'    => 'Todo',
                                     'moduleLabel'   => 'Todo',
                                     'firstDisplay'  => '',
                                     'secondDisplay' => '',
                                     'projectId'     => 1));
        $this->assertEquals($tag->getModulesByTag('this'), $result);

        // limit
        $result = array(
                        '0' => array('id'            => 1,
                                     'moduleId'      => 1,
                                     'moduleName'    => 'Project',
                                     'moduleLabel'   => 'Project',
                                     'firstDisplay'  => 'test',
                                     'secondDisplay' => '',
                                     'projectId'     => 1),
                        '1' => array('id'            => 2,
                                     'moduleId'      => 1,
                                     'moduleName'    => 'Project',
                                     'moduleLabel'   => 'Project',
                                     'firstDisplay'  => '',
                                     'secondDisplay' => '',
                                     'projectId'     => 1));
        $this->assertEquals($tag->getModulesByTag('this', 2), $result);

        // None
        $this->assertEquals($tag->getModulesByTag('', 2), array());
        $this->assertEquals($tag->getModulesByTag('wordthatnotsaved', 2), array());
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
        $this->assertEquals($tag->getTagsByModule(1, 1), $result);

        // No  ID
        $this->assertEquals($tag->getTagsByModule(1, 4), array());

        // NO Module
        $this->assertEquals($tag->getTagsByModule(200, 4), array());

        // Limit
        $result = array('0' => array('string' => 'this',
                                     'count'  => 1));
        $this->assertEquals($tag->getTagsByModule(1, 1, 1), $result);
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
        $this->assertEquals($tag->getRelationIdByModule(1, 1), $result);
    }
}
