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
 * Utility class to read Timecard models from SabreDAV VObjects.
 */
class Timecard_Models_VObjectReader
{
    private $_timecard;
    private $_vevent;

    public static function read(Sabre_VObject_Component $vobject)
    {
        $reader = new self(new Timecard_Models_Timecard(), $vobject);
        return $reader->performRead();
    }

    public static function readBasedOnExistingTimecard(Timecard_Models_Timecard $timecard, Sabre_VObject_Component $vobject)
    {
        $reader = new self($timecard, $vobject);
        return $reader->performRead();
    }

    private function __construct(Timecard_Models_Timecard $base, Sabre_VObject_Component $vobject)
    {
        if (strtolower($vobject->name) !== 'vevent') {
            throw new InvalidArgumentException(
                "Invalid type of vobject_component passed to Calendar2_Models_Calendar2::fromVobject ({$vobject->name})"
            );
        }

        $this->_timecard = $base;
        $this->_vevent   = $vobject;
    }

    private function isVEvent(Sabre_VObject_Component $vobject)
    {
        return strtolower($vobject->name) == 'vevent';
    }

    private function performRead()
    {
        $this->_timecard->notes = '';

        $this->parseVEventSummary($this->_vevent->SUMMARY->value);
        if (isset($this->_vevent->DESCRIPTION)) {
            $this->addParagraphToNotes($this->_vevent->DESCRIPTION->value);
        }

        $this->_timecard->uid = $this->_vevent->uid->value;

        $this->applyICalendarTimes(
            $this->_vevent->dtstart->value,
            $this->_vevent->dtend->value,
            $this->_vevent->dtstart['tzid'] ? $this->_vevent->dtstart['tzid']->value : null
        );

        return $this->_timecard;
    }

    private function parseVEventSummary($summary)
    {
        if ($summary == Phprojekt::getInstance()->translate('Unassigned')) {
            $this->_timecard->projectId = 1;
            return;
        }

        if (is_numeric($summary) && $summary > 0) {
            $where = Phprojekt::getInstance()->getDb()->quoteInto('id = ?', intval($summary));
        } else {
            $matches = array();
            if (preg_match("/\[(\d+)\]$/", $summary, $matches)) {
                $where = Phprojekt::getInstance()->getDb()->quoteInto('id = ?', intval($matches[1]));
            } else {
                $where = Phprojekt::getInstance()->getDb()->quoteInto('title = ?', $summary);
            }
        }

        $project  = new Project_Models_Project();
        $projects = $project->fetchAll($where);
        if ($projects) {
            $this->_timecard->projectId = $projects[0]->id;
        } else {
            $this->addParagraphToNotes($summary);
        }
    }

    private function applyICalendarTimes($start, $end, $timezoneID = null)
    {
        $utc          = new DateTimezone('UTC');
        $timezone     = null;
        $userTimeZone = Phprojekt_User_User::getUserDateTimeZone();
        if ('Z' === substr($start, -1)) {
            $timezone = $utc;
        } else if (!is_null($timezoneID)) {
            $timezone = new DateTimeZone($timezoneID);
        } else {
            $timezone = $userTimeZone;
        }

        // We can't use ->setTimezone with the timezones returned by getUserDateTimeZone, as these are non-standard
        // timezones. Unless we start storing correct timezones, we can't directly set the user timezone, so we go to
        // UTC and convert to usertime from there. Because utcToUser returns a unix timestamp, but ActiveRecords expects
        // a "Y-m-d H:i:s" timestamp, we have to go through Datetime again.
        $start = new Datetime($start, $timezone);
        $start->setTimezone($utc);
        $startTs = Phprojekt_Converter_Time::utcToUser($start->format('Y-m-d H:i:s'));
        $start = new Datetime('@' . $startTs);
        $end   = new Datetime($end, $timezone);
        $end->setTimezone($utc);
        $endTs = Phprojekt_Converter_Time::utcToUser($end->format('Y-m-d H:i:s'));
        $end   = new Datetime('@' . $endTs);

        if ($start->diff($end)->invert) {
            throw new Sabre_DAV_Exception_BadRequest('Start must be before End');
        }

        $this->_timecard->startDatetime = $start->format('Y-m-d H:i:s');
        if ($start->format('z') == $end->format('z')) {
            // Same day
            $this->_timecard->endTime = $end->format('H:i:s');
        } else {
            $this->_timecard->endTime = '23:59:00';
        }
    }

    private function addParagraphToNotes($string)
    {
        if ($this->_timecard->notes) {
            $this->_timecard->notes .= "\n";
        }
        $this->_timecard->notes .= $string;
    }
}
