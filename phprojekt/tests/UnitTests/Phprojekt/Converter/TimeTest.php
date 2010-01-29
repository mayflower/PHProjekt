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
 * Tests Phprojekt Convert Time class
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 * @group      phprojekt
 * @group      converter
 * @group      time
 * @group      phprojekt-converter
 * @group      phprojekt-converter-time
 */
class Phprojekt_Converter_TimeTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test convertMinutesToHours
     */
    public function testConvertMinutesToHours()
    {
        $converter = new Phprojekt_Converter_Time();
        $this->assertEquals('00:20', $converter->convertMinutesToHours(20));
        $this->assertEquals('01:20', $converter->convertMinutesToHours(80));
        $this->assertEquals('10:05', $converter->convertMinutesToHours(605));
    }
}
