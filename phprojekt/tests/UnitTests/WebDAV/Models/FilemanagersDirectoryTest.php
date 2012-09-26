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
class WebDAV_Models_FilemanagersDirectoryTest extends FrontInit
{
    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/../../common.xml');
    }

    private function _createDir()
    {
        $project    = new Project_Models_Project;
        $project    = $project->find(1);
        return new WebDAV_Models_FilemanagersDirectory($project);
    }

    public function testGetChild()
    {
        $dir    = $this->_createDir();
        $foobar = $dir->getChild('TestFoobar');
        $this->assertInstanceOf('WebDAV_Models_FilemanagerDirectory', $foobar);
    }

    public function testGetNonexistingChild()
    {
        $this->setExpectedException('Sabre_DAV_Exception_NotFound');
        $this->_createDir()->getChild('I do not exist');
    }

    public function testChildExists()
    {
        $dir = $this->_createDir();
        $this->assertTrue($dir->childExists('TestFoobar'));
        $this->assertTrue($dir->childExists('TestEinself'));
        $this->assertFalse($dir->childExists('I do not exists'));
    }

    public function testCreateFileFails()
    {
        $this->setExpectedException('Sabre_DAV_Exception_NotImplemented');
        $this->_createDir()->createFile('name', 'content');
    }

    public function testNameIsCorrect()
    {
        $this->assertEquals(WebDAV_Constants::FILEMANAGERS_NAME, $this->_createDir()->getName());
    }

    public function testGetChildrenCount()
    {
        $this->assertEquals(2, count($this->_createDir()->getChildren()));
    }

    public function testCreateDir()
    {
        $dir = $this->_createDir();
        $dir->createDirectory('new filemanager entry');

        $filemanager = new Filemanager_Models_Filemanager();
        $results     = $filemanager->fetchAll('title = "new filemanager entry"');
        $this->assertNotEmpty($results);
    }
}
