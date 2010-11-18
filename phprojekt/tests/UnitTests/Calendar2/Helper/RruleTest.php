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
 * @subpackage Calendar2
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: @package_version@
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 */

require_once 'phprojekt/application/Calendar2/Helper/Rrule.php';

/**
 * Tests for Calendar Index Controller
 *
 * @category   PHProjekt
 * @package    UnitTests
 * @subpackage Calendar
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: @package_version@
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 * @group      calendar2
 * @group      helper
 * @group      calendar2-helper
 */
class Calendar2_Helper_Rrule_Test extends PHPUnit_Framework_TestCase
{
    public function testGetDatesInPeriod()
    {
        $tz = new DateTimeZone('UTC');
        $first = new Datetime('2010-10-09 08:00:00', $tz);
        $rrule = 'FREQ=DAILY;UNTIL=20101114T080000Z;INTERVAL=1';
        $expected = array(
            new Datetime('2010-10-12 08:00:00', $tz),
            new Datetime('2010-10-13 08:00:00', $tz),
            new Datetime('2010-10-14 08:00:00', $tz),
            new Datetime('2010-10-15 08:00:00', $tz)
        );

        $helper = new Calendar2_Helper_Rrule($first, $rrule);
        $start  = new Datetime('2010-10-11 10:00:00', $tz);
        $end    = new Datetime('2010-10-15 10:00:00', $tz);
        $actual  = $helper->getDatesInPeriod($start, $end);

        $this->assertEquals($expected, $actual);
    }
}
