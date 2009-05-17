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
 * @version    $Id$
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */
require_once 'PHPUnit/Framework.php';

/**
 * Tests Note Model class
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 */
class Note_Models_Note_Test extends PHPUnit_Framework_TestCase
{
    /**
     * Test creating a item -in fact via via default model
     */
    public function testCreatingItem()
    {
        $noteModel            = new Note_Models_Note();
        $noteModel->title     = 'Title 3';
        $noteModel->comments  = 'Comments 3';
        $noteModel->projectId = 1;
        $noteModel->category  = 'category 3';
        $noteModel->save();

        $this->assertEquals("Title 3", $noteModel->title);
        $this->assertEquals("Comments 3", $noteModel->comments);
        $this->assertEquals(1, $noteModel->projectId);
        $this->assertEquals("category 3", $noteModel->category);
    }

    /**
     * Test editing a item -in fact via default model
     */
    public function testEditingItem()
    {
        $noteModel = new Note_Models_Note();
        $noteModel->find(3);
        $noteModel->title     = 'Title 3 modified';
        $noteModel->comments  = 'Comments 3 modified';
        $noteModel->projectId = 1;
        $noteModel->category  = 'category 3 modified';
        $noteModel->save();

        $this->assertEquals("Title 3 modified", $noteModel->title);
        $this->assertEquals("Comments 3 modified", $noteModel->comments);
        $this->assertEquals("category 3 modified", $noteModel->category);
    }

    /**
     * Test deletion of item -in fact via default model
     */
    public function testDeletingItem()
    {
        $noteModel  = new Note_Models_Note();
        $rowsBefore = count($noteModel->fetchAll());
        $noteModel->find(2);
        $noteModel->delete();
        $rowsAfter = count($noteModel->fetchAll());
        $this->assertEquals($rowsBefore - 1, $rowsAfter);
    }
}
