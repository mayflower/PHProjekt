<?php
/**
 * Calendar2 Caldav Calendarbackend
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
 * @package    Application
 * @subpackage Calendar2
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: @package_version@
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 */

/**
 * Calendar2 Caldav Calendarbackend
 *
 * This class implements a calendar backend for sabredav
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Calendar2
 * @copyright  Copyright (c) 2011 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1.0
 * @version    Release: @package_version@
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 */
class Calendar2_CalDAV_CalendarBackend extends Sabre_CalDAV_Backend_Abstract
{
    public function getCalendarsForUser($principalUri)
    {
        // We have exactly one calendar per principal.
        $user = new Phprojekt_User_User();
        $user = $user->findByUsername(preg_filter('|.*principals/([^/]+)$|', '$1', $principalUri));
        if (is_null($user)) {
            throw new Exception("principal not found under $principalUri when retrieving calendars, username $username");
        }
        return array(
            array(
                'id' => $user->id,
                'uri' => 'default',
                'principaluri' => $principalUri
            )
        );
    }

    public function createCalendar($principalUri, $calendarUri, array $properties)
    {
        throw new Exception('Calendar2_CalDAV_CalendarBackend->createCalendar is not implemented yet');
    }

    public function updateCalendar($calendarId, array $properties)
    {
        throw new Exception('Calendar2_CalDAV_CalendarBackend->updateCalendar is not implemented yet');
    }

    public function deleteCalendar($calendarId)
    {
        throw new Exception('Calendar2_CalDAV_CalendarBackend->deleteCalendar is not implemented yet');
    }

    public function getCalendarObjects($calendarId)
    {
        throw new Exception('Calendar2_CalDAV_CalendarBackend->getCalendarObjects is not implemented yet');
    }

    public function getCalendarObject($calendarId, $objectUri)
    {
        throw new Exception('Calendar2_CalDAV_CalendarBackend->getCalendarObject is not implemented yet');
    }

    public function createCalendarObject($calendarId, $objectUri, $calendarData)
    {
        throw new Exception('Calendar2_CalDAV_CalendarBackend->createCalendarObject is not implemented yet');
    }

    public function updateCalendarObject($calendarId, $objectUri, $calendarData)
    {
        throw new Exception('Calendar2_CalDAV_CalendarBackend->updateCalendarObject is not implemented yet');
    }

    public function deleteCalendarObject($calendarId, $objectUri)
    {
        throw new Exception('Calendar2_CalDAV_CalendarBackend->deleteCalendarObject is not implemented yet');
    }
}
