<?php
/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details
 *
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */
require_once 'Sabre.autoload.php';

/**
 * Calendar2 model class.
 *
 * An object of this class corresponds to a series of objects.
 */
class Calendar2_Models_Calendar2 extends Phprojekt_Item_Abstract
{
    /**
     * Values for the status value
     */
    const STATUS_PENDING  = 1;
    const STATUS_ACCEPTED = 2;
    const STATUS_REJECTED = 3;

    /**
     * Values for the visibility
     */
    const VISIBILITY_PUBLIC  = 1;
    const VISIBILITY_PRIVATE = 2;

    /** Overwrite field to display in the search results. */
    public $searchFirstDisplayField = 'summary';

    /**
     * The confirmation statuses of this event's participants.
     *
     * Must never be null if $_participantData is not null.
     *
     * @var array of (int => int) (ParticipantId => Status)
     */
    protected $_participantData = null;

    /**
     * The participant data of the event as it is stored in the database.
     *
     * @var array of (int => int) (ParticipantId => Status)
     */
    protected $_participantDataInDb = null;

    /**
     * Whether this is the first occurence of this series.
     *
     * @var boolean
     */
    protected $_isFirst = true;

    /**
     * Holds the original start date of this event as stored in the db.
     * Is never null if this event exists in the db.
     *
     * @var Datetime
     */
    protected $_originalStart = null;

    /**
     * The ModelInformation object.
     *
     * @var Phprojekt_ModelInformation_Interface
     */
    protected $_information;

    /**
     * Checks if the given value is a valid status.
     *
     * @param mixed $value The value to check.
     *
     * @return If $value is a valid status.
     */
    public static function isValidStatus($var)
    {
        if ($var === self::STATUS_PENDING
                || $var === self::STATUS_ACCEPTED
                || $var === self::STATUS_REJECTED) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks if the given value is a valid visibility.
     *
     * @param mixed $value The value to check.
     *
     * @return If $value is a valid status.
     */
    public static function isValidVisibility($var)
    {
        if ($var === self::VISIBILITY_PUBLIC
                || $var === self::VISIBILITY_PRIVATE) {
            return true;
        } elseif (is_string($var)) {
            return self::isValidVisibility((int) $var);
        } else {
            return false;
        }
    }

    /**
     * Constructor.
     */
    public function __construct($db = null)
    {
        parent::__construct($db);

        // This is needed to make fields read-only if we're not the owner.
        $this->_information = new Calendar2_Models_CalendarInformation();

        $this->uid = Phprojekt::generateUniqueIdentifier();

        // Default values
        $this->visibility = self::VISIBILITY_PUBLIC;
        $this->ownerId    = Phprojekt_Auth_Proxy::getEffectiveUserId();
    }

    /**
     * Return the Phprojekt_ModelInformation object for this model.
     *
     * @return Phprojekt_ModelInformation_Interface
     */
    public function getInformation()
    {
        if ($this->ownerId && (Phprojekt_Auth_Proxy::getEffectiveUserId() != $this->ownerId)) {
            return new Calendar2_Models_InformationDecoratorReadonly($this->_information);
        } else {
            return $this->_information;
        }
    }

    /**
     * Overwrite save function. Writes the data in this object into the
     * database.
     *
     * @return int The id of the saved object
     */
    public function save()
    {
        if (!$this->recordValidate()) {
            $errors = $this->getError();
            $error  = array_pop($errors);
            throw new Zend_Controller_Action_Exception($error['label'] . ': ' . $error['message'], 400);
        }

        if ($this->_isFirst) {
            $endOfLast = $this->getRruleHelper()->getUpperTimeBoundary();
            if ($endOfLast) {
                $this->lastEnd = $endOfLast->format('Y-m-d H:i:s');
            } else {
                // I hate to do this, but item casts null to '1970-01-01 00;00:00'.
                $this->_data['lastEnd'] = null;
            }
            if (!self::isValidVisibility($this->visibility)) {
                throw new Zend_Controller_Action_Exception("Invalid visibility {$this->visibility}", 400);
            }

            $this->_fetchParticipantData();

            // We need to check this before we call parent::save() as it will be
            // set after.
            $isNew = empty($this->_storedId);

            $now = new Datetime('now', new DateTimeZone('UTC'));
            $this->lastModified = $now->format('Y-m-d H:i:s');
            if (!$this->uri) {
                $this->uri = $this->uid;
            }

            $start = new Datetime(
                '@' . Phprojekt_Converter_Time::userToUtc($this->start)
            );
            // This is here to fix a special case where P6 interprets things different from iCalendar. Assume a event
            // starts on tuesdays, has bi-weekly recurrence and BYDAY monday and wednesday. P6 will have the tuesday,
            // the wednesday after that and monday on the next week, wednesday the week after that, etc. Caldav will
            // have one week pause and then monday and wednesday in the same week.
            // TODO: Remove this after we changed to a predicate-based generation of events.
            if (strpos($this->rrule, ';BYDAY=') && ! strpos($this->rrule, ';WKST=')) {
                $this->rrule = $this->rrule . ';WKST=' . strtoupper(substr($start->format('D'), 0, 2));
            }
            parent::save();
            $this->_saveParticipantData();
            $this->_updateRights();

            // If the start time has changed, we have to adjust all the excluded
            // dates.
            if (!$isNew && $start != $this->_originalStart) {
                $delta = $this->_originalStart->diff($start);
                $db = $this->getAdapter();
                foreach ($this->getExcludedDates() as $date) {
                    $where  = $db->quoteInto('calendar2_id = ?', $this->id);
                    $where .= $db->quoteInto(
                        'AND date = ?',
                        $date->format('Y-m-d H:i:s')
                    );
                    $date->add($delta);
                    $db->update(
                        'calendar2_excluded_dates',
                        array('date' => $date->format('Y-m-d H:i:s')),
                        $where
                    );
                }
            }
        } else {
            // Split the series into two parts. $this will be the second part.
            $new = $this;
            $old = $this->create();
            $old->find($this->id);

            $splitDate = new Datetime(
                '@' . Phprojekt_Converter_Time::userToUtc($new->start)
            );
            $helper = $old->getRruleHelper();
            $rrules = $helper->splitRrule($splitDate);

            $old->rrule = $rrules['old'];

            // Only overwrite the new rrule if the user didn't change it
            if ($new->rrule == $old->rrule) {
                $new->rrule = $rrules['new'];
            }

            // Regenerate the uid if the new event has multiple occurrences, see
            // http://jira.opensource.mayflower.de/jira/browse/PHPROJEKT-298
            // As they don't belong together anymore, we also need to set a new uri.
            if ($new->rrule) {
                $this->uid = Phprojekt::generateUniqueIdentifier();
                $new->uri = $new->uid;
            }

            $old->save();
            $new->_saveToNewRow();

            // Update the excluded occurences
            $where  = "id = {$old->id} ";
            $where .= "AND date >= '{$splitDate->format('Y-m-d H:i:s')}'";

            $this->getAdapter()->update(
                'calendar2_excluded_dates',
                array('id' => $new->id),
                $where
            );
        }

        $this->_originalStart = new Datetime(
            '@' . Phprojekt_Converter_Time::userToUtc($this->start)
        );
        return $this->id;
    }

    /**
     * Saves this single Occurence.
     * If it's part of a recurring series, it will be extracted from the series.
     *
     * @return int The (new) id of this event.
     */
    public function saveSingleEvent()
    {
        if (!$this->hasMultipleOccurrences()) {
            return $this->save();
        }

        if (!$this->recordValidate()) {
            $errors = $this->getError();
            $error  = array_pop($errors);
            throw new Zend_Controller_Action_Exception($error['label'] . ': ' . $error['message'], 400);
        }

        if (is_null($this->_storedId)) {
            // This event is not saved yet. Reset the rrule and save it.
            $this->rrule = null;
            return $this->save();
        }

        $start = new Datetime(
            '@' . Phprojekt_Converter_Time::userToUtc($this->start)
        );

        $series = clone $this;
        $series->find($this->id);
        $series->_excludeDate($this->_originalStart);

        $this->_data['rrule']        = null;
        $this->_data['recurrenceId'] = $this->_originalStart->format('Y-m-d H:i:s');
        $this->_isFirst              = true;
        $this->_saveToNewRow();

        return $this->id;
    }

    /**
     * Deletes all events in this series beginning with this one.
     *
     * @return void.
     */
    public function delete()
    {
        $db = $this->getAdapter();

        if ($this->_isFirst) {
            $db->delete(
                'calendar2_user_relation',
                $db->quoteInto('calendar2_id = ?', $this->id)
            );
            $db->delete(
                'calendar2_excluded_dates',
                $db->quoteInto('calendar2_id = ?', $this->id)
            );

            $tag = new Phprojekt_Tags();
            $tag->deleteTagsByItem(
                Phprojekt_Module::getId('Calendar2'),
                $this->id
            );

            parent::delete();
        } else {
            $first = clone $this;
            $first->find($this->id);

            $start = new Datetime(
                '@' . Phprojekt_Converter_Time::userToUtc($this->start)
            );
            // Adjust the rrule.
            $helper = $first->getRruleHelper();
            $split  = $helper->splitRrule($start);
            $first->rrule = $split['old'];
            $first->save();

            // Delete all excludes after this event.
            $where  = $db->quoteInto('calendar2_id = ?', $this->id);
            $where .= $db->quoteInto(
                'AND date >= ?',
                $start->format('Y-m-d H:i:s')
            );
            $db->delete(
                'calendar2_excluded_dates',
                $where
            );
        }
    }

    /**
     * Deletes just the single event out of this series.
     *
     * @return void
     */
    public function deleteSingleEvent()
    {
        if (empty($this->rrule)) {
            // If this is a non-recurring event, call delete()
            $this->delete();
        } else {
            $start = new Datetime(
                '@' . Phprojekt_Converter_Time::userToUtc($this->start)
            );
            $this->_excludeDate($start);
        }

        // Update the lastModified-Time
        // We can't assume that the user calls save() after this method, so we
        // have to update the database here.
        $now = new Datetime('now', new DateTimeZone('UTC'));
        $this->lastModified = $now->format('Y-m-d H:i:s');
        $this->update(
            array('last_modified' => $now->format('Y-m-d H:i:s')),
            $this->getAdapter()->quoteInto('id = ?', $this->id)
        );
    }

    /**
     * Implemented because we need to reset the participant data.
     */
    public function find()
    {
        $this->_isFirst             = true;
        $this->_participantData     = null;
        $this->_participantDataInDb = null;

        // This is very uncool, but activeRecord declares find()
        // while expecting find($id)...
        $args = func_get_args();

        if (1 != count($args)) {
            throw new Phprojekt_ActiveRecord_Exception(
                'Wrong number of arguments for find'
            );
        }

        $find = parent::find($args[0]);
        if (is_object($find)) {
            $find->_originalStart = new Datetime(
                '@' . Phprojekt_Converter_Time::userToUtc($find->start)
            );
        }

        return $find;
    }

    /**
     * Implemented because we need to reset the participant data.
     */
    public function __clone()
    {
        parent::__clone();
        $this->_participantData     = null;
        $this->_participantDataInDb = null;
        $this->_originalStart       = null;
        $this->_isFirst             = true;
    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null, $select = null, $join = null)
    {
        $events = Phprojekt_ActiveRecord_Abstract::fetchAll($where, $order, $count, $offset, $select, $join);
        foreach ($events as $e) {
            $e->_originalStart = new Datetime('@' . Phprojekt_Converter_Time::userToUtc($e->start));
        }
        return $events;
    }

    /**
     * Returns the count of objects matching the given sql clause
     *
     * @param string $where The sql where-clause
     *
     * @return int Count of matching entries
     */
    public function count($where = null) {
        return Phprojekt_ActiveRecord_Abstract::count($where);
    }

    /**
     * Get all events in the period that the currently active user participates
     * in. Both given times are inclusive.
     *
     * Note that even if $user is given and different from the current user,
     * only public events will be returned.
     *
     * @param Datetime $start The start of the period.
     * @param Datetime $end   The end of the period.
     * @param int      $user  The id of the user to query for. If omitted or
     *                        null, the currently logged in user is used.
     *
     * @return array of Calendar2_Models_Calendar
     */
    public function fetchAllForPeriod(Datetime $start, Datetime $end, $user = null)
    {
        if (is_null($user)) {
            // Default to the current user.
            $user = Phprojekt_Auth_Proxy::getEffectiveUserId();
        }

        $db     = $this->getAdapter();
        $where  = $db->quoteInto(
            'calendar2_user_relation.user_id = ?',
            (int) $user
        );

        if (Phprojekt_Auth_Proxy::getEffectiveUserId() != $user) {
            $where .= $db->quoteInto(
                ' AND calendar2.visibility != ?',
                (int) self::VISIBILITY_PRIVATE
            );
        }
        //TODO: This might query a lot of objects. Consider saving the last
        //      date of occurrence too so this is faster.
        $where .= $db->quoteInto(' AND calendar2.start <= ?', $end->format('Y-m-d H:i:s'));
        $where .= $db->quoteInto(
            ' AND (calendar2.last_end IS NULL OR calendar2.last_end >= ?)',
            $start->format('Y-m-d H:i:s')
        );
        $join   = 'JOIN calendar2_user_relation '
                    . 'ON calendar2.id = calendar2_user_relation.calendar2_id';

        // This is a horrible hack. Phprojekt_ActiveRecord_Abstract::fetchAll is
        // not static, but has to be called that way because we need events that
        // the current user might not have rights to see.
        $models = Phprojekt_ActiveRecord_Abstract::fetchAll(
            $where, null, null, null, null, $join
        );


        // Expand the recurrences.
        $ret = array();
        foreach ($models as $model) {
            $startDt  = new Datetime($model->start);
            $endDt    = new Datetime($model->end);
            $duration = $startDt->diff($endDt);

            $helper = $model->getRruleHelper();
            foreach ($helper->getDatesInPeriod($start, $end) as $date) {
                $m        = $model->copy();
                $m->start = Phprojekt_Converter_Time::utcToUser(
                    $date->format('Y-m-d H:i:s')
                );
                $m->_originalStart = clone $date;
                $date->add($duration);
                $m->end = Phprojekt_Converter_Time::utcToUser(
                    $date->format('Y-m-d H:i:s')
                );
                $m->_isFirst = ($m->start == $model->start);
                $isFirst     = false;
                $ret[]       = $m;
            }
        }

        return $ret;
    }

    /**
     * Find a special occurrence based on a id and a recurrence id.
     * Will throw an exception for invalid values.
     *
     * @param int    $id           The id of the event.
     * @param string $recurrenceId The recurrence id of the occurrence to find.
     *
     * @return $this
     */
    public function findWithRecurrenceId($id, $recurrenceId)
    {
        $date = new Datetime($recurrenceId, new DateTimeZone('UTC'));
        return $this->findOccurrence($id, $date);
    }

    /**
     * Returns an array of all calendar objects for the given uid.
     *
     * @param string The uid of the calendar collection
     *
     * @return array of Calendar2_Models_Calendar2 All objects belonging to that uid
     */
    public function fetchByUid($uid)
    {
        $db    = Phprojekt::getInstance()->getDb();
        $where = $db->quoteInto('uid = ?', $uid);
        return Phprojekt_ActiveRecord_Abstract::fetchAll($where);
    }

    /**
     * Finds a special occurence of an event. Will throw an exception
     * if the date is not part of this event.
     *
     * @param int      $id   The id of the event to find.
     * @param Datetime $date The date of the occurence.
     *
     * @throws Exception If $date is no occurence of this event.
     *
     * @return $this
     */
    public function findOccurrence($id, Datetime $date)
    {
        $find = $this->find($id);
        if ($find !== $this) {
            // Some error occured, maybe the id doesn't exist in the db
            return $find;
        }

        $date->setTimezone(new DateTimeZone('UTC'));

        $excludes = $this->getAdapter()->fetchCol(
            'SELECT date FROM calendar2_excluded_dates WHERE calendar2_id = ?',
            $id
        );
        if (in_array($date->format('Y-m-d H:i:s'), $excludes)) {
            return null;
        }

        $start = new Datetime(
            '@'.Phprojekt_Converter_Time::userToUtc($this->start)
        );

        if ($date != $start) {
            if (!$this->getRruleHelper()->containsDate($date)) {
                throw new Exception(
                    "Occurence on {$date->format('Y-m-d H:i:s')}, "
                    . "{$date->getTimezone()->getName()} not found."
                );
            }
            $end = new Datetime(
                '@' . Phprojekt_Converter_Time::userToUtc($this->end)
            );
            $duration = $start->diff($end);

            $start = $date;
            $end   = clone $start;
            $end->add($duration);

            $this->start = Phprojekt_Converter_Time::utcToUser(
                $start->format('Y-m-d H:i:s')
            );
            $this->end = Phprojekt_Converter_Time::utcToUser(
                $end->format('Y-m-d H:i:s')
            );

            $this->_originalStart = $start;
            $this->_isFirst = false;
        }

        return $this;
    }

    /**
     * Get the participants of this event.
     *
     * @return array of int
     */
    public function getParticipants()
    {
        $this->_fetchParticipantData();
        return array_keys($this->_participantData);
    }

    /**
     * Returns the names of all Participants.
     *
     * @return array of string
     */
    public function getParticipantsNames()
    {
        $participants = $this->getParticipants();
        $names = array();
        foreach ($participants as $p) {
            $user    = new Phprojekt_User_User();
            $user    = $user->findUserById($p);
            $names[] = $user->firstname . ' ' . $user->lastname;
        }
        return $names;
    }

    /**
     * Returns whether the given user participates in this event.
     *
     * @param int $id The id of the user to check for.
     *
     * @return boolean Whether the user participates in the event.
     */
    public function hasParticipant($id)
    {
        $this->_fetchParticipantData();
        return array_key_exists($id, $this->_participantData);
    }

    /**
     * Set the participants of this event.
     * All confirmation statuses will be set to pending.
     *
     * @param array of int $ids The ids of the participants.
     *
     * @return void
     */
    public function setParticipants(array $ids)
    {
        $hasOwner = !is_null($this->ownerId);
        if ($hasOwner) {
            $this->_fetchParticipantData();
            $ownerStatus = $this->_participantData[$this->ownerId];
        }

        $this->_participantData = array();

        if ($hasOwner) {
            $this->addParticipant($this->ownerId, $ownerStatus);
        }

        foreach ($ids as $id) {
            $this->addParticipant($id);
        }
    }

    /**
     * Add a single Participant to the event.
     *
     * @param int $id The id of the participant to add.
     *
     * @return void
     */
    public function addParticipant($id, $status = self::STATUS_PENDING)
    {
        $this->_fetchParticipantData();

        if ($this->hasParticipant($id)) {
            throw new Exception("Tried to add already participating user $id");
        } elseif (!self::isValidStatus($status)) {
            throw new Exception("Tried to save invalid status $status");
        }
        $this->_participantData[$id] = $status;
    }

    /**
     * Removes a single Participant from the event.
     *
     * @param int $id The id of the participant to remove.
     *
     * @return void
     */
    public function removeParticipant($id)
    {
        $this->_fetchParticipantData();

        if (!$this->hasParticipant($id)) {
            throw new Exception("Tried to remove unknown participant $id");
        } else if ($id == $this->ownerId) {
            throw new Exception('Must not remove owner');
        }
        unset($this->_participantData[$id]);
    }

    /**
     * Returns the occurrence that identifies this event in it's series.
     * It's just the start time, except that it's always in UTC.
     *
     * @return string
     */
    public function getOccurrence()
    {
        $occurrence = new Datetime('@' . Phprojekt_Converter_Time::userToUtc($this->start));
        return $occurrence->format('Y-m-d H:i:s');
    }

    /**
     * Get a participant's confirmation status.
     *
     * If no id is given, the currently logged in user's status
     * will be returned.
     *
     * @param int $id The id of the participant.
     *
     * @return int
     */
    public function getConfirmationStatus($id = null)
    {
        if (is_null($id)) {
            $id = Phprojekt_Auth_Proxy::getEffectiveUserId();
        }
        $this->_fetchParticipantData();

        if (!$this->hasParticipant($id)) {
            // We can not throw an exception here because if a user edits a new
            // entry, a empty model object will be requested and serialized.
            // Returning null here will yield the default value as configured
            // in the database_manager table.
            return null;
        }
        return $this->_participantData[$id];
    }

    /**
     * Gets the confirmations status of all participants.
     *
     * @return array of (int => int) UserId => Confirmation status
     */
    public function getConfirmationStatuses()
    {
        $this->_fetchParticipantData();
        return $this->_participantData;
    }

    /**
     * Update a participant's confirmation status.
     *
     * @param int $id        The Id of the participant.
     * @param int $newStatus The new confirmation status.
     *
     * @return void.
     */
    public function setConfirmationStatus($id, $newStatus)
    {
        $this->_fetchParticipantData();

        if (!$this->hasParticipant($id)) {
            throw new Exception("Participant #$id not found");
        } elseif (!self::isValidStatus($newStatus)) {
            throw new Exception("Tried to save invalid status $newStatus");
        }

        $this->_participantData[$id] = $newStatus;
    }

    /**
     * Updates the confirmation statuses of all participants.
     * The owner's status will not be updated.
     *
     * @param int $status The new Status
     *
     * @return void.
     */
    public function setParticipantsConfirmationStatuses($status)
    {
        if (!self::isValidStatus($status)) {
            throw new Exception("Tried to save invalid status $status");
        }

        foreach ($this->participants as $p) {
            if ($p !== $this->ownerId) {
                $this->setConfirmationStatus($p, $status);
            }
        }
    }

    /**
     * Returns the dates excluded from this reccurring event.
     *
     * @return array of Datetime
     */
    public function getExcludedDates()
    {
        //TODO: Maybe cache this?
        $excludes = $this->getAdapter()->fetchCol(
            'SELECT date FROM calendar2_excluded_dates WHERE calendar2_id = ?',
            $this->id
        );

        $ret = array();
        foreach ($excludes as $date) {
            $ret[] = new Datetime($date, new DateTimeZone('UTC'));
        }

        return $ret;
    }

    /**
     * Returns a textual representation of the rrule of this event.
     *
     * @return string The recurrence in human-readable form.
     */
    public function getRecurrence()
    {
        return $this->getRruleHelper()->getHumanreadableRrule();
    }

    /**
     * Dummy in order to make activerecord think that a recurrence field exists.
     */
    public function setRecurrence()
    {
    }

    /**
     * Returns a Calendar2_Helper_Rrule object initialized with this objects
     * start date, recurrence rule and excluded ocurrences.
     *
     * @return Calendar2_Helper_Rrule
     */
    public function getRruleHelper()
    {
        if ($this->_isFirst) {
            $start = new Datetime('@' . Phprojekt_Converter_Time::userToUtc($this->start));
            $end   = new Datetime('@' . Phprojekt_Converter_Time::userToUtc($this->end));
        } else {
            $original = $this->create();
            $original->find($this->id);
            $start = new Datetime('@' . Phprojekt_Converter_Time::userToUtc($original->start));
            $end = new Datetime('@' . Phprojekt_Converter_Time::userToUtc($original->end));
        }

        return new Calendar2_Helper_Rrule(
            $start,
            $start->diff($end),
            $this->rrule,
            $this->getExcludedDates()
        );
    }

    /**
     * Sets the owner id.
     * The owner is also added as an participant with 'Accepted' as status.
     *
     * @param int $id The owner id.
     *
     * @return void.
     */
    public function setOwnerId($id)
    {
        $this->_fetchParticipantData();

        if ($this->hasParticipant($id)) {
            unset($this->_participantData[$id]);
        }
        $this->addParticipant($id, self::STATUS_ACCEPTED);

        $this->_data['ownerId'] = $id;
    }

    /**
     * Returns a copy of this model object.
     *
     * This function would best be written as __clone, but ActiveRecord
     * already implements __clone to create a new, empty model object
     * and we don't want to break the semantics of cloning model objects.
     */
    public function copy()
    {
        $m            = clone $this;
        $m->_data     = $this->_data;
        $m->_storedId = $this->_storedId;

        return $m;
    }

    /**
     * Returns the notification object for this calendar item.
     *
     * @return Calendar2_Models_Notification
     **/
    public function getNotification()
    {
        $notification = new Calendar2_Models_Notification();
        $notification->setModel($this);
        return $notification;
    }

    /**
     * Updates this Calendar2 object with data from the given VEVENT
     *
     * The returned object must be save()d before it is persistent.
     * This also means that additional changes can be made before any database calls are made.
     *
     * @param Sabre_VObject_Component $vevent The vevent component
     *
     * @throws Exception If the provided component is not a vevent
     *
     * @return void
     */
    public function fromVObject(Sabre_VObject_Component $vevent)
    {
        if (strtolower($vevent->name) !== 'vevent') {
            throw new Exception(
                "Invalid type of vobject_component passed to Calendar2_Models_Calendar2::fromVobject ({$vevent->name})"
            );
        }
        // Workarounds for missing features. We currently don't support locale-time (we just assume it's the user's
        // usual timzeone) or date values without time (we just assume 0800 - 2000 there).
        if (!is_null($vevent->dtstart['VALUE']) && $vevent->dtstart['VALUE']->value === 'DATE') {
            // No T means it's only a date. iCalendar dicates that dtend must be a date, too.
            $vevent->dtstart->value .= 'T080000';
            unset($vevent->dtstart['VALUE']);
            // Caldav end dates are not inclusive
            $end = new Datetime($vevent->dtend->value);
            $end->sub(new DateInterval('P1D'));
            $vevent->dtend->value = $end->format('Ymd') . 'T200000';
            unset($vevent->dtend['VALUE']);
        }

        $utc = new DateTimezone('UTC');
        $timezone = null;
        if ('Z' === substr($vevent->dtstart->value, -1)) {
            $timezone = $utc;
        } else if (!is_null($vevent->dtstart['tzid'])) {
            $timezone = new DateTimeZone($vevent->dtstart['tzid']->value);
        } else {
            $timezone = Phprojekt_User_User::getUserDateTimeZone();
        }

        // 0-1
        // handled:
        //  last-mod, description, dtstart, location, summary, uid
        // not handled
        //  class, created, geo, organizer, priority, dtstamp, seq, status, transp, url, recurid
        //
        // none or one of these two
        //  dtend, duration (only assumes dtend case for now)
        //
        // 0 - n
        // TODO: Check how we can handle these. Maybe just concat them?
        // handling: (only one is handled atm, though)
        //  comment, rrule
        // not handling:
        //  attach, attendee, categories, contact, exdate, exrule, rstatus, related, resources, rdate, x-prop
        $mappable = array(
            array('veventkey' => 'SUMMARY', 'ourkey' => 'summary', 'default' => '_'),
            array('veventkey' => 'LOCATION', 'ourkey' => 'location', 'default' => ''),
            array('veventkey' => 'DESCRIPTION', 'ourkey' => 'description', 'default' => ''),
            array('veventkey' => 'COMMENT', 'ourkey' => 'comments'),
            array('veventkey' => 'UID', 'ourkey' => 'uid'),
            array('veventkey' => 'LAST-MODIFIED', 'ourkey' => 'lastModified'),
            array('veventkey' => 'RRULE', 'ourkey' => 'rrule', 'default' => '')
        );
        foreach ($mappable as $m) {
            if (isset($vevent->$m['veventkey'])) {
                $this->$m['ourkey'] = $vevent->$m['veventkey'];
            } else if (array_key_exists('default', $m)) {
                $this->$m['ourkey'] = $m['default'];
            }
        }

        $start = new Datetime($vevent->dtstart->value, $timezone);
        $start->setTimezone($utc);
        $this->start = Phprojekt_Converter_Time::utcToUser($start->format('Y-m-d H:i:s'));

        if ($vevent->dtend) {
            $end = new Datetime($vevent->dtend->value, $timezone);
        } else if ($vevent->duration) {
            $duration = new DateInterval($vevent->duration->value);
            $end = clone $start;
            $end->add($duration);
        }
        $end->setTimezone($utc);
        $this->end = Phprojekt_Converter_Time::utcToUser($end->format('Y-m-d H:i:s'));
    }

    /**
     * Returns a Sabre_Vobject_Component representing this object.
     *
     * @param subevents All events with the uid of this one. If not given, these will be fetched from the database.
     *
     * @return Sabre_VObject_Component representing $this.
     */
    public function asVObject($subevents = null)
    {
        $vobject = new Sabre_VObject_Component('vevent');
        if ($this->summary) {
            $vobject->add('summary', $this->summary);
        }
        if ($this->description) {
            $vobject->add('description', $this->description);
        }
        if ($this->comments) {
            $vobject->add('comment', $this->comments);
        }
        if ($this->location) {
            $vobject->add('location', $this->location);
        }
        if ($this->recurrenceId) {
            $recurrenceId = new Datetime($this->recurrenceId);
            $vobject->add('recurrence-id', $recurrenceId->format('Ymd\THis\Z'));
        } else if ($this->rrule) {
            $vobject->add('rrule', $this->rrule);
            $exdates = array();
            foreach ($this->getExcludedDates() as $d) {
                $exdates[] = $d->format('Ymd\THis\Z');
            }
            if (!empty($exdates)) {
                // The problem here ist that if we change a single occurrence of an recurring event in P6, we mark the
                // occurrence as excluded in the original event. In caldav, the exclusion takes precedence over the
                // extracted event, which means that events that have been changed will not show up in caldav clients.
                // To go around this, we filter these dates out of the excluded dates.
                if (is_null($subevents)) {
                    $subevents = $this->fetchByUid($this->uid);
                }
                $subeventDates = array();
                foreach ($subevents as $e) {
                    if ($e->recurrenceId) {
                        $dt = new Datetime($e->recurrenceId);
                        $subeventDates[] = $dt->format('Ymd\THis\Z');
                    }
                }
                $exdates = array_diff($exdates, $subeventDates);
                $vobject->add('exdate', implode(',', $exdates));
            }
        }
        $start = new Datetime('@' . Phprojekt_Converter_Time::userToUtc($this->start));
        $vobject->add('dtstart', $start->format('Ymd\THis\Z'));
        $end = new Datetime('@' . Phprojekt_Converter_Time::userToUtc($this->end));
        $vobject->add('dtend', $end->format('Ymd\THis\Z'));
        $lastMod = new DateTime($this->lastModified);
        $vobject->add('dtstamp', $lastMod->format('Ymd\THis\Z'));
        $vobject->add('uid', $this->uid);
        return $vobject;
    }

    /**
     * Returns whether this calendar object has multiple occurrences.
     *
     * @return bool True if this object represents multiple occurrences.
     */
    public function hasMultipleOccurrences()
    {
        return !empty($this->_originalData['rrule']);
    }

    /**
     * Excludes the given date from this series.
     * Do not call save() after this, or you might reset the rrule to the old value.
     *
     * @param Datetime $date The date to remove
     *
     * @return void
     */
    protected function _excludeDate(Datetime $date)
    {
        // This function distinguishes three cases.
        // 1. This is the first event in the series.
        // 2. This is the last event in the series.
        // 3. Somewhere in between.
        if (empty($this->id)) {
            throw new Exception('Can only exclude dates from saved events');
        }

        $series = clone $this;
        $series->find($this->id);

        $helper = $series->getRruleHelper();

        if (!$helper->containsDate($date)) {
            throw new Exception(
                'Trying to exclude date that is not part of this series'
            );
        }
        $start  = new Datetime(
            '@' . Phprojekt_Converter_Time::userToUtc($series->start)
        );
        $end    = new Datetime(
            '@' . Phprojekt_Converter_Time::userToUtc($series->end)
        );

        if ($start == $date) {
            // If it's the first in it's series, adjust the start date,
            // remove excluded dates that we skipped while doing that and
            // finally, check if we still need a rrule at all.
            $duration = $start->diff($end);

            $newStart  = $helper->firstOccurrenceAfter($start);
            if (is_null($newStart)) {
                throw new Exception('$newStart should not be null');
            }
            $newEnd = clone $newStart;
            $newEnd->add($duration);

            $series->start = Phprojekt_Converter_Time::utcToUser(
                $newStart->format('Y-m-d H:i:s')
            );
            $series->end   = Phprojekt_Converter_Time::utcToUser(
                $newEnd->format('Y-m-d H:i:s')
            );

            // Delete all obsolete excludes
            $db     = $this->getAdapter();
            $where  = $db->quoteInto('calendar2_id = ?', $this->id);
            $where .= $db->quoteInto(
                'AND date < ?',
                $newStart->format('Y-m-d H:i:s')
            );
            $db->delete('calendar2_excluded_dates', $where);

            // Check if this is still a recurring event
            if ($helper->islastOccurrence($newStart)) {
                $series->rrule = null;
            }
            $series->save();
        } elseif ($helper->isLastOccurrence($date)) {
            // If it's the last in it's series, adjust the Rrule and delete
            // now obsolete excludes.
            $newLast = $helper->lastOccurrenceBefore($date);

            // Check if this is still a recurring event
            if ($helper->isFirstOccurrence($newLast)) {
                $series->rrule = null;
            } else {
                // Adjust the rrule
                $series->rrule = preg_replace(
                    '/UNTIL=[^;]*/',
                    "UNTIL={$newLast->format('Ymd\THis\Z')}",
                    $series->rrule
                );
            }
            $series->save();

            // Delete all obsolete excludes
            $db     = $this->getAdapter();
            $where  = $db->quoteInto('calendar2_id = ?', $this->id);
            $where .= $db->quoteInto(
                'AND date > ?',
                $newLast->format('Y-m-d H:i:s')
            );
            $db->delete('calendar2_excluded_dates', $where);
        } else {
            // If it's somewhere in between, just add it to the list of
            // excluded dates.
            $this->getAdapter()->insert(
                'calendar2_excluded_dates',
                array(
                    'calendar2_id' => $this->id,
                    'date'         => $date->format('Y-m-d H:i:s')
                )
            );
        }
    }

    /**
     * Get the participants and their confirmation statuses from the database.
     * Modifies $this->_participantData.
     *
     * @return void
     */
    private function _fetchParticipantData()
    {
        if (is_null($this->_participantDataInDb)) {
            if (null === $this->_storedId) {
                // We don't exist in the database. So we don't have any.
                $this->_participantDataInDb = array();
            } else {
                $this->_participantDataInDb = $this->getAdapter()->fetchPairs(
                    'SELECT user_id,confirmation_status '
                    . 'FROM calendar2_user_relation '
                    . 'WHERE calendar2_id = :id',
                    array('id' => $this->id)
                );
            }

            if (is_null($this->_participantData)) {
                // Avoid overwriting new data
                $this->_participantData = $this->_participantDataInDb;
            }
        }
    }

    /**
     * Saves the participants for this event.
     * This object must have already been save()d for this method to work.
     *
     * @return void
     */
    private function _saveParticipantData()
    {
        $db = $this->getAdapter();

        foreach ($this->_participantData as $id => $status) {
            if (!array_key_exists($id, $this->_participantDataInDb)) {
                $db->insert(
                    'calendar2_user_relation',
                    array(
                        'calendar2_id'        => $this->id,
                        'user_id'             => $id,
                        'confirmation_status' => $status
                    )
                );
            } else if ($status != $this->_participantDataInDb[$id]) {
                $where  = $db->quoteInto('calendar2_id = ?', $this->id);
                $where .= $db->quoteInto(' AND user_id = ?', $id);
                $db->update(
                    'calendar2_user_relation',
                    array('confirmation_status' => $status),
                    $where
                );
            }
        }

        foreach ($this->_participantDataInDb as $id => $status) {
            if (!array_key_exists($id, $this->_participantData)
                    && $id !== $this->ownerId) {
                $db->delete(
                    'calendar2_user_relation',
                    array(
                        $db->quoteInto('calendar2_id = ?', $this->id),
                        $db->quoteInto('user_id = ?', $id)
                    )
                );
            }
        }

        $this->_participantDataInDb = $this->_participantData;
    }

    /**
     * Updates the rights for added or removed participants.
     *
     * @return void
     */
    private function _updateRights()
    {
        foreach ($this->participants as $p) {
            $rights[$p] = Phprojekt_Acl::READ;
        }
        $rights[$this->ownerId] = Phprojekt_Acl::ALL;

        $this->saveRights($rights);
    }

    /**
     * Saves this object to a new row, even if it is already backed by the
     * database. After a call to this function, the id will be different.
     *
     * @return int The id of the saved row.
     */
    private function _saveToNewRow()
    {
        $tagsObject = new Phprojekt_Tags();
        $moduleId = Phprojekt_Module::getId('Calendar2');
        $tags = array();
        foreach ($tagsObject->getTagsByModule($moduleId, $this->id) as $val) {
            $tags[] = $val;
        }

        $this->_fetchParticipantData();
        $excludedDates              = $this->getExcludedDates();
        $this->_storedId            = null;
        $this->_data['id']          = null;
        $this->_participantDataInDb = array();
        $this->_isFirst             = true;
        $this->save();

        $tagsObject->saveTags($moduleId, $this->id, implode(' ', $tags));

        return $this->id;
    }

}
