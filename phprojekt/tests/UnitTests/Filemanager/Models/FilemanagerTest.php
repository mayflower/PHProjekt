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
 * Tests Filemanager Model class
 *
 * @version    Release: 6.1.0
 * @group      filemanager
 * @group      model
 * @group      filemanager-model
 */
class Filemanager_Models_Filemanager_Test extends PHPUnit_Framework_TestCase
{
    private $_model = null;

    /**
     * setUp method for PHPUnit
     */
    public function setUp()
    {
        $this->_model = new Filemanager_Models_Filemanager();
    }

    /**
     * Test creating a item -in fact via via default model
     */
    public function testCreatingItem()
    {
        // Insert
        $model = clone($this->_model);
        $model->ownerId   = 1;
        $model->title     = 'Title 3';
        $model->comments  = 'Comments 3';
        $model->projectId = 1;
        $model->files     = 'fnkskljsfijrsbj42036thnfe|file.txt';
        $model->save();

        // Check it was well inserted
        $this->assertEquals("Title 3", $model->title);
        $this->assertEquals("Comments 3", $model->comments);
        $this->assertEquals(1, $model->projectId);
        $this->assertEquals("fnkskljsfijrsbj42036thnfe|file.txt", $model->files);
    }

    /**
     * Test editing a item -in fact via default model
     */
    public function testEditingItem()
    {
        // Edit
        $model = clone($this->_model);
        $model->find(2);
        $model->title     = 'Ultimate title modification';
        $model->comments  = 'Ultimate comments modification';
        $model->projectId = 1;
        $model->ownerId   = 1;
        $model->files     = 'Ankskljsfijrsbj42036thnfe|file2.txt';
        $model->save();

        // Check it was well edited
        unset($model);
        $model = clone($this->_model);
        $model->find(2);
        $this->assertEquals("Ultimate title modification", $model->title);
        $this->assertEquals("Ultimate comments modification", $model->comments);
        $this->assertEquals("Ankskljsfijrsbj42036thnfe|file2.txt", $model->files);
    }

    /**
     * Test deletion of item -in fact via default model
     */
    public function testDeletingItem()
    {
        // Delete
        $rowsBefore = count($this->_model->fetchAll());
        $model = clone($this->_model);
        $model->find(2);
        $model->delete();

        // Check it was deleted indeed
        $rowsAfter = count($this->_model->fetchAll());
        $this->assertEquals($rowsBefore - 1, $rowsAfter);
    }
}
