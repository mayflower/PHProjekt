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
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    CVS: $Id: Calendar.php 635 2008-04-02 19:32:05Z david $
 * @link       http://www.phprojekt.com
 * @author     Eduardo Polidor <polidor@mayflower.de>
 * @since      File available since Release 1.0
 */

/**
 * Calendar model class
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
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
     * Gets the metadata information from database
     *
     * @return array with the info of the calendar database modified
     */
    public function info()
    {
        $tmp = parent::info();

        // participant id is provided as the list of participants of the event,
        // not the individual id of the participat
        $tmp['metadata']['participantId']['DATA_TYPE'] = 'text';
        return $tmp;
    }

    /**
     * Save or inserts an event. It inserts one envent by participant
     *
     * @param Request $request
     *
     * @return integer the id of the root event
     */
    public static function saveEvent($request)
    {
        $userId        = Phprojekt_Auth::getUserId();
        $id            = (int) $request->getParam('id');
        $participantId = $request->getParam('participantId');
        $moduleName    = $request->getModuleName();
        $participants  = array();
        $rootEventId   = self::getRootEventId($id);
        $relatedEvents = self::getRelatedEvents($rootEventId);
        $startDate     = $request->getParam('startDate');
        $rrule         = $request->getParam('rrule', null);

        // getting reqesuted dates for the serial meeting (if it is serial)
        if (!empty($rrule)) {
            $dateCollection = new Phprojekt_Date_Collection($startDate);
            $dateCollection->applyRrule($rrule);
            $eventDates = $dateCollection->getValues();
        } else {
            $eventDates = array(new Zend_Date(strtotime($startDate)));
        }

        // getting the participant list from request
        if (is_array($participantId)) {
            // we will put the owner id first, just to make it clear
            if (!in_array($userId, $participantId)) {
                $participants[] = $userId;
            }
            foreach ($participantId as $oneParticipant) {
                if (!in_array((int)$oneParticipant, $participants)) {
                    $participants[] = (int)$oneParticipant;
                }
            }
        } elseif ((is_numeric($participantId) && ($userId <> (int)$participantId))) {
            $participants[] = $userId;
            $participants[] = (int)$participantId;
        } else {
            $participants[] = $userId;
        }

        // first, we will do the selection by date
        $model = Phprojekt_Loader::getModel($moduleName, $moduleName);
        foreach ($eventDates as $oneDate) {

            $date = date("Y-m-d", $oneDate->get());
            $request->setParam('startDate', $date);
            $request->setParam('endDate', $date);

            // now the insertion or edition for each invited user
            foreach ($participants as $oneParticipant) {
                $request->setParam('participantId', $oneParticipant);
                $clone = clone($model);
                $clone->uid = md5($date . $oneParticipant . time());
                if (isset($relatedEvents[$date][$oneParticipant])) {
                    if ($relatedEvents[$date][$oneParticipant] != $rootEventId) {
                        $request->setParam('parentId', $rootEventId);
                    } else {
                        $request->setParam('parentId', 0);
                    }
                    $clone->find($relatedEvents[$date][$oneParticipant]);
                    unset($relatedEvents[$date][$oneParticipant]);
                }
                Default_Helpers_Save::save($clone, $request->getParams());
                if ($rootEventId == 0) {
                    $rootEventId = $clone->id;
                    $request->setParam('parentId', $rootEventId);
                }
                unset($clone);
            }
        }

        // removing not included dates
        $startDate = date("Y-m-d", strtotime($startDate));
        foreach ($relatedEvents as $checkDate => $oneDate) {
            // now, I'll delete the other participants (uninvited?)
            if (is_array($oneDate) && count($oneDate) > 0 && $checkDate >= $startDate) {
                foreach ($oneDate as $oneId) {
                    $clone = clone($model);
                    $clone->find($oneId);
                    $clone->delete();
                    unset($clone);
                }
            }
        }

        return (int)$rootEventId;
    }

    /**
     * Returns the id of the root event of the id provided
     *
     * @param integer $id id of any event
     *
     * @return integer id of the root event
     */
    public static function getRootEventId($id)
    {
        $rootEventId = 0;
        $rootEvent = Phprojekt_Loader::getModel('Calendar', 'Calendar');
        $rootEvent->find($id);

        if (null !== $rootEvent->parentId || $rootEvent->parentId > 0) {
            $rootEventId = (int)$rootEvent->parentId;
        } else {
            $rootEventId = (int)$rootEvent->id;
        }

        return $rootEventId;
    }

    /**
     * Gets the list of related events of one root event
     *
     * @param integer $rootEventId id of one root event
     * @param boolean $onlyUsers indicates if only the user list is necessary
     *
     * @return array with startDate => participantId => event id or only participantId => event id if it is indicated
     */
    public function getRelatedEvents($rootEventId, $onlyUsers = false)
    {
        $relatedEvents = array();

        // the main event is related to himself
        $rootEvent = Phprojekt_Loader::getModel('Calendar', 'Calendar');
        $rootEvent->find($rootEventId);

        if (!empty($rootEvent->id)) {
            if ($onlyUsers) {
                $relatedEvents[$rootEvent->participantId] = $rootEventId;
            } else {
                $relatedEvents[$rootEvent->startDate][$rootEvent->participantId] = $rootEventId;
            }
            // getting the event list -all related events-
            $eventList = $rootEvent->fetchAll(" parentId = " . (int)$rootEventId);
            if (is_array($eventList)) {
                foreach ($eventList as $oneEvent) {
                    $tmpUserId = (int)$oneEvent->participantId;
                    if ($onlyUsers) {
                        $relatedEvents[$tmpUserId] = (int)$oneEvent->id;
                    } else {
                        $tmpStartDate = $oneEvent->startDate;
                        $relatedEvents[$tmpStartDate][$tmpUserId] = (int)$oneEvent->id;
                    }
                }
            }
        }

        return $relatedEvents;
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
        return true;
    }

    /**
     * Sets on participantId the list of all participants of one event
     *
     * @return void
     *
     */
    public function getAllParticipants()
    {
        $relatedEvents = array();

        if (!empty($this->id)) {
            $rootEventId   = $this->getRootEventId($this->id);
            $relatedEvents = $this->getRelatedEvents($rootEventId, true);
            $this->participantId = implode(",", array_keys($relatedEvents));
        }
    }

    /**
     * Deletes all events related to this event excepts itself
     *
     * @return void
     */
    public function deleteRelatedEvents()
    {
        $rootEventId   = $this->getRootEventId($this->id);
        $relatedEvents = $this->getRelatedEvents($rootEventId);
        // deleting all related event entries except this item
        if (is_array($relatedEvents) && count($relatedEvents) > 0) {
            $model = Phprojekt_Loader::getModel('Calendar', 'Calendar');
            foreach ($relatedEvents as $oneDate) {
                if (is_array($oneDate)) {
                    foreach ($oneDate as $oneId) {
                        if ($oneId <> $this->id) {
                            $clone = clone($model);
                            $clone->find($oneId);
                            $clone->delete();
                            unset($clone);
                        }
                    }
                }
            }
        }
    }
}
