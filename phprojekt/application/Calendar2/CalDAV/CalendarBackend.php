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
 * Calendar2 Caldav Calendarbackend
 *
 * This class implements a calendar backend for sabredav
 */
class Calendar2_CalDAV_CalendarBackend extends Sabre_CalDAV_Backend_Abstract
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
        $user = new Phprojekt_User_User();
        $user = $user->findByUsername(preg_filter('|.*principals/([^/]+)$|', '$1', $principalUri));
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
        $db = Phprojekt::getInstance()->getDb();
        $calendar = new Calendar2_Models_Calendar2();
        $where    = $db->quoteInto('u.user_id = ?', $calendarId);
        $join     = 'JOIN calendar2_user_relation AS u ON calendar2.id = u.calendar2_id';
        $events   = $calendar->fetchAll($where, 'uri', null, null, null, $join);

        if (empty($events)) {
            return array();
        }

        $ret = array();

        $currentUri = $events[0]->uri;
        $group      = array(array_shift($events));
        while (!empty($events)) {
            $e = array_shift($events);
            if ($e->uri === $currentUri) {
                $group[] = $e;
            } else {
                $ret[] = $this->getCalendarObjectsGroup($calendarId, $group);

                $currentUri = $e->uri;
                $group      = array($e);
            }
        }
        $ret[] = $this->getCalendarObjectsGroup($calendarId, $group);

        return $ret;
    }

    /**
     * Helper function to get the iCalendar representation of a group of events with a common uri.
     *
     * @param string $calendarId                 The calendarId to use.
     * @param array of Calendar2_Models_Calendar The events to convert. Must have the same ->uri.
     *
     * @return array The representation to use for SabreDav.
     */
    private function getCalendarObjectsGroup($calendarId, $group)
    {
        $calendarData = new Sabre_VObject_Component('vcalendar');
        $calendarData->add('version', '2.0');
        $calendarData->add('prodid', 'Phprojekt ' . Phprojekt::getVersion());
        $lastModified = $group[0]->lastModified;
        foreach ($group as $event) {
            $calendarData->add($event->asVObject($group));
            $lastModified = max($lastModified, $event->lastModified);
        }

        return array(
            'id'           => $group[0]->uid,
            'uri'          => $group[0]->uri,
            'lastmodified' => $lastModified,
            'calendarid'   => $calendarId,
            'calendardata' => $calendarData->serialize()
        );
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
        $db = Phprojekt::getInstance()->getDb();
        $events = new Calendar2_Models_Calendar2();
        $events = $events->fetchAll($db->quoteInto('uri = ?', $objectUri));
        if (!is_array($events) || empty($events)) {
            return array();
        }

        $calendarData = new Sabre_VObject_Component('vcalendar');
        $calendarData->add('version', '2.0');
        $calendarData->add('prodid', 'Phprojekt ' . Phprojekt::getVersion());
        $lastModified = $events[0]->lastModified;
        foreach ($events as $e) {
            $calendarData->add($e->asVObject($events));
            $lastModified = max($lastModified, $e->lastModified);
        }
        $lastModified = new Datetime($lastModified);


        return array(
            'id'           => $events[0]->uid,
            'uri'          => $objectUri,
            'lastmodified' => $lastModified->format('Ymd\THis\Z'),
            'calendarid'   => $calendarId,
            'calendardata' => $calendarData->serialize()
        );
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
        $event     = new Calendar2_Models_Calendar2();
        $event->fromVObject($vcalendar->vevent);
        $event->uri = $objectUri;
        $event->save();
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
        $db    = Phprojekt::getInstance()->getDb();
        $events = new Calendar2_Models_Calendar2();
        $events = $events->fetchAll($db->quoteInto('uri = ?', $objectUri));
        if (!$events) {
            throw new Sabre_DAV_Exception_FileNotFound("Nothing found under uri $objectUri");
        }
        if (count($events) > 1) {
            throw new Sabre_DAV_Exception_NotImplemented('Cannot alter events with modified occurrences');
        }
        $vevent = Sabre_VObject_Reader::read($calendarData)->vevent;
        if ($vevent->count() > 1) {
            throw new Sabre_DAV_Exception_NotImplemented('Cannot update specific occurrences');
        }
        $events[0]->fromVObject($vevent);
        $events[0]->save();
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
        $db    = Phprojekt::getInstance()->getDb();
        $event = new Calendar2_Models_Calendar2();
        $event = $event->fetchAll($db->quoteInto('uri = ?', $objectUri));
        if (!array_key_exists(0, $event)) {
            Phprojekt::getInstance()->getLog()->debug("Could not delete object with uri $objectUri.");
        }
        $event[0]->delete();
    }
}
