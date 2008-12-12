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
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    CVS: $Id$
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */
require_once 'PHPUnit/Framework.php';

/**
 * Tests for database manager field
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_DatabaseManager_FieldTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test __get
     */
    public function testGetField()
    {
        $project = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $dbField = new Phprojekt_DatabaseManager_Field($project->getInformation(), 'title', '');

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
