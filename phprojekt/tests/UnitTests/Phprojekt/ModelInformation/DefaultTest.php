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
 * Tests Phprojekt Model Information Default class
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 * @group      phprojekt
 * @group      modelinformation
 * @group      phprojekt-modelinformation
 * @group      model
 * @group      activerecord
 * @group      databasemanager
 */
class Phprojekt_ModelInformation_DefaultTest extends PHPUnit_Framework_TestCase
{
    private $_defaultForm = array (
            0 => array (
                'key'      => '',
                'label'    => '',
                'type'     => 'string',
                'hint'     => '',
                'order'    => 0,
                'position' => 0,
                'fieldset' => null,
                'range'    => '',
                'required' => false,
                'right'    => 'write',
                'readOnly' => false),
        );

    private $_testForm = array (
            0 => array (
                'key'      => 'test',
                'label'    => '',
                'type'     => 'string',
                'hint'     => '',
                'order'    => 0,
                'position' => 0,
                'fieldset' => null,
                'range'    => '',
                'required' => false,
                'right'    => 'write',
                'readOnly' => false),
        );

    private $_testList = array (
            0 => array (
                'key'      => 'test list',
                'label'    => '',
                'type'     => 'string',
                'hint'     => '',
                'order'    => 0,
                'position' => 0,
                'fieldset' => null,
                'range'    => '',
                'required' => false,
                'right'    => 'write',
                'readOnly' => false),
        );

    /**
     * Test get field definition
     */
    public function testGetFieldDefinition()
    {
        $object  = new Phprojekt_ModelInformation_Default();
        $records = $object->getFormFields();
        $this->assertEquals($records, $this->_defaultForm);

        $records = $object->getListFields();
        $this->assertEquals($records, $this->_defaultForm);

        $records = $object->getTitles();
        $this->assertEquals($records[0], '');

        $records = $object->getTitles(Phprojekt_ModelInformation_Default::ORDERING_FORM);
        $this->assertEquals($records[0], '');
    }

    /**
     * Test fieldDefinition function
     */
    public function testFieldDefinition()
    {
        $object  = new Phprojekt_ModelInformation_Default();
        $records = $object->getFieldDefinition(Phprojekt_ModelInformation_Default::ORDERING_LIST);
        $this->assertEquals($records, $this->_defaultForm);

        $records = $object->getFieldDefinition(Phprojekt_ModelInformation_Default::ORDERING_FORM);
        $this->assertEquals($records, $this->_defaultForm);
    }

    public function testConstuct()
    {
        $object  = new Phprojekt_ModelInformation_Default($this->_testList);
        $records = $object->getListFields();
        $this->assertEquals($records, $this->_testList);

        $records = $object->getFormFields();
        $this->assertEquals($records, $this->_testList);

        $object  = new Phprojekt_ModelInformation_Default(null, $this->_testForm);
        $records = $object->getListFields();
        $this->assertEquals($records, $this->_testForm);

        $records = $object->getFormFields();
        $this->assertEquals($records, $this->_testForm);

        $object  = new Phprojekt_ModelInformation_Default($this->_testList, $this->_testForm);
        $records = $object->getListFields();
        $this->assertEquals($records, $this->_testList);

        $records = $object->getFormFields();
        $this->assertEquals($records, $this->_testForm);
    }
}
