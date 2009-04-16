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
 * @version    $Id$
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */
require_once 'PHPUnit/Framework.php';

/**
 * Tests for database manager
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
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
        $db     = new Phprojekt_DatabaseManager($project, array('db' => $this->sharedFixture));
        $fields = $db->getFieldDefinition(1);

        foreach ($fields as $field) {
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

        foreach ($fields as $field) {
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
        $fields = $db->getInfo(Phprojekt_ModelInformation_Default::ORDERING_LIST,
            Phprojekt_DatabaseManager::COLUMN_TITLE);
        $this->assertEquals($this->_listLabelResult, $fields);

        $project = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $db     = new Phprojekt_DatabaseManager($project, array('db' => $this->sharedFixture));
        $fields = $db->getInfo(Phprojekt_ModelInformation_Default::ORDERING_FORM,
            Phprojekt_DatabaseManager::COLUMN_TITLE);
        $this->assertEquals($this->_formLabelResult, $fields);
    }

    /**
     * get titles
     *
     */
    public function testGetTitles()
    {
        $project = new Phprojekt_Project(array('db' => $this->sharedFixture));
        $db      = new Phprojekt_DatabaseManager($project, array('db' => $this->sharedFixture));
        $fields  = $db->getTitles();
        $this->assertEquals($this->_listLabelResult, $fields);
    }
}
