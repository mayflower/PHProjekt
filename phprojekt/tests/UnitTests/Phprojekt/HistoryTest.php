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
 * Tests History
 *
 * @group      phprojekt
 * @group      history
 * @group      phprojekt-history
 */
class Phprojekt_HistoryTest extends DatabaseTest
{
    public function setUp()
    {
        parent::setUp();
        $this->sharedFixture = Phprojekt::getInstance()->getDb();
    }

    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/data.xml');
    }

    /**
     * Test add history
     */
    public function testAddCall()
    {
        $project = new Project_Models_Project(array('db' => $this->sharedFixture));

        $project->projectId = 2;
        $project->path = '/1/';
        $project->title = 'TEST';
        $project->startDate = '1981-05-12';
        $project->endDate = '1981-05-12';
        $project->priority = 1;
        $project->currentStatus = 2;
        $project->save();

        $history = new Phprojekt_History(array('db' => $this->sharedFixture));
        $data = $history->getHistoryData($project, $project->id);
        $array = array('userId'   => '1',
                       'moduleId' => '1',
                       'itemId'   => $project->id,
                       'field'    => 'currentStatus',
                       'label'    => 'Current status',
                       'oldValue' => '',
                       'newValue' => 'Ordered',
                       'action'   => 'add',
                       'datetime' => date("Y-m-d"));
        foreach($data as &$e) {
            $e['datetime'] = substr($e['datetime'], 0, 10);
        }

        $this->assertContains($array, $data);
    }

    /**
     * Test edit history
     */
    public function testEditCall()
    {
        $project = new Project_Models_Project(array('db' => $this->sharedFixture));
        $history = new Phprojekt_History(array('db' => $this->sharedFixture));

        $project->find(1);
        $project->title = 'EDITED TEST';
        $project->save();

        $history = new Phprojekt_History(array('db' => $this->sharedFixture));

        $data = $history->getHistoryData($project, $project->id);
        $array = array('userId'   => '1',
                       'moduleId' => '1',
                       'itemId'   => '1',
                       'field'    => 'title',
                       'label'    => 'Title',
                       'oldValue' => 'PHProjekt',
                       'newValue' => 'EDITED TEST',
                       'action'   => 'edit',
                       'datetime' => date("Y-m-d"));
        foreach($data as &$e) {
            $e['datetime'] = substr($e['datetime'], 0, 10);
        }

        $this->assertContains($array, $data);
    }

    /**
     * Test get data
     */
    public function testGetHistoryData()
    {
        $project = new Project_Models_Project(array('db' => $this->sharedFixture));
        $history = new Phprojekt_History(array('db' => $this->sharedFixture));

        $data  = $history->getHistoryData($project, 1);
        $array = array('userId'   => '1',
                       'moduleId' => '1',
                       'itemId'   => '1',
                       'field'    => 'title',
                       'label'    => 'Title',
                       'oldValue' => 'TEST',
                       'newValue' => 'EDITED TEST',
                       'action'   => 'edit',
                       'datetime' => '2001-02-23 23:23:42');
        $this->assertContains($array, $data);
    }

    /**
     * Test get last data
     */
    public function testGetLastHistoryData()
    {
        $project = new Project_Models_Project(array('db' => $this->sharedFixture));
        $project->find(1);
        $history = new Phprojekt_History(array('db' => $this->sharedFixture));

        $data     = $history->getHistoryData($project, 1);
        $lastData = $history->getLastHistoryData($project);

        $this->assertEquals(1, count($data));
        $this->assertEquals($data, $lastData);
        $this->assertequals(1, count($lastData));
    }

    /**
     * Test delete history
     */
    public function testDeleteCall()
    {
        $project = new Project_Models_Project(array('db' => $this->sharedFixture));
        $history = new Phprojekt_History(array('db' => $this->sharedFixture));

        $this->markTestIncomplete("not working");
        $project->delete();

        $data  = $history->getHistoryData($project, Zend_Registry::get('insertedId'));
        $array = array('userId'   => '1',
                       'moduleId' => '1',
                       'itemId'   => Zend_Registry::get('insertedId'),
                       'field'    => 'title',
                       'label'    => 'Title',
                       'oldValue' => 'EDITED TEST',
                       'newValue' => '',
                       'action'   => 'delete',
                       'datetime' => date("Y-m-d"));
        $found = 0;
        foreach ($data as $values) {
            /* Remove the hour */
            $values['datetime'] = substr($values['datetime'], 0, 10);
            $result = array_diff_assoc($values, $array);

            if (empty($result)) {
                $found = 1;
            }
        }
        if (!$found) {
            $this->fail('Save delete history error');
        }
    }
}
