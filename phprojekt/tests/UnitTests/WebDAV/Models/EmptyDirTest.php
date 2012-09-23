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
class WebDAV_Models_EmptyDirTest extends PHPUnit_Framework_TestCase
{
    public function testIsEmpty()
    {
        $dir = new WebDAV_Models_EmptyDir();
        $this->assertEquals(array(), $dir->getChildren());
    }

    public function testNameIsEmpty()
    {
        $dir = new WebDAV_Models_EmptyDir();
        $this->assertEquals('Empty', $dir->getName());
    }
}
