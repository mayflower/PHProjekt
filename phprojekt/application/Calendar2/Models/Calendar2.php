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
     * Overwrite save function. Writes the data in this object into the database.
     *
     * @return int The id of the saved object
     */
    public function save()
    {
        //TODO: Make this update-safe
        // Save our regular values.
        parent::save();

        // Check if we already have a seriesId. If not, get us one.
        if (empty($this->seriesId)) {
            $this->seriesId = $this->id;
        }
        parent::save();

        // Save the participants.
        if (null !== $this->_participantData) {
            foreach ($this->_participantData as $id => $status) {
                $this->getAdapter()->insert(
                    'calendar2_user_relation',
                    array(
                        'calendar2_id'        => $this->id,
                        'user_id'             => $id,
                        'confirmation_status' => $status
                    )
                );
            }
        }

        // Adjust the access rights
        foreach ($this->participants as $p) {
            //TODO: Figure out what rights we need exactly.
            $rights[$p] = Phprojekt_Acl::READ;
        }
        $rights = array($this->ownerId => Phprojekt_Acl::ALL);

        $this->saveRights($rights);

        return $this->id;
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
        $db = $this->getAdapter();
        $where  = $db->quoteInto('calendar2_user_relation.user_id = ? ', Phprojekt_Auth::getUserId());
        $where .= $db->quoteInto('AND start >= ?', $start->format('Y-m-d H:i:s'));
        $where .= $db->quoteInto('AND start <= ?', $end->format('Y-m-d H:i:s'));
        $join   = $db->quoteInto('JOIN calendar2_user_relation ON calendar2.id = calendar2_user_relation.calendar2_id');

        return $this->fetchAll($where, null, null, null, null, $join);
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
    public function setParticipants(Array $ids)
    {
        $this->_participantData = array_fill_keys($ids, self::STATUS_PENDING);
    }

    /**
     * Add a single Participant to the event.
     *
     * @param int $id The id of the participant to add.
     *
     * @return void
     */
    public function addParticipant($id)
    {
        if (null === $this->_participantData) {
            $this->_fetchParticipantData();
        }

        if (array_key_exists($id, $this->_participantData)) {
            throw new Exception("Tried to add already participating user $id");
        }
        $this->_participantData[$id] = self::STATUS_PENDING;
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
     * Get the participants and their confirmation statuses from the database.
     * Modifies $this->_participantData.
     *
     * @return void
     */
    private function _fetchParticipantData()
    {
        if (null === $this->_storedId) {
            // We don't exist in the database. So we don't have any.
            $this->_participantData = array();
        } else {
            $this->_participantData = $this->getAdapter()->fetchPairs(
                'SELECT user_id,confirmation_status '
                    . 'FROM calendar2_user_relation '
                    . 'WHERE calendar2_id = :id',
                array('id' => $this->id)
            );
        }
    }
}
