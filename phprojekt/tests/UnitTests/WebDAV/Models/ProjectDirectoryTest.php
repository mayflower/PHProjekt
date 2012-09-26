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
class WebDAV_Models_ProjectDirectoryTest extends FrontInit
{
    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/../../common.xml');
    }

    private $_dir;

    public function setUp()
    {
        $project    = new Project_Models_Project();
        $project    = $project->find(1);
        $this->_dir = new WebDAV_Models_ProjectDirectory($project);
    }

    public function testGetNameReturnsTitle()
    {
        $this->assertEquals('PHProjekt', $this->_dir->getName());
    }

    public function testGetChildSubproject()
    {
        $child = $this->_dir->getChild(WebDAV_Constants::SUBPROJECTS_NAME);
        $this->assertEquals('WebDAV_Models_SubprojectsDirectory', get_class($child));
    }

    public function testGetChildFilemanagers()
    {
        $child = $this->_dir->getChild(WebDAV_Constants::FILEMANAGERS_NAME);
        $this->assertEquals('WebDAV_Models_FilemanagersDirectory', get_class($child));
    }

    public function testGetInvalidChild()
    {
        $this->setExpectedException('Sabre_DAV_Exception_NotFound');
        $this->_dir->getChild('testinvalid');
    }

    public function testChildExists()
    {
        $this->assertTrue($this->_dir->childExists(WebDAV_Constants::SUBPROJECTS_NAME));
        $this->assertTrue($this->_dir->childExists(WebDAV_Constants::FILEMANAGERS_NAME));
        $this->assertFalse($this->_dir->childExists('test'));
    }

    public function testCreateFileIsUnsupported()
    {
        $this->setExpectedException('Sabre_DAV_Exception_NotImplemented');
        $this->_dir->createFile('name', 'content');
    }

    public function testCreateDirectoryIsUnsupported()
    {
        $this->setExpectedException('Sabre_DAV_Exception_NotImplemented');
        $this->_dir->createDirectory('name');
    }

    public function testGetChildren()
    {
        $this->assertEquals(2, count($this->_dir->getChildren()));

        list($fst, $snd) = $this->_dir->getChildren();
        if ($fst->getName() === WebDAV_Constants::SUBPROJECTS_NAME) {
            $this->assertEquals(WebDAV_Constants::FILEMANAGERS_NAME, $snd->getName());
        } else {
            $this->assertEquals(WebDAV_Constants::FILEMANAGERS_NAME, $fst->getName());
            $this->assertEquals(WebDAV_Constants::SUBPROJECTS_NAME, $snd->getName());
        }

    }
}
