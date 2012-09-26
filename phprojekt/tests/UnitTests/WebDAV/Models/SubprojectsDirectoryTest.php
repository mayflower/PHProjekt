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
 * @copyright  Copyright (c) 2012 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * @group      webdav
 * @group      model
 * @group      webdav-model
 */
class WebDAV_Models_SubprojectsDirectoryTest extends FrontInit
{
    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/../../common.xml');
    }

    private function _createDir()
    {
        $project    = new Project_Models_Project;
        $project    = $project->find(1);
        return new WebDAV_Models_SubprojectsDirectory($project);
    }

    public function testGetChild()
    {
        $dir    = $this->_createDir();
        $foobar = $dir->getChild('Test Project');
        $this->assertInstanceOf('WebDAV_Models_ProjectDirectory', $foobar);
    }

    public function testGetNonexistingChild()
    {
        $this->setExpectedException('Sabre_DAV_Exception_NotFound');
        $this->_createDir()->getChild('I do not exist');
    }

    public function testChildExists()
    {
        $dir = $this->_createDir();
        $this->assertTrue($dir->childExists('Test Project'));
        $this->assertFalse($dir->childExists('I do not exists'));
    }

    public function testCreateFileFails()
    {
        $this->setExpectedException('Sabre_DAV_Exception_NotImplemented');
        $this->_createDir()->createFile('name', 'content');
    }

    public function testNameIsCorrect()
    {
        $this->assertEquals(WebDAV_Constants::SUBPROJECTS_NAME, $this->_createDir()->getName());
    }

    public function testGetChildrenCount()
    {
        $this->assertEquals(1, count($this->_createDir()->getChildren()));
    }

    public function testCreateDir()
    {
        $this->setExpectedException('Sabre_DAV_Exception_NotImplemented');
        $this->_createDir()->createDirectory('name');
    }
}
