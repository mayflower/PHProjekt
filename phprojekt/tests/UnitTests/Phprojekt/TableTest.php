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
 * @copyright  Copyright (c) 2013 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */


/**
 * Tests for phprojekt table
 *
 * @group      phprojekt
 * @group      table
 */
class Phprojekt_TableTest extends DatabaseTest
{
    protected function getDataSet() {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/data.xml');
    }

    /**
     * Test createTable
     */
    public function testCreateTable()
    {
        $table = new Phprojekt_Table();
        $table->createTable('test_table', array(
            'id' => array('type' => 'int',
                          'null' => false),
            'name' => array('type' => 'varchar',
                            'length' => 255)));

        $this->assertEquals(0,
            $this->getConnection()->getRowCount('test_table'));

        $stm = PHProjekt::getInstance()->getDb()->prepare(
            'INSERT INTO test_table(id, name) VALUES (:id, :name)');
        $stm->execute(array(':id' => 1, ':name' => null));
        $this->assertEquals(1,
            $this->getConnection()->getRowCount('test_table'));
        $table->dropTable('test_table');
    }
}
