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
 * @version    CVS: $Id:
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */
require_once 'PHPUnit/Framework.php';

/**
 * Tests Converter class
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */
class Phprojekt_ConverterTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test json converter
     *
     */
    public function testConvert()
    {
        $converted = substr('{}&&({"metadata":[{"key":"title","label":"Title","type":', 0, 23);

        $authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');
        $authNamespace->userId = 1;

        $object  = Phprojekt_Loader::getModel('Project', 'Project');
        $records = $object->fetchAll(null, null, 20, 0);
        $data    = Phprojekt_Converter_Json::convert($records);

        $this->assertEquals($converted, substr($data, 0, 23));
    }

    /**
     * Test json convert tree
     *
     */
    public function testConvertTree()
    {
        $converted = substr('{}&&({"identifier":"id","label":"name","items":[{"name"', 0, 23);

        $authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');
        $authNamespace->userId = 1;

        $object = Phprojekt_Loader::getModel('Project', 'Project');
        $tree   = new Phprojekt_Tree_Node_Database($object, 1);
        $tree->setup();
        $data = Phprojekt_Converter_Json::convert($tree);

        $this->assertEquals($converted, substr($data, 0, 23));
    }

    /**
     * Test json convertion of single value
     *
     */
    public function testConvertValue()
    {
        $data      = 'This is a test of convetion';
        $converted = '{}&&("This is a test of convetion")';
        $result    = Phprojekt_Converter_Json::convert($data);
        $this->assertEquals($converted, $result);

        $data      = array('This is a test of convetion');
        $converted = '{}&&(["This is a test of convetion"])';
        $result    = Phprojekt_Converter_Json::convert($data);
        $this->assertEquals($converted, $result);
    }
}
