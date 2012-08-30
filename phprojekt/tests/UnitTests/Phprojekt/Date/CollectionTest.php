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
 * Tests Phprojekt Date Collection class
 *
 * @group      phprojekt
 * @group      date
 * @group      phprojekt-date
 */
class Phprojekt_Date_CollectionTest extends PHPUnit_Framework_TestCase
{
    private $_collection = null;
    private $_startDate  = '2009-05-21';

    /**
     * setUp method for PHPUnit
     */
    public function setUp()
    {
        $this->_collection = new Phprojekt_Date_Collection($this->_startDate);
    }

    /**
     * Test simple daily recurrence
     */
    public function testDaily()
    {
        $collection = clone($this->_collection);
        $rrule      = 'FREQ=DAILY;UNTIL=20090524T110000Z;INTERVAL=1;BYDAY=';
        $return     = $collection->applyRrule($rrule);
        $this->assertTrue($return);

        $eventDates = $collection->getValues();
        $this->assertEquals(4, count($eventDates));

        $date = array();
        foreach ($eventDates as $oneDate) {
            $date[] = date("Y-m-d", $oneDate);
        }
        $this->assertEquals('2009-05-21', $date[0]);
        $this->assertEquals('2009-05-22', $date[1]);
        $this->assertEquals('2009-05-23', $date[2]);
        $this->assertEquals('2009-05-24', $date[3]);
    }

    /**
     * Test simple weekly recurrence
     */
    public function testWeekly()
    {
        $collection = clone($this->_collection);
        $rrule      = 'FREQ=WEEKLY;UNTIL=20090625T110000Z;INTERVAL=1;BYDAY=';
        $return     = $collection->applyRrule($rrule);
        $this->assertTrue($return);

        $eventDates = $collection->getValues();
        $this->assertEquals(6, count($eventDates));

        $date = array();
        foreach ($eventDates as $oneDate) {
            $date[] = date("Y-m-d", $oneDate);
        }
        $this->assertEquals('2009-05-21', $date[0]);
        $this->assertEquals('2009-05-28', $date[1]);
        $this->assertEquals('2009-06-04', $date[2]);
        $this->assertEquals('2009-06-11', $date[3]);
        $this->assertEquals('2009-06-18', $date[4]);
        $this->assertEquals('2009-06-25', $date[5]);
    }

    /**
     * Test simple monthly recurrence
     */
    public function testMonthly()
    {
        $collection = clone($this->_collection);
        $rrule      = 'FREQ=MONTHLY;UNTIL=20091021T110000Z;INTERVAL=1;BYDAY=';
        $return     = $collection->applyRrule($rrule);
        $this->assertTrue($return);

        $eventDates = $collection->getValues();
        $this->assertEquals(6, count($eventDates));

        $date = array();
        foreach ($eventDates as $oneDate) {
            $date[] = date("Y-m-d", $oneDate);
        }
        $this->assertEquals('2009-05-21', $date[0]);
        $this->assertEquals('2009-06-21', $date[1]);
        $this->assertEquals('2009-07-21', $date[2]);
        $this->assertEquals('2009-08-21', $date[3]);
        $this->assertEquals('2009-09-21', $date[4]);
        $this->assertEquals('2009-10-21', $date[5]);
    }

    /**
     * Test simple yearly recurrence
     */
    public function testYearly()
    {
        $collection = clone($this->_collection);
        $rrule      = 'FREQ=YEARLY;UNTIL=20110521T110000Z;INTERVAL=1;BYDAY=';
        $return     = $collection->applyRrule($rrule);
        $this->assertTrue($return);

        $eventDates = $collection->getValues();
        $this->assertEquals(3, count($eventDates));

        $date = array();
        foreach ($eventDates as $oneDate) {
            $date[] = date("Y-m-d", $oneDate);
        }
        $this->assertEquals('2009-05-21', $date[0]);
        $this->assertEquals('2010-05-21', $date[1]);
        $this->assertEquals('2011-05-21', $date[2]);
    }

    /**
     * Test Daily recurrence using Interval
     */
    public function testDailyInterval()
    {
        $collection = clone($this->_collection);
        $rrule      = 'FREQ=DAILY;UNTIL=20090529T110000Z;INTERVAL=3;BYDAY=';
        $return     = $collection->applyRrule($rrule);
        $this->assertTrue($return);

        $eventDates = $collection->getValues();
        $this->assertEquals(3, count($eventDates));

        $date = array();
        foreach ($eventDates as $oneDate) {
            $date[] = date("Y-m-d", $oneDate);
        }
        $this->assertEquals('2009-05-21', $date[0]);
        $this->assertEquals('2009-05-24', $date[1]);
        $this->assertEquals('2009-05-27', $date[2]);
    }

    /**
     * Test Weekly recurrence using Interval
     */
    public function testWeeklyInterval()
    {
        $collection = clone($this->_collection);
        $rrule      = 'FREQ=WEEKLY;UNTIL=20090621T110000Z;INTERVAL=2;BYDAY=';
        $return     = $collection->applyRrule($rrule);
        $this->assertTrue($return);

        $eventDates = $collection->getValues();
        $this->assertEquals(3, count($eventDates));

        $date = array();
        foreach ($eventDates as $oneDate) {
            $date[] = date("Y-m-d", $oneDate);
        }
        $this->assertEquals('2009-05-21', $date[0]);
        $this->assertEquals('2009-06-04', $date[1]);
        $this->assertEquals('2009-06-18', $date[2]);
    }

    /**
     * Test Monthly recurrence using Interval
     */
    public function testMonthlyInterval()
    {
        $collection = clone($this->_collection);
        $rrule      = 'FREQ=MONTHLY;UNTIL=20091020T110000Z;INTERVAL=2;BYDAY=';
        $return     = $collection->applyRrule($rrule);
        $this->assertTrue($return);

        $eventDates = $collection->getValues();
        $this->assertEquals(3, count($eventDates));

        $date = array();
        foreach ($eventDates as $oneDate) {
            $date[] = date("Y-m-d", $oneDate);
        }
        $this->assertEquals('2009-05-21', $date[0]);
        $this->assertEquals('2009-07-21', $date[1]);
        $this->assertEquals('2009-09-21', $date[2]);
    }

    /**
     * Test Yearly recurrence using Interval
     */
    public function testYearlyInterval()
    {
        $collection = clone($this->_collection);
        $rrule      = 'FREQ=YEARLY;UNTIL=20150520T110000Z;INTERVAL=2;BYDAY=';
        $return     = $collection->applyRrule($rrule);
        $this->assertTrue($return);

        $eventDates = $collection->getValues();
        $this->assertEquals(3, count($eventDates));

        $date = array();
        foreach ($eventDates as $oneDate) {
            $date[] = date("Y-m-d", $oneDate);
        }
        $this->assertEquals('2009-05-21', $date[0]);
        $this->assertEquals('2011-05-21', $date[1]);
        $this->assertEquals('2013-05-21', $date[2]);
    }

    /**
     * Test recurrence using Wrong frequence
     */
    public function testWrongFrequence()
    {
        $collection = clone($this->_collection);
        $rrule      = 'FREQ=SOMETIMES;UNTIL=20150520T110000Z;INTERVAL=2;BYDAY=';
        $return     = $collection->applyRrule($rrule);
        $this->assertFalse($return);
    }

    /**
     * Test 'filter' method
     */
    public function testFilter()
    {
        $collection = clone($this->_collection);
        $rrule      = 'FREQ=DAILY;UNTIL=20090525T110000Z;INTERVAL=1;BYDAY=';
        $return     = $collection->applyRrule($rrule);
        $this->assertTrue($return);

        $removeEvents = array(strtotime('2009-05-22'),
                              strtotime('2009-05-23'));
        $collection->filter($removeEvents);
        $eventDates = $collection->getValues();
        $this->assertEquals(3, count($eventDates));

        $date = array();
        foreach ($eventDates as $oneDate) {
            $date[] = date("Y-m-d", $oneDate);
        }
        $this->assertEquals('2009-05-21', $date[0]);
        $this->assertEquals('2009-05-24', $date[1]);
        $this->assertEquals('2009-05-25', $date[2]);
    }

    /**
     * Test 'addArray' method
     */
    public function testAddArray()
    {
        $collection = clone($this->_collection);
        $rrule      = 'FREQ=DAILY;UNTIL=20090522T110000Z;INTERVAL=1;BYDAY=';
        $return     = $collection->applyRrule($rrule);
        $this->assertTrue($return);

        $extraEvents = array(strtotime('2009-05-30'),
                             strtotime('2009-05-31'));
        $collection->addArray($extraEvents);
        $eventDates = $collection->getValues();
        $this->assertEquals(4, count($eventDates));

        $date = array();
        foreach ($eventDates as $oneDate) {
            $date[] = date("Y-m-d", $oneDate);
        }
        $this->assertEquals('2009-05-21', $date[0]);
        $this->assertEquals('2009-05-22', $date[1]);
        $this->assertEquals('2009-05-30', $date[2]);
        $this->assertEquals('2009-05-31', $date[3]);
    }

    /**
     * Test 'add' method
     */
    public function testAdd()
    {
        $collection = clone($this->_collection);
        $rrule      = 'FREQ=DAILY;UNTIL=20090522T110000Z;INTERVAL=1;BYDAY=';
        $return     = $collection->applyRrule($rrule);
        $this->assertTrue($return);

        $extraEvent = strtotime('2009-05-30');
        $collection->add($extraEvent);
        $eventDates = $collection->getValues();
        $this->assertEquals(3, count($eventDates));

        $date = array();
        foreach ($eventDates as $oneDate) {
            $date[] = date("Y-m-d", $oneDate);
        }
        $this->assertEquals('2009-05-21', $date[0]);
        $this->assertEquals('2009-05-22', $date[1]);
        $this->assertEquals('2009-05-30', $date[2]);
    }

    /**
     * Test Weekly recurrence By day
     */
    public function testWeeklyByday()
    {
        $collection = clone($this->_collection);
        $rrule      = 'FREQ=WEEKLY;UNTIL=20090625T110000Z;INTERVAL=1;BYDAY=WE';
        $return     = $collection->applyRrule($rrule, true);
        $this->assertTrue($return);

        $eventDates = $collection->getValues();
        $this->assertEquals(5, count($eventDates));

        $date = array();
        foreach ($eventDates as $oneDate) {
            $date[] = date("Y-m-d", $oneDate);
        }
        $this->assertEquals('2009-05-27', $date[0]);
        $this->assertEquals('2009-06-03', $date[1]);
        $this->assertEquals('2009-06-10', $date[2]);
        $this->assertEquals('2009-06-17', $date[3]);
        $this->assertEquals('2009-06-24', $date[4]);
    }

    /**
     * Test Monthly recurrence By day
     */
    public function testMonthlyByday()
    {
        $collection = clone($this->_collection);
        $rrule      = 'FREQ=MONTHLY;UNTIL=20091220T110000Z;INTERVAL=2;BYDAY=MO';
        $return     = $collection->applyRrule($rrule, true);
        $this->assertTrue($return);

        $eventDates = $collection->getValues();
        $this->assertEquals(3, count($eventDates));

        $date = array();
        foreach ($eventDates as $oneDate) {
            $date[] = date("Y-m-d", $oneDate);
        }
        $this->assertEquals('2009-07-20', $date[0]);
        $this->assertEquals('2009-09-21', $date[1]);
        $this->assertEquals('2009-11-16', $date[2]);
    }

    /**
     * Test Weekly recurrence By days Monday, Wednesday and Friday
     */
    public function testWeeklyBydayComplex()
    {
        $collection = clone($this->_collection);
        $rrule      = 'FREQ=WEEKLY;UNTIL=20090625T110000Z;INTERVAL=1;BYDAY=MO,WE,FR';
        $return     = $collection->applyRrule($rrule, true);
        $this->assertTrue($return);

        $eventDates = $collection->getValues();
        $this->assertEquals(15, count($eventDates));

        $date = array();
        foreach ($eventDates as $oneDate) {
            $date[] = date("Y-m-d", $oneDate);
        }
        $this->assertEquals('2009-05-22', $date[0]);
        $this->assertEquals('2009-05-25', $date[1]);
        $this->assertEquals('2009-05-27', $date[2]);
        $this->assertEquals('2009-05-29', $date[3]);
        $this->assertEquals('2009-06-01', $date[4]);
        $this->assertEquals('2009-06-03', $date[5]);
        $this->assertEquals('2009-06-05', $date[6]);
        $this->assertEquals('2009-06-08', $date[7]);
        $this->assertEquals('2009-06-10', $date[8]);
        $this->assertEquals('2009-06-12', $date[9]);
        $this->assertEquals('2009-06-15', $date[10]);
        $this->assertEquals('2009-06-17', $date[11]);
        $this->assertEquals('2009-06-19', $date[12]);
        $this->assertEquals('2009-06-22', $date[13]);
        $this->assertEquals('2009-06-24', $date[14]);
    }

    /**
     * Test Weekly recurrence By days Wednesday and Friday producing a very specific result
     */
    public function testWeeklyBydayTricky()
    {
        $collection = clone($this->_collection);
        $rrule      = 'FREQ=WEEKLY;UNTIL=20090604T110000Z;INTERVAL=1;BYDAY=WE,FR';
        $return     = $collection->applyRrule($rrule, true);
        $this->assertTrue($return);

        $eventDates = $collection->getValues();
        $this->assertEquals(4, count($eventDates));

        $date = array();
        foreach ($eventDates as $oneDate) {
            $date[] = date("Y-m-d", $oneDate);
        }
        $this->assertEquals('2009-05-22', $date[0]);
        $this->assertEquals('2009-05-27', $date[1]);
        $this->assertEquals('2009-05-29', $date[2]);
        $this->assertEquals('2009-06-03', $date[3]);
    }

    /**
     * Test Weekly recurrence By days Wednesday producing no result
     */
    public function testWeeklyBydayTricky2()
    {
        $collection = clone($this->_collection);
        $rrule      = 'FREQ=WEEKLY;UNTIL=20090526T110000Z;INTERVAL=1;BYDAY=WE';
        $return     = $collection->applyRrule($rrule, true);
        $this->assertTrue($return);

        $eventDates = $collection->getValues();
        $this->assertEquals(0, count($eventDates));
    }

    /**
     * Test Yearly recurrence By day, hour, minute and second
     */
    public function testYearlyByDayHourMinute()
    {
        $collection = clone($this->_collection);
        $rrule      = 'FREQ=YEARLY;UNTIL=20140522T110000Z;INTERVAL=2;BYDAY=SA;BYHOUR=14;BYMINUTE=30';
        $return     = $collection->applyRrule($rrule, true);
        $this->assertTrue($return);

        $eventDates = $collection->getValues();
        $this->assertEquals(3, count($eventDates));

        $date = array();
        foreach ($eventDates as $oneDate) {
            $date[] = date("Y-m-d G:i:s", $oneDate);
        }
        $this->assertEquals('2009-05-23 14:30:00', $date[0]);
        $this->assertEquals('2011-05-21 14:30:00', $date[1]);
        $this->assertEquals('2013-05-25 14:30:00', $date[2]);
    }

    /**
     * Test Yearly recurrence By months April and October
     */
    public function testYearlyByMonth()
    {
        $collection = clone($this->_collection);
        $rrule      = 'FREQ=YEARLY;UNTIL=20140522T110000Z;INTERVAL=2;BYMONTH=4,10';
        $return     = $collection->applyRrule($rrule, true);
        $this->assertTrue($return);

        $eventDates = $collection->getValues();
        $this->assertEquals(5, count($eventDates));

        $date = array();
        foreach ($eventDates as $oneDate) {
            $date[] = date("Y-m-d", $oneDate);
        }
        $this->assertEquals('2009-10-21', $date[0]);
        $this->assertEquals('2011-04-21', $date[1]);
        $this->assertEquals('2011-10-21', $date[2]);
        $this->assertEquals('2013-04-21', $date[3]);
        $this->assertEquals('2013-10-21', $date[4]);
    }

    /**
     * Test recurrence without specifying UNTIL
     */
    public function testNotUntil()
    {
        $collection = clone($this->_collection);
        $rrule      = 'FREQ=DAILY;INTERVAL=1';
        $return     = $collection->applyRrule($rrule, true);
        $this->assertTrue($return);

        $eventDates = $collection->getValues();
        $this->assertEquals(1, count($eventDates));

        $date = array();
        foreach ($eventDates as $oneDate) {
            $date[] = date("Y-m-d", $oneDate);
        }
        $this->assertEquals('2009-05-21', $date[0]);
    }
}
