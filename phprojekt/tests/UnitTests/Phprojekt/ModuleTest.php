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
 */


/**
 * Tests for Module Class
 *
 * @group      phprojekt
 * @group      module
 * @group      phprojekt-module
 */
class Phprojekt_ModuleTest extends DatabaseTest
{
    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/data.xml');
    }

    /**
     * Test getId
     */
    public function testGetId()
    {
        $this->assertEquals(1, Phprojekt_Module::getId('Project'));
    }


    /**
     * Test getModuleName
     */
    public function testGetModuleName()
    {
        $this->assertEquals('Project', Phprojekt_Module::getModuleName(1));
    }
}
