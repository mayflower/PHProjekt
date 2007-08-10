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
     * Test getFieldsForList
     *
     */
    public function testGetFieldsForList()
    {
        $db     = new Phprojekt_DatabaseManager(array('db' => $this->sharedFixture));
        $fields = $db->getFieldsForList('project','listPosition');
        $this->assertEquals(array_keys($this->_listResult), array_keys($fields));

        /* Second call */
        $fields_second = $db->getFieldsForList('project','listPosition');
        $this->assertEquals($fields, $fields_second);

        $db     = new Phprojekt_DatabaseManager(array('db' => $this->sharedFixture));
        $fields = $db->getFieldsForList('nothing','nothing');
        $this->assertEquals($this->_emptyResult, $fields);
    }

    /**
     * Test getFieldsForForm
     *
     */
    public function testGetFieldsForForm()
    {
        $db     = new Phprojekt_DatabaseManager(array('db' => $this->sharedFixture));
        $fields = $db->getFieldsForForm('project','formPosition');
        $this->assertEquals(array_keys($this->_formResult), array_keys($fields));

        /* Second call */
        $fields_second = $db->getFieldsForForm('project','formPosition');
        $this->assertEquals($fields, $fields_second);

        $db     = new Phprojekt_DatabaseManager(array('db' => $this->sharedFixture));
        $fields = $db->getFieldsForForm('nothing','nothing');
        $this->assertEquals($this->_emptyResult, $fields);
    }
}
