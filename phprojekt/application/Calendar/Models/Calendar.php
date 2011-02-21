<?php
/**
 * Calendar model class.
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Calendar
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */

/**
 * Calendar model class.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Calendar
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */
class Calendar_Models_Calendar extends Phprojekt_Item_Abstract
{
    /**
     * Type of event once (single date event).
     */
    const EVENT_TYPE_ONCE = 1;

    /**
     * Type of event daily.
     */
    const EVENT_TYPE_DAILY = 2;

    /**
     * Type of event weekly.
     */
    const EVENT_TYPE_WEEKLY = 3;

    /**
     * Type of event monthly.
     */
    const EVENT_TYPE_MONTLY = 4;

    /**
     * Type of event anual.
     */
    const EVENT_TYPE_ANUAL = 5;

    /**
     * Values for the status value
     */
    const EVENT_STATUS_PENDING  = 0;
    const EVENT_STATUS_ACCEPTED = 1;
    const EVENT_STATUS_REJECTED = 2;

    /**
     * User list to be notified.
     *
     * @var array
     */
    public $notifParticipants;

    /**
     * Start date for use in notifications.
     *
     * @var string
     */
    public $startDateNotif;

    /**
     * End date for use in notifications.
     *
     * @var string
     */
    public $endDateNotif;

    /**
     * Constructor initializes additional Infomanager.
     *
     * @param array $db Configuration for Zend_Db_Table.
     *
     * @return void
     */
    public function __construct($db = null)
    {
        parent::__construct($db);

        $this->_dbManager = new Calendar_Models_CalendarInformation($this, $db);
    }

    /**
     * Save or inserts an event. It inserts one envent by participant.
     *
     * @param array   $request              Array with all the data for the item (Basic Data).
     * @param integer $id                   Current item id or null for new one.
     * @param string  $startDate            Date of the event.
     * @param string  $rrule                Rule for apply the recurring.
     * @param array   $participants         Array with the users involved in the event.
     * @param boolean $multipleEvents       Apply changes to one event or all the recurring events.
     * @param boolean $multipleParticipants Action for multiple participants or just the logged one.
     *
     * @return integer The id of the root event.
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
                $errors = $this->getError();
                $error  = array_pop($errors);
                throw new Phprojekt_PublishedException($error['label'] . ': ' . $error['message']);
            }
            if ($multipleEvents) {
                $model = clone($this);
                $model->find($id);
                // If the startDate has changed, apply that difference of days to all the events of recurrence
                if ($startDate != $this->getDate($model->startDatetime)) {
                    $diffSeconds    = strtotime($startDate) - strtotime($this->getDate($model->startDatetime));
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
        foreach (array_keys($participants) as $oneParticipant) {
            $participantsList[(int) $oneParticipant] = (int) $oneParticipant;
        }
        if ($id == 0) {
            $sendNotification = false;
            if (array_key_exists('sendNotification', $request)) {
                if ($request['sendNotification'] == 1) {
                    $sendNotification            = true;
                    $request['sendNotification'] = 0;
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
     * Returns the id of the root event of the current record.
     *
     * @param Phprojekt_Model_Interface $model The model to check.
     *
     * @return integer Id of the root event.
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
     * Validate the data of the current record.
     *
     * @return boolean True for valid.
     */
    public function recordValidate()
    {
        if (strtotime($this->startDatetime) >= strtotime($this->endDatetime)) {
            $this->_validate->error->addError(array(
                'field'   => "Event duration",
                'label'   => Phprojekt::getInstance()->translate('Event duration'),
                'message' => Phprojekt::getInstance()->translate('End date and time has to be after Start date and '
                    . 'time')));
            return false;
        }

        return parent::recordValidate();
    }

    /**
     * Get all the participants of one event.
     *
     * @return string User IDs comma separated.
     */
    public function getAllParticipants()
    {
        $participantsList = array();
        $participants     = '';

        if (!empty($this->id)) {
            $rootEventId = $this->getRootEventId($this);
            $where       = sprintf('(parent_id = %d OR id = %d) AND start_datetime = %s', (int) $rootEventId,
                (int) $rootEventId, $this->getAdapter()->quote($this->applyTimeZone($this->startDatetime)));
            $records = $this->fetchAll($where);
            foreach ($records as $record) {
                if (null === $record->rrule) {
                    if ($record->startDatetime == $this->startDatetime) {
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
     * Return the first startDate of the recurring events.
     * If the user is the owner, can change it.
     *
     * @param integer $id        The current item id.
     * @param string  $startDate The current startDate from the POST value.
     *
     * @return string The first date value.
     */
    public function getRecursionStartDate($id, $startDate)
    {
        $model = clone($this);
        $model->find($id);

        $rootEventId = $this->getRootEventId($model);
        $where       = sprintf('(parent_id = %d OR id = %d) AND participant_id = %d', (int) $rootEventId,
            (int) $rootEventId, (int) $model->participantId);
        $records = $model->fetchAll($where, 'start_datetime ASC');

        $startDate = null;
        // Always use the minimal value for startDate
        if (isset($records[0])) {
            $startDate = $this->getDate($records[0]->startDatetime);
        }

        return $startDate;
    }

    /**
     * Deletes single or multiple events, both for single or multiple participants.
     *
     * @param boolean $multipleEvents       Action for multiple events or single one.
     * @param boolean $multipleParticipants Action for multiple participants or just the logged one.
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
                $where .= sprintf(' AND start_datetime = %s',
                    $this->getAdapter()->quote($this->applyTimeZone($this->startDatetime)));
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
     * Return all the events for all the users selected in a date.
     * The function use the ActiveRecord fetchAll for skip the itemRights restrictions.
     *
     * @param string  $usersId User IDs comma separated.
     * @param string  $date    Date for search.
     * @param integer $count   Count for the fetchall.
     * @param integer $offset  Offset for the fetchall.
     *
     * @return array Array of Calendar_Models_Calendar.
     */
    public function getUserSelectionRecords($usersId, $date, $count, $offset)
    {
        $db      = Phprojekt::getInstance()->getDb();
        $date    = $db->quote($date);
        $records = array();

        if (count($usersId) > 0) {
            $where = sprintf('participant_id IN (%s) AND DATE(start_datetime) <= %s AND DATE(end_datetime) >= %s',
                implode(", ", $usersId), $date, $date);

            $records = Phprojekt_ActiveRecord_Abstract::fetchAll($where, null, $count, $offset);

            // Hide the title, place and note from the private events
            $userId = Phprojekt_Auth::getUserId();
            foreach ($records as $key => $record) {
                if ($record->visibility == 1 && $record->participantId != $userId) {
                    $record->title = "-";
                    $record->notes = "-";
                    $record->place = "-";
                }
            }
        }

        return $records;
    }

    /**
     * Check if the user is the owner of the item.
     *
     * @param Phprojekt_Model_Interface $model The event to check.
     *
     * @return boolean True if is the owner.
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
     * Save a new event (Single or a recurring one).
     *
     * @param array   $request          Array with all the POST data.
     * @param array   $eventDates       Array with the dates of the recurring.
     * @param integer $daysDuration     How many days are between the start and end dates.
     * @param integer $participantId    Id of the user to add the event.
     * @param boolean $lastParticipant  If is the last participant in the list.
     * @param boolean $sendNotification If must send or not the notification.
     * @param array   $participantsList Array with the users involved in the event.
     * @param integer $parentId         Id of the parent event.
     *
     * @return integer The parentId.
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
                $request['sendNotification'] = 1;
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
     * - changes in the users (add, delete) (ONLY FOR OWNERS OF THE ITEM).
     * - changes in the recurring (add, delete, update) (ONLY FOR OWNERS OF THE ITEM).
     *
     * @param array   $request              Array with the POST data.
     * @param integer $id                   Id of the current event.
     * @param array   $eventDates           Array with the dates of the recurring.
     * @param integer $daysDuration         How many days are between the start and end dates.
     * @param array   $participantsList     Array with the users involved in the event.
     * @param boolean $multipleParticipants Action for multiple participants or just the logged one.
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
                if (!$found && $date == $this->getDate($record->startDatetime)) {
                    // Update old entry of recurrence
                    $this->_saveEvent($request, $record, $oneDate, $daysDuration, $record->participantId,
                        $record->parentId);

                    // If notification email was requested, then send it just once
                    $request['sendNotification'] = 0;

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
                    if ($date == $this->getDate($record->startDatetime)) {
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
                            $request['sendNotification'] = 0;
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
                                    if (strtotime($this->getDate($record->startDatetime)) == $oneDate) {
                                        $addParticipant = true;
                                        break;
                                    }
                                }
                                if ($addParticipant) {
                                    $this->_saveEvent($request, $newModel, $oneDate, $daysDuration, $participantId,
                                        $parentId);
                                    // If notification email was requested, then send it just once
                                    $request['sendNotification'] = 0;
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
     * @param array   $request              Array with the POST data.
     * @param integer $id                   Id of the current event.
     * @param array   $eventDates           Array with the dates of the recurring.
     * @param integer $daysDuration         How many days are between the start and end dates.
     * @param array   $participantsList     Array with the users involved in the event.
     * @param boolean $multipleParticipants Action for multiple participants or just the logged one.
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
            $request['sendNotification'] = 0;

            $rootEventId = $this->getRootEventId($this);
            $where       = sprintf('(parent_id = %d OR id = %d) AND DATE(start_datetime) = %s', (int) $rootEventId,
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
                    $where       = sprintf('(parent_id = %d OR id = %d) AND participant_id = %d '
                        . 'AND DATE(start_datetime) = %s', (int) $rootEventId, (int) $rootEventId,
                        (int) $currentParticipantId, $this->getAdapter()->quote(date("Y-m-d", $oneDate)));
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
     * @param array                     $request       Array with the POST data.
     * @param Calendar_Models_Calendar  $model         The model to save.
     * @param Phprojekt_Date_Collection $oneDate       Date object to save.
     * @param integer                   $daysDuration  How many days are between the start and end dates.
     * @param integer                   $participantId Id of the user to save the event.
     * @param integer                   $parentId      Id of the parent event.
     *
     * @return integer The parentId.
     */
    private function _saveEvent($request, $model, $oneDate, $daysDuration, $participantId, $parentId)
    {
        $request['startDatetime'] = date("Y-m-d", $oneDate) . ' ' . $request['startTime'];
        $request['endDatetime']   = date("Y-m-d", $oneDate + ($daysDuration * 24 * 60 * 60)) . ' '
            . $request['endTime'];
        $request['participantId'] = $participantId;
        $request['parentId']      = $parentId;

        // The save is needed?
        if ($this->_needSave($model, $request)) {
            // Add 'read, write, downlaod and delete' access to the participant
            $request = Default_Helpers_Right::allowReadWriteDownloadDelete($request, $participantId);

            // Access for the owner
            if (null !== $model->ownerId) {
                $ownerId = $model->ownerId;
            } else {
                $ownerId = Phprojekt_Auth::getUserId();
            }

            // Set the status to "Pending" if there is any change and the event is for other user
            if ($participantId != $ownerId && $participantId != Phprojekt_Auth::getUserId()) {
                $request['status'] = self::EVENT_STATUS_PENDING;
            }

            $request = Default_Helpers_Right::allowAll($request, $ownerId);

            Default_Helpers_Save::save($model, $request);
        }

        if (null === $parentId) {
            $model->parentId = $model->id;
        }

        return $model->parentId;
    }

    /**
     * Check if some of the fields was changed.
     *
     * @param Calendar_Models_Calendar $model   Calendar model.
     * @param array                    $request Array with POST values.
     *
     * @return boolean True if there is any change.
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
     * Recurrence fields basic validation.
     *
     * @param String $rrule Event recurrence.
     *
     * @return boolean True for a valid string.
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
     * Returns all the events connected with the current one by the parentId,
     * for the logged user as participant.
     * Doesn't return the current event among them.
     *
     * @return array Array of Calendar IDs.
     */
    public function getRelatedEvents()
    {
        $return      = array();
        $rootEventId = $this->getRootEventId($this);

        if ($rootEventId > 0) {
            $userId = Phprojekt_Auth::getUserId();
            $where  = sprintf('(parent_id = %d OR id = %d) AND id != %d AND participant_id = %d', (int) $rootEventId,
                (int) $rootEventId, (int) $this->id, (int) $userId);
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
     * Returns an instance of notification class for this module.
     *
     * @return Phprojekt_Notification An instance of Phprojekt_Notification.
     */
    public function getNotification()
    {
        $notification = Phprojekt_Loader::getModel('Calendar', 'Notification');
        $notification->setModel($this);

        return $notification;
    }

    /**
     * Return only the date from a datetime.
     *
     * @param string $datetime The datetime to get.
     *
     * @return string Date string.
     */
    public function getDate($datetime)
    {
        return substr($datetime, 0, 10);
    }

    /**
     * Apply the timezone to one datetime for use it in the database.
     *
     * @param string $dateTime The datetime to transform.
     *
     * @return string Datetime string.
     */
    public function applyTimeZone($dateTime)
    {
        return date("Y-m-d H:i:s", Phprojekt_Converter_Time::userToUtc($dateTime));
    }
}
