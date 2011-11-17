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
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */


/**
 * Tests Converter class
 *
 * @category   PHProjekt
 * @package    UnitTests
 * @subpackage Phprojekt
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 * @group      phprojekt
 * @group      converter
 * @group      json
 * @group      phprojekt-converter
 * @group      phprojekt-converter-json
 */
class Phprojekt_Converter_JsonTest extends DatabaseTest
{
    public function setUp()
    {
        parent::setUp();
        $this->sharedFixture = Phprojekt::getInstance()->getDb();
    }

    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/../data.xml');
    }
    /**
     * Test json converter
     */
    public function testConvert()
    {
        $converted = substr('{}&&({"metadata":[{"key":"title","label":"Title","type":', 0, 23);
        $object    = new Project_Models_Project();
        $records   = $object->fetchAll();
        $result    = Phprojekt_Converter_Json::convert($records);
        $this->assertEquals($converted, substr($result, 0, strlen($converted)));

        $result = Phprojekt_Converter_Json::convert($object->find(1));
        $this->assertEquals($converted, substr($result, 0, strlen($converted)));
    }

    /**
     * Test json convert tree
     */
    public function testConvertTree()
    {
        $converted = '{}&&({"identifier":"id","label":"name","items":[{"name"';
        $object    = new Project_Models_Project();
        $tree      = new Phprojekt_Tree_Node_Database($object, 1);
        $tree      = $tree->setup();
        $result = Phprojekt_Converter_Json::convert($tree);
        $this->assertEquals($converted, substr($result, 0, strlen($converted)));
    }

    /**
     * Test json convertion of single value
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

        $result    = Phprojekt_Converter_Json::convert(array());
        $converted = '{}&&({"metadata":[]})';
        $this->assertEquals($converted, $result);
    }

    /**
     * Test json convertion of tags
     */
    public function testConvertTags()
    {
        $tagObj    = Phprojekt_Tags::getInstance();
        $tags      = $tagObj->getTags(1);
        $fields    = $tagObj->getFieldDefinition();
        $result    = Phprojekt_Converter_Json::convert($tags, $fields);
        $converted = '{}&&({"metadata":[{"key":"string","label":"Tag"},{"key":"count","label":"Count"}],"data":[{"';
        $this->assertEquals($converted, substr($result, 0, strlen($converted)));
    }
}
