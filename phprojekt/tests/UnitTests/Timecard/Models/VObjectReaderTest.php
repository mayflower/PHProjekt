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
 * @copyright  Copyright (c) 2012 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * Tests Timecard Model VObjectReader class
 *
 * @group      timecard
 * @group      model
 * @group      timecard-model
 * @group      vobject
 */
class Timecard_Models_VObjectReader_Test extends PHPUnit_Framework_TestCase
{
    public function testReadFailsOnWrongType()
    {
        $this->setExpectedException('InvalidArgumentException');
        Timecard_Models_VObjectReader::read(new Sabre_VObject_Component('vcalendar'));
    }
    public function testReadWithExistingFailsOnWrongType()
    {
        $this->setExpectedException('InvalidArgumentException');
        Timecard_Models_VObjectReader::readBasedOnExistingTimecard(
            new Timecard_Models_Timecard(),
            new Sabre_VObject_Component('vcalendar')
        );
    }

    private function _callRead(array $options)
    {
        return Timecard_Models_VObjectReader::read($this->_generateVEvent($options));
    }

    private function _callReadFromExisting(Timecard_Models_Timecard $timecard, array $options)
    {
        return Timecard_Models_VObjectReader::readBasedOnExistingTimecard(
            $timecard,
            $this->_generateVEvent($options)
        );
    }

    private function _generateVEvent(array $options)
    {
        $defaults = array(
            'uid' => 'default uid, dont use in tests',
            'summary' => 'Default summary, dont use in tests',
            'start'   => '20100102T150000Z',
            'end'     => '20100102T180000Z'
        );
        $options        = array_merge($defaults, $options);
        $timezonestring = array_key_exists('tzid', $options) ? ";TZID={$options['tzid']}" : '';

        $vevent = <<<HERE
BEGIN:VEVENT
UID:{$options['uid']}
SUMMARY{$timezonestring}:{$options['summary']}
DTSTART{$timezonestring}:{$options['start']}
DTEND:{$options['end']}
HERE;
        if (array_key_exists('description', $options)) {
            $vevent .= "\nDESCRIPTION:" . $options['description'];
        }
        $vevent .= "\nEND:VEVENT";
        return Sabre_VObject_Reader::read($vevent);
    }

    public function testFromVObjectPutsSummaryInNotes()
    {
        $this->assertStringStartsWith('a summary', $this->_callRead(array('summary' => 'a summary'))->notes);
    }

    public function testFromVObjectUsesDescription()
    {
        $this->assertStringEndsWith(
            'This is a nice description',
            $this->_callRead(array('description' => 'This is a nice description'))->notes
        );
    }

    public function testFromVObjectUsesUid()
    {
        $this->assertEquals('testuid', $this->_callRead(array('uid' => 'testuid'))->uid);
    }

    // Usertime is +1
    public function testFromVObjectUsesDTStartWithZ()
    {
        $this->assertEquals(
            '2000-01-01 09:00:00',
            $this->_callRead(array('start' => '20000101T080000Z'))->startDatetime
        );
    }

    public function testFromVObjectUsesDTEndWithZ()
    {
        $this->assertEquals(
            '13:00:00',
            $this->_callRead(array('end' => '20000101T120000Z', 'start' => '20000101T080000Z'))->endTime
        );
    }

    public function testExceptionOnEndBeforeStart()
    {
        $this->setExpectedException('Sabre_DAV_Exception_BadRequest');
        $this->_callRead(array('end' => '20000101T080000Z', 'start' => '20000101T120000Z'));
    }

    public function testFromVObjectUsesDTStartWithTzId()
    {
        $timecard = $this->_callRead(
            array(
                'tzid'  => 'America/New_York',
                'start' => '20000101T080000',
                'end'   => '20000101T120000'
            )
        );

        $this->assertEquals('2000-01-01 14:00:00', $timecard->startDatetime);
    }

    public function testFromVObjectUsesDTEndWithTzId()
    {
        $timecard = $this->_callRead(
            array(
                'tzid'  => 'America/New_York',
                'start' => '20000101T080000',
                'end'   => '20000101T120000'
            )
        );

        $this->assertEquals('18:00:00', $timecard->endTime);
    }

    public function testFromVObjectWithEndOnAnotherDay()
    {
        $tc = $this->_callRead(array('start' => '20000101T080000Z', 'end' => '20000102T120000Z'));
        $this->assertEquals('2000-01-01 09:00:00', $tc->startDatetime);
        $this->assertEquals('23:59:00', $tc->endTime);
    }

    public function testFromVObjectWithLocalTime()
    {
        $tc = $this->_callRead(array('start' => '20000101T080000', 'end' => '20000101T120000'));
        $this->assertEquals('2000-01-01 08:00:00', $tc->startDatetime);
        $this->assertEquals('12:00:00', $tc->endTime);
    }

    public function testFromVObjectSettingProjectViaId()
    {
        $tc = $this->_callRead(array('summary' => '6'));
        $this->assertEquals(6, $tc->projectId);
    }

    public function testFromVObjectSettingProjectViaNonexistantId()
    {
        $tc = $this->_callRead(array('summary' => '123456789'));
        $this->assertEquals(null, $tc->projectId);
        $this->assertStringStartsWith('123456789', $tc->notes);
    }

    public function testFromVObjectSettingProjectViaName()
    {
        $tc = $this->_callRead(array('summary' => 'Sub Sub Project 2'));
        $this->assertEquals(7, $tc->projectId);
    }

    public function testFromVObjectAddsSummaryToNotes()
    {
        $tc = $this->_callRead(
            array(
                'summary'     => 'a nice summary here',
                'description' => 'foo'
            )
        );
        $this->assertEquals("a nice summary here\nfoo", $tc->notes);
    }

    public function testFromVObjectDoesntAddCurrentProjectNameToNotes()
    {
        $tc = new Timecard_Models_Timecard();
        $tc->projectId = 7;
        $tc = $this->_callReadFromExisting(
            $tc,
            array(
                'summary'     => 'Sub Sub Project 2 [7]',
                'description' => 'foo'
            )
        );
        $this->assertEquals('foo', $tc->notes);
    }

    public function testFromVObjectRemovesOldNotes()
    {
        $tc = new Timecard_Models_Timecard();
        $tc->notes = 'notes';
        $tc = $this->_callReadFromExisting($tc, array('summary' => 'something', 'description' => 'foo'));
        $this->assertEquals("something\nfoo", $tc->notes);
    }

    public function testFromVObjectRecognizesUnassigned()
    {
        $tc = new Timecard_Models_Timecard();
        $tc->projectId = 7;
        $tc = $this->_callReadFromExisting($tc, array('summary' => 'Unassigned'));
        $this->assertEquals(1, $tc->projectId);
    }

    public function testFromVObjectDoesntAddUnassignedToNotes()
    {
        $tc = new Timecard_Models_Timecard();
        $tc->projectId = 1;
        $tc = $this->_callReadFromExisting($tc, array('summary' => 'Unassigned', 'description' => 'foo'));
        $this->assertEquals('foo', $tc->notes);
    }

    public function testFromVObjectPrefersIdOverName()
    {
        $tc = $this->_callRead(array('summary' => 'Sub Sub Project 2 [2]'));
        $this->assertEquals(2, $tc->projectId);
    }
}

