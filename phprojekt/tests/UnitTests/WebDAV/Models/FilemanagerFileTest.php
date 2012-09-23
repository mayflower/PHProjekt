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
class WebDAV_Models_FilemanagerFileTest extends FrontInit
{
    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/../../common.xml');
    }

    public function testDelete()
    {
        $filemanager = new Filemanager_Models_Filemanager();
        $filemanager = $filemanager->find(1);
        $filename    = Phprojekt::getInstance()->getConfig()->uploadPath . '/966f9bfa01ec4a2a3fa6282bb8fa8d56';

        file_put_contents($filename, 'content');
        $file = new WebDAV_Models_FilemanagerFile('articles.txt', '966f9bfa01ec4a2a3fa6282bb8fa8d56', $filemanager);
        $file->delete();

        $filemanager = new Filemanager_Models_Filemanager();
        $filemanager = $filemanager->find(1);
        $this->assertEquals('', $filemanager->files);
        $this->assertFileNotExists($filename);
    }

    public function testSetName()
    {
        $filemanager = new Filemanager_Models_Filemanager();
        $filemanager = $filemanager->find(1);

        $file = new WebDAV_Models_FilemanagerFile('articles.txt', '966f9bfa01ec4a2a3fa6282bb8fa8d56', $filemanager);
        $file->setName('foo');

        $filemanager = new Filemanager_Models_Filemanager();
        $filemanager = $filemanager->find(1);
        $this->assertEquals('966f9bfa01ec4a2a3fa6282bb8fa8d56|foo', $filemanager->files);
    }
}
