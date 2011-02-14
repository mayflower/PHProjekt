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
 * Tests for database manager field
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
 * @group      databasemanager
 * @group      field
 * @group      phprojekt-databasemanager
 * @group      phprojekt-databasemanager-field
 */
class Phprojekt_DatabaseManager_FieldTest extends PHPUnit_Framework_TestCase
{
    public function setUp() {
        $this->sharedFixture = Phprojekt::getInstance()->getDb();
    }

    /**
     * Test __get
     */
    public function testGetField()
    {
        $project = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $dbField = new Phprojekt_DatabaseManager_Field($project->getInformation(), 'title');

        $this->assertEquals(1, $dbField->isRequired);
        $this->assertNull($dbField->title);
    }

    /**
     * Test __toString
     */
    public function testToString()
    {
        $project = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $dbField = new Phprojekt_DatabaseManager_Field($project->getInformation(), 'parent', 'testvalue');

        ob_start();
        echo $dbField;
        $stringValue = ob_get_contents();
        ob_end_clean();

        $this->assertEquals('testvalue', $stringValue);
    }
}
