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
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */


/**
 * Tests for database manager field
 *
 * @group      phprojekt
 * @group      databasemanager
 * @group      field
 * @group      phprojekt-databasemanager
 * @group      phprojekt-databasemanager-field
 */
class Phprojekt_DatabaseManager_FieldTest extends DatabaseTest
{
    public function setUp() {
        parent::setUp();
        $this->sharedFixture = Phprojekt::getInstance()->getDb();
    }

    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/../data.xml');
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

        $this->assertEquals('testvalue', $dbField->value);
    }
}
