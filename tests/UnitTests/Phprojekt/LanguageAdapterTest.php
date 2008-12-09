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
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    CVS: $Id:
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */
require_once 'PHPUnit/Framework.php';

/**
 * Tests for Language Adapter
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_LanguageAdapterTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test the load function
     */
    public function testIsLoaded()
    {
        /* The adapter do not load the file for it self */
        $adapter = new Phprojekt_LanguageAdapter('es');
        $this->assertFalse($adapter->isLoaded('es'));
        $this->assertFalse($adapter->isLoaded('de'));

        /* The language class must load the files needed */
        $lang = new Phprojekt_Language('es');
        $this->assertTrue($lang->_adapter->isLoaded('es'));
        $this->assertFalse($lang->_adapter->isLoaded('de'));
    }

    /**
     * Test name of the class
     */
    public function testToString()
    {
        $lang   = new Phprojekt_LanguageAdapter('es');
        $string = $lang->toString();
        $this->assertEquals('Phprojekt', $string);
    }

    /**
     * Test all the lang files using the const defined
     */
    public function testAllFiles()
    {
        $reflect = new ReflectionClass('Phprojekt_LanguageAdapter');
        $constants = $reflect->getConstants();
        foreach ($constants as $value) {
            if (strstr($value, 'inc.php')) {
                $value = ereg_replace('.inc.php', '', $value);
                new Phprojekt_Language($value);
            }
        }
    }
}
