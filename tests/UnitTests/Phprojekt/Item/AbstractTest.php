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
 * Tests for items
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_Item_AbstractTest extends PHPUnit_Extensions_ExceptionTestCase
{
    /**
     * setUp method for PHPUnit. We use a shared db connection
     *
     */
    public function setUp()
    {
        $this->_emptyResult = array();

        $this->_formResult = array(
                        'parent' => array(
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
                        'parent' => array(
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
        $result = array();
        $result[] = array('field'    => 'title',
                          'message'  => 'Is a required field');
        $result[] = array('field'    => 'endDate',
                          'message'  => 'Is a required field');
        $item->recordValidate();
        $this->assertEquals($result, $item->getError());
    }

    /**
     * Test set for integer fields
     *
     * @return void
     */
    public function testIntegerFieldSet()
    {
        $item = new Project_Models_Project(array('db' => $this->sharedFixture));
        $item->priority = 'AA';
        $this->assertEquals(0, $item->priority);

        $item = new Project_Models_Project(array('db' => $this->sharedFixture));
        $item->priority = '7';
        $this->assertEquals(7, $item->priority);
    }

    /**
     * Test for get errors
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
    }

    /**
     * Test getFieldsForList
     *
     */
    public function testGetFieldsForList()
    {
        $item   = new Project_Models_Project(array('db' => $this->sharedFixture));
        $fields = $item->getFieldsForList('project');
        $this->assertEquals(array_keys($this->_listResult), array_keys($fields));

        /* Second call */
        $fields_second = $item->getFieldsForList('project');
        $this->assertEquals($fields, $fields_second);
    }

    /**
     * Test getFieldsForForm
     *
     */
    public function testGetFieldsForForm()
    {
        $item   = new Project_Models_Project(array('db' => $this->sharedFixture));
        $fields = $item->getFieldsForForm('project');
        $this->assertEquals(array_keys($this->_formResult), array_keys($fields));

        /* Second call */
        $fields_second = $item->getFieldsForForm('project');
        $this->assertEquals($fields, $fields_second);
    }

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
        $locale = Zend_Locale::setLocale('es_AR');
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
        $locale = Zend_Locale::setLocale('es_AR');
        $item   = new Project_Models_Project(array('db' => $this->sharedFixture));
        $item->budget = '';
    }

    /**
     * Check the getListData function for project (using parent)
     *
     */
    public function testGetListDataProject()
    {
        $module = Phprojekt_Loader::getModel('Project', 'Project', array('db' => $this->sharedFixture));

        /* First call */
        $array = $module->getListData();
        $this->assertEquals(count($array[0]), 3);

        /* Test the project filter */
        $pageNamespace = new Zend_Session_Namespace();
        $pageNamespace->lastProjectId = 2;
        $array = $module->getListData();
        $this->assertEquals(count($array[0]), 2);

        /* Test the paging */
        $pageNamespace = new Zend_Session_Namespace(2 . 'Project');
        $pageNamespace->currentPage = 100;
        $array = $module->getListData();
        $this->assertEquals(count($array[0]), 0);

        $pageNamespace->currentPage = 1;
        $array = $module->getListData();
        $this->assertEquals(count($array[0]), 1);
    }

    /**
     * Check the getListData function for default modules (using projectId)
     *
     */
    public function testGetListData()
    {
        $module = Phprojekt_Loader::getModel('Todo', 'Todo', array('db' => $this->sharedFixture));

        /* First call */
        $array = $module->getListData();
        $this->assertEquals(count($array[0]), 0);

        /* Test the project filter */
        $pageNamespace = new Zend_Session_Namespace();
        $pageNamespace->lastProjectId = 1;
        $array = $module->getListData();
        $this->assertEquals(count($array[0]), 1);

        /* Test the paging */
        $pageNamespace = new Zend_Session_Namespace(1 . 'Todo');
        $pageNamespace->currentPage = 100;
        $array = $module->getListData();
        $this->assertEquals(count($array[0]), 0);

        $pageNamespace->currentPage = 0;
        $array = $module->getListData();
        $this->assertEquals(count($array[0]), 1);
    }

    /**
     * Check the getSubModules function
     *
     */
    public function testSubModules()
    {
        $module = Phprojekt_Loader::getModel('Project', 'Project', array('db' => $this->sharedFixture));
        $array = $module->getSubModules();
        $this->assertEquals(array('Todo'),$array);

        $module = Phprojekt_Loader::getModel('Todo', 'Todo', array('db' => $this->sharedFixture));
        $array = $module->getSubModules();
        $this->assertEquals(array(),$array);
    }
}