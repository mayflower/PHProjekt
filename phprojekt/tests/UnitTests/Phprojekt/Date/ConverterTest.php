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
 * Tests Phprojekt Date Collection class
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_Date_ConverterTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test simple daily recurrence
     */
    public function testParseIsoDateTime()
    {
        $converter = new Phprojekt_Date_Converter();
        $date = '20090512T230000';
        $this->assertEquals('2009-05-12', date("Y-m-d", $converter->parseIsoDateTime($date)->get()));

        $date   = '20090512T230000';
        $return = date("Y-m-d", $converter->parseIsoDateTime($date, 'America/Argentina/Buenos_Aires')->get());
        $this->assertEquals('2009-05-13', $return);
    }

    /**
     * Test convertMinutesToHours
     */
    public function testConvertMinutesToHours()
    {
        $converter = new Phprojekt_Date_Converter();
        $this->assertEquals('00:20', $converter->convertMinutesToHours(20));
        $this->assertEquals('01:20', $converter->convertMinutesToHours(80));
        $this->assertEquals('10:05', $converter->convertMinutesToHours(605));
    }
}
