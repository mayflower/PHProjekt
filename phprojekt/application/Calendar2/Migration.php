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
 * Calendar2 Migration
 *
 * This is used to install the Cal2 tables and Convert the Calendar1 data to the
 * new format.
 */
class Calendar2_Migration extends Phprojekt_Migration_Abstract
{
    /**
     * The database on which to migrate
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db;

    /**
     * Return the current module version.
     *
     * Implements Phprojekt_Migration_Abstract->getCurrentModuleVersion
     *
     * @return String Version
     */
    public function getCurrentModuleVersion()
    {
        return '6.1.0-beta1';
    }

    /**
     * Upgrade to the latest version.
     *
     * @param String $currentVersion Phprojekt version string indicating our
     *                               current version
     * @param Zend_Db_Adapter_Abstract $db The database to use
     *
     * @return void
     * @throws Exception On Errors
     */
    public function upgrade($currentVersion, Zend_Db_Adapter_Abstract $db)
    {
        date_default_timezone_set('utc');
        $this->_db = $db;

        if (is_null($currentVersion)
                || Phprojekt::compareVersion($currentVersion, '6.1.0-beta1') < 0) {
            $this->parseDbFile('Calendar2');
            Phprojekt::getInstance()->getCache()->clean(Zend_Cache::CLEANING_MODE_ALL);
            $this->_migrateFromOldCalendar();
            $this->_removeOldCalendar();
        }
    }

    /**
     * Change this to true to enable (quite noisy) debug output for the
     * migration routines.
     */
    private $_debug = false;

    private $_oldCalId = null;
    private $_newCalId = null;
    private $_search   = null;

    private function _migrateFromOldCalendar()
    {
        $db = $this->_db;

        $this->_oldCalId = $db->select()->from('module')->where('name = "Calendar"')->query()->fetchColumn();
        if (!in_array('calendar', $db->listTables()) || empty($this->_oldCalId)) {
            throw new Exception('Old Calendar is gone, cannot migrate');
        }

        $this->_newCalId = $db->select()->from('module')->where('name = "Calendar"')->query()->fetchColumn();

        $this->_db->delete('calendar2');

        $this->_copyEvents();
        $this->_updateSingleChangedOccurrences();

        $this->_updateUsers();
        $this->_updateRights();
        $this->_updateLastEnd();

        $this->_updateTags();
        $this->_updateHistory();

        $this->_regenerateSearch();
    }

    private function _copyEvents()
    {
        $db = $this->_db;

        $db->query(
            'INSERT INTO calendar2 SELECT id, project_id, title AS summary, notes AS description, place AS
            location, "" AS comments, start_datetime AS start, NULL AS last_end, end_datetime AS end,
            owner_id, rrule, NULL AS recurrence_id, visibility, id AS uid, NOW() AS last_modified, id AS
            uri FROM calendar WHERE parent_id = 0 OR parent_id = id OR rrule != ""'
        );

        $db->query('UPDATE calendar2 SET end = start WHERE end < start');
    }

    private function _updateSingleChangedOccurrences()
    {
        if ($this->_debug) {
            Phprojekt::getInstance()->getLog()->debug('_updateSingleChangedOccurrences');
        }
        $limit = 500;
        $start = 0;

        $done = false;

        do {
            if ($this->_debug) {
                Phprojekt::getInstance()->getLog()->debug($start);
            }
            $entries = $this->_db->select()->from(array('c' => 'calendar'))
                ->join(array('p' => 'calendar'), 'c.parent_id = p.id', array())
                ->where('c.parent_id != 0')
                ->where('c.parent_id != c.id')
                ->where('c.rrule  = ""')
                ->where('p.rrule != ""')
                ->order(array('c.parent_id ASC', 'c.end_datetime ASC'))
                ->limit($limit, $start)
                ->query()->fetchAll();
            $start += $limit;

            if (empty($entries)) {
                if ($this->_debug) {
                    Phprojekt::getInstance()->getLog()->debug('done');
                }
                $done = true;
            } else {
                $group           = array();
                $currentParentId = $entries[0]['parent_id'];

                foreach ($entries as $event) {
                    if ($event['parent_id'] == $currentParentId) {
                        $group[] = $event;
                    } else {
                        $this->_updateSingleChangedEventGroup($group);

                        $group           = array($event);
                        $currentParentId = $event['parent_id'];
                    }
                }
            }
        } while (!$done);
    }

    /**
     * Update a group of events with a common parent id
     */
    private function _updateSingleChangedEventGroup($events)
    {
        $parent = $this->_db->select()->from('calendar')
            ->where(
                'id = ?',
                $events[0]['parent_id']
            ) ->query()->fetch();

        $start    = new Datetime($parent['start_datetime']);
        $duration = $start->diff(new Datetime($parent['end_datetime']));
        $helper   = new Calendar2_Helper_Rrule($start, $duration, $parent['rrule']);

        $last = new Datetime($events[count($events) - 1]['end_datetime']);

        $occurrences = $helper->getDatesInPeriod($start, $last);
        $deleted = array();
        $added   = array();

        while (!empty($events) && $events[0]['start_datetime'] == $parent['start_datetime']) {
            array_shift($events);
        }

        $addedTimes   = array();
        $regularTimes = array();
        foreach ($events as $e) {
            // At this point we throw away confirmation information, mostly because I'm too tired and it's too
            // complicated to retrieve them.
            if ($e['participant_id'] != $parent['owner_id']) {
                continue;
            }

            if (empty($occurrences)) {
                $addedTimes[] = $e['start_datetime'];
                $added[] = $e;
                continue;
            }
            $cmp = $this->_compareDatetimeWithEvent($occurrences[0], $e);
            if ($cmp < 0) {
                // occurrence is before
                $deleted[] = array_shift($occurrences);
            } else if ($cmp === 0) {
                $regularTimes[] = array_shift($occurrences);
                if ($this->_eventDataDiffers($parent, $e)) {
                    $addedTimes[] = $e['start_datetime'];
                    $deleted[] = new Datetime($e['start_datetime']);
                    $added[] = $e;
                }
            } else {
                // event is before occurrence
                $added[] = $e;
            }
        }

        $addedEventsParticipants = array();
        foreach ($events as $e) {
            if ($e['participant_id'] == $parent['owner_id']) {
                continue;
            }

            if (in_array($e['start_datetime'], $addedTimes)) {
                if (!array_key_exists($e['start_datetime'], $addedEventsParticipants)) {
                    $addedEventsParticipants[$e['start_datetime']] = array();
                }
                $addedEventsParticipants[$e['start_datetime']][] = array(
                    'id' => $e['participant_id'],
                    'status' => $e['status']
                );
            } else if (!in_array($e['start_datetime'], $regularTimes)) {
                // This event doesn't really belong here. We just create a new one for the user independent of $parent.
                $uid = Phprojekt::generateUniqueIdentifier();
                $this->_db->insert(
                    'calendar2',
                    array(
                        'project_id' => $e['project_id'],
                        'summary' => $e['title'],
                        'description' => $e['notes'],
                        'location' => $e['place'],
                        'comments' => "",
                        'start' => $e['start_datetime'],
                        'last_end' => $e['end_datetime'],
                        'end' => $e['end_datetime'],
                        'owner_id' => $e['participant_id'],
                        'rrule' => '',
                        'visibility' => $e['visibility'] + 1,
                        'uri' => $uid,
                        'uid' => $uid

                    )
                );
                $newCalendarId = $this->_db->lastInsertId();
                $this->_db->insert(
                    'calendar2_user_relation',
                    array(
                        'calendar2_id' => $newCalendarId,
                        'user_id' => $e['participant_id'],
                        'confirmation_status' => $e['status']
                    )
                );
            }
        }

        if (!empty($deleted)) {
            $this->_deletedOccurrences($parent, $deleted);
        }
        if (!empty($added)) {
            $this->_addedOccurrences($parent, $added, $addedEventsParticipants);
        }
    }

    /** negative if $dt < $event, 0 on ==, positive if $dt > $event */
    private function _compareDatetimeWithEvent($dt, $event)
    {
        return $dt->getTimestamp() - strToTime($event['start_datetime']);
    }

    private function _eventDataDiffers($a, $b)
    {
        return ($a['title'] !== $b['title'] || $a['place'] !== $b['place'] || $a['notes'] !== $b['notes']);
    }

    private function _deletedOccurrences($parent, $deleted)
    {
        $sql   = "INSERT INTO calendar2_excluded_dates (calendar2_id, date) values \n";
        $first = array_shift($deleted);
        $sql  .= '(' . (int) $parent['id'] . ", '"  . $first->format('Y-m-d H:i:s') . "')\n";
        foreach ($deleted as $dt) {
            $sql .= ', (' . (int) $parent['id'] . ", '" . $dt->format('Y-m-d H:i:s') . "')\n";
        }

        if ($this->_debug) {
            Phprojekt::getInstance()->getLog()->debug($sql);
        }
        $this->_db->query($sql);
    }

    private function _addedOccurrences($parent, $added, $participants)
    {
        $newParent = $this->_db->select()->from('calendar2')->where('id = ?', $parent['id'])->query()->fetch();

        $toInsert  = array();

        foreach ($added as $e) {
            $toInsert[]  = '('
                . (int) $e['project_id'] . ', '
                . "'{$e['title']}'" . ', '
                . "'{$e['notes']}'" . ', '
                . "'{$e['place']}'" . ', '
                . "'{$e['start_datetime']}'" . ', '
                . "'{$e['end_datetime']}'" . ', '
                . "'{$e['end_datetime']}'" . ', '
                . (int) $e['owner_id'] . ', '
                . "'{$e['start_datetime']}'" . ', '
                . (int) $e['visibility'] . ', '
                . "'{$newParent['uid']}'" . ', '
                . 'NOW(), '
                . "'{$newParent['uri']}'" . ')';
        }

        $sql  = 'INSERT INTO calendar2 (project_id, summary, description, location, start, last_end, end, owner_id, '
                . 'recurrence_id, visibility, uid, last_modified, uri) VALUES ' . "\n";
        $sql .= implode(", \n", $toInsert);
        if ($this->_debug) {
            Phprojekt::getInstance()->getLog()->debug($sql);
        }
        $this->_db->query($sql);

        foreach ($participants as $start => $data) {
            $newCalendarId = (int) $this->_db->select()->from('calendar2', 'id')
                ->where('uid = ?', $newParent['uid'])
                ->where('start = ?', $start)
                ->query()->fetchColumn();

            foreach ($data as $k => $d) {
                $data[$k] = '(' . $newCalendarId . ', ' . $d['id'] . ', ' . $d['status'] . ")";
            }
            $sql  = "INSERT INTO calendar2_user_relation (calendar2_id, user_id, confirmation_status) VALUES \n";
            $sql .= implode(", \n", $data);
            if ($this->_debug) {
                Phprojekt::getInstance()->getLog()->debug($sql);
            }
            $this->_db->query($sql);
        }
    }

    private function _updateUsers()
    {
        $db = $this->_db;

        $db->query(
            'INSERT IGNORE INTO calendar2_user_relation (calendar2_id, user_id, confirmation_status) SELECT
            p.id AS calendar2_id, c.participant_id AS user_id, c.status + 1 AS confirmation_status FROM calendar AS
            c JOIN calendar AS p ON c.parent_id = p.id WHERE c.parent_id != 0 AND c.parent_id != c.id'
        );

        $db->query(
            'INSERT IGNORE INTO calendar2_user_relation (calendar2_id, user_id, confirmation_status) SELECT
            id AS calendar2_id, participant_id AS user_id, status + 1 AS confirmation_status FROM calendar WHERE
            parent_id = 0 OR parent_id = id'
        );
    }

    private function _updateRights()
    {
        $db = $this->_db;

        $db->query(
            'INSERT IGNORE INTO item_rights (module_id, item_id, user_id, access) SELECT
            ' . $this->_newCalId . ' AS module_id, calendar2_id AS item_id, user_id, 1 AS access
            FROM calendar2_user_relation'
        );

        $db->query(
            'INSERT INTO item_rights (module_id, item_id, user_id, access) SELECT
            ' . $this->_newCalId . ' AS module_id, id AS item_id, owner_id AS user_id, 255 AS
            access FROM calendar2 ON DUPLICATE KEY UPDATE access = 255'
        );
    }

    /**
     * Updates the lastEnd values of all entries
     *
     * @return void
     */
    private function _updateLastEnd()
    {
        if ($this->_debug) {
            Phprojekt::getInstance()->getLog()->debug('_updateLastEnd');
        }
        $this->_db->query('UPDATE calendar2 SET last_end = end WHERE rrule = ""');

        $entries = $this->_db->select()->from('calendar2', array('id', 'start', 'end', 'rrule'))
            ->where('rrule != ""')
            ->query()->fetchAll();

        $update = $this->_db->prepare('UPDATE calendar2 SET last_end = :last_end WHERE id = :id');

        $x = 0;
        foreach ($entries as $key => $e) {
            if ($this->_debug && ++$x % 100 == 0) Phprojekt::getInstance()->getLog()->debug($x);
            $start    = new Datetime($e['start']);
            $end      = new Datetime($e['end']);
            $duration = $start->diff($end);

            $helper = new Calendar2_Helper_Rrule($start, $duration, $e['rrule']);
            $update->execute(
                array(
                    ':last_end' => $helper->getUpperTimeBoundary()->format('Y-m-d H:i:s'),
                    ':id' => $e['id']
                )
            );
        }

    }

    private function _updateTags()
    {
        $db = $this->_db;

        $db->query(
            $db->quoteInto(
                'DELETE FROM tm
                USING tags_modules AS tm
                    LEFT OUTER JOIN calendar2 AS c
                    ON tm.item_id = c.id
                WHERE tm.module_id = ?
                    AND c.id IS NULL',
                $this->_oldCalId
            )
        );

        $db->update(
            'tags_modules',
            array('module_id' => $this->_newCalId),
            $db->quoteInto('module_id = ?', $this->_oldCalId)
        );
    }

    private function _updateHistory()
    {
        $db = $this->_db;

        $db->query(
            $db->quoteInto(
                'DELETE FROM h
                USING history AS h
                    LEFT OUTER JOIN calendar2 AS c
                    ON h.item_id = c.id
                WHERE h.module_id = ?
                    AND c.id IS NULL',
                $this->_oldCalId
            )
        );

        $db->update(
            'history',
            array('module_id' => $this->_newCalId),
            $db->quoteInto('module_id = ?', $this->_oldCalId)
        );
    }

    private function _regenerateSearch()
    {
        $step  = 100;
        $start = 0;
        $max   = $this->_db->select()->from('calendar2', 'MAX(id)')->query()->fetchColumn();

        $model  = new Calendar2_Models_Calendar2();
        $search = new Phprojekt_Search();

        while ($start <= $max) {
            if ($this->_debug) {
                Phprojekt::getInstance()->getLog()->debug($start . ' - ' . ($start + $step));
            }

            $events = $model->fetchAll("id >= $start AND id < " . ($start + $step));
            $start += $step;

            foreach ($events as $e) {
                $search->indexObjectItem($e);
            }
        }
    }

    /**
     * Removes the old calendar.
     */
    private function _removeOldCalendar()
    {
        $db = $this->_db;

        $db->query('DROP TABLE calendar');
        $db->delete('module', 'name = "Calendar"');
        $db->delete('item_rights', $db->quoteInto('module_id = ?', $this->_oldCalId));
        $db->delete('database_manager', 'table_name = "Calendar"');
        $db->delete('history', $db->quoteInto('module_id = ?', $this->_oldCalId));
    }
}
