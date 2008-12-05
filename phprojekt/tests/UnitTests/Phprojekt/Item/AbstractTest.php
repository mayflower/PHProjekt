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
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    CVS: $Id:
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */
require_once 'PHPUnit/Framework.php';

class Customized_Project extends Project_Models_Project
{
    public function validatePriority($value)
    {
        if ($value > 0) {
            return null;
        } else {
            return 'Bad priority';
        }
    }
}

/**
 * Tests for items
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_Item_AbstractTest extends PHPUnit_Framework_TestCase
{
    /**
     * setUp method for PHPUnit. We use a shared db connection
     *
     */
    public function setUp()
    {
        $this->_emptyResult = array();

        $this->_formResult = array(
        'projectId' => array(
        'id'                => '',
        'tableName'         => '',
        'tablefield'        => '',
        'formTab'           => '',
        'formLabel'         => '',
        'formTooltip'       => '',
        'formType'          => '',
        'formPosition'      => '',
        'formColumns'       => '',
        'formRegexp'        => '',
        'formRange'         => '',
        'defaultValue'      => '',
        'listPosition'      => '',
        'listAlign'         => '',
        'listUseFilter'     => '',
        'altPosition'       => '',
        'status'            => '',
        'isInteger'         => '',
        'isRequired'        => '',
        'isUnique'          => ''),
        'title' => array(
        'id'                => '',
        'tableName'         => '',
        'tablefield'        => '',
        'formTab'           => '',
        'formLabel'         => '',
        'formTooltip'       => '',
        'formType'          => '',
        'formPosition'      => '',
        'formColumns'       => '',
        'formRegexp'        => '',
        'formRange'         => '',
        'defaultValue'      => '',
        'listPosition'      => '',
        'listAlign'         => '',
        'listUseFilter'     => '',
        'altPosition'       => '',
        'status'            => '',
        'isInteger'         => '',
        'isRequired'        => '',
        'isUnique'          => ''),
        'notes'     => array(
        'id'                => '',
        'tableName'         => '',
        'tablefield'        => '',
        'formTab'           => '',
        'formLabel'         => '',
        'formTooltip'       => '',
        'formType'          => '',
        'formPosition'      => '',
        'formColumns'       => '',
        'formRegexp'        => '',
        'formRange'         => '',
        'defaultValue'      => '',
        'listPosition'      => '',
        'listAlign'         => '',
        'listUseFilter'     => '',
        'altPosition'       => '',
        'status'            => '',
        'isInteger'         => '',
        'isRequired'        => '',
        'isUnique'          => ''),
        'startDate' => array(
        'id'                => '',
        'tableName'         => '',
        'tablefield'        => '',
        'formTab'           => '',
        'formLabel'         => '',
        'formTooltip'       => '',
        'formType'          => '',
        'formPosition'      => '',
        'formColumns'       => '',
        'formRegexp'        => '',
        'formRange'         => '',
        'defaultValue'      => '',
        'listPosition'      => '',
        'listAlign'         => '',
        'listUseFilter'     => '',
        'altPosition'       => '',
        'status'            => '',
        'isInteger'         => '',
        'isRequired'        => '',
        'isUnique'          => ''),
        'endDate' => array(
        'id'                => '',
        'tableName'         => '',
        'tablefield'        => '',
        'formTab'           => '',
        'formLabel'         => '',
        'formTooltip'       => '',
        'formType'          => '',
        'formPosition'      => '',
        'formColumns'       => '',
        'formRegexp'        => '',
        'formRange'         => '',
        'defaultValue'      => '',
        'listPosition'      => '',
        'listAlign'         => '',
        'listUseFilter'     => '',
        'altPosition'       => '',
        'status'            => '',
        'isInteger'         => '',
        'isRequired'        => '',
        'isUnique'          => ''),
        'priority'  => array(
        'id'                => '',
        'tableName'         => '',
        'tablefield'        => '',
        'formTab'           => '',
        'formLabel'         => '',
        'formTooltip'       => '',
        'formType'          => '',
        'formPosition'      => '',
        'formColumns'       => '',
        'formRegexp'        => '',
        'formRange'         => '',
        'defaultValue'      => '',
        'listPosition'      => '',
        'listAlign'         => '',
        'listUseFilter'     => '',
        'altPosition'       => '',
        'status'            => '',
        'isInteger'         => '',
        'isRequired'        => '',
        'isUnique'          => ''),
        'currentStatus' => array(
        'id'                => '',
        'tableName'         => '',
        'tablefield'        => '',
        'formTab'           => '',
        'formLabel'         => '',
        'formTooltip'       => '',
        'formType'          => '',
        'formPosition'      => '',
        'formColumns'       => '',
        'formRegexp'        => '',
        'formRange'         => '',
        'defaultValue'      => '',
        'listPosition'      => '',
        'listAlign'         => '',
        'listUseFilter'     => '',
        'altPosition'       => '',
        'status'            => '',
        'isInteger'         => '',
        'isRequired'        => '',
        'isUnique'          => ''),
        'completePercent' => array(
        'id'                => '',
        'tableName'         => '',
        'tablefield'        => '',
        'formTab'           => '',
        'formLabel'         => '',
        'formTooltip'       => '',
        'formType'          => '',
        'formPosition'      => '',
        'formColumns'       => '',
        'formRegexp'        => '',
        'formRange'         => '',
        'defaultValue'      => '',
        'listPosition'      => '',
        'listAlign'         => '',
        'listUseFilter'     => '',
        'altPosition'       => '',
        'status'            => '',
        'isInteger'         => '',
        'isRequired'        => '',
        'isUnique'          => ''),
        'budget' => array(
        'id'                => '',
        'tableName'         => '',
        'tablefield'        => '',
        'formTab'           => '',
        'formLabel'         => '',
        'formTooltip'       => '',
        'formType'          => '',
        'formPosition'      => '',
        'formColumns'       => '',
        'formRegexp'        => '',
        'formRange'         => '',
        'defaultValue'      => '',
        'listPosition'      => '',
        'listAlign'         => '',
        'listUseFilter'     => '',
        'altPosition'       => '',
        'status'            => '',
        'isInteger'         => '',
        'isRequired'        => '',
        'isUnique'          => '')
        );

        $this->_listResult = array(
        'title' => array(
        'id'                => '',
        'tableName'         => '',
        'tablefield'        => '',
        'formTab'           => '',
        'formLabel'         => '',
        'formTooltip'       => '',
        'formType'          => '',
        'formPosition'      => '',
        'formColumns'       => '',
        'formRegexp'        => '',
        'formRange'         => '',
        'defaultValue'      => '',
        'listPosition'      => '',
        'listAlign'         => '',
        'listUseFilter'     => '',
        'altPosition'       => '',
        'status'            => '',
        'isInteger'         => '',
        'isRequired'        => '',
        'isUnique'          => ''),
        'startDate' => array(
        'id'                => '',
        'tableName'         => '',
        'tablefield'        => '',
        'formTab'           => '',
        'formLabel'         => '',
        'formTooltip'       => '',
        'formType'          => '',
        'formPosition'      => '',
        'formColumns'       => '',
        'formRegexp'        => '',
        'formRange'         => '',
        'defaultValue'      => '',
        'listPosition'      => '',
        'listAlign'         => '',
        'listUseFilter'     => '',
        'altPosition'       => '',
        'status'            => '',
        'isInteger'         => '',
        'isRequired'        => '',
        'isUnique'          => ''),
        'endDate'     => array(
        'id'                => '',
        'tableName'         => '',
        'tablefield'        => '',
        'formTab'           => '',
        'formLabel'         => '',
        'formTooltip'       => '',
        'formType'          => '',
        'formPosition'      => '',
        'formColumns'       => '',
        'formRegexp'        => '',
        'formRange'         => '',
        'defaultValue'      => '',
        'listPosition'      => '',
        'listAlign'         => '',
        'listUseFilter'     => '',
        'altPosition'       => '',
        'status'            => '',
        'isInteger'         => '',
        'isRequired'        => '',
        'isUnique'          => ''),
        'priority'     => array(
        'id'                => '',
        'tableName'         => '',
        'tablefield'        => '',
        'formTab'           => '',
        'formLabel'         => '',
        'formTooltip'       => '',
        'formType'          => '',
        'formPosition'      => '',
        'formColumns'       => '',
        'formRegexp'        => '',
        'formRange'         => '',
        'defaultValue'      => '',
        'listPosition'      => '',
        'listAlign'         => '',
        'listUseFilter'     => '',
        'altPosition'       => '',
        'status'            => '',
        'isInteger'         => '',
        'isRequired'        => '',
        'isUnique'          => ''),
        'currentStatus'     => array(
        'id'                => '',
        'tableName'         => '',
        'tablefield'        => '',
        'formTab'           => '',
        'formLabel'         => '',
        'formTooltip'       => '',
        'formType'          => '',
        'formPosition'      => '',
        'formColumns'       => '',
        'formRegexp'        => '',
        'formRange'         => '',
        'defaultValue'      => '',
        'listPosition'      => '',
        'listAlign'         => '',
        'listUseFilter'     => '',
        'altPosition'       => '',
        'status'            => '',
        'isInteger'         => '',
        'isRequired'        => '',
        'isUnique'          => ''),
        'completePercent'     => array(
        'id'                => '',
        'tableName'         => '',
        'tablefield'        => '',
        'formTab'           => '',
        'formLabel'         => '',
        'formTooltip'       => '',
        'formType'          => '',
        'formPosition'      => '',
        'formColumns'       => '',
        'formRegexp'        => '',
        'formRange'         => '',
        'defaultValue'      => '',
        'listPosition'      => '',
        'listAlign'         => '',
        'listUseFilter'     => '',
        'altPosition'       => '',
        'status'            => '',
        'isInteger'         => '',
        'isRequired'        => '',
        'isUnique'          => '')
        );
    }

    /**
     * Test set
     * Should throw an exception
     *
     * @return void
     */
    public function testWrongSet()
    {
        $item = new Project_Models_Project(array('db' => $this->sharedFixture));
        $this->setExpectedException('Exception');
        $item->wrongAttribute = 'Hello World';
    }

    /**
     * Test set for required fields
     * Should throw an exception
     *
     * @return void
     */
    public function testRequiredFieldSet()
    {
        $item = new Project_Models_Project(array('db' => $this->sharedFixture));
        $item->title = '';
        $item->notes = 'TEST';
        $item->startDate = '1981-05-12';
        $item->priority = 1;
        $item->recordValidate();
        $this->assertEquals(1, count($item->getError()));
    }

    /**
     * Test set for integer fields
     *
     * @expectedException InvalidArgumentException
     *
     * @return void
     */
    public function testIntegerFieldSet()
    {

        $item = new Project_Models_Project(array('db' => $this->sharedFixture));
        $item->priority = 'AA';
        $this->assertEquals(0, $item->priority);
        $item = new Project_Models_Project(array('db' => $this->sharedFixture));
        $item->priority = 7;
        $this->assertEquals(7, $item->priority);
    }

    /**
     * Test for get errors
     *
     * @expectedException InvalidArgumentException
     *
     */
    public function testGetError()
    {
        $result= array();
        $result[] = array('field'    => 'title',
        'message'  => 'Is a required field');
        $result[] = array('field'    => 'startDate',
        'message'  => 'Invalid format for date');
        $item = new Project_Models_Project(array('db' => $this->sharedFixture));
        $item->getError();
        $this->assertEquals(array(), $item->getError());

        $item->title = '';
        $item->notes = 'TEST';
        $item->startDate = '20-';
        $item->endDate = '1981-05-12';
        $item->priority = 1;
        $item->recordValidate();
        $this->assertEquals($result, $item->getError());
    }

    /**
     * Test for validations
     *
     */
    public function testRecordValidate()
    {
        $item = new Project_Models_Project(array('db' => $this->sharedFixture));
        $item->title = '';
        $this->assertFalse($item->recordValidate());

        $item->title = 'TEST';
        $item->notes = 'TEST';
        $item->startDate = '1981-05-12';
        $item->endDate = '1981-05-12';
        $item->priority = 1;
        $this->assertTrue($item->recordValidate());

        $item = new Customized_Project(array('db' => $this->sharedFixture));
        $item->title = 'TEST';
        $item->notes = 'TEST';
        $item->startDate = '1981-05-12';
        $item->endDate = '1981-05-12';
        $item->priority = 0;
        $this->assertFalse($item->recordValidate());
    }

    /**
     * Test date field
     *
     * @expectedException InvalidArgumentException
     *
     */
    public function testDate()
    {
        $item = new Project_Models_Project(array('db' => $this->sharedFixture));
        $item->title = 'TEST';
        $item->notes = 'TEST';
        $item->startDate = 'aaaaaaaaaa';
        $item->endDate = '1981-05-12';
        $item->priority = 1;
        $result = array(array(
        'field'   => 'startDate',
        'message' => 'Invalid format for date'));
        $item->recordValidate();
        $this->assertEquals($result, $item->getError());

        $item->startDate = '1981-05-12';
        $this->assertEquals(array(), $item->getError());
    }

    /**
     * Test float values
     *
     */
    public function testFloat()
    {
        Zend_Locale::setLocale('es_AR');
        $item   = new Project_Models_Project(array('db' => $this->sharedFixture));
        $item->budget = '1000,30';
        $item->budget;
    }

    /**
     * Test empty float values
     *
     */
    public function testEmptyFloat()
    {
        Zend_Locale::setLocale('es_AR');
        $item   = new Project_Models_Project(array('db' => $this->sharedFixture));
        $item->budget = '';
    }

    /**
     * test filters data
     *
     */
    public function testGetFieldsForFilter()
    {
        $module = Phprojekt_Loader::getModel('Project', 'Project', array('db' => $this->sharedFixture));
        $array = $module->getFieldsForFilter();
        $this->assertEquals(array_keys($this->_listResult), $array);
    }

    /**
     * test getrights function
     */
    public function testGetRights()
    {
        $module = Phprojekt_Loader::getModel('Project', 'Project', array('db' => $this->sharedFixture));
        $module->find(2);

        $getRights = $module->getRights();
        $this->assertTrue($getRights['currentUser']['admin']);
        $this->assertEquals($getRights['currentUser']['userName'], 'david');
        $this->assertEquals($getRights[3]['userName'], 'inactive');

        $module = Phprojekt_Loader::getModel('Timecard', 'Timecard', array('db' => $this->sharedFixture));
        $this->assertEquals(array(), $module->getRights());
    }
}
