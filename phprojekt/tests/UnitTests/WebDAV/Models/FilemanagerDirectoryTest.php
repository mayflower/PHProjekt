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
class WebDAV_Models_FilemanagerDirectoryTest extends FrontInit
{
    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/../../common.xml');
    }

    private function _generateDir(array $settings = array())
    {
        $settings = array_merge(
            array(
                'title' => '',
                'comments' => '',
                'files' => ''
            ),
            $settings
        );

        $filemanager           = new Filemanager_Models_Filemanager();
        $filemanager->title    = $settings['title'];
        $filemanager->comments = $settings['comments'];
        $filemanager->files    = $settings['files'];

        return new WebDAV_Models_FilemanagerDirectory($filemanager);
    }

    public function testGetChild()
    {
        $dir = $this->_generateDir(
            array(
                'files' => 'e07910a06a086c83ba41827aa00b26ed|testname with spaces||'
                           . 'c157a79031e1c40f85931829bc5fc552|otherFile||'
                           . '7fc0b3aa4885ca67d5c825ac36867da5|spécial chärs'
            )
        );
        $child = $dir->getChild('testname with spaces');
        $this->assertEquals('WebDAV_Models_FilemanagerFile', get_class($child));
        $this->assertEquals('testname with spaces', $child->getName());

        $child = $dir->getChild('otherFile');
        $this->assertEquals('WebDAV_Models_FilemanagerFile', get_class($child));
        $this->assertEquals('otherFile', $child->getName());

        $child = $dir->getChild('spécial chärs');
        $this->assertEquals('WebDAV_Models_FilemanagerFile', get_class($child));
        $this->assertEquals('spécial chärs', $child->getName());
    }

    public function testUnknownChildThrows()
    {
        $dir = $this->_generateDir(array('files' => ''));
        $this->setExpectedException('Sabre_DAV_Exception_NotFound');
        $dir->getChild('nonexistant');
    }

    public function testChildExists()
    {
        $dir = $this->_generateDir(
            array(
                'files' => 'e07910a06a086c83ba41827aa00b26ed|testname with spaces||'
                           . 'c157a79031e1c40f85931829bc5fc552|otherFile||'
                           . '7fc0b3aa4885ca67d5c825ac36867da5|spécial chärs'
            )
        );
        $this->assertTrue($dir->childExists('testname with spaces'));
        $this->assertTrue($dir->childExists('otherFile'));
        $this->assertTrue($dir->childExists('spécial chärs'));
    }

    public function testGetName()
    {
        $dir = $this->_generateDir(array('title' => 'test title'));
        $this->assertEquals('test title', $dir->getName());
    }

    public function testCreateFileWithExistingFile()
    {
        $filemanager = new Filemanager_Models_Filemanager();
        $filemanager = $filemanager->find(1);

        $dir = new WebDAV_Models_FilemanagerDirectory($filemanager);
        $dir->createFile('filename', 'some data');

        $filemanager = new Filemanager_Models_Filemanager();
        $filemanager = $filemanager->find(1);
        $this->assertRegExp('/[0-9a-zA-Z]{32}\|filename/', $filemanager->files);

        $matches = array();
        preg_match('/([0-9a-zA-Z]{32})\|filename/', $filemanager->files, $matches);
        $filename = Phprojekt::getInstance()->getConfig()->uploadPath . '/' . $matches[1];
        $this->assertFileExists($filename);
        $this->assertEquals('some data', file_get_contents($filename));

        unlink($filename);
    }

    public function testCreateFile()
    {
        $filemanager = new Filemanager_Models_Filemanager();
        $filemanager = $filemanager->find(3);

        $dir = new WebDAV_Models_FilemanagerDirectory($filemanager);
        $dir->createFile('filename', 'some data');

        $filemanager = new Filemanager_Models_Filemanager();
        $filemanager = $filemanager->find(3);
        $this->assertRegExp('/^[0-9a-zA-Z]{32}\|filename$/', $filemanager->files);

        $matches = array();
        preg_match('/([0-9a-zA-Z]{32})\|filename/', $filemanager->files, $matches);
        $filename = Phprojekt::getInstance()->getConfig()->uploadPath . '/' . $matches[1];
        $this->assertFileExists($filename);
        $this->assertEquals('some data', file_get_contents($filename));

        unlink($filename);
    }

    public function testCreateFileWithInvalidUploadPath()
    {
        $config     = Phprojekt::getInstance()->getConfig();
        $uploadPath = $config->uploadPath;
        $config->uploadPath = $uploadPath . '/this/directory/does/not/exist/';

        $filemanager = new Filemanager_Models_Filemanager();
        $filemanager = $filemanager->find(1);
        $dir = new WebDAV_Models_FilemanagerDirectory($filemanager);

        $caught = false;
        try {
            @$dir->createFile('filename', 'some data');
        } catch (Phprojekt_Exception_IOException $e) {
            $caught = true;
        }
        $config->uploadPath = $uploadPath;
        $this->assertTrue($caught);

    }

    public function testCreateDirectory()
    {
        $this->setExpectedException('Sabre_DAV_Exception_NotImplemented');
        $this->_generateDir()->createDirectory('test');
    }

    public function testDelete()
    {
        $filemanager = new Filemanager_Models_Filemanager();
        $filemanager = $filemanager->find(3);
        $dir = new WebDAV_Models_FilemanagerDirectory($filemanager);
        $dir->delete();

        $filemanager = new Filemanager_Models_Filemanager();
        $result      = $filemanager->fetchAll('id = 3');
        $this->assertEmpty($result);
    }

    public function testSetName()
    {
        $filemanager = new Filemanager_Models_Filemanager();
        $filemanager = $filemanager->find(3);
        $dir = new WebDAV_Models_FilemanagerDirectory($filemanager);
        $dir->setName('new name');

        $filemanager = new Filemanager_Models_Filemanager();
        $filemanager = $filemanager->find(3);
        $this->assertEquals('new name', $filemanager->title);
    }

    public function testGetChildren()
    {
        $dir = $this->_generateDir(
            array(
                'files' => 'e07910a06a086c83ba41827aa00b26ed|testname with spaces||'
                           . 'c157a79031e1c40f85931829bc5fc552|otherFile'
            )
        );

        $found = array(
            'testname with spaces' => false,
            'otherFile'            => false
        );
        foreach ($dir->getChildren() as $child) {
            $name = $child->getName();
            if (!array_key_exists($name, $found)) {
                $this->fail('Invalid entry in getChildren');
            } elseif ($found[$name]) {
                $this->fail('Duplicate entry in getChildren');
            } else {
                $found[$name] = true;
            }
        }

        foreach ($found as $f) {
            $this->assertTrue($f);
        }

    }
}
