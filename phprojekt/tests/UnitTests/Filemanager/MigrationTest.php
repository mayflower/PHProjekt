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
 * @group      filemanager
 * @group      migration
 * @group      filemanager-migration
 */
class Filemanager_MigrationTest extends FrontInit
{
    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/migrationData.xml');
    }

    public function testMigrationMakesFilenamesUnique()
    {
        $migration = new Filemanager_Migration();
        $migration->upgrade('6.1.0', Phprojekt::getInstance()->getDb());

        $rows = Phprojekt::getInstance()->getDb()->select()
            ->from('filemanager', array('id', 'files'))
            ->order('id')
            ->query()->fetchAll();

        $expected = array(
            array('id' => '1', 'files' => '966f9bfa01ec4a2a3fa6282bb8fa8d56|articles.txt (1)||aaaaaa|articles.txt (2)'),
            array('id' => '2', 'files' => 'deadbeef01ec4a2a3fa6282bb8fa8d56|articles.txt|articles.txt2'),
            array(
                'id' => '3',
                'files' => '2b00042f7481c7b056c4b410d28f33cf|foo (1)||'
                         . '2219d815ba0757484194cac396115c6a|foo (2)||'
                         . 'd8016131a2724252b2419bf645aab221|bar'
            )
        );

        $this->assertEquals($expected, $rows);
    }
}
