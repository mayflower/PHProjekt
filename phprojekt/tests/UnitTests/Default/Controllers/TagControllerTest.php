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
 * @subpackage Default
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */


/**
 * Tests for Tag Controller
 *
 * @category   PHProjekt
 * @package    UnitTests
 * @subpackage Default
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 * @group      default
 * @group      controller
 * @group      default-controller
 */
class Phprojekt_TagController_Test extends FrontInit
{
    protected function getDataSet() {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/../data.xml');
    }

    /**
     * Test of json get tags
     */
    public function testJsonGetTagsAction()
    {
        $this->setRequestUrl('Default/Tag/jsonGetTags/');
        $this->request->setParam('nodeId', 1);
        $this->request->setParam('limit', 2);
        $response = FrontInit::phprJsonToArray($this->getResponse());
        $expected = array(
            'metadata' => array(
                array(
                    'key'   => 'string',
                    'label' => 'Tag',
                ),
                array(
                    'key'   => 'count',
                    'label' => 'Count',
                ),
            ),
            'data' => array(
                array(
                    'string' => 'this',
                    'count'  => '3',
                ),
            ),
            'numRows' => 1,
        );
        $this->assertEquals($expected, $response);
    }

    /**
     * Test of GetModulesByTag
     */
    public function testJsonSaveTagsAction()
    {
        $this->setRequestUrl('Default/Tag/jsonSaveTags/');
        $this->request->setParam('moduleName', 'Project');
        $this->request->setParam('string', 'test');
        $this->request->setParam('id', 1);
        $this->request->setParam('projectId', 1);
        $this->getResponse();
    }

    /**
     * Test of GetModulesByTag
     */
    public function testJsonGetModulesByTagAction()
    {
        $this->setRequestUrl('Default/Tag/jsonGetModulesByTag/');
        $this->request->setParam('nodeId', 1);
        $this->request->setParam('tag', 'this');
        $this->request->setParam('limit', 2);
        $response = $this->getResponse();
        $expected = '{"id":1,"moduleId":1,"moduleName":"Project","moduleLabel":"Project","firstDisplay":"test",'
            . '"secondDisplay":null,"projectId":1}';
        $this->assertContains($expected, $response);
    }
}
