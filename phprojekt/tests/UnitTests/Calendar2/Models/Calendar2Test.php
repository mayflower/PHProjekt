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
 * @copyright  Copyright (c) 2011 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: @package_version@
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 */


/**
 * Tests Calendar2 Model class
 *
 * @category   PHProjekt
 * @package    UnitTests
 * @subpackage Calendar2
 * @copyright  Copyright (c) 2011 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: @package_version@
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 * @group      calendar
 * @group      calendar2
 * @group      model
 * @group      calendar-model
 * @group      calendar2-model
 */
class Calendar2_Models_Calendar2_Test extends PHPUnit_Framework_TestCase
{
    /**
     * Test fromVObject with duration.
     *
     */
    public function testFromVObject()
    {
        $calendarData = <<<HERE
BEGIN:VCALENDAR
VERSION:2.0
CALSCALE:GREGORIAN
PRODID:-//Pentabarf//Schedule//EN
BEGIN:VEVENT
DURATION:PT1H00M
LOCATION:Saal 1
SEQUENCE:0
URL:http://events.ccc.de/congress/2011/Fahrplan/events/4816.en.html
DTSTART;TZID=Europe/Berlin:20111229T160000
UID:4816@28C3@pentabarf.org
DTSTAMP:20111206T185008
CATEGORIES:Lecture
DESCRIPTION:Some
  Description
SUMMARY:the summary is
  here
STATUS:CONFIRMED
END:VEVENT
END:VCALENDAR
HERE;
        $vcalendar = Sabre_VObject_Reader::read($calendarData);
        $model = new Calendar2_Models_Calendar2();
        $model->fromVObject($vcalendar->vevent);
        $this->assertEquals('Saal 1', $model->location);
        $this->assertEquals('Some Description', $model->description);
        $this->assertEquals('the summary is here', $model->summary);
        // User timezone is utc in tests, so 1500 is correct
        $this->assertEquals('2011-12-29 15:00:00', $model->start);
        $this->assertEquals('2011-12-29 16:00:00', $model->end);
    }
}
