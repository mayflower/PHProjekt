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
require_once 'PHPUnit/Extensions/ExceptionTestCase.php';

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
class Phprojekt_DatabaseManagerTest extends PHPUnit_Extensions_ExceptionTestCase
{
    /**
     * setUp method for PHPUnit. We use a shared db connection
     *
     */
    public function setUp()
    {
        $this->_emptyResult = array();

        $this->_formResult = array(
                        'parent','title','notes','startDate','endDate',
                        'priority','currentStatus','completePercent','budget');

        $this->_listResult = array(
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
        $fields = $db->getFieldsForList();

        foreach ($fields as $key => $field) {
            $result[$field->tableField]['tableField'] = $field->tableField;
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
        $fields = $db->getFieldsForForm();

        foreach ($fields as $key => $field) {
            $result[$field->tableField]['tableField'] = $field->tableField;
        }
        $this->assertEquals($this->_formResult, array_keys($result));
    }
}