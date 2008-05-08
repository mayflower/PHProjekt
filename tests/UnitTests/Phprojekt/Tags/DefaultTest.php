<?php
/**
 * Unit test
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
*/
require_once 'PHPUnit/Framework.php';

/**
 * Tests for Tags
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_Tags_DefaultTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test save
     *
     * @return void
     */
    public function testSaveTags()
    {
        // Test add
        $tag = Phprojekt_Tags_Default::getInstance();
        $tag->saveTags(1, 1, 'This is a tag test');
        $result = array('0' => array('string' => 'this',
                                     'count'  => 1),
                        '1' => array('string' => 'test',
                                     'count'  => 1),
                        '2' => array('string' => 'tag',
                                     'count'  => 1));

        $this->assertEquals($tag->getTagsByModule(1, 1), $result);

        // Test update
        $tag->saveTags(1, 1, 'This is a tag');
        $result = array('0' => array('string' => 'this',
                                     'count'  => 1),
                        '1' => array('string' => 'tag',
                                     'count'  => 1));
        $this->assertEquals($tag->getTagsByModule(1, 1), $result);
        $tag->saveTags(1, 1, 'This is a tag test');
    }

    /**
     * Test get
     *
     * @return void
     */
    public function testGetTags()
    {
        $tag = Phprojekt_Tags_Default::getInstance();
        $result = array('0' => array('string' => 'tag',
                                     'count'  => 1),
                        '1' => array('string' => 'test',
                                     'count'  => 1),
                        '2' => array('string' => 'this',
                                     'count'  => 1));
        $this->assertEquals($tag->getTags(), $result);

        $tag->saveTags(1, 2, 'This is other test');
        $tag->saveTags(2, 1, 'This is other test for todo');
        $result = array('0' => array('string' => 'this',
                                     'count'  => 3),
                        '1' => array('string' => 'test',
                                     'count'  => 3),
                        '2' => array('string' => 'other',
                                     'count'  => 2),
                        '3' => array('string' => 'for',
                                     'count'  => 1),
                        '4' => array('string' => 'tag',
                                     'count'  => 1),
                        '5' => array('string' => 'todo',
                                     'count'  => 1));
        $this->assertEquals($tag->getTags(), $result);

        // Limit
        $result = array('0' => array('string' => 'this',
                                     'count'  => 3),
                        '1' => array('string' => 'test',
                                     'count'  => 3),
                        '2' => array('string' => 'other',
                                     'count'  => 2));
        $this->assertEquals($tag->getTags(3), $result);
    }

    /**
     * Test get Modules
     *
     * @return void
     */
    public function testGetModulesByTag()
    {
        $tag = Phprojekt_Tags_Default::getInstance();
        $result = array('0' => array('id' => 1,
                                     'module' => 'Project'),
                        '1' => array('id' => 2,
                                     'module' => 'Project'),
                        '2' => array('id' => 1,
                                     'module' => 'Todo'));

        $this->assertEquals($tag->getModulesByTag('this'), $result);

        // limit
        $result = array('0' => array('id' => 1,
                                     'module' => 'Project'),
                        '1' => array('id' => 2,
                                     'module' => 'Project'));
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
        $tag = Phprojekt_Tags_Default::getInstance();
        $result = array('0' => array('string' => 'this',
                                     'count'  => 1),
                        '1' => array('string' => 'test',
                                     'count'  => 1),
                        '2' => array('string' => 'tag',
                                     'count'  => 1));
        $this->assertEquals($tag->getTagsByModule(1, 1), $result);

        // No  ID
        $this->assertEquals($tag->getTagsByModule(1, 4), array());

        // NO Module
        $this->assertEquals($tag->getTagsByModule(200, 4), array());

        // Limit
        $result = array('0' => array('string' => 'this',
                                     'count'  => 1),
                        '1' => array('string' => 'test',
                                     'count'  => 1));
        $this->assertEquals($tag->getTagsByModule(1, 1, 2), $result);
    }

    /**
     * Test get relations
     *
     * @return void
     */
    public function testGetRelationIdByModule()
    {
        $tag = Phprojekt_Tags_Default::getInstance();
        $result = array('1', '3', '4');
        $this->assertEquals($tag->getRelationIdByModule(1, 1), $result);
    }
}