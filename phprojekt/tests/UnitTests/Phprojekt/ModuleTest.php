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
 * Tests for Module Class
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 * @group      phprojekt
 * @group      module
 * @group      phprojekt-module
 */
class Phprojekt_ModuleTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test getId
     */
    public function testGetId()
    {
        $this->assertEquals(1, Phprojekt_Module::getId('Project'));
        $this->assertEquals(2, Phprojekt_Module::getId('Todo'));
    }


    /**
     * Test getModuleName
     */
    public function testGetModuleName()
    {
        $this->assertEquals('Project', Phprojekt_Module::getModuleName(1));
        $this->assertEquals('Todo', Phprojekt_Module::getModuleName(2));
    }
}
