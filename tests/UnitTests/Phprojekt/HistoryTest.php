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
require_once 'PHPUnit/Extensions/ExceptionTestCase.php';

/**
 * Tests History
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_HistoryTest extends PHPUnit_Extensions_ExceptionTestCase
{
    /**
     * Test empty call
     *
     */
    public function testEmptyObject()
    {
        $history = new Phprojekt_History(array('db' => $this->sharedFixture));

        $this->setExpectedException('Exception');
        $history->saveFields('','add');
    }

    /**
     * Test add history
     *
     */
    public function testAddCall()
    {
        $project = new Project_Models_Project(array('db' => $this->sharedFixture));

        $project->parent = 2;
        $project->title = 'TEST';
        $project->startDate = '1981-05-12';
        $project->endDate = '1981-05-12';
        $project->priority = 1;
        $project->save();
        Zend_Registry::set('insertedId', $project->id);

        /* Wait for the save */
        sleep(2);
        $history = new Phprojekt_History(array('db' => $this->sharedFixture));

        $data = $history->getHistoryData($project, $project->id);
        $array = array('userId' => '1',
                       'module' => 'Project',
                       'dataobjectId' => $project->id,
                       'field' => 'parent',
                       'oldValue' => '',
                       'newValue' => '2',
                       'action' => 'add',
                       'datetime' => date("Y-m-d"));
        /* Remove the hour */
        $data[0]['datetime'] = substr($data[0]['datetime'],0,10);
        $this->assertEquals($array, $data[0]);
    }

    /**
     * Test edit history
     *
     */
    public function testEditCall()
    {
        $project = new Project_Models_Project(array('db' => $this->sharedFixture));
        $history = new Phprojekt_History(array('db' => $this->sharedFixture));

        $project->find(5);
        $project->title = 'TEST';
        $project->save();
        /* Wait for the save */
        sleep(2);
        $history = new Phprojekt_History(array('db' => $this->sharedFixture));

        $data = $history->getHistoryData($project,$project->id);
        $array = array('userId' => '1',
                       'module' => 'Project',
                       'dataobjectId' => 5,
                       'field' => 'title',
                       'oldValue' => 'Test Project',
                       'newValue' => 'TEST',
                       'action' => 'edit',
                       'datetime' => date("Y-m-d"));
        /* Remove the hour */
        $data[0]['datetime'] = substr($data[0]['datetime'],0,10);
        $this->assertEquals($array, $data[0]);
    }

    public function testGetHistoryData()
    {
        $project = new Project_Models_Project(array('db' => $this->sharedFixture));
        $history = new Phprojekt_History(array('db' => $this->sharedFixture));

        $data = $history->getHistoryData($project, 5);
        $array = array('userId' => '1',
                       'module' => 'Project',
                       'dataobjectId' => 5,
                       'field' => 'title',
                       'oldValue' => 'Test Project',
                       'newValue' => 'TEST',
                       'action' => 'edit',
                       'datetime' => date("Y-m-d"));
        /* Remove the hour */
        $data[0]['datetime'] = substr($data[0]['datetime'],0,10);
        $this->assertEquals($array, $data[0]);
    }

    /**
     * Test delete history
     *
     */
    public function testDeleteCall()
    {
        $project = new Project_Models_Project(array('db' => $this->sharedFixture));
        $history = new Phprojekt_History(array('db' => $this->sharedFixture));

        $project->find(Zend_Registry::get('insertedId'));
        $project->delete();
    }
}