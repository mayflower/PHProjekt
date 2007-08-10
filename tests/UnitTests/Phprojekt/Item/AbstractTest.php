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
        $result = array(
            array('field'    => 'title',
                  'message'  => 'Is a required field')
                  );
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
        $result = array(array(
            'field'   => 'startDate',
            'message' => 'Invalid format for date'));
        $item->recordValidate();
        $this->assertEquals($result, $item->getError());

        $item->startDate = '1981-05-12';
        //$this->assertEquals(array(), $item->getError());
    }

    /**
     * Test float values
     *
     */
    public function testFloat()
    {
        $item   = new Project_Models_Project(array('db' => $this->sharedFixture));
        $item->budget = '1,3';
        $tmp = '1.3';
        $this->assertEquals(Zend_Locale_Format::toFloat($tmp, array('precision' => 2)), $item->budget);

        $item   = new Project_Models_Project(array('db' => $this->sharedFixture));
        $item->budget = '1.3';
        $tmp = '13.00';
        $this->assertEquals(Zend_Locale_Format::toFloat($tmp, array('precision' => 2)), $item->budget);
    }
}