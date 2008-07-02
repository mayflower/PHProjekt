<?php
/**
 * Calendar model class
 *
 * LICENSE: Licensed under the terms of the GNU Publice License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
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
 * @license    http://phprojekt.com/license PHProjekt 6 License
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
     * Integer with the value of the root event Id
     *
     * @var integer
     */
    protected $_rootEventId = 0;

    /**
     * Gets the metadata information from database
     *
     * @return array with the info of the calendar database modified
     */
    public function info() {
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
    public static function saveEvent($request) {
        $userId        = Phprojekt_Auth::getUserId();
        $rootEventId   = 0;
        $id            = (int) $request->getParam('id');
        $participantId = $request->getParam('participantId');
        $moduleName    = $request->getModuleName();
        $participants  = array();
        $rootEventId   = self::getRootEventId($id);
        $relatedEvents = self::getRelatedEvents($rootEventId);
        $startDate     = $request->getParam('startDate');
        $endDate       = $request->getParam('endDate', $startDate);
        $serialType    = (int)$request->getParam('serialType', Calendar_Models_Calendar::EVENT_TYPE_ONCE);
        $serialDates   = $request->getParam('serialDates', array());
        if (!is_array($serialDates)) {
            $serialDates[] = array(1 => (int)$serialDates);
        }
        
        // getting reqesuted dates for the serial meeting (if it is serial)
        $eventDates = self::getSerialDates($startDate, $endDate, $serialType, $serialDates);

        // getting the participant list from request
        if (is_array($participantId)) {
            // we will put the owner id first, just to make it clear
            if (in_array($userId, $participantId)) {
                $participants[] = $userId;
            }
            foreach ($participantId as $oneParticipant) {
                if (!in_array((int)$oneParticipant, $participants)) {
                    $participants[] = (int)$oneParticipant;
                }
            }
        } elseif ((is_numeric($participantId) && ($userId <> (int)$participantId))) {
            $participants[] = (int)$participantId;
            // $participants[] = (int)$userId;

        } else {
            $participants[] = $userId;
        }
        
        // first, we will do the selection by date
        foreach ($eventDates as $oneDate) {
            $request->setParam('startDate', $oneDate);
                
            // now the insertion or edition for each invited user
            foreach ($participants as $oneParticipant) {
                $request->setParam('participantId', $oneParticipant);
                
                $model  = Phprojekt_Loader::getModel($moduleName, $moduleName);
                if (isset($relatedEvents[$oneDate][$oneParticipant])) {
                    if ($relatedEvents[$oneDate][$oneParticipant] <> $rootEventId) {
                        $request->setParam('parentId', $rootEventId);
                    } else {
                        $request->setParam('parentId', 0);
                    }
                    $model->find($relatedEvents[$oneDate][$oneParticipant]);
                    unset($relatedEvents[$oneDate][$oneParticipant]);
                    
                }
                
    
                Default_Helpers_Save::save($model, $request->getParams());
    
                if ($rootEventId == 0) {
                    $rootEventId = $model->id;
                    $request->setParam('parentId', $rootEventId);
                }
                
                unset($model);
            }
        }

        // removing not included dates 
        foreach ($relatedEvents as $startDate => $oneDate) {
            // now, I'll delete the other participants (uninvited?)
            if (is_array($oneDate) && count($oneDate) > 0) {
                foreach ($oneDate as $oneParticipant => $oneId) {
                    $model = Phprojekt_Loader::getModel($moduleName, $moduleName);
                    $model->find($oneId);
                    $model->delete();
                    unset($model);
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
    public static function getRootEventId ($id) {
        $rootEventId = 0;
        $rootEvent = Phprojekt_Loader::getModel('Calendar', 'Calendar');
        $rootEvent->find($id);
        while (!empty($rootEvent->parentId)) {
            $rootEvent->find($rootEvent->parentId);
        }
        $rootEventId = (int)$rootEvent->id;

        return $rootEventId;
    }

    /**
     * Gets the list of related events of one root event
     *
     * @param integer $rootEventId id of one root event
     * @param boolean $getOnlyParticipants indicates if only the user list is necessary
     * @return array with startDate => participantId => event id or only participantId => event id if it is indicated
     */
    public function getRelatedEvents ($rootEventId, $getOnlyParticipants = false) {
        $relatedEvents = array();

        // the main event is related to himself
        $rootEvent = Phprojekt_Loader::getModel('Calendar', 'Calendar');
        $rootEvent->find($rootEventId);

        if (!empty($rootEvent->id)) {
            $relatedEvents[$rootEvent->startDate][$rootEvent->participantId] = $rootEventId;

            // getting the event list -all related events-
            $eventList = $rootEvent->fetchAll("parentId = ".$rootEventId);
            if (is_array($eventList)) {
                foreach ($eventList as $oneEvent) {
                    $tmpUserId = (int)$oneEvent->participantId;
                    if ($getOnlyParticipants) {
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
     * Sets on participantId the list of all participants of one event
     *
     * @return void
     *
     */
    public function getAllParticipants() {
        $relatedEvents = array();
        $tmp           = "";

        if (!empty($this->id)) {
            $rootEventId   = $this->getRootEventId($this->id);
            $relatedEvents = $this->getRelatedEvents($rootEventId, true);

            $tmp = ",";
            $relatedEvents = array_keys($relatedEvents);
            foreach ($relatedEvents as $value) {
                $tmp .= $value.",";
            }
            $this->participantId = $tmp;
        }
    }

    /**
     * Deletes all events related to this event excepts itself
     *
     * @return void
     */
    public function deleteRelatedEvents() {
        $rootEventId   = $this->getRootEventId($this->id);
        $relatedEvents = $this->getRelatedEvents($rootEventId);

        // deleting all related event entries except this item
        if (is_array($relatedEvents) && count($relatedEvents) > 0) {
            foreach ($relatedEvents as $oneDate) {
                if (is_array($oneDate)) {
                    foreach ($oneDate as $oneId) {
                        if ($oneId <> $this->id) {
                            $model  = Phprojekt_Loader::getModel('Calendar', 'Calendar');
                            $model->find($oneId);
                            $model->delete();
                            unset($model);
                        }
                    }
                }
            }
        }
    }

    /**
     * Gsts an array with the selected dates based on serial information
     *
     * @param date $startDate Date when the serie starts
     * @param date $endDate Date when the serie ends
     * @param integer $serialType Type of seria (check Calendar_Models_Calendar constants for further information)
     * @param array $weekDays with the days of the week included on serial selection (ISO-8601 numeric representation)
     * @return array of dates
     */
    public static function getSerialDates($startDate, 
                                          $endDate, 
                                          $serialType = Calendar_Models_Calendar::EVENT_TYPE_ONCE, 
                                          $weekDays = array()) {

        $dates = array();
        $startDate = strtotime($startDate);
        $endDate = strtotime($endDate);


        switch ($serialType) {
            case Calendar_Models_Calendar::EVENT_TYPE_ONCE :
                $dates[] = date("Y-m-d", $startDate);
                break;
            case Calendar_Models_Calendar::EVENT_TYPE_DAILY :

                while ($startDate <= $endDate) {
                    $dates[] = date("Y-m-d", $startDate);
                    $startDate = mktime(0, 0, 0, date("m", $startDate), 
                                 date("d", $startDate) + 1, date("Y", $startDate));

                }
                break;

            case Calendar_Models_Calendar::EVENT_TYPE_WEEKLY :

                while ($startDate <= $endDate) {
                    if (in_array(date("N", $startDate), $weekDays)) {
                        $dates[] = date("Y-m-d", $startDate);
                    }
                    $startDate = mktime(0, 0, 0, date("m", $startDate), 
                                 date("d", $startDate) + 1, date("Y", $startDate));

                }
                break;
            case Calendar_Models_Calendar::EVENT_TYPE_MONTLY  :

                while ($startDate <= $endDate) {
                    $dates[] = date("Y-m-d", $startDate);
                    $startDate = mktime(0, 0, 0, date("m", $startDate) + 1, 
                                 date("d", $startDate), date("Y", $startDate));

                }
                break;
            case Calendar_Models_Calendar::EVENT_TYPE_ANUAL :

                while ($startDate <= $endDate) {
                    $dates[] = date("Y-m-d", $startDate);
                    $startDate = mktime(0, 0, 0,date("m", $startDate), 
                                 date("d", $startDate), date("Y", $startDate) + 1);

                }
                break;
        }
        
        return $dates;


    }

}