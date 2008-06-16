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
        $rootEventId = self::getRootEventId($id);

        $relatedEvents = self::getRelatedEvents($rootEventId);

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

        // now the insertion or edition for each invited user
        foreach ($participants as $oneParticipant) {

            $request->setParam('participantId', $oneParticipant);
            $model  = Phprojekt_Loader::getModel($moduleName, $moduleName);
            if (isset($relatedEvents[$oneParticipant])) {

                if ($relatedEvents[$oneParticipant] <> $rootEventId) {
                    $request->setParam('parentId', $rootEventId);
                } else {
                    $request->setParam('parentId', 0);
                }
                $model->find($relatedEvents[$oneParticipant]);
                unset($relatedEvents[$oneParticipant]);
            }

            Default_Helpers_Save::save($model, $request->getParams());

            if ($rootEventId == 0) {
                $request->setParam('parentId',$model->id);
            }
            unset($model);
        }


        // now, I'll delete the other participants (uninvited?)
        if (is_array($relatedEvents) && count($relatedEvents) > 0) {
            foreach ($relatedEvents as $oneParticipant => $oneId) {
                $model  = Phprojekt_Loader::getModel($moduleName, $moduleName);

                $model->find($oneId);
                $model->delete();
                unset($model);

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

        $rootEvent = new Calendar_Models_Calendar();
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
     * @return array with participantId => event id
     */
    public function getRelatedEvents ($rootEventId) {

        $relatedEvents = array();

        // the main event is related to himself

        $rootEvent = new Calendar_Models_Calendar();
        $rootEvent->find($rootEventId);

        if (!empty($rootEvent->id)) {

            $relatedEvents[$rootEvent->participantId] = $rootEventId;

            // getting the event list -all related events-
            $eventList = $rootEvent->fetchAll("parentId = ".$rootEventId);
            if (is_array($eventList)) {
                foreach ($eventList as $oneEvent) {
                    $tmp = (int)$oneEvent->participantId;
                    $relatedEvents[$tmp] = (int)$oneEvent->id;
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
        $tmp = "";

        if (!empty($this->id)) {

            $rootEventId = $this->getRootEventId($this->id);

            $relatedEvents = $this->getRelatedEvents($rootEventId);

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

        $rootEventId = $this->getRootEventId($this->id);

        $relatedEvents = $this->getRelatedEvents($rootEventId);

        // deleting all related event entries except this item
        if (is_array($relatedEvents) && count($relatedEvents) > 0) {

            foreach ($relatedEvents as $oneId) {
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