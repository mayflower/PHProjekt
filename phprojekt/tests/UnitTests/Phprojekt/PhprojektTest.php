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
 * @copyright  Copyright (c) 2009 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */
require_once 'PHPUnit/Framework.php';

/**
 * Tests for database manager
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     David Soria Parra <dsp@php.net>
 */
class Phprojekt_PhprojektTest extends PHPUnit_Framework_TestCase
{
    public function testGetVersion()
    {
        $this->assertEquals("6.0.0", Phprojekt::getVersion());
    }

    public function testCompareVersion()
    {
        $this->assertGreaterThan(0, Phprojekt::compareVersion("6.0.1", Phprojekt::getVersion()));
        $this->assertGreaterThan(0, Phprojekt::compareVersion("6.0.1", "6.0.0"));
        $this->assertLessThan(0, Phprojekt::compareVersion("6.0.1", "6.1.0"));
        $this->assertGreaterThan(0, Phprojekt::compareVersion("6.0.1-RC2", "6.0.1-RC1"));
        $this->assertLessThan(0, Phprojekt::compareVersion("6.0.0-RC1", "6.0.0"));
        $this->assertEquals(0, Phprojekt::compareVersion("6.0.0-RC1", "6.0.0-RC1"));
        $this->assertEquals(0, Phprojekt::compareVersion("6.0.1", "6.0.1"));
    }
}
