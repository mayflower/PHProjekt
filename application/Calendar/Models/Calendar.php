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

    private $_notifParticipants;
    private $_startDate;

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
            $sendNotification = false;
            if (array_key_exists('sendNotification', $request)) {
                if ($request['sendNotification'] == 'on') {
                    $sendNotification = true;
                    $request['sendNotification'] = '';
                }
            }

            // New Event
            $totalParticipants = count($participantsList);
            $participantNumber = 0;
            foreach ($participantsList as $participantId) {
                $participantNumber ++;

                // Last participant?
                if ($participantNumber == $totalParticipants) {
                    $lastParticipant = true;
                } else {
                    $lastParticipant = false;
                }
                $returnId = self::_saveNewEvent($request, $eventDates, $participantId, $lastParticipant,
                                                $sendNotification, $participantsList);
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
     * @return string
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
    private function _saveNewEvent($request, $eventDates, $participantId, $lastParticipant, $sendNotification,
                                   $participantsList)
    {
        static $parentId = null;

        $model                     = Phprojekt_Loader::getModel('Calendar', 'Calendar');
        $model->_startDate         = $request['startDate'];
        $model->_notifParticipants = implode(",", $participantsList);

        $totalEvents = count($eventDates);
        $eventNumber = 0;
        $lastEvent   = false;
        foreach ($eventDates as $oneDate) {
            $eventNumber ++;
            // Last participant and last event? -> check if it was requested to send notification
            if ($eventNumber == $totalEvents) {
                $lastEvent = true;
            }
            if ($lastParticipant & $lastEvent & $sendNotification) {
                $request['sendNotification'] = 'on';
            }

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
        $model                     = Phprojekt_Loader::getModel('Calendar', 'Calendar');
        $model->_startDate         = $request['startDate'];
        $model->_notifParticipants = implode(",", $participantsList);

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
            $found = false;
            if (null === $record->deleted && null !== $record->rrule) {
                $currentParticipants[$record->participantId] = $record->participantId;
            }
            foreach ($eventDates as $oneDate) {
                $date = date("Y-m-d", $oneDate->get());
                if (!$found && $date == $record->startDate) {
                    // Update old entry of recurrence
                    self::_saveEvent($request, $record, $oneDate, $record->participantId, $record->parentId);

                    // If notification email was requested, then send it just once
                    $request['sendNotification'] = '';

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

                        // If notification email was requested, then send it just once
                        $request['sendNotification'] = '';
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

                            // If notification email was requested, then send it just once
                            $request['sendNotification'] = '';
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
        $model                     = Phprojekt_Loader::getModel('Calendar', 'Calendar');
        $model->_startDate         = $request['startDate'];
        $model->_notifParticipants = implode(",", $participantsList);

        $clone = clone($model);

        $addParticipants = true;
        $model->find($id);

        $oneDate = $eventDates[0];
        self::_saveEvent($request, $model, $oneDate, $model->participantId, $model->parentId);

        // If notification email was requested, then send it just once
        $request['sendNotification'] = '';

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
            $request['sendNotification'] = '';
            $request['parentId'] = $model->id;
            Default_Helpers_Save::save($model, $request);
        }

        return $model->parentId;
    }

    /**
     * Gets all the recipients for the mail notification
     *
     * @return string
     */
    public function getNotificationRecipients()
    {
        return $this->_notifParticipants;
    }

    /**
     * Returns the body 'Current data' part of the Notification email
     *
     * @return array
     */
    public function getNotificationBodyData()
    {
        $bodyData   = Array();
        $bodyData[] = array('label' => Phprojekt::getInstance()->translate('Title'),
                            'value' => $this->title);
        $bodyData[] = array('label' => Phprojekt::getInstance()->translate('Notes'),
                            'value' => $this->notes);
        $bodyData[] = array('label' => Phprojekt::getInstance()->translate('startDate'),
                            'value' => $this->_translateDate($this->_startDate));
        $bodyData[] = array('label' => Phprojekt::getInstance()->translate('Start Time'),
                            'value' => substr($this->startTime, 0, 5));
        $bodyData[] = array('label' => Phprojekt::getInstance()->translate('End Time'),
                            'value' => substr($this->endTime, 0, 5));

        $phpUser           = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
        $participants      = split(",", $this->_notifParticipants);
        $participantsValue = "";
        $i                 = 0;
        $lastItem          = count($participants);

        // Participants field 
        foreach($participants as $participant) {
            $i++;
            $phpUser->find((int) $participant);
            $fullname = trim($phpUser->firstname . ' ' . $phpUser->lastname);
            if (!empty($fullname)) {
                $participantsValue .= $fullname . ' (' . $phpUser->username . ')';
            } else {
                $participantsValue .= $phpUser->username;
            }
            if ($i < $lastItem) {
                $participantsValue .= ", "; 
            }
        }
        $bodyData[] = array('label' => Phprojekt::getInstance()->translate('Participants'),
                            'value' => $participantsValue);

        if ($this->rrule != null) {
            $bodyData = array_merge($bodyData, $this->_getRruleDescriptive($this->rrule));
        }

        return $bodyData;
    }

    /**
     * Returns the body 'Changes done' part of the Notification email
     *
     * @return array
     */
    public function getNotificationBodyChanges($changes) {
        $fieldDefinition = $this->getInformation()->getFieldDefinition(Phprojekt_ModelInformation_Default::ORDERING_FORM);

        // Iterate in every change done
        for ($i = 0; $i < count($changes); $i++) {

            // Translate the name of the field
            foreach ($fieldDefinition as $field) {
                // Find the field definition for the field that has been modified
                if ($field['key'] == $changes[$i]['field']) {
                    $changes[$i]['field'] = $field['label'];
                }
            }

            // Recurrence
            if ($changes[$i]['field'] == 'rrule') {
                $oldRruleEmpty = false;
                $newRruleEmpty = false;
                if ($changes[$i]['oldValue'] != null) {
                    $oldRrule = $this->_getRruleDescriptive($changes[$i]['oldValue']);
                } else {
                    $oldRruleEmpty = true;
                }
                if ($changes[$i]['newValue'] != null) {
                    $newRrule = $this->_getRruleDescriptive($changes[$i]['newValue']);
                } else {
                    $newRruleEmpty = true;
                }

                // FIELDS: Repeats, Interval and Until
                for ($i=0; $i < 3; $i++) {
                    if (!$oldRruleEmpty) {
                        $fieldName = Phprojekt::getInstance()->translate('Recurrence') . " - " .
                            $oldRrule[$i]['label'];
                    } else {
                        $fieldName = Phprojekt::getInstance()->translate('Recurrence') . " - " .
                            $newRrule[$i]['label'];
                    }
                    if (!$oldRruleEmpty) {
                        $fieldOldValue = $oldRrule[$i]['value'];
                    } else {
                        $fieldOldValue = "";
                    }
                    if (!$newRruleEmpty) {
                        $fieldNewValue = $newRrule[$i]['value'];
                    } else {
                        $fieldNewValue = "";
                    }
                    if ($fieldOldValue != $fieldNewValue) {
                        $changes[] = Array('field'    => $fieldName,
                                           'oldValue' => $fieldOldValue,
                                           'newValue' => $fieldNewValue);
                    }
                }

                // FIELD: Weekday (optional)
                $oldWeekDayExists = false;
                if (!$oldRruleEmpty) {
                    if (count($oldRrule) == 4) {
                        $oldWeekDayExists = true;
                    }
                }
                $newWeekDayExists = false;
                if (!$newRruleEmpty) {
                    if (count($newRrule) == 4) {
                        $newWeekDayExists = true;
                    }
                }
                if ($oldWeekDayExists || $newWeekDayExists) {
                    if (!$oldRruleEmpty) {
                        $fieldName = Phprojekt::getInstance()->translate('Recurrence') . " - " .
                            $oldRrule[3]['label'];
                        $fieldOldValue = $oldRrule[3]['value'];
                    } else {
                        $fieldOldValue = "";
                    }
                    if (!$newRruleEmpty) {
                        $fieldName = Phprojekt::getInstance()->translate('Recurrence') . " - " .
                            $newRrule[3]['label'];
                        $fieldNewValue = $newRrule[3]['value'];
                    } else {
                        $fieldOldValue = "";
                    }

                    if ($fieldOldValue != $fieldNewValue) {
                        $changes[] = Array('field'    => $fieldName,
                                           'oldValue' => $fieldOldValue,
                                           'newValue' => $fieldNewValue);
                    }
                }
            } else if ($changes[$i]['field'] == 'startDate') {
                $changes[$i]['oldValue'] = $this->_translateDate($changes[$i]['oldValue']);
                $changes[$i]['newValue'] = $this->_translateDate($changes[$i]['newValue']);
            }
        }

        // Take out the original confusing 'rrule' element, if it is there
        for($i = 0; $i < count($changes); $i++) {
            if ($changes[$i]['field'] == 'rrule') {
                unset($changes[$i]);
            }
        }

        return $changes;
    }

    /**
     * Returns the body 'Changes done' part of the Notification email
     *
     * @param string   $rrule      String with the recurrence 'rrule' field, as it is saved in the DB.
     * 
     * @return array
     */
    private function _getRruleDescriptive($rrule) {
        $tmp1     = split(";", $rrule);
        $tmp2     = split("=", $tmp1[0]);
        $freq     = $tmp2[1];
        $tmp2     = split("=", $tmp1[1]);
        $until    = $tmp2[1];
        $tmp2     = split("=", $tmp1[2]);
        $interval = $tmp2[1];
        $tmp2     = split("=", $tmp1[3]);
        $byday    = $tmp2[1];

        $freq = ucfirst(strtolower($freq));
        $rruleFields[] = array('label' => Phprojekt::getInstance()->translate('Repeats'),
                               'value' => Phprojekt::getInstance()->translate($freq));
        $rruleFields[] = array('label' => Phprojekt::getInstance()->translate('Interval'),
                               'value' => Phprojekt::getInstance()->translate($interval));

        if ($until != null) {
            $year      = substr($until, 0, 4);
            $month     = substr($until, 4, 2);
            $dayNum    = substr($until, 6, 2);
            $untilDate = mktime(0, 0, 0, $month, $dayNum, $year);
            $dayDesc   = date("D", $untilDate);
            $monthDesc = date("M", $untilDate);
            $untilDesc = $dayDesc . " " . $monthDesc . " " . $dayNum . " " . $year;

            $rruleFields[] = array('label' => Phprojekt::getInstance()->translate('Until'),
                                  'value' => $this->_translateDate($untilDesc));
        }

        if ($byday != null) {
            switch ($byday) {
                case 'MO':
                    $weekDay = "Monday";
                    break;
                case 'TU':
                    $weekDay = "Tuesday";
                    break;
                case 'WE':
                    $weekDay = "Wednesday";
                    break;
                case 'TH':
                    $weekDay = "Thursday";
                    break;
                case 'FR':
                    $weekDay = "Friday";
                    break;
                case 'SA':
                    $weekDay = "Saturday";
                    break;
                case 'SU':
                    $weekDay = "Sunday";
                    break;
            }

            $rruleFields[] = array('label' => 'Weekdays',
                                   'value' => Phprojekt::getInstance()->translate($weekDay));
        }

    return $rruleFields;
    }

    /**
     * Converts the date format and language, from an english '2009-04-25' or 'Sat Apr 25 2009' to
     * 'Wednesday - March 24 2009' in the according language. 
     *
     * @param string  $date      String with the original date in english
     * 
     * @return string
     */
    private function _translateDate($date)
    {
        if (strlen($date) == 10) {
            // '2009-04-25' style
            $year      = (int) substr($date, 0, 4);
            $month     = (int) substr($date, 5, 2);
            $day       = (int) substr($date, 8, 2);
        } else {
            // 'Sat Apr 25 2009' style
            $monthShort = substr($date, 4, 3);
            $day        = (int) substr($date, 8, 2);
            $year       = (int) substr($date, 11, 4);

            switch ($monthShort) {
                case 'Jan':
                    $month = 1;
                    break;
                case 'Feb':
                    $month = 2;
                    break;
                case 'Mar':
                    $month = 3;
                    break;
                case 'Apr':
                    $month = 4;
                    break;
                case 'May':
                    $month = 5;
                    break;
                case 'Jun':
                    $month = 6;
                    break;
                case 'Jul':
                    $month = 7;
                    break;
                case 'Ago':
                    $month = 8;
                    break;
                case 'Sep':
                    $month = 9;
                    break;
                case 'Oct':
                    $month = 10;
                    break;
                case 'Nov':
                    $month = 11;
                    break;
                case 'Dec':
                    $month = 12;
                    break;
            }
        }
        $dateUnix  = mktime(0, 0, 0, $month, $day, $year);
        $dayDesc   = Phprojekt::getInstance()->translate(date("l", $dateUnix));
        $monthDesc = Phprojekt::getInstance()->translate(date("F", $dateUnix));
        $dateString = $dayDesc . " - " . $monthDesc . " " . $day . " " . $year;

        return $dateString;
    }
}
