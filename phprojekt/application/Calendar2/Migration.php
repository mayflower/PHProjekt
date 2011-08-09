<?php
/**
 * Calendar2 Migration
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
 * Calendar2 Migration
 *
 * This is used to install the Cal2 tables and Convert the Calendar1 data to the
 * new format.
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
class Calendar2_Migration extends Phprojekt_Migration_Abstract
{
    /**
     * Return the current module version.
     *
     * Implements Phprojekt_Migration_Abstract->getCurrentModuleVersion
     *
     * @return String Version
     */
    public function getCurrentModuleVersion()
    {
        return '6.1.0-dev';
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
        if (is_null($currentVersion)
                || Phprojekt::compareVersion($currentVersion, '6.1.0-dev') < 0) {
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

    private $_tagsObj  = null;
    private $_oldCalId = null;
    private $_newCalId = null;
    private $_search   = null;

    private function _migrateFromOldCalendar()
    {
        $db = Phprojekt::getInstance()->getDb();
        $db->beginTransaction();

        try {
            if (!in_array('calendar', $db->listTables())) {
                throw new Exception('Old Calendar is gone, cannot migrate');
            }

            $this->_tagsObj  = Phprojekt_Tags::getInstance();
            $this->_oldCalId = Phprojekt_Module::getId('Calendar');
            $this->_newCalId = Phprojekt_Module::getId('Calendar2');
            $this->_search   = Phprojekt_Loader::getLibraryClass('Phprojekt_Search');

            // Clear the calendar2 tables.
            $db->delete('calendar2');
            $db->delete('calendar2_user_relation');
            $db->delete('calendar2_excluded_dates');

            $entries = $db->fetchAll('SELECT * from calendar ORDER BY id ASC');

            // First, let's combine those that belong together, i.e. those with
            // the same parent id X plus the one with id X.
            $grouped = array();
            $parents = array();
            foreach ($entries as $entry) {
                $parentId    = (int) $entry['parent_id'];
                $id           = (int) $entry['id'];
                $parents[$id] = $parentId;
                if (0 === $parentId) {
                    $grouped[$id][] = $entry;
                } else {
                    while (0 !== $parents[$parentId]) {
                        $parentId = $parents[$parentId];
                    }
                    $grouped[$parentId][] = $entry;
                }
            }

            foreach ($grouped as $series) {
                if (empty($series[0]['rrule'])) {
                    $this->_migrateSingleEvent($series);
                } else {
                    $this->_migrateRecurringEvent($series);
                }
            }

            $db->update('calendar2',  array('uri' => 'id'));
            $db->commit();
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    /**
     * This function takes a set of calendar entries representing a complete,
     * non-recurring event. It then creates a new calendar2 event based on the
     * data of the owner's event's instance (discarding changes other
     * participants might have made to their versions) and adds the other
     * participants to it.
     *
     * If $uid is set, this will be used as the uid of the new event
     *
     * @return The newly created model object or null if $entries is empty
     */
    private function _migrateSingleEvent(array $entries, $uid = null)
    {
        if (empty($entries)) {
            if ($this->_debug) Phprojekt::getInstance()->getLog()
                ->debug('got empty array, nothing to migrate');
            return null;
        }
        if ($this->_debug) Phprojekt::getInstance()->getLog()
            ->debug("migrating\n".print_r($entries, true));
        $ownerId             = (int) $entries[0]['owner_id'];
        $ownerEntry          = null;
        $participantStatuses = array();

        foreach ($entries as $entry) {
            if ((int) $entry['participant_id'] === $ownerId) {
                $ownerEntry = $entry;
            } else {
                $pId                       = (int) $entry['participant_id'];
                $status                    = (int) $entry['status'] + 1;
                $participantStatuses[$pId] = $status;
            }
            $this->_search->deleteObjectItemByIds($this->_oldCalId, $entry['id']);
        }

        if (is_null($ownerEntry)) {
            // The owners version has been deleted. We need the owner to be part
            // of the event though, so this puts us in a situation where we have
            // to add him as participant again.
            // Instead of the owners version, we just use the first other
            // version.
            if ($this->_debug) Phprojekt::getInstance()->getLog()
                ->debug('Recreating owners entry');
            $ownerEntry = $entries[0];
        }

        $model = new Calendar2_Models_Calendar2();

        // General data
        $model->summary     = $ownerEntry['title'];
        $model->description = '';
        $model->location    = $ownerEntry['place'];
        $model->comments    = $ownerEntry['notes'];
        $model->start       = Phprojekt_Converter_Time::utcToUser(
            $ownerEntry['start_datetime']
        );
        $model->end         = Phprojekt_Converter_Time::utcToUser(
            $ownerEntry['end_datetime']
        );
        $model->ownerId     = $ownerId;
        $model->visibility  = $ownerEntry['visibility'] + 1;
        if (!is_null($uid)) {
            $model->uid = $uid;
        }

        // Participant data
        $model->setConfirmationStatus($ownerId, $ownerEntry['status'] + 1);
        foreach ($participantStatuses as $id => $status) {
            $model->addParticipant($id, $status);
        }

        $model->save();

        // Migrate the tags
        $tags = $this->_tagsObj->getTagsByModule(
            $this->_oldCalId,
            $ownerEntry['id']
        );
        $this->_tagsObj->saveTags($this->_newCalId, $model->id, $tags);

        return $model;
    }

    /**
     * Migrate a recurring event
     *
     * @param array $entries The cal1 entries making up the event
     *
     * @return void
     * @throws Exception On Errors
     */
    private function _migrateRecurringEvent($entries)
    {
        // We do the following:
        // 1. Convert the first event, including recurrence.
        // 2. Look for dates that, following this rrule, should have an
        //    occurrence but doesn't. Delete these occurrences.
        // 3. Search through the events for occurrences where data has changed
        //    in the owner's version. Delete these occurrences and recreate them
        //    seperated from this occurrences.
        // 4. Add occurrences that do not fit in the recurrence. They have been
        //    moved.
        if ($this->_debug) $log = Phprojekt::getInstance()->getLog();

        if (empty($entries)) {
            // Nothing to do
            return;
        }

        $byStart         = array();
        $firstOccurrence = array();
        $minStart        = '99999'; // 4 chars for the year and '9' > '-'
        $maxStart        = '0';
        foreach ($entries as $entry) {
            if ('0' === $entry['parent_id']) {
                $firstOccurrence[] = $entry;
            }
            $start = $entry['start_datetime'];
            $byStart[$start][] = $entry;
            if ($start < $minStart) {
                $minStart = $start;
            }
            if ($start > $maxStart) {
                $maxStart = $start;
            }
            $this->_search->deleteObjectItemByIds($this->_oldCalId, $entry['id']);
        }

        if ($this->_debug) $log->debug("ByStart:\n" . print_r($byStart, true));
        // 1. Convert the base recurrence
        $firstOccurrence = $byStart[$firstOccurrence[0]['start_datetime']];

        $rootEvent        = $this->_migrateSingleEvent($firstOccurrence);
        $rootEvent->rrule = $firstOccurrence[0]['rrule'];
        $rootEvent->save();

        // 2. Remove deleted events
        $min    = new Datetime($minStart . ' UTC');
        $max    = new Datetime($maxStart . ' UTC');
        $helper = $rootEvent->getRruleHelper();
        $dates  = $helper->getDatesInPeriod($min, $max);
        foreach ($dates as $date) {
            if (!array_key_exists($date->format('Y-m-d H:i:s'), $byStart)) {
                if ($this->_debug) $log->debug(
                    "deleting occurrence on {$date->format('Y-m-d H:i:s')}"
                );
                $model = $rootEvent->create();
                $model->findOccurrence($rootEvent->id, $date);
                $model->deleteSingleEvent();
            }
        }

        // 3 + 4. Update changed events and add new ones.
        //        We only look at the owner's versions.
        //TODO: What if the owner deleted his or her version?
        //TODO: Different participants/confirmation statuses are ignored atm

        // First, let's format some data to compare the single events with.
        $reference       = $firstOccurrence[0];
        $refEnd          = new Datetime($reference['end_datetime'] . ' UTC');
        $refParticipants = array();
        $refTags         = $this->_tagsObj->getTagsByModule(
            $this->_oldCalId,
            $reference['id']
        );
        $ownerId         = $reference['owner_id'];
        foreach ($firstOccurrence as $row) {
            $pid = $row['participant_id'];
            if ($pid != $ownerId) {
                $refParticipants[$pid] = $row['status'];
            }
        }

        foreach ($byStart as $start => $occurrence) {
            if ($this->_debug) $startStr = $start;
            $start = new Datetime($start . ' UTC');

            if (!$helper->containsDate($start)) {
                // This is not a regular part of the recurrence.
                if ($this->_debug) $log->debug(
                    Phprojekt_Converter_Time::userToUtc($rootEvent->start)
                    . ','.$rootEvent->rrule
                );
                if ($this->_debug) $log->debug(
                    "Adding irregular event on $startStr"
                );
                $this->_migrateSingleEvent($occurrence, $rootEvent->uid);
                continue;
            }

            // We need to find the owners version. While we're looping through
            // the events, we can also check for new or missing participants
            $myRefParticipants = $refParticipants;
            $participantsChanged = false;
            $event  = null;
            foreach ($occurrence as $row) {
                $pId = $row['participant_id'];
                if ($pId == $ownerId) {
                    $event = $row;
                }
                // We don't care for the owner because if he is the only one
                // deleted, we would have to recreate his version. If this is
                // the only change, it's the same as just leavin this event as
                // it is.
                if ($pId != $ownerId) {
                    if (!array_key_exists($pId, $myRefParticipants)) {
                        $participantsChanged = true;
                    } else {
                        unset($myRefParticipants[$pId]);
                    }
                }
            }
            if (is_null($event)) {
                // If the owners version is deleted, we just use another one.
                if ($this->_debug) $log->debug('Owners version deleted');
                $event = $occurrence[0];
            }
            if (!empty($myRefParticipants)) {
                if ($this->_debug) $log->debug(
                    "Remaining participants:\n"
                    . print_r($myRefParticipants, true)
                );
                $participantsChanged = true;
            }

            $end = new Datetime($event['end_datetime'] . ' UTC');

            $tags = $this->_tagsObj->getTagsByModule(
                $this->_oldCalId,
                $event['id']
            );

            //The recurrence could also have been changed. We ignore that
            //for now as I don't have a clue what to do in that case.
            //TODO: What if the duration was changed by a multiple of one day?
            if ($this->_debug) {
                if ($participantsChanged) {
                    $log->debug(
                        "updating event on $startStr, participants changed"
                    );
                } else if ($reference['title']      !== $event['title']) {
                    $log->debug("updating event on $startStr, title changed");
                } else if ($reference['place']      !== $event['place']) {
                    $log->debug("updating event on $startStr, place changed");
                } else if ($reference['notes']      !== $event['notes']) {
                    $log->debug("updating event on $startStr, notes changed");
                } else if ($reference['visibility'] !== $event['visibility']) {
                    $log->debug(
                        "updating event on $startStr, visibility changed"
                    );
                } else if ($refEnd->format('H-i-s') !== $end->format('H-i-s')) {
                    $log->debug(
                        "updating event on $startStr, duration changed"
                    );
                } else if ($refTags != $tags) {
                    $log->debug("updating event on $startStr, tags changed");
                }
            }
            if ($participantsChanged
                    || $reference['title']      !== $event['title']
                    || $reference['place']      !== $event['place']
                    || $reference['notes']      !== $event['notes']
                    || $reference['visibility'] !== $event['visibility']
                    || $refEnd->format('H-i-s') !== $end->format('H-i-s')
                    || $refTags                 !==  $tags) {
                $model = $rootEvent->create();
                $model->findOccurrence($rootEvent->id, $start);
                $model->deleteSingleEvent();
                $this->_migrateSingleEvent($occurrence, $rootEvent->uid);
            }
        }
        return $rootEvent;
    }

    /**
     * Removes the old calendar.
     */
    private function _removeOldCalendar()
    {
        $db = Phprojekt::getInstance()->getDb();

        $db->query('DROP TABLE calendar');
        $db->delete('module', 'name = "Calendar"');
        $db->delete('item_rights', $db->quoteInto('module_id = ?', $this->_oldCalId));
        $db->delete('database_manager', 'table_name = "Calendar"');
    }
}
