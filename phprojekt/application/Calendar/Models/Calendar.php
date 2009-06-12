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
    private $_endDate;

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
    public function saveEvent($request, $id, $startDate, $endDate, $rrule, $participants, $multipleEvents)
    {
        $userId           = Phprojekt_Auth::getUserId();
        $participantsList = array();
        $daysDuration     = (strtotime($endDate) - strtotime($startDate)) / (24*60*60);
        $parentId         = null;

        // Getting requested dates for the serial meeting (if it is serial)
        if (!empty($rrule)) {
            if (!$this->_validateRecurrence($rrule)) {
                $error = array_pop($this->getError());
                throw new Phprojekt_PublishedException($error['label'] . ': ' . $error['message']);
            }
            if ($multipleEvents) {
                $model = clone($this);
                $model->find($id);
                // If the startDate has changed, apply that difference of days to all the events of recurrence
                if ($startDate != $model->startDate) {
                    $startDateZendPm = new Zend_Date($startDate);
                    $startDiff       = $startDateZendPm->compare($model->startDate);
                    $startDate       = $this->getRecursionStartDate($id, $startDate);
                    $startDateZendNw = new Zend_Date($startDate);
                    $startDateZendNw->addDay($startDiff);
                    $startDate = $startDateZendNw->get();
                    $startDate = date("Y-m-d", $startDate);
                }
            }
            $dateCollection = new Phprojekt_Date_Collection($startDate);
            $dateCollection->applyRrule($rrule);
            $eventDates = $dateCollection->getValues();
        } else {
            $eventDates = array(strtotime($startDate));
        }

        // We will put the owner id first, just to make it clear
        if (!in_array($userId, $participants)) {
            $participantsList[$userId] = $userId;
        }
        foreach ($participants as $oneParticipant) {
            $participantsList[(int) $oneParticipant] = (int) $oneParticipant;
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

                $parentId = $this->_saveNewEvent($request, $eventDates, $daysDuration, $participantId, $lastParticipant,
                                                $sendNotification, $participantsList, $parentId);
                if ($id == 0) {
                    $id = $parentId;
                }
            }
        } else {
            // Edit Multiple Events
            if ($multipleEvents) {
                $this->_updateMultipleEvents($request, $id, $eventDates, $daysDuration, $participantsList);
            } else {
                $this->_updateSingleEvent($request, $id, $eventDates, $daysDuration, $participantsList);
            }
        }

        return $id;
    }

    /**
     * Returns the id of the root event of the current record
     *
     * @param Phprojekt_Model_Interface $model The model to check
     *
     * @return integer id of the root event
     */
    public function getRootEventId($model)
    {
        $rootEventId = null;

        if ($model->parentId > 0) {
            $rootEventId = (int) $model->parentId;
        } else {
            $rootEventId = ($model->id > 0) ? (int) $model->id : 0;
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
        if (Phprojekt_Module::getSaveType(Phprojekt_Module::getId($this->getModelName())) >= 1) {
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
            $rootEventId  = $this->getRootEventId($this);
            $where        = sprintf('parent_id = %d OR id = %d', (int) $rootEventId, (int) $rootEventId);
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
        $model = clone($this);
        $model->find($id);

        $rootEventId = $this->getRootEventId($model);
        $where       = sprintf('(parent_id = %d OR id = %d) AND participant_id = %d', (int) $rootEventId,
            (int) $rootEventId, (int) $model->participantId);
        $records = $model->fetchAll($where, 'start_date ASC');

        if ($this->_isOwner($model)) {
            // Only use the new startDate if the user is owner
            // and the starDate is less than the minimal value for startDate
            //if ($records[0]->startDate < $startDate) {
                $startDate = $records[0]->startDate;
            //}
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
            $rootEventId = $this->getRootEventId($this);
            if (!$this->_isOwner($this)) {
                $where = sprintf('(parent_id = %d OR id = %d) AND participant_id = %d', (int) $rootEventId,
                    (int) $rootEventId, (int) $this->participantId);
            } else {
                $where = sprintf('(parent_id = %d OR id = %d)', (int) $rootEventId, (int) $rootEventId);
            }

            $records = $this->fetchAll($where);
            foreach ($records as $record) {
                Default_Helpers_Delete::delete($record);
            }
        } else {
            Default_Helpers_Delete::delete($this);
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
        $db    = Phprojekt::getInstance()->getDb();
        $date  = $db->quote($date);
        $where = sprintf('participant_id IN (%s) AND start_date <= %s AND end_date >= %s', $usersId, $date, $date);

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
    private function _saveNewEvent($request, $eventDates, $daysDuration, $participantId, $lastParticipant,
                                   $sendNotification, $participantsList, $parentId)
    {
        $this->_startDate         = $request['startDate'];
        $this->_endDate           = $request['endDate'];
        $this->_notifParticipants = implode(",", $participantsList);

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

            $clone    = clone($this);
            $parentId = $this->_saveEvent($request, $clone, $oneDate, $daysDuration, $participantId, $parentId);
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
     * @param array   $request          Array with the POST data
     * @param integer $id               Id of the current event
     * @param array   $eventDates       Array with the dates of the recurring
     * @param array   $participantsList Array with the users involved in the event
     *
     * @return void
     */
    private function _updateMultipleEvents($request, $id, $eventDates, $daysDuration, $participantsList)
    {
        $this->_startDate         = $request['startDate'];
        $this->_endDate           = $request['startDate'];
        $this->_notifParticipants = implode(",", $participantsList);

        $addParticipants = true;
        $this->find($id);

        $rootEventId = $this->getRootEventId($this);
        $where       = sprintf('(parent_id = %d OR id = %d)', (int) $rootEventId, (int) $rootEventId);
        if (!$this->_isOwner($this)) {
            // Save only the own events under the parentId
            $where .= sprintf(' AND participant_id = %d', (int) $this->participantId);
        }

        $currentParticipants = array();
        $records             = $this->fetchAll($where);
        foreach ($records as $record) {
            $found = false;
            if (null !== $record->rrule) {
                $currentParticipants[$record->participantId] = $record->participantId;
            }
            foreach ($eventDates as $oneDate) {
                $date = date("Y-m-d", $oneDate);
                if (!$found && $date == $record->startDate) {
                    // Update old entry of recurrence
                    $this->_saveEvent($request, $record, $oneDate, $daysDuration, $record->participantId,
                                     $record->parentId);

                    // If notification email was requested, then send it just once
                    $request['sendNotification'] = '';

                    $found = true;
                    break;
                }
            }

            if (!$found) {
                // Delete old entry of recurrence
                Default_Helpers_Delete::delete($record);
            }
        }

        // Only for owners
        if ($this->_isOwner($this)) {
            // Add the new entry of recurrence
            foreach ($eventDates as $oneDate) {
                $newEntry = true;
                $date     = date("Y-m-d", $oneDate);
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
                        $newModel = clone($this);
                        $this->_saveEvent($request, $newModel, $oneDate, $daysDuration, $participantId,
                                         $this->getRootEventId($this));

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
                    $removeModel = clone($this);
                    $rootEventId = $this->getRootEventId($this);
                    $where       = sprintf('(parent_id = %d OR id = %d) AND participant_id = %d', (int) $rootEventId,
                        (int) $rootEventId, (int) $currentParticipantId);
                    $records = $removeModel->fetchAll($where);
                    foreach ($records as $record) {
                        Default_Helpers_Delete::delete($record);
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
                            $newModel = clone($this);
                            $parentId = $this->getRootEventId($this);
                            $this->_saveEvent($request, $newModel, $oneDate, $daysDuration, $participantId, $parentId);

                            // If notification email was requested, then send it just once
                            $request['sendNotification'] = '';
                        }
                    }
                }
            }
        }
    }

    /**
     * Update only one event
     *
     * @param array $request          Array with the POST data
     * @param integer $id             Id of the current event
     * @param array $eventDates       Array with the dates of the recurring
     * @param array $participantsList Array with the users involved in the event
     *
     * @return void
     */
    private function _updateSingleEvent($request, $id, $eventDates, $daysDuration, $participantsList)
    {
        $this->_startDate         = $request['startDate'];
        $this->_endDate           = $request['endDate'];
        $this->_notifParticipants = implode(",", $participantsList);

        $this->find($id);

        $oneDate = $eventDates[0];
        $this->_saveEvent($request, $this, $oneDate, $daysDuration, $this->participantId, $this->parentId);

        // If notification email was requested, then send it just once
        $request['sendNotification'] = '';

        $rootEventId = $this->getRootEventId($this);
        if (!$this->_isOwner($this)) {
            // Save only the own events under the parentId
            $where = sprintf('(parent_id = %d OR id = %d) AND participant_id = %d', (int) $rootEventId,
                (int) $rootEventId, (int) $this->participantId);
        } else {
            // Save all the events under the parentId
            $where = sprintf('(parent_id = %d OR id = %d)', (int) $rootEventId, (int) $rootEventId);
        }

        $currentParticipants = array();
        $records             = $this->fetchAll($where);
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
                $removeModel = clone($this);
                $rootEventId = $this->getRootEventId($this);
                $where       = sprintf('(parent_id = %d OR id = %d) AND participant_id = %d AND start_date = %s',
                    (int) $rootEventId, (int) $rootEventId, (int) $currentParticipantId,
                    $this->getAdapter()->quote(date("Y-m-d", $oneDate)));
                $records = $removeModel->fetchAll($where);
                foreach ($records as $record) {
                    Default_Helpers_Delete::delete($record);
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
                $newModel = clone($this);
                $this->_saveEvent($request, $newModel, $oneDate, $daysDuration, $participantId,
                    $this->getRootEventId($this));
            }
        }
    }

    /**
     * Do the save for the event
     * Add the full access to the owner and Read, Write and Delete access to the user involved
     *
     * @param array                     $request       Array with the POST data
     * @param Phprojekt_Model_Interface $model         The model to check
     * @param Phprojekt_Date_Collection $oneDate       Date object to save
     * @param integer                   $participantId Id of the user to save the event
     * @param integer                   $parentId      Id of the parent event
     *
     * @return integer The parentId
     */
    private function _saveEvent($request, $model, $oneDate, $daysDuration, $participantId, $parentId)
    {
        $date                     = date("Y-m-d", $oneDate);
        $request['startDate']     = $date;
        $request['endDate']       = date("Y-m-d", strtotime($date) + ($daysDuration * 24 * 60 * 60));
        $request['participantId'] = $participantId;
        $request['parentId']      = $parentId;

        // The save is needed?
        if ($this->_needSave($model, $request)) {
            // Add 'read, write and delete' access to the participant
            $request = Default_Helpers_Right::allowReadWriteDelete($request, $participantId);

            // Access for the owner
            if (null !== $model->ownerId) {
                $ownerId = $model->ownerId;
            } else {
                $ownerId = Phprojekt_Auth::getUserId();
            }
            $request = Default_Helpers_Right::allowAll($request, $ownerId);

            if (null === $model->uid) {
                $model->uid = md5($date . $participantId . time());
            }

            Default_Helpers_Save::save($model, $request);
        }

        if (null === $parentId) {
            $model->parentId = $model->id;
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
        $bodyData   = array();
        $bodyData[] = array('label' => Phprojekt::getInstance()->translate('Title'),
                            'value' => $this->title);
        $bodyData[] = array('label' => Phprojekt::getInstance()->translate('Place'),
                            'value' => $this->place);
        $bodyData[] = array('label' => Phprojekt::getInstance()->translate('Notes'),
                            'value' => $this->notes);
        $bodyData[] = array('label' => Phprojekt::getInstance()->translate('Start date'),
                            'value' => $this->_translateDate($this->_startDate));
        $bodyData[] = array('label' => Phprojekt::getInstance()->translate('Start time'),
                            'value' => substr($this->startTime, 0, 5));
        $bodyData[] = array('label' => Phprojekt::getInstance()->translate('End date'),
                            'value' => $this->_translateDate($this->_endDate));
        $bodyData[] = array('label' => Phprojekt::getInstance()->translate('End time'),
                            'value' => substr($this->endTime, 0, 5));

        $phpUser           = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
        $participants      = explode(",", $this->_notifParticipants);
        $participantsValue = "";
        $i                 = 0;
        $lastItem          = count($participants);

        // Participants field
        foreach ($participants as $participant) {
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

        if ($this->rrule !== null) {
            $bodyData = array_merge($bodyData, $this->_getRruleDescriptive($this->rrule));
        }

        return $bodyData;
    }

    /**
     * Returns the body 'Changes done' part of the Notification email
     *
     * @return array
     */
    public function getNotificationBodyChanges($changes)
    {
        $order           = Phprojekt_ModelInformation_Default::ORDERING_FORM;
        $fieldDefinition = $this->getInformation()->getFieldDefinition($order);

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
            if (strtolower($changes[$i]['field']) == 'rrule') {
                $oldRruleEmpty = false;
                $newRruleEmpty = false;
                if ($changes[$i]['oldValue'] !== null) {
                    $oldRrule = $this->_getRruleDescriptive($changes[$i]['oldValue']);
                } else {
                    $oldRruleEmpty = true;
                }
                if ($changes[$i]['newValue'] !== null) {
                    $newRrule = $this->_getRruleDescriptive($changes[$i]['newValue']);
                } else {
                    $newRruleEmpty = true;
                }

                // FIELDS: Repeats, Interval and Until
                for ($i=0; $i < 3; $i++) {
                    if (!$oldRruleEmpty) {
                        $fieldName = Phprojekt::getInstance()->translate('Recurrence') . " - " . $oldRrule[$i]['label'];
                    } else {
                        $fieldName = Phprojekt::getInstance()->translate('Recurrence') . " - " . $newRrule[$i]['label'];
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
                        $changes[] = array('field'    => $fieldName,
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
                        $fieldName = Phprojekt::getInstance()->translate('Recurrence') . " - " . $oldRrule[3]['label'];
                        $fieldOldValue = $oldRrule[3]['value'];
                    } else {
                        $fieldOldValue = "";
                    }
                    if (!$newRruleEmpty) {
                        $fieldName = Phprojekt::getInstance()->translate('Recurrence') . " - " . $newRrule[3]['label'];
                        $fieldNewValue = $newRrule[3]['value'];
                    } else {
                        $fieldOldValue = "";
                    }

                    if ($fieldOldValue != $fieldNewValue) {
                        $changes[] = array('field'    => $fieldName,
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
        for ($i = 0; $i < count($changes); $i++) {
            if ($changes[$i]['field'] == 'rrule') {
                unset($changes[$i]);
            }
        }

        return $changes;
    }

    /**
     * Returns the body 'Changes done' part of the Notification email
     *
     * @param string $rrule String with the recurrence 'rrule' field, as it is saved in the DB.
     *
     * @return array
     */
    private function _getRruleDescriptive($rrule)
    {
        $tmp1     = explode(";", $rrule);
        $tmp2     = explode("=", $tmp1[0]);
        $freq     = $tmp2[1];
        $tmp2     = explode("=", $tmp1[1]);
        $until    = $tmp2[1];
        $tmp2     = explode("=", $tmp1[2]);
        $interval = $tmp2[1];
        $tmp2     = explode("=", $tmp1[3]);
        $byday    = $tmp2[1];
        $freq     = ucfirst(strtolower($freq));

        $rruleFields[] = array('label' => Phprojekt::getInstance()->translate('Repeats'),
                               'value' => Phprojekt::getInstance()->translate($freq));
        $rruleFields[] = array('label' => Phprojekt::getInstance()->translate('Interval'),
                               'value' => Phprojekt::getInstance()->translate($interval));

        if ($until !== null) {
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

        if ($byday !== null) {
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
     * @param string $date String with the original date in english
     *
     * @return string
     */
    private function _translateDate($date)
    {
        if (strlen($date) == 10) {
            // '2009-04-25' style
            $year  = (int) substr($date, 0, 4);
            $month = (int) substr($date, 5, 2);
            $day   = (int) substr($date, 8, 2);
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
        $dateUnix   = mktime(0, 0, 0, $month, $day, $year);
        $dayDesc    = Phprojekt::getInstance()->translate(date("l", $dateUnix));
        $monthDesc  = Phprojekt::getInstance()->translate(date("F", $dateUnix));
        $dateString = $dayDesc . " - " . $monthDesc . " " . $day . " " . $year;

        return $dateString;
    }

    /**
     * Check if some of the fields was changed
     *
     * @param Object $model   Calendar model
     * @param array  $request Array with POST values
     *
     * @return boolean
     */
    private function _needSave($model, $request)
    {
        $save = false;

        foreach ($request as $k => $v) {
            if (isset($model->$k)) {
                if ($model->$k != $v && $k != 'id' && $k != 'rrule') {
                    $save = true;
                    break;
                }
            }
        }

        return $save;
    }

    /**
     * Recurrence fields basic validation
     *
     * @param String $rrule Event recurrence
     *
     * @return boolean
     */
    private function _validateRecurrence($rrule)
    {
        $valid = true;

        // Parse 'rrule' values
        $rruleItems = explode(";", $rrule);
        $freq       = null;
        $interval   = null;
        $until      = null;
        foreach ($rruleItems as $rruleItem) {
            $item = explode("=", $rruleItem);
            switch ($item[0]) {
                case 'FREQ':
                    $freq = $item[1];
                    break;
                case 'INTERVAL':
                    $interval = (int) $item[1];
                    break;
                case 'UNTIL':
                    $until = $item[1];
                    break;
                case 'BYDAY':
                default:
                    break;
            }
        }

        // Do the checking
        if ($freq !== null) {
            if ($interval === null || $interval < 0 || $interval > 1000) {
                $valid = false;
                $this->_validate->error->addError(array(
                    'field'   => 'Interval',
                    'label'   => Phprojekt::getInstance()->translate('Interval'),
                    'message' => Phprojekt::getInstance()->translate('Wrong Recurrence Interval')));
            } else {
                if ($until === null) {
                    $valid = false;
                    $this->_validate->error->addError(array(
                        'field'   => 'Until',
                        'label'   => Phprojekt::getInstance()->translate('Until'),
                        'message' => Phprojekt::getInstance()->translate('Incomplete Recurrence Until field')));
                }
            }
        }

        return $valid;
    }

    /**
     * Returns all the connected events (by the parentId) for the logged user
     *
     * @return array
     */
    public function getRelatedEvents()
    {
        $rootEventId = $this->getRootEventId($this);
        $userId      = Phprojekt_Auth::getUserId();
        $where       = sprintf('(parent_id = %d OR id = %d) AND participant_id = %d',
            (int) $rootEventId, (int) $rootEventId, (int) $userId);
        $records = $this->fetchAll($where);
        $return  = array();
        foreach ($records as $record) {
            if ($record->id != $this->id) {
                $return[] = $record->id;
            }
        }
        return $return;
    }
}
