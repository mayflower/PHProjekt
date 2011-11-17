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
    /** The helper object to tests */
    private $helper;

    public function setUp()
    {
        $first  = new Datetime('2010-10-09 08:00:00 UTC');
        $rrule  = 'FREQ=DAILY;UNTIL=20101114T080000Z;INTERVAL=1';
        $except = array(
            new Datetime('2010-10-14 08:00:00 UTC'),
            new Datetime('2010-10-12 08:00:00 UTC'),
            new Datetime('2010-10-15 08:00:00 UTC')
        );

        $this->helper = new Calendar2_Helper_Rrule($first, $rrule, $except);
    }

    public function testGetDatesInPeriod()
    {
        $start  = new Datetime('2010-10-11 08:00:00 UTC');
        $end    = new Datetime('2010-10-17 08:00:00 UTC');
        $actual  = $this->helper->getDatesInPeriod($start, $end);

        $expected = array(
            new Datetime('2010-10-11 08:00:00 UTC'),
            new Datetime('2010-10-13 08:00:00 UTC'),
            new Datetime('2010-10-16 08:00:00 UTC'),
            new Datetime('2010-10-17 08:00:00 UTC')
        );
        $this->assertEquals($expected, $actual);
    }

    public function testContainsDate()
    {
        $contained = array(
            new Datetime('2010-10-09 08:00:00 UTC'),
            new Datetime('2010-10-19 08:00:00 UTC'),
            new Datetime('2010-11-03 08:00:00 UTC'),
            new Datetime('2010-11-14 08:00:00 UTC')
        );

        $notContained = array(
            new Datetime('2010-10-09 10:00:00 UTC'), // wrong time
            new Datetime('2010-10-19 08:00:01 UTC'), // second off
            new Datetime('2010-10-03 08:00:00 UTC'), // too early
            new Datetime('2010-11-15 08:00:00 UTC')  // too late
        );

        foreach ($contained as $date) {
            $this->assertTrue($this->helper->containsDate($date));
        }

        foreach ($notContained as $date){
            $this->assertFalse($this->helper->containsDate($date));
        }
    }

    public function testSplitRrule()
    {
        $split = $this->helper->splitRrule(
            new Datetime('2010-10-28 08:00:00 UTC')
        );

        $this->assertEquals(
            'FREQ=DAILY;UNTIL=20101027T080000Z;INTERVAL=1',
            $split['old']
        );
        $this->assertEquals(
            'FREQ=DAILY;UNTIL=20101114T080000Z;INTERVAL=1',
            $split['new']
        );
    }

    public function testIsLastOcurrence()
    {
        $last = new Datetime('2010-11-14 08:00:00 UTC');
        $this->assertTrue($this->helper->isLastOccurrence($last));
    }

    public function testIsFirstOccurrence()
    {
        $first = new Datetime('2010-10-09 08:00:00 UTC');
        $this->assertTrue($this->helper->isFirstOccurrence($first));
    }

    public function testFirstOccurrenceAfter()
    {
        $datetime = new Datetime('2010-11-02 08:00:00 UTC');
        $expected = new Datetime('2010-11-03 08:00:00 UTC');
        $actual   = $this->helper->firstOccurrenceAfter($datetime);
        $this->assertEquals($expected, $actual);

        // Now with an excluded date in between
        $datetime = new Datetime('2010-10-11 08:00:00 UTC');
        $expected = new Datetime('2010-10-13 08:00:00 UTC');
        $actual   = $this->helper->firstOccurrenceAfter($datetime);
        $this->assertEquals($expected, $actual);
    }

    public function testLastOccurrenceBefore()
    {
        $datetime = new Datetime('2010-11-02 08:00:00 UTC');
        $expected = new Datetime('2010-11-01 08:00:00 UTC');
        $actual   = $this->helper->lastOccurrenceBefore($datetime);
        $this->assertEquals($expected, $actual);

        // Now with an excluded date in between
        $datetime = new Datetime('2010-10-13 08:00:00 UTC');
        $expected = new Datetime('2010-10-11 08:00:00 UTC');
        $actual   = $this->helper->lastOccurrenceBefore($datetime);
        $this->assertEquals($expected, $actual);
    }

    public function testGetHumanReadableRrule()
    {
        $actual   = $this->helper->getHumanReadableRrule();
        $expected = 'Every day until 2010-11-14';
        $this->assertEquals($expected, $actual);
    }
}
