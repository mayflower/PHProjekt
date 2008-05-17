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
 * Tests for Default Search class
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */
class Phprojekt_Search_DefaultTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test index
     */
    public function testIndex()
    {
        $project = new Project_Models_Project();
        $project->title = 'Hello World Project to delete';
        $project->startDate = '1981-05-12';
        $project->endDate = '1981-05-12';
        $project->priority = 1;
        $project->path = '/';
        $project->save();

        $search = new Phprojekt_Search_Default();
        $search->indexObjectItem($project);

        $result = $search->search('HELLO WORLD PROJECT DELETE', 'AND');
        $this->assertEquals(1, count($result));
    }

    /**
     * Test search
     */
    public function testSearch()
    {
        $search = new Phprojekt_Search_Default();
        $result = (array)$search->search('HELLO WORLD PROJECT DELETE','AND');
        $this->assertEquals(1, count($result));

        $result = (array)$search->search('HELLO WORLD PROJECT DELETE','OR');
        $this->assertEquals(6, count($result));

        $result = (array)$search->search('HEL WOR PRO DEL','OR');
        $this->assertEquals(6, count($result));
    }

    /**
     * Test delete
     */
    public function testDelete()
    {
        $project = new Project_Models_Project();
        $project->find(11);

        $search = new Phprojekt_Search_Default();
        $search->deleteObjectItem($project);

        $result = (array)$search->search('HELLO WORLD PROJECT DELETE','OR');
        $this->assertEquals(5, count($result));
    }
}