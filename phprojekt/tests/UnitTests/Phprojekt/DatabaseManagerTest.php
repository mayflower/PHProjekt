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
 * Tests for database manager
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
 * @group      phprojekt-databasemanager
 * @group      activerecord
 */
class Phprojekt_DatabaseManagerTest extends PHPUnit_Framework_TestCase
{
    /**
     * setUp method for PHPUnit. We use a shared db connection
     */
    public function setUp()
    {
        $this->_emptyResult = array();

        $this->_formResult = array(
                        'title','notes','projectId','startDate','endDate',
                        'priority','currentStatus','completePercent','budget',
                        'contactId');

        $this->_formLabelResult = array(
                        'Title','Notes','Parent','Start date','End date',
                        'Priority','Current status','Complete percent','Budget',
                        'Contact');

        $this->_listResult = array(
                        'title','startDate','endDate','priority',
                        'currentStatus','completePercent');

        $this->_listLabelResult = array(
                        'Title','Start date','End date','Priority',
                        'Current status','Complete percent');
    }

    /**
     * Test getFieldsForList
     *
     */
    public function testGetFieldsForList()
    {
        $project = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $db      = new Phprojekt_DatabaseManager($project, array('db' => $this->sharedFixture));
        $fields  = $db->getFieldDefinition(1);
        foreach ($fields as $field) {
            $result[$field['key']] = $field['key'];
        }
        $this->assertEquals($this->_listResult, array_keys($result));
    }

    /**
     * Test getFieldsForForm
     */
    public function testGetFieldsForForm()
    {
        $project = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $db      = new Phprojekt_DatabaseManager($project, array('db' => $this->sharedFixture));
        $fields  = $db->getFieldDefinition(2);
        foreach ($fields as $field) {
            $result[$field['key']] = $field['key'];
        }
        $this->assertEquals($this->_formResult, array_keys($result));
    }

    /**
     * Get info
     */
    public function testGetInfo()
    {
        $project = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $db      = new Phprojekt_DatabaseManager($project, array('db' => $this->sharedFixture));
        $fields  = $db->getInfo(Phprojekt_ModelInformation_Default::ORDERING_LIST,
            Phprojekt_DatabaseManager::COLUMN_TITLE);
        $this->assertEquals($this->_listLabelResult, $fields);

        $project = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $db      = new Phprojekt_DatabaseManager($project, array('db' => $this->sharedFixture));
        $fields  = $db->getInfo(Phprojekt_ModelInformation_Default::ORDERING_FORM,
            Phprojekt_DatabaseManager::COLUMN_TITLE);
        $this->assertEquals($this->_formLabelResult, $fields);
    }

    /**
     * Test validations
     */
    public function testRecordValidate()
    {
        $project = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $db      = new Phprojekt_DatabaseManager($project, array('db' => $this->sharedFixture));

        $message = 'The Module must contain at least one field';
        $this->assertFalse($db->recordValidate(array()));
        $db->recordValidate(array());
        $error = $db->getError();
        $this->assertEquals($message, $error['message']);

        $message = 'Please enter a name for this module';
        $this->assertFalse($db->recordValidate(array(
            array('tableName' => ''))));
        $error = $db->getError();
        $this->assertEquals($message, $error['message']);

        $message = 'The module name must start with a letter';
        $this->assertFalse($db->recordValidate(array(
            array('tableName' => '212'))));
        $error = $db->getError();
        $this->assertEquals($message, $error['message']);

        $message = 'All the fields must have a table name';
        $this->assertFalse($db->recordValidate(array(
            array('tableName'   => 'Project',
                  'tableType'   => '',
                  'tableField'  => '',
                  'tableLength' => 0,
                  'formType'    => ''),
            array('tableName'   => '',
                  'tableType'   => '',
                  'tableField'  => '',
                  'tableLength' => 0,
                  'formType'    => ''))));
        $error = $db->getError();
        $this->assertEquals($message, $error['message']);

        $message = 'There are two fields with the same Field Name';
        $this->assertFalse($db->recordValidate(array(
            array('tableName'   => 'Project',
                  'tableType'   => '',
                  'tableField'  => 'project_id',
                  'tableLength' => 0,
                  'formType'    => ''),
            array('tableName'   => 'Project',
                  'tableType'   => '',
                  'tableField'  => 'project_id',
                  'tableLength' => 0,
                  'formType'    => ''))));
        $error = $db->getError();
        $this->assertEquals($message, $error['message']);

        $message = 'Invalid parameters';
        $this->assertFalse($db->recordValidate(array(
            array('tableName'   => 'Project',
                  'tableType'   => 'varchar',
                  'tableField'  => 'project_id',
                  'tableLength' => 11))));
        $error = $db->getError();
        $this->assertEquals($message, $error['message']);

        $message = 'The length of the varchar fields must be between 1 and 255';
        $this->assertFalse($db->recordValidate(array(
            array('tableName'   => 'Project',
                  'tableType'   => 'varchar',
                  'tableField'  => 'project_id',
                  'tableLength' => 260,
                  'formType'    => ''))));
        $error = $db->getError();
        $this->assertEquals($message, $error['message']);

        $message = 'The length of the int fields must be between 1 and 11';
        $this->assertFalse($db->recordValidate(array(
            array('tableName'   => 'Project',
                  'tableType'   => 'int',
                  'tableField'  => 'project_id',
                  'tableLength' => 12,
                  'formType'    => ''))));
        $error = $db->getError();
        $this->assertEquals($message, $error['message']);

        $message = 'Invalid form Range for the select field';
        $this->assertFalse($db->recordValidate(array(
            array('tableName'   => 'Project',
                  'tableType'   => 'int',
                  'tableField'  => 'project_id',
                  'tableLength' => 11,
                  'formType'    => 'selectValues'))));
        $error = $db->getError();
        $this->assertEquals($message, $error['message']);

        $message = 'Invalid form Range for the select field';
        $this->assertFalse($db->recordValidate(array(
            array('tableName'   => 'Project',
                  'tableType'   => 'int',
                  'tableField'  => 'project_id',
                  'tableLength' => 11,
                  'formType'    => 'selectValues',
                  'formRange'   => ''))));
        $error = $db->getError();
        $this->assertEquals($message, $error['message']);

        $message = 'Invalid form Range for the select field';
        $this->assertFalse($db->recordValidate(array(
            array('tableName'   => 'Project',
                  'tableType'   => 'int',
                  'tableField'  => 'project_id',
                  'tableLength' => 11,
                  'formType'    => 'selectValues',
                  'formRange'   => 'Project#id',
                  'selectType'  => 'project'))));
        $error = $db->getError();
        $this->assertEquals($message, $error['message']);

        $message = 'Invalid form Range for the select field';
        $this->assertFalse($db->recordValidate(array(
            array('tableName'   => 'Project',
                  'tableType'   => 'int',
                  'tableField'  => 'project_id',
                  'tableLength' => 11,
                  'formType'    => 'selectValues',
                  'formRange'   => '1#e',
                  'selectType'  => 'custom'))));
        $error = $db->getError();
        $this->assertEquals($message, $error['message']);

        $message = 'Invalid form Range for the select field';
        $this->assertFalse($db->recordValidate(array(
            array('tableName'   => 'Project',
                  'tableType'   => 'int',
                  'tableField'  => 'project_id',
                  'tableLength' => 11,
                  'formType'    => 'selectValues',
                  'formRange'   => '1#e#2',
                  'selectType'  => 'custom'))));
        $error = $db->getError();
        $this->assertEquals($message, $error['message']);

        $message = 'Invalid form Range for the select field';
        $this->assertFalse($db->recordValidate(array(
            array('tableName'   => 'Project',
                  'tableType'   => 'int',
                  'tableField'  => 'project_id',
                  'tableLength' => 11,
                  'formType'    => 'selectValues',
                  'formRange'   => '1#e | 2',
                  'selectType'  => 'custom'))));
        $error = $db->getError();
        $this->assertEquals($message, $error['message']);

        $message = 'The module must have a project selector called project_id';
        $this->assertFalse($db->recordValidate(array(
            array('tableName'   => 'Project',
                  'tableType'   => 'int',
                  'tableField'  => 'project',
                  'tableLength' => 11,
                  'formType'    => 'selectValues',
                  'formRange'   => '1#Gustavo | 2#Solt',
                  'selectType'  => 'custom'))));
        $error = $db->getError();
        $this->assertEquals($message, $error['message']);

        $message = 'The module must have at least one field with the list position greater than 0';
        $this->assertFalse($db->recordValidate(array(
            array('tableName'    => 'Project',
                  'tableType'    => 'int',
                  'tableField'   => 'project_id',
                  'tableLength'  => 11,
                  'formType'     => 'selectValues',
                  'formRange'    => '1#Gustavo | 2#Solt',
                  'selectType'   => 'custom',
                  'listPosition' => 0))));
        $error = $db->getError();
        $this->assertEquals($message, $error['message']);
    }

    /**
     * Test get model
     */
    public function testGetModel()
    {
        $project = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $db      = new Phprojekt_DatabaseManager($project, array('db' => $this->sharedFixture));
        $this->assertTrue($db->getModel() instanceof Phprojekt_Model_Interface);
    }
}
