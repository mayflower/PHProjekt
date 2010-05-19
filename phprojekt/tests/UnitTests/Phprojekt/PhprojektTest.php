<?php
/**
 * Unit test
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @category   PHProjekt
 * @package    UnitTests
 * @subpackage Phprojekt
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */

require_once 'PHPUnit/Framework.php';

/**
 * Tests for database manager
 *
 * @category   PHProjekt
 * @package    UnitTests
 * @subpackage Phprojekt
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 * @group      phprojekt
 * @group      main
 * @group      phprojekt-main
 */
class Phprojekt_PhprojektTest extends PHPUnit_Framework_TestCase
{
    public function testGetVersion()
    {
        $this->assertEquals("6.0.2", Phprojekt::getVersion());
    }

    public function testCompareVersion()
    {
        $this->assertGreaterThan(0, Phprojekt::compareVersion("6.0.10", Phprojekt::getVersion()));
        $this->assertGreaterThan(0, Phprojekt::compareVersion("6.0.1", "6.0.0"));
        $this->assertLessThan(0, Phprojekt::compareVersion("6.0.1", "6.1.0"));
        $this->assertGreaterThan(0, Phprojekt::compareVersion("6.0.1-RC2", "6.0.1-RC1"));
        $this->assertLessThan(0, Phprojekt::compareVersion("6.0.0-RC1", "6.0.0"));
        $this->assertEquals(0, Phprojekt::compareVersion("6.0.0-RC1", "6.0.0-RC1"));
        $this->assertEquals(0, Phprojekt::compareVersion("6.0.1", "6.0.1"));
    }
}
