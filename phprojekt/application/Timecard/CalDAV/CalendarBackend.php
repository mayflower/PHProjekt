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
 * Timecard Caldav Calendarbackend
 *
 * This class implements a calendar backend for sabredav
 */
class Timecard_CalDAV_CalendarBackend extends Sabre_CalDAV_Backend_Abstract
{
    /**
     * Implements getCalendarsForUser from Sabre_CalDAV_Backend_Abstract
     *
     * This is simplified for PHProjekt. We only have one calendar per user, so we return hard-coded data based on the
     * user name. The id of the calendar is the id of the user it belongs to.
     *
     * @param string $principalUri The uri of the user whose calendar to get
     *
     * @return array calendar description
     */
    public function getCalendarsForUser($principalUri)
    {
        // We have exactly one calendar per principal.
        $user     = new Phprojekt_User_User();
        $username = preg_filter('|.*principals/([^/]+)$|', '$1', $principalUri);
        $user     = $user->findByUsername($username);
        if (is_null($user)) {
            throw new Exception("principal not found under $principalUri when retrieving calendars for $username");
        }
        return array(
            array(
                'id'                => $user->id,
                'uri'               => 'default',
                'principaluri'      => $principalUri,
                '{DAV:}displayname' => 'default',
                '{http://apple.com/ns/ical/}calendar-color'               => 'blue',
                '{http://apple.com/ns/ical/}calendar-order'               => 0,
                '{' . Sabre_CalDAV_Plugin::NS_CALENDARSERVER . '}getctag' => time(),
                '{' . Sabre_CalDAV_Plugin::NS_CALDAV . '}supported-calendar-component-set'
                    => new Sabre_CalDAV_Property_SupportedCalendarComponentSet(array('VEVENT'))
            )
        );
    }

    /**
     * As defined in Sabre_CalDAV_Backend_Abstract
     */
    public function createCalendar($principalUri, $calendarUri, array $properties)
    {
        throw new Sabre_DAV_Exception_NotImplemented('Creation of new calendars is not supported by PHProjekt.');
    }

    /**
     * As defined in Sabre_CalDAV_Backend_Abstract
     *
     * We don't support operations on calendars.
     */
    public function updateCalendar($calendarId, array $properties)
    {
        throw new Sabre_DAV_Exception_NotImplemented('Altering calendars is not supported by PHProjekt.');
    }

    /**
     * As defined in Sabre_CalDAV_Backend_Abstract
     *
     * We don't support operations on calendars.
     */
    public function deleteCalendar($calendarId)
    {
        throw new Sabre_DAV_Exception_NotImplemented('Deleting calendars is not supported by PHProjekt.');
    }

    /**
     * As defined in Sabre_CalDAV_Backend_Abstract
     * 
     * Returns all calendar objects for the given calendar id.
     * We don't return all calendar data here.
     *
     * @param string $calendarId The id of the calendar to retrieve. Corresponds to the id of the user it belongs to.
     *
     * @return array CalendarObject data as specified by SabreDAV.
     */
    public function getCalendarObjects($calendarId)
    {
        $timecards = Phprojekt::getInstance()->getDb()->select()
            ->from(
                array('t' => 'timecard'),
                array('id', 'start_datetime', 'end_time', 'notes', 'uri', 'uid', 'project_id', 'module_id')
            )
            ->joinLeft(array('p' => 'project'), 'p.id = t.project_id', array('title'))
            ->joinLeft(array('m' => 'module'), 'm.id = t.module_id', array('label'))
            ->where('t.owner_id = ?', Phprojekt_Auth_Proxy::getEffectiveUserId())
            ->query()->fetchAll();

        $now = new Datetime();
        $now = $now->getTimestamp();

        $ret = array();
        foreach ($timecards as $entry) {
            $ret[] = array(
                'id'           => $entry['id'],
                'uri'          => $entry['uri'],
                'lastmodified' => $now,
                'calendarid'   => $calendarId,
                'calendardata' => $this->_getDataForEntry($entry)
            );
        }

        return $ret;
    }

    /**
     * As defined in Sabre_CalDAV_Backend_Abstract
     *
     * Retrieves a single Calendar object.
     *
     * @param string $calendarId The id of the calendar. Corresponds to the id of the user it belongs to.
     * @param string $objectUri  The uri of the calendarobject to retrieve.
     *
     * @return array As specified by SabreDAV.
     */
    public function getCalendarObject($calendarId, $objectUri)
    {
        $entry = Phprojekt::getInstance()->getDb()->select()
            ->from(
                array('t' => 'timecard'),
                array('id', 'start_datetime', 'end_time', 'notes', 'module_id', 'uri', 'uid', 'project_id')
            )
            ->joinLeft(array('p' => 'project'), 'p.id = t.project_id', array('title'))
            ->joinLeft(array('m' => 'module'), 'm.id = t.module_id', array('label'))
            ->where('t.owner_id = ?', Phprojekt_Auth_Proxy::getEffectiveUserId())
            ->where('t.uri = ?', $objectUri)
            ->query()->fetch();

        if (!$entry) {
            throw new Sabre_DAV_Exception_NotFound("Timecard entry with uri $objectUri not found");
        }

        $now = new Datetime();
        $now = $now->format('Ymd\THis\Z');

        return array(
            'id'           => $entry['id'],
            'uri'          => $entry['uri'],
            'lastmodified' => $now,
            'calendarid'   => $calendarId,
            'calendardata' => $this->_getDataForEntry($entry)
        );
    }

    /**
     * Converts a timecard join project join module row to a vobject string.
     *
     * @param array $entry
     *
     * @return string
     */
    private function _getDataForEntry(array $entry)
    {
        $v = new Sabre_VObject_Component('vevent');
        if (1 == $entry['project_id']) {
            $v->add('summary', Phprojekt::getInstance()->translate('Unassigned'));
        } else {
            $v->add('summary', $entry['title'] . ' [' . $entry['project_id'] . ']');
        }

        $notes = trim($entry['notes']);
        if (!is_null($entry['module_id'])) {
            if ($notes) {
                $notes .= "\n";
            }
            $notes .= Phprojekt::getInstance()->translate('There is an attachment of type ')
                . Phprojekt::getInstance()->translate($entry['label']);
        }

        if ($notes) {
            $v->add('description', $notes);
        }

        $start = new DateTime('@' . Phprojekt_Converter_Time::userToUtc($entry['start_datetime']));
        $end   = substr($entry['start_datetime'], 0, 11) . $entry['end_time'];
        $end   = new DateTime('@' . Phprojekt_Converter_Time::userToUtc($end));

        $v->add('dtstart', $start->format('Ymd\THis\Z'));
        $v->add('dtend', $end->format('Ymd\THis\Z'));

        $v->add('uid', 'phprojekt-timecard-entry' . $entry['uid']);

        $calendarData = new Sabre_VObject_Component('vcalendar');
        $calendarData->add('version', '2.0');
        $calendarData->add('prodid', 'Phprojekt ' . Phprojekt::getVersion());
        $calendarData->add($v);

        return $calendarData->serialize();
    }

    /**
     * As defined in Sabre_CalDAV_Backend_Abstract
     *
     * Creates a new calendar object from the given data.
     *
     * @param string $calendarId   The id of the calendar. Equals to the id of the user it belongs to.
     * @param string $objectUri    The uri that the new object should have.
     * @param string $calendarData The vobject data for the new calendar object.
     *
     * @return void
     */
    public function createCalendarObject($calendarId, $objectUri, $calendarData)
    {
        $vcalendar = Sabre_VObject_Reader::read($calendarData);
        $timecard  = Timecard_Models_VObjectReader::read($vcalendar->vevent);

        $timecard->projectId = 1;
        $timecard->ownerId   = Phprojekt_Auth_Proxy::getEffectiveUserId();
        $timecard->uri       = $objectUri;
        $timecard->save();
    }

    /**
     * As defined in Sabre_CalDAV_Backend_Abstract
     *
     * Alters a calendar object. This is currently not supported if the event is recurring and any occurrences have been
     * modified in PHProjekt or if this operation would modify any speficic occurrences.
     *
     * @param string $calendarId   The id of the calendar. Equals to the id of the user it belongs to.
     * @param string $objectUri    The uri of the object.
     * @param string $calendarData The vobject data that the object should be modified to.
     *
     * @return void
     */
    public function updateCalendarObject($calendarId, $objectUri, $calendarData)
    {
        $vcalendar = Sabre_VObject_Reader::read($calendarData);
        $timecard = new Timecard_Models_Timecard();
        $timecard = $timecard->findByUri($objectUri);

        if (!$timecard) {
            throw new Sabre_DAV_Exception_NotFound("Timecard entry with uri $objectUri not found");
        }

        if ($timecard->ownerId != Phprojekt_Auth_Proxy::getEffectiveUserId()) {
            throw new Sabre_DAV_Exception_Forbidden("You are not allowed to modify this entry");
        }

        $timecard = Timecard_Models_VObjectReader::readBasedOnExistingTimecard($timecard, $vcalendar->vevent);
        $timecard->save();
    }

    /**
     * As defined in Sabre_CalDAV_Backend_Abstract
     *
     * Deletes the object.
     *
     * @param string $calendarId   The id of the calendar. Equals to the id of the user it belongs to.
     * @param string $objectUri    The uri of the object.
     *
     * @return void
     */
    public function deleteCalendarObject($calendarId, $objectUri)
    {
        $timecard = new Timecard_Models_Timecard();
        $timecard = $timecard->findByUri($objectUri);

        if (!$timecard) {
            throw new Sabre_DAV_Exception_NotFound("Timecard entry with uri $objectUri not found");
        }

        $timecard->delete();
    }
}
