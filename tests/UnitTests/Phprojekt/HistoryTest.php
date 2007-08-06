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

        $project->title = 'TEST';
        $project->save();
    }

    /**
     * Test edit history
     *
     */
    public function testEditCall()
    {
        $project = new Project_Models_Project(array('db' => $this->sharedFixture));

        $project->find(1);
        $project->title = 'TEST';
        $project->save();
    }

    /**
     * Test delete history
     *
     */
    public function testDeleteCall()
    {
        $project = new Project_Models_Project(array('db' => $this->sharedFixture));

        $project->find(1);
        $project->delete();
    }
}