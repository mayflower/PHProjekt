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
 * Tests for Word search class
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */
class Phprojekt_Search_WordsTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test index
     */
    public function testIndex()
    {
        $search = new Phprojekt_Search_Words();
        $search->indexWords(1,1,'THIS IS A NICE TEST');

        $result = (array)$search->searchWords('THIS IS A NICE TEST','AND');
        $this->assertEquals(1, count($result));
    }

    /**
     * Test search
     */
    public function testSearch()
    {
        $search = new Phprojekt_Search_Words();
        $result = (array)$search->searchWords('THIS IS A NICE TEST','AND');
        $this->assertEquals(1, count($result));

        $result = (array)$search->searchWords('THIS IS A NICE TEST','OR');
        $this->assertEquals(2, count($result));
    }

    /**
     * Test delete
     */
    public function testDelete()
    {
        $search = new Phprojekt_Search_Words();
        $search->deleteWords(1,1);

        $result = (array)$search->searchWords('THIS IS A NICE TEST','OR');
        $this->assertEquals(1, count($result));
    }
}