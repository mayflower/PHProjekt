<?php
/**
 * Calendar model class
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @link       http://www.phprojekt.com
 * @author     Eduardo Polidor <polidor@mayflower.de>
 * @since      File available since Release 6.0
 */

/**
 * Calendar model class
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */
class Calendar_Models_Calendar extends Phprojekt_Item_Abstract
{
    /**
     * Type of event once (single date event)
     *
     */
    const EVENT_TYPE_ONCE = 1;

    /**
     * Type of event daily
     *
     */
    const EVENT_TYPE_DAILY = 2;

    /**
     * Type of event weekly
     *
     */
    const EVENT_TYPE_WEEKLY = 3;

    /**
     * Type of event monthly
     *
     */
    const EVENT_TYPE_MONTLY = 4;

    /**
     * Type of event anual
     *
     */
    const EVENT_TYPE_ANUAL = 5;

    /**
     * Save or inserts an event. It inserts one envent by participant
     *
     * @param array   $request        Array with all the data for the item (Basic Data)
     * @param integer $id             Current item id or null for new one
     * @param string  $startDate      Date of the event
     * @param string  $rrule          Rule for apply the recurring
     * @param array   $participants   Array with the users involved in the event
     * @param bool    $multipleEvents Apply changes to one event or all the recurring events
     *
     * @return integer the id of the root event
     */
    public static function saveEvent($request, $id, $startDate, $rrule, $participants, $multipleEvents)
    {
        $userId = Phprojekt_Auth::getUserId();
        $model  = Phprojekt_Loader::getModel('Calendar', 'Calendar');

        $participantsList = array();

        // Getting requested dates for the serial meeting (if it is serial)
        if (!empty($rrule)) {
            if ($multipleEvents) {
                $startDate = self::getRecursionStartDate($id, $startDate);
            }
            $dateCollection = new Phprojekt_Date_Collection($startDate);
            $dateCollection->applyRrule($rrule);
            $eventDates = $dateCollection->getValues();
        } else {
            $eventDates = array(new Zend_Date(strtotime($startDate)));
        }

        if (is_array($participants)) {
            // We will put the owner id first, just to make it clear
            if (!in_array($userId, $participants)) {
                $participantsList[$userId] = $userId;
            }
            foreach ($participants as $oneParticipant) {
                $participantsList[(int) $oneParticipant] = (int) $oneParticipant;
            }
        } else {
            $participantsList[$userId] = $userId;
        }

        if ($id == 0) {
            // New Event
            foreach ($participantsList as $participantId) {
                $returnId = self::_saveNewEvent($request, $eventDates, $participantId);
                if ($id == 0) {
                    $id = $returnId;
                }
            }
        } else {
            // Edit Multiple Events
            if ($multipleEvents) {
                self::_updateMultipleEvents($request, $id, $eventDates, $participantsList);
            } else {
                self::_updateSingleEvent($request, $id, $eventDates, $participantsList);
            }
        }

        return $id;
    }

    /**
     * Use soft delete for keep the events changed or deleted
     *
     * @return void
     */
    public function softDeleteEvent()
    {
        $this->deleted = 1;
        $this->save();
    }

    /**
     * Returns the id of the root event of the current record
     *
     * @param Phprojekt_Model_Interface $model The model to check
     *
     * @return integer id of the root event
     */
    public static function getRootEventId($model)
    {
        $rootEventId = null;

        if (null !== $model->parentId || $model->parentId > 0) {
            $rootEventId = (int) $model->parentId;
        } else {
            $rootEventId = ($model->id > 0) ? (int) $model->id : null;
        }

        return $rootEventId;
    }

    /**
     * Validate the data of the current record
     *
     * @return boolean
     */
    public function recordValidate()
    {
        // one is the unique value available because calendar is a global module
        if (Phprojekt_Module::getSaveType(Phprojekt_Module::getId($this->_name)) >= 1) {
            $this->projectId = 1;
        }

        return parent::recordValidate();
    }

    /**
     * Get all the participants of one event
     *
     * @return void
     */
    public function getAllParticipants()
    {
        $participantsList = array();
        $participants     = array();

        if (!empty($this->id)) {
            $rootEventId  = self::getRootEventId($this);
            $where        = " parentId = " . (int) $rootEventId . " AND deleted is NULL";
            $records      = $this->fetchAll($where);
            foreach ($records as $record) {
                if (null === $record->rrule) {
                    if ($record->startDate == $this->startDate) {
                        $participantsList[$record->participantId] = $record->participantId;
                    }
                } else {
                    $participantsList[$record->participantId] = $record->participantId;
                }
            }
            $participants = implode(",", $participantsList);
        }

        return $participants;
    }

    /**
     * Return the first startDate of the recurring events
     * If the user is the owner, can change it.
     *
     * @param integer $id        The current item id
     * @param string  $startDate The current startDate from the POST value
     *
     * @return string
     */
    public function getRecursionStartDate($id, $startDate)
    {
        $model = Phprojekt_Loader::getModel('Calendar', 'Calendar');
        $model->find($id);

        $where   = ' parentId = ' . (int) $model->parentId. ' AND participantId = ' . (int) $model->participantId
            . ' AND deleted is NULL';
        $records = $model->fetchAll($where, 'startDate ASC');

        if (self::_isOwner($model)) {
            // Only use the new startDate if the user is owner
            // and the starDate is less than the minimal value for startDate
            if ($records[0]->startDate < $startDate) {
                $startDate = $records[0]->startDate;
            }
        } else {
            // If the user is not the owner, always use the minimal value for startDate
            $startDate = $records[0]->startDate;
        }

        return $startDate;
    }

    /**
     * Delete routine
     *
     * If the delete is for all, delete all events related to this event
     * If the delete is for one item:
     *  If have rrule (other events) make a soft delete
     *  If is a single event, make a hard delete
     *
     * @param bool $multipleEvents Action to multiple events or single one
     *
     * @return void
     */
    public function deleteEvents($multipleEvents)
    {
        if ($multipleEvents) {
            if (!self::_isOwner($this)) {
                $where = ' parentId = ' . (int) $this->parentId
                    . ' AND participantId = ' . (int) $this->participantId;
            } else {
                $where = ' parentId = ' . (int) $this->parentId;
            }

            $records = $this->fetchAll($where);
            foreach ($records as $record) {
                $record->delete();
            }
        } else {
            if (null === $this->rrule) {
                $this->delete();
            } else {
                $this->softDeleteEvent();
            }
        }
    }

    /**
     * Return all the events for all the users selected in a date
     * The function use the ActiveRecord fetchAll for skip the itemRights restrictions
     *
     * @param string  $usersId UserId separated by coma
     * @param string  $date    Date for search
     * @param integer $count   Count for the fetchall
     * @param integer $offset  Offset for the fetchall
     *
     * @return unknown
     */
    public function getUserSelectionRecords($usersId, $date, $count, $offset)
    {
        $where = 'deleted is NULL AND participantId IN (' . $usersId . ') AND startDate = "' . $date . '"';
        return Phprojekt_ActiveRecord_Abstract::fetchAll($where, null, $count, $offset);
    }

    /**
     * Check if the user is the owner of the item
     *
     * @param Phprojekt_Model_Interface $model The event to check
     *
     * @return boolean
     */
    private function _isOwner($model)
    {
        $owner  = true;
        $userId = Phprojekt_Auth::getUserId();

        if ($userId != $model->ownerId) {
            $owner = false;
        }

        return $owner;
    }

    /**
     * Save a new event (Single or a recurring one)
     *
     * @param array   $request        Array with all the POST data
     * @param array   $eventDates     Array with the dates of the recurring
     * @param integer $participantId  Id of the user to add the event
     *
     * @return integer
     */
    private function _saveNewEvent($request, $eventDates, $participantId)
    {
        static $parentId = null;
        $model = Phprojekt_Loader::getModel('Calendar', 'Calendar');

        foreach ($eventDates as $oneDate) {
            $clone    = clone($model);
            $parentId = self::_saveEvent($request, $clone, $oneDate, $participantId, $parentId);
        }

        return $parentId;
    }

    /**
     * Update events
     *
     * The function will check for:
     * - changes in the users (add, delete) (ONLY FOR OWNERS OF THE ITEM)
     * - changes in the recurring (add, delete, update) (ONLY FOR OWNERS OF THE ITEM)
     *
     * @param array $request          Array with the POST data
     * @param integer $id             Id of the current event
     * @param array $eventDates       Array with the dates of the recurring
     * @param array $participantsList Array with the users involved in the event
     *
     * @return void
     */
    private function _updateMultipleEvents($request, $id, $eventDates, $participantsList)
    {
        $model = Phprojekt_Loader::getModel('Calendar', 'Calendar');
        $clone = clone($model);

        $addParticipants = true;
        $model->find($id);

        if (!self::_isOwner($model)) {
            // Save only the own events under the parentId
            $where = ' parentId = ' . (int) $model->parentId
                . ' AND participantId = ' . (int) $model->participantId;
        } else {
            // Save all the events under the parentId
            $where = ' parentId = ' . (int) $model->parentId;
        }

        $currentParticipants = array();
        $records             = $model->fetchAll($where);
        foreach ($records as $record) {
            $found                                       = false;
            if (null === $record->deleted && null !== $record->rrule) {
                $currentParticipants[$record->participantId] = $record->participantId;
            }
            foreach ($eventDates as $oneDate) {
                $date = date("Y-m-d", $oneDate->get());
                if (!$found && $date == $record->startDate) {
                    // Update old entry of recurrence
                    self::_saveEvent($request, $record, $oneDate, $record->participantId, $record->parentId);
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                // Delete old entry of recurrence
                $record->softDeleteEvent();
            }
        }

        // Only for owners
        if (self::_isOwner($model)) {
            // Add the new entry of recurrence
            foreach ($eventDates as $oneDate) {
                $newEntry = true;
                $date = date("Y-m-d", $oneDate->get());
                foreach ($records as $record) {
                    if ($date == $record->startDate) {
                        $newEntry = false;
                        break;
                    }
                }
                if ($newEntry) {
                    // Add participans is not nessesary since is done here
                    $addParticipants = false;
                    foreach ($participantsList as $participantId) {
                        $newModel = clone($clone);
                        self::_saveEvent($request, $newModel, $oneDate, $participantId, self::getRootEventId($model));
                    }
                }
            }

            // Delete removed participans
            foreach ($currentParticipants as $currentParticipantId) {
                $found = false;
                foreach ($participantsList as $participantId) {
                    if ($participantId == $currentParticipantId) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    // Delete old participant
                    $removeModel = clone($clone);
                    $where       = ' parentId = '. (int) self::getRootEventId($model)
                        . ' AND participantId = '. (int) $currentParticipantId;
                    $records = $removeModel->fetchAll($where);
                    foreach ($records as $record) {
                        $record->softDeleteEvent();
                    }
                }
            }

            // Add the new entry for a new participant
            if ($addParticipants) {
                foreach ($participantsList as $participantId) {
                    $newEntry = true;
                    foreach ($currentParticipants as $currentParticipantId) {
                        if ($currentParticipantId == $participantId) {
                            $newEntry = false;
                            break;
                        }
                    }
                    if ($newEntry) {
                        foreach ($eventDates as $oneDate) {
                            $newModel = clone($clone);
                            $parentId = self::getRootEventId($model);
                            self::_saveEvent($request, $newModel, $oneDate, $participantId, $parentId);
                        }
                    }
                }
            }
        }
    }

    /**
     * Update only one  event
     *
     * @param array $request          Array with the POST data
     * @param integer $id             Id of the current event
     * @param array $eventDates       Array with the dates of the recurring
     * @param array $participantsList Array with the users involved in the event
     *
     * @return void
     */
    private function _updateSingleEvent($request, $id, $eventDates, $participantsList)
    {
        $model = Phprojekt_Loader::getModel('Calendar', 'Calendar');
        $clone = clone($model);

        $addParticipants = true;
        $model->find($id);

        $oneDate = $eventDates[0];
        self::_saveEvent($request, $model, $oneDate, $model->participantId, $model->parentId);

        if (!self::_isOwner($model)) {
            // Save only the own events under the parentId
            $where = ' parentId = ' . (int) $model->parentId
                . ' AND participantId = ' . (int) $model->participantId;
        } else {
            // Save all the events under the parentId
            $where = ' parentId = ' . (int) $model->parentId;
        }

        $currentParticipants = array();
        $records             = $model->fetchAll($where);
        foreach ($records as $record) {
            $currentParticipants[$record->participantId] = $record->participantId;
        }

        // Delete removed participans
        foreach ($currentParticipants as $currentParticipantId) {
            $found = false;
            foreach ($participantsList as $participantId) {
                if ($participantId == $currentParticipantId) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                // Delete old participant
                $removeModel = clone($clone);
                $where       = ' parentId = '. (int) self::getRootEventId($model)
                    . ' AND participantId = '. (int) $currentParticipantId
                    . ' AND startDate = "' . date("Y-m-d", $oneDate->get()) . '"';
                $records = $removeModel->fetchAll($where);
                foreach ($records as $record) {
                    $record->softDeleteEvent();
                }
            }
        }

        // Add the new entry for a new participant
        foreach ($participantsList as $participantId) {
            $newEntry = true;
            foreach ($currentParticipants as $currentParticipantId) {
                if ($currentParticipantId == $participantId) {
                    $newEntry = false;
                    break;
                }
            }
            if ($newEntry) {
                $newModel = clone($clone);
                self::_saveEvent($request, $newModel, $oneDate, $participantId, self::getRootEventId($model));
            }
        }
    }

    /**
     * Do the save for the event
     * Add the full access to the owner and Read, Write and Delete access to the user involved
     *
     * @param array $request                           Array with the POST data
     * @param Phprojekt_Model_Interface $model         The model to check
     * @param Phprojekt_Date_Collection $oneDate       Date object to save
     * @param integer                   $participantId Id of the user to save the event
     * @param integer                   $parentId      Id of the parent event
     *
     * @return integer The parentId
     */
    private function _saveEvent($request, $model, $oneDate, $participantId, $parentId)
    {
        $date                     = date("Y-m-d", $oneDate->get());
        $request['startDate']     = $date;
        $request['endDate']       = $date;
        $request['participantId'] = $participantId;
        $request['parentId']      = $parentId;

        // Add 'read, write and delete' access to the participant
        $checkNoneAccess     = array();
        $checkReadAccess     = array();
        $checkWriteAccess    = array();
        $checkAccessAccess   = array();
        $checkCreateAccess   = array();
        $checkCopyAccess     = array();
        $checkDeleteAccess   = array();
        $checkDownloadAccess = array();
        $checkAdminAccess    = array();
        $dataAccess          = array();

        // Access for the user
        $dataAccess[$participantId]        = $participantId;
        $checkReadAccess[$participantId]   = 1;
        $checkWriteAccess[$participantId]  = 1;
        $checkDeleteAccess[$participantId] = 1;

        // Access for the owner
        if (null !== $model->ownerId) {
            $ownerId = $model->ownerId;
        } else {
            $ownerId = Phprojekt_Auth::getUserId();
        }
        $dataAccess[$ownerId]          = $ownerId;
        $checkNoneAccess[$ownerId]     = $ownerId;
        $checkReadAccess[$ownerId]     = $ownerId;
        $checkWriteAccess[$ownerId]    = $ownerId;
        $checkAccessAccess[$ownerId]   = $ownerId;
        $checkCreateAccess[$ownerId]   = $ownerId;
        $checkCopyAccess[$ownerId]     = $ownerId;
        $checkDeleteAccess[$ownerId]   = $ownerId;
        $checkDownloadAccess[$ownerId] = $ownerId;
        $checkAdminAccess[$ownerId]    = $ownerId;

        // Set the access
        $request['dataAccess']          = $dataAccess;
        $request['checkNoneAccess']     = $checkNoneAccess;
        $request['checkReadAccess']     = $checkReadAccess;
        $request['checkWriteAccess']    = $checkWriteAccess;
        $request['checkAccessAccess']   = $checkAccessAccess;
        $request['checkCreateAccess']   = $checkCreateAccess;
        $request['checkCopyAccess']     = $checkCopyAccess;
        $request['checkDeleteAccess']   = $checkDeleteAccess;
        $request['checkDownloadAccess'] = $checkDownloadAccess;
        $request['checkAdminAccess']    = $checkAdminAccess;

        if (null === $model->uid) {
            $model->uid = md5($date . $participantId . time());
        }

        Default_Helpers_Save::save($model, $request);

        // Update the event for save the parentId with the id
        if (null === $parentId) {
            $request['parentId'] = $model->id;
            Default_Helpers_Save::save($model, $request);
        }

        return $model->parentId;
    }
}
