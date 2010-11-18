<?php
/**
 * Calendar2 model class.
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
 * @subpackage Calendar2
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: @package_version@
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 */

/**
 * Calendar2 model class.
 *
 * An object of this class corresponds to a series of objects.
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
class Calendar2_Models_Calendar2 extends Phprojekt_Item_Abstract
{
    /**
     * Values for the status value
     */
    const STATUS_PENDING  = 1;
    const STATUS_ACCEPTED = 2;
    const STATUS_REJECTED = 3;

    /**
     * The confirmation statuses of this event's participants.
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
     * Constructor.
     */
    public function __construct($db = null)
    {
        parent::__construct($db);
        // UID generation method taken from rfc 5545
        $this->uid = time() . '-' . getMyPid() . '@' . php_uname('n');
    }

    /**
     * Overwrite save function. Writes the data in this object into the database.
     *
     * @return int The id of the saved object
     */
    public function save()
    {
        $isNew = empty($this->_storedId);
        $db    = $this->getAdapter();

        // Find the added and removed participants
        $this->_fetchParticipantData();

        // Save our regular values.
        parent::save();

        //TODO: Don't make that many queries.
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
                $db->update(
                    'calendar2_user_relation',
                    array('confirmation_status' => $status),
                    array(
                        'calendar2_id' => $this->id,
                        'user_id' => $id
                    )
                );
            }
        }

        foreach ($this->_participantDataInDb as $id => $status) {
            if (!array_key_exists($id, $this->_participantData) && $id !== $this->ownerId) {
                $db->delete(
                    'calendar2_user_relation',
                    array(
                        $db->quoteInto('calendar2_id = ?', $this->id),
                        $db->quoteInto('user_id = ?', $id)
                    )
                );
            }
        }

        // If this is a new event, we also have to add the owner
        if ($isNew) {
            $db->insert(
                'calendar2_user_relation',
                array(
                    'calendar2_id'        => $this->id,
                    'user_id'             => $this->ownerId,
                    'confirmation_status' => self::STATUS_ACCEPTED
                )
            );
        }

        // Adjust the access rights
        foreach ($this->participants as $p) {
            //TODO: Figure out what rights we need exactly.
            $rights[$p] = Phprojekt_Acl::READ;
        }
        $rights[$this->ownerId] = Phprojekt_Acl::ALL;

        $this->saveRights($rights);

        return $this->id;
    }

    /**
     * Deletes this series of events.
     */
    public function delete()
    {
        $this->getAdapter()->delete(
            'calendar2_user_relation',
            $this->getAdapter()->quoteInto('calendar2_id = ?', $this->id)
        );
        parent::delete();
    }

    /**
     * Implemented because we need to reset the participant data.
     */
    public function find()
    {
        $this->_participantData     = null;
        $this->_participantDataInDb = null;

        // This is very uncool, but activeRecord declares find()
        // while expecting find($id)...
        $args = func_get_args();

        if (1 != count($args)) {
            throw new Phprojekt_ActiveRecord_Exception('Wrong number of arguments for find');
        }

        return parent::find($args[0]);
    }

    /**
     * Implemented because we need to reset the participant data.
     */
    public function __clone()
    {
        parent::__clone();
        $this->_participantData     = null;
        $this->_participantDataInDb = null;
    }

    /**
     * Get all events in the period that the currently active user participates in.
     *
     * @param Datetime $start The start of the period.
     * @param Datetime $end   The end of the period.
     *
     * @return array of Calendar2_Models_Calendar
     */
    public function fetchAllForPeriod(Datetime $start, Datetime $end)
    {
        $db     = $this->getAdapter();
        $where  = $db->quoteInto('calendar2_user_relation.user_id = ? ', Phprojekt_Auth::getUserId());
        $where .= $db->quoteInto('AND start >= ?', $start->format('Y-m-d H:i:s'));
        $where .= $db->quoteInto('AND start <= ?', $end->format('Y-m-d H:i:s'));
        $join   = 'JOIN calendar2_user_relation ON calendar2.id = calendar2_user_relation.calendar2_id';

        $models = $this->fetchAll($where, null, null, null, null, $join);
        $ret    = array();

        foreach ($models as $model) {
            $helper = new Calendar2_Helper_Rrule(
                new Datetime($model->start),
                $model->rrule,
                array()
            );
            $startDT  = new Datetime($model->start);
            $endDT    = new Datetime($model->end);
            $duration = $startDT->diff($endDT);

            foreach ($helper->getDatesInPeriod($start, $end) as $date) {
                $m        = $model->copy();
                $m->uid   = $model->uid;
                $m->start = $date->format('Y-m-d H:i:s');
                $date->add($duration);
                $m->end   = $date->format('Y-m-d H:i:s');
                $ret[]    = $m;
            }
        }

        return $ret;
    }

    /**
     * Get the participants of this event.
     *
     * @return array of int
     */
    public function getParticipants()
    {
        if (null === $this->_participantData) {
            $this->_fetchParticipantData();
        }
        return array_keys($this->_participantData);
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
        $this->_participantData = array();
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
        if (null === $this->_participantData) {
            $this->_fetchParticipantData();
        }

        if (array_key_exists($id, $this->_participantData)) {
            throw new Exception("Tried to add already participating user $id");
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
        if (null === $this->_participantData) {
            $this->_fetchParticipantData();
        }

        if (!array_key_exists($id, $this->_participantData)) {
            throw new Exception("Tried to remove unknown participant $id");
        }
        unset($this->_participantData[$id]);
    }

    /**
     * Get a participant's confirmation status.
     *
     * @param int $id The id of the participant.
     *
     * @return int
     */
    public function getConfirmationStatus($id)
    {
        if (null === $this->_participantData) {
            $this->_fetchParticipantData();
        }

        if (!array_key_exists($id, $this->_participantData)) {
            throw new Exception('Participant not found');
        }
        return $this->_participantData[$id];
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
        if (null === $this->_participantData) {
            $this->_fetchParticipantData();
        }

        if (!array_key_exists($id, $this->participants)) {
            throw new Exception('Participant not found');
        }

        $this->_participantData[$id] = $newStatus;
    }

    /**
     * Retrieves the recurrenceId as specified by the iCalendar standard.
     *
     * @return string The recurrence Id.
     */
    public function getRecurrenceId()
    {
        $dt = new Datetime(
            '@' . Phprojekt_Converter_Time::userToUtc($this->start)
        );
        return $dt->format('Ymd\THis');
    }

    /**
     * Get the participants and their confirmation statuses from the database.
     * Modifies $this->_participantData.
     *
     * @return void
     */
    private function _fetchParticipantData()
    {
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

        if (null === $this->_participantData) {
            // Avoid overwriting new data
            $this->_participantData = $this->_participantDataInDb;
        }
    }

    /**
     * Returns a copy of this model object.
     *
     * Note that a call to save will fail, only show the objects
     * returned by this function to the client.
     *
     * This function would best be written as __clone, but ActiveRecord
     * already implements __clone to create a new, empty model object
     * and we don't want to break the semantics of cloning model objects.
     */
    private function copy() {
        $m = new Calendar2_Models_Calendar2();

        // use _data to bypass __set
        foreach($this->_data as $k => $v) {
            $m->_data[$k] = $v;
        }
        return $m;
    }
}
