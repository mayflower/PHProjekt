<?php
/**
 * Unit test
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
*/
require_once 'PHPUnit/Framework.php';

/**
 * Tests for database manager field
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_DatabaseManager_FieldTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test __get
     *
     */
    public function testGetField()
    {
        $project = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $dbField = new Phprojekt_DatabaseManager_Field($project->getInformation(),
                                                   'title',
                                                   '');

        $this->assertEquals(1,$dbField->isRequired);
        $this->assertNull($dbField->title);
    }

    /**
     * Test __toString
     *
     */
    public function testToString()
    {
        $project = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $dbField = new Phprojekt_DatabaseManager_Field($project->getInformation(),
                                                   'parent',
                                                   'testvalue');
        echo $dbField;
    }
}