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

    public $notifParticipants;
    public $startDateNotif;
    public $endDateNotif;

    /**
     * Save or inserts an event. It inserts one envent by participant
     *
     * @param array   $request              Array with all the data for the item (Basic Data)
     * @param integer $id                   Current item id or null for new one
     * @param string  $startDate            Date of the event
     * @param string  $rrule                Rule for apply the recurring
     * @param array   $participants         Array with the users involved in the event
     * @param boolean $multipleEvents       Apply changes to one event or all the recurring events
     * @param boolean $multipleParticipants Action for multiple participants or just the logged one
     *
     * @return integer the id of the root event
     */
    public function saveEvent($request, $id, $startDate, $endDate, $rrule, $participants, $multipleEvents,
        $multipleParticipants)
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
                    $diffSeconds    = strtotime($startDate) - strtotime($model->startDate);
                    $startDateTemp  = $this->getRecursionStartDate($id, $startDate);
                    $startDateTemp  = strtotime($startDate) + $diffSeconds;
                    $startDate      = date("Y-m-d", $startDateTemp);
                } else {
                    $startDate = $this->getRecursionStartDate($id, $startDate);
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
                $this->_updateMultipleEvents($request, $id, $eventDates, $daysDuration, $participantsList,
                    $multipleParticipants);
            } else {
                $this->_updateSingleEvent($request, $id, $eventDates, $daysDuration, $participantsList,
                    $multipleParticipants);
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
            $where        = sprintf('(parent_id = %d OR id = %d) AND start_date = %s', (int) $rootEventId,
                (int) $rootEventId, $this->getAdapter()->quote($this->startDate));
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

        $startDate = null;
        // Always use the minimal value for startDate
        if (isset($records[0])) {
            $startDate = $records[0]->startDate;
        }

        return $startDate;
    }

    /**
     * Delete routine
     *
     * Deletes single or multiple events, both for single or multiple participants.
     *
     * @param boolean $multipleEvents       Action for multiple events or single one
     * @param boolean $multipleParticipants Action for multiple participants or just the logged one
     *
     * @return void
     */
    public function deleteEvents($multipleEvents, $multipleParticipants)
    {
        $rootEventId    = $this->getRootEventId($this);
        $where          = sprintf('(parent_id = %d OR id = %d)', (int) $rootEventId, (int) $rootEventId);
        $alreadyDeleted = false;

        if ($multipleEvents) {
            if (!$this->_isOwner($this) || !$multipleParticipants) {
                $where .= sprintf(' AND participant_id = %d', (int) $this->participantId);
            }
        } else {
            if (!$this->_isOwner($this) || !$multipleParticipants) {
                Default_Helpers_Delete::delete($this);
                $alreadyDeleted = true;
            } else {
                $where .= sprintf(' AND start_date = %s', $this->getAdapter()->quote($this->startDate));
            }
        }

        if (!$alreadyDeleted) {
            $records = $this->fetchAll($where);
            foreach ($records as $record) {
                Default_Helpers_Delete::delete($record);
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
        $this->startDateNotif     = $request['startDate'];
        $this->endDateNotif       = $request['endDate'];
        $this->notifParticipants  = $participantsList;

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
     * @param array   $request              Array with the POST data
     * @param integer $id                   Id of the current event
     * @param array   $eventDates           Array with the dates of the recurring
     * @param array   $participantsList     Array with the users involved in the event
     * @param boolean $multipleParticipants Action for multiple participants or just the logged one
     *
     * @return void
     */
    private function _updateMultipleEvents($request, $id, $eventDates, $daysDuration, $participantsList,
        $multipleParticipants)
    {
        $this->startDateNotif     = $request['startDate'];
        $this->endDateNotif       = $request['endDate'];
        $this->notifParticipants  = $participantsList;

        $this->find($id);

        $rootEventId = $this->getRootEventId($this);
        $where       = sprintf('(parent_id = %d OR id = %d)', (int) $rootEventId, (int) $rootEventId);
        if (!$this->_isOwner($this) || !$multipleParticipants) {
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
            // Add any new occurrences of the event for all participants
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
                    foreach ($participantsList as $participantId) {
                        if (Phprojekt_Auth::getUserId() == $participantId || $multipleParticipants) {
                            $newModel = clone($this);
                            $this->_saveEvent($request, $newModel, $oneDate, $daysDuration, $participantId,
                                $this->getRootEventId($this));

                            // If notification email was requested, then send it just once
                            $request['sendNotification'] = '';
                        }
                    }
                }
            }

            if ($multipleParticipants) {
                // Delete removed participants
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
                        $where       = sprintf('(parent_id = %d OR id = %d) AND participant_id = %d',
                            (int) $rootEventId, (int) $rootEventId, (int) $currentParticipantId);
                        $recordsDelete = $removeModel->fetchAll($where);
                        foreach ($recordsDelete as $record) {
                            Default_Helpers_Delete::delete($record);
                        }
                    }
                }

                // Add new participants to existing events
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
                            if (Phprojekt_Auth::getUserId() == $participantId || $multipleParticipants) {
                                $newModel       = clone($this);
                                $parentId       = $this->getRootEventId($this);
                                $addParticipant = false;
                                foreach ($records as $record) {
                                    if (strtotime($record->startDate) == $oneDate) {
                                        $addParticipant = true;
                                        break;
                                    }
                                }
                                if ($addParticipant) {
                                    $this->_saveEvent($request, $newModel, $oneDate, $daysDuration, $participantId,
                                        $parentId);
                                    // If notification email was requested, then send it just once
                                    $request['sendNotification'] = '';
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Update only one event
     *
     * @param array   $request              Array with the POST data
     * @param integer $id                   Id of the current event
     * @param array   $eventDates           Array with the dates of the recurring
     * @param array   $participantsList     Array with the users involved in the event
     * @param boolean $multipleParticipants Action for multiple participants or just the logged one*
     *
     * @return void
     */
    private function _updateSingleEvent($request, $id, $eventDates, $daysDuration, $participantsList,
        $multipleParticipants)
    {
        $this->startDateNotif     = $request['startDate'];
        $this->endDateNotif       = $request['endDate'];
        $this->notifParticipants  = $participantsList;

        $this->find($id);

        $oneDate = $eventDates[0];
        $this->_saveEvent($request, $this, $oneDate, $daysDuration, $this->participantId, $this->parentId);

        // Edit the rest of participants?
        if ($this->_isOwner($this) && $multipleParticipants) {
            // If notification email was requested, then send it just once
            $request['sendNotification'] = '';

            $rootEventId = $this->getRootEventId($this);
            $where       = sprintf('(parent_id = %d OR id = %d) AND start_date = %s', (int) $rootEventId,
                (int) $rootEventId, $this->getAdapter()->quote(date("Y-m-d", $oneDate)));

            $currentParticipants = array();
            $records             = $this->fetchAll($where);
            foreach ($records as $record) {
                $currentParticipants[$record->participantId] = $record->participantId;
                $currentParticipantId                        = $record->participantId;

                $found = false;
                foreach ($participantsList as $participantId) {
                    if ($participantId == $currentParticipantId) {
                        $found = true;
                        if ($participantId != $this->ownerId) {
                            // Update basic data
                            $this->_saveEvent($request, $record, $oneDate, $daysDuration, $participantId, $this->id);
                            break;
                        }
                    }
                }
                if (!$found) {
                    // Delete removed participant
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
     * Returns the rrule in descriptive mode
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
                                   'value' => $this->translateDate($untilDesc));
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
    public function translateDate($date)
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
     * Returns all the events connected with the current one by the parentId, for the logged user as participant.
     * Doesn't return the current event among them
     *
     * @return array
     */
    public function getRelatedEvents()
    {
        $return      = array();
        $rootEventId = $this->getRootEventId($this);

        if ($rootEventId > 0) {
            $userId      = Phprojekt_Auth::getUserId();
            $where       = sprintf('(parent_id = %d OR id = %d) AND id != %d AND participant_id = %d',
                (int) $rootEventId, (int) $rootEventId, (int) $this->id, (int) $userId);
            $records = $this->fetchAll($where);
            $return  = array();
            foreach ($records as $record) {
                if ($record->id != $this->id) {
                    $return[] = $record->id;
                }
            }
        }

        return $return;
    }

    /**
     * Returns an instance of notification class for this module
     *
     * @return Phprojekt_Notification
     */
    public function getNotification()
    {
        $notification = Phprojekt_Loader::getModel('Calendar', 'Notification');
        $notification->setModel($this);

        return $notification;
    }
}
