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
 * Tests Phprojekt Convert Time class
 *
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
