<?php
/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @version    Release: 6.1.0
 */


/**
 * Tests for Tag Controller
 *
 * @version    Release: 6.1.5
 * @group      controller
 * @group      default-controller
 */
class Phprojekt_TagController_Test extends FrontInit
{
    protected function getDataSet() {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/../data.xml');
    }

    /**
     * Test of jsonSaveTagsAction
     */
    public function testJsonSaveTagsAction()
    {
        $this->setRequestUrl('Default/Tag/jsonSaveTags/');
        $this->request->setParam('moduleName', 'Project');
        $this->request->setParam('string', 'test');
        $this->request->setParam('id', 1);
        $this->request->setParam('projectId', 1);
        $response = $this->getResponse();
        $this->assertEquals('{}&&({"type":"success","message":"The Tags were added correctly","id":0})', $response);

        $tag  = new Phprojekt_Tags();
        $tags = $tag->search("test");
        $this->assertEquals(array(
            array(
                'id'            => 1,
                'moduleId'      => 1,
                'moduleName'    => 'Project',
                'moduleLabel'   => 'Project',
                'firstDisplay'  => 'test',
                'secondDisplay' => null,
                'projectId'     => 1
            )
        ), $tags);
    }

    /**
     * Test of jsonSaveTagsAction
     */
    public function testJsonSaveTagsActionMultiple()
    {
        $this->setRequestUrl('Default/Tag/jsonSaveTags/');
        $this->request->setParam('moduleName', 'Project');
        $this->request->setParam('string', 'test awesome');
        $this->request->setParam('id', 1);
        $this->request->setParam('projectId', 1);
        $response = $this->getResponse();
        $this->assertEquals('{}&&({"type":"success","message":"The Tags were added correctly","id":0})', $response);

        $tag = new Phprojekt_Tags();
        $tags = $tag->search("test awesome");
        $this->assertEquals(array(
            array(
                'id'            => 1,
                'moduleId'      => 1,
                'moduleName'    => 'Project',
                'moduleLabel'   => 'Project',
                'firstDisplay'  => 'test',
                'secondDisplay' => null,
                'projectId'     => 1
            )
        ), $tags);
    }

    /**
     * Test of jsonSaveTagsAction
     */
    public function testJsonSaveTagsActionInvalid()
    {
        $this->setExpectedException('Zend_Controller_Action_Exception');
        $this->setRequestUrl('Default/Tag/jsonSaveTags/');
        $this->request->setParam('moduleName', 'Project');
        $this->request->setParam('string', 'test');
        $this->request->setParam('projectId', 1);
        $response = $this->getResponse();
    }

    /**
     * Test of jsonDeleteTagsAction
     */
    public function testJsonDeleteTagsAction()
    {
        $tag = new Phprojekt_Tags();
        $tags = $tag->search("this");
        $this->assertEquals(array(
            array(
                'id'            => 2,
                'moduleId'      => 1,
                'moduleName'    => 'Project',
                'moduleLabel'   => 'Project',
                'firstDisplay'  => '',
                'secondDisplay' => '',
                'projectId'     => 1
            )
        ), $tags);

        $this->setRequestUrl('Default/Tag/jsonDeleteTags/');
        $this->request->setParam('moduleName', 'Project');
        $this->request->setParam('id', 2);
        $response = $this->getResponse();

        $this->assertEquals('{}&&({"type":"success","message":"The Tags were deleted correctly","id":0})', $response);

        $tags = $tag->search("this");
        $this->assertTrue(empty($tags));
    }

    /**
     * Test of jsonDeleteTagsAction
     */
    public function testJsonDeleteTagsActionInvalid()
    {
        $this->setExpectedException('Zend_Controller_Action_Exception');
        $this->setRequestUrl('Default/Tag/jsonDeleteTags/');
        $this->request->setParam('moduleName', 'Project');
        $response = $this->getResponse();
    }

    /**
     * Test of jsonGetTagsByModuleAction
     */
    public function testGetTagsByModuleAction()
    {
        $this->setRequestUrl('Default/Tag/jsonGetTagsByModule/');
        $this->request->setParam('moduleName', 'Project');
        $this->request->setParam('id', 2);
        $response = FrontInit::phprJsonToArray($this->getResponse());

        $this->assertEquals(array(
            'metadata' => array(
                array(
                    'key'   => 'string',
                    'label' => 'Tags'
                ),
                array(
                    'key'   => 'count',
                    'label' => 'Count'
                )
            ),
            'data'     => array('this', 'fake'),
            'numRows'  => 2
        ), $response);
    }

    /**
     * Test of jsonGetTagsByModuleAction
     */
    public function testGetTagsByModuleActionNoId()
    {
        $this->setRequestUrl('Default/Tag/jsonGetTagsByModule/');
        $this->request->setParam('moduleName', 'Project');
        $response = FrontInit::phprJsonToArray($this->getResponse());

        $this->assertEquals(array(
            'metadata' => array(
                array(
                    'key'   => 'string',
                    'label' => 'Tags'
                ),
                array(
                    'key'   => 'count',
                    'label' => 'Count'
                )
            ),
            'data'     => array(),
            'numRows'  => 0
        ), $response);
    }

}
