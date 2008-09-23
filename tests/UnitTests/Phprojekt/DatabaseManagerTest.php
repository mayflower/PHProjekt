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
 * Tests for database manager
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_DatabaseManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * setUp method for PHPUnit. We use a shared db connection
     *
     */
    public function setUp()
    {
        $this->_emptyResult = array();

        $this->_formResult = array(
                        'title','notes','projectId','startDate','endDate',
                        'priority','currentStatus','completePercent','budget');

        $this->_formLabelResult = array(
                        'title','notes','parent','startDate','endDate',
                        'priority','currentStatus','completePercent','budget');

        $this->_listResult = array(
                        'title','projectId','startDate','endDate','priority',
                        'currentStatus','completePercent');

        $this->_listLabelResult = array(
                        'title','parent','startDate','endDate','priority',
                        'currentStatus','completePercent');
    }

    /**
     * Test getFieldsForList
     *
     */
    public function testGetFieldsForList()
    {
        $project = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $db     = new Phprojekt_DatabaseManager($project, array('db' => $this->sharedFixture));
        $fields = $db->getFieldDefinition(1);

        foreach ($fields as $key => $field) {
            $result[$field['key']] = $field['key'];
        }
        $this->assertEquals($this->_listResult, array_keys($result));
    }

    /**
     * Test getFieldsForForm
     *
     */
    public function testGetFieldsForForm()
    {
        $project = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $db     = new Phprojekt_DatabaseManager($project, array('db' => $this->sharedFixture));
        $fields = $db->getFieldDefinition(2);

        foreach ($fields as $key => $field) {
            $result[$field['key']] = $field['key'];
        }
        $this->assertEquals($this->_formResult, array_keys($result));
    }

    /**
     * get info
     *
     */
    public function testGetInfo()
    {
        $project = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $db     = new Phprojekt_DatabaseManager($project, array('db' => $this->sharedFixture));
        $fields = $db->getInfo(Phprojekt_ModelInformation_Default::ORDERING_LIST, Phprojekt_DatabaseManager::COLUMN_TITLE);
        $this->assertEquals($this->_listLabelResult, $fields);

        $project = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $db     = new Phprojekt_DatabaseManager($project, array('db' => $this->sharedFixture));
        $fields = $db->getInfo(Phprojekt_ModelInformation_Default::ORDERING_FORM, Phprojekt_DatabaseManager::COLUMN_TITLE);
        $this->assertEquals($this->_formLabelResult, $fields);
    }

    /**
     * get titles
     *
     */
    public function testGetTitles()
    {
        $project = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $db     = new Phprojekt_DatabaseManager($project, array('db' => $this->sharedFixture));
        $fields = $db->getTitles();
        $this->assertEquals($this->_listLabelResult, $fields);
    }
}