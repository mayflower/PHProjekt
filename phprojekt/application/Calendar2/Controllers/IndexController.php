<?php
/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * Calendar2 Module Controller.
 */
class Calendar2_IndexController extends IndexController
{
    public function jsonGetSpecificUsersAction()
    {
        $ids = Cleaner::sanitize(
            'arrayofint',
            $this->getRequest()->getParam('users', array())
        );

        if (empty($ids)) {
            $ids[] = (int) PHprojekt_Auth::getUserId();
        }

        $db      = Phprojekt::getInstance()->getDb();
        $where   = sprintf(
            'status = %s AND id IN (%s)',
            $db->quote('A'),
            implode(", ", $ids)
        );
        $user    = new Phprojekt_User_User();
        $records = $user->fetchAll($where);

        $data = array();
        foreach ($records as $record) {
            $data['data'][] = array(
                'id'      => (int) $record->id,
                'display' => $record->displayName
            );
        }

        Phprojekt_Converter_Json::echoConvert(
            $data,
            Phprojekt_ModelInformation_Default::ORDERING_LIST
        );
    }

    /**
     * Returns the list of items for one model.
     *
     * The return have:
     *  - The metadata of each field.
     *  - The data of all the rows.
     *  - The number of rows.
     *
     * The function use Phprojekt_ModelInformation_Default::ORDERING_LIST for get and sort the fields.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - integer <b>id</b>     List only this id.
     *  - integer <b>nodeId</b> List all the items with projectId == nodeId.
     *  - integer <b>count</b>  Use for SQL LIMIT count.
     *  - integer <b>offset</b> Use for SQL LIMIT offset.
     *  - integer <b>userId</b> UserId of the user requesting the list (for proxy mode).
     *  - boolean <b>recursive</b> Include items of subprojects.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonListAction()
    {
        $userId = $this->getRequest()->getParam('userId', Phprojekt_Auth_Proxy::getEffectiveUserId());

        if (!Cleaner::validate('int', $userId)) {
            throw new Zend_Controller_Action_Exception("Invalid userId '$userId'", 400);
        }

        $userId = (int) $userId;

        if (!Phprojekt_Auth_Proxy::hasProxyRightForUserById($userId)) {
            throw new Zend_Controller_Action_Exception("Current user has no proxy rights for this user $userId", 403);
        } else {
            Phprojekt_Auth_Proxy::switchToUserById($userId);
        }

        parent::jsonListAction();
    }

    /**
     * Returns all events in the given period of time that the user is
     * involved in. Only days are recognized.
     *
     * Request parameters:
     * <pre>
     *  - datetime Start
     *  - datetime End
     * </pre>
     */
    public function jsonPeriodListAction()
    {
        $dateStart = $this->getRequest()->getParam('dateStart');
        $dateEnd   = $this->getRequest()->getParam('dateEnd');
        $userId    = $this->getRequest()->getParam('userId', (int) Phprojekt_Auth_Proxy::getEffectiveUserId());

        if (!Cleaner::validate('isoDate', $dateStart)) {
            throw new Zend_Controller_Action_Exception("Invalid dateStart '$dateStart'", 400);
        }

        if (!Cleaner::validate('isoDate', $dateEnd)) {
            throw new Zend_Controller_Action_Exception("Invalid dateEnd $dateEnd", 400);
        }

        if (!Cleaner::validate('int', $userId)) {
            throw new Zend_Controller_Action_Exception("Invalid userId '$userId'", 400);
        }

        $userId = (int) $userId;

        if (!Phprojekt_Auth_Proxy::hasProxyRightForUserById($userId)) {
            throw new Zend_Controller_Action_Exception("Current user has no proxy rights for this user $userId", 403);
        } else {
            Phprojekt_Auth_Proxy::switchToUserById($userId);
        }

        $timezone = Phprojekt_User_User::getUserDateTimeZone();
        $start = new Datetime($dateStart, $timezone);
        $start->setTime(0, 0, 0);
        $end = new Datetime($dateEnd, $timezone);
        $end->setTime(23, 59, 59);

        $model  = new Calendar2_Models_Calendar2();
        $events = $model->fetchAllForPeriod($start, $end);

        Phprojekt_Converter_Json::echoConvert(
            $events,
            Phprojekt_ModelInformation_Default::ORDERING_FORM
        );
    }

    /**
     * Returns all events on the given day that the user is involved in.
     *
     * Request parameters:
     * <pre>
     *  - date <b>date</b>
     * </pre>
     */
    public function jsonDayListSelfAction()
    {
        $date = $this->getRequest()->getParam('date');
        if (!Cleaner::validate('isoDate', $date)) {
            throw new Zend_Controller_Action_Exception("Invalid date '$date'", 400);
        }

        $this->getRequest()->setParam('dateStart', $date);
        $this->getRequest()->setParam('dateEnd', $date);
        $this->jsonPeriodListAction();
    }

    /**
     * Returns the list of events where some users are involved,
     * only for one date.
     *
     * The return have:
     *  - The metadata of each field.
     *  - The data of all the rows.
     *  - The number of rows.
     *
     * The function use Phprojekt_ModelInformation_Default::ORDERING_LIST for
     * get and sort the fields.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - date    <b>date</b>
     *  - users   <b>users</b>  Comma separated ids of the users.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonDayListSelectAction()
    {
        $date  = $this->getRequest()->getParam('date');
        $users = $this->getRequest()->getParam('users');
        $users = explode(',', $users);

        if (!Cleaner::validate('isoDate', $date)) {
            throw new Zend_Controller_Action_Exception("Invalid date '$date'", 400);
        }
        foreach ($users as $index => $user) {
            if (!Cleaner::validate('int', $user)) {
                throw new Zend_Controller_Action_Exception("Invalid user '$user'", 400);
            }
            $users[$index] = (int) $user;
        }

        $start = new Datetime($date, Phprojekt_User_User::getUserDateTimeZone());
        $start->setTime(0, 0, 0);
        $end = clone $start;
        $end->setTime(23, 59, 59);

        // We build an two-dimensional array of the form
        // {
        //   id => {
        //           recurrenceId => event
        //         }
        // }
        // to make sure that we have each occurrence only once.
        $events = array();
        foreach ($users as $user) {
            $model    = new Calendar2_Models_Calendar2();
            foreach ($model->fetchAllForPeriod($start, $end, $user) as $event) {
                $events[$event->id][$event->recurrenceId] = $event;
            }
        }
        // Then we flatten it to send it to the client
        $ret = array();
        foreach ($events as $byId) foreach ($byId as $event) {
            $ret[] = $event;
        }

        Phprojekt_Converter_Json::echoConvert(
            $ret,
            Phprojekt_ModelInformation_Default::ORDERING_FORM
        );
    }

    /**
     * Saves the current item.
     *
     * If the request parameter "id" is null or 0, the function will add a new
     * item, if the "id" is an existing item, the function will update it.
     *
     * Request parameters:
     * <pre>
     *  - integer <b>id</b>                  id of the item to save.
     *  - string  <b>start</b>               Start datetime.
     *  - string  <b>end</b>                 End datetime.
     *  - string  <b>rrule</b>               Recurrence rule.
     *  - array   <b>dataParticipant</b>     Participating users' ids.
     *  - boolean <b>multipleEvents</b>      Save for this occurrence only or
     *                                       for all that follow it too?
     *  - boolean <b>sendNotifications</b>   Whether Notifications will be send.
     *  - mixed   <b>other module fields</b> All the fields values to save.
     * </pre>
     *
     * If there is an error, the save will throw a Zend_Controller_Action_Exception,
     * if not, it returns a string in JSON format with:
     * <pre>
     *  - type    => 'success'.
     *  - message => Success message.
     *  - id      => Id of the item.
     * </pre>
     *
     * @throws Zend_Controller_Action_Exception On error or wrong id.
     *
     * @return void
     */
    public function jsonSaveAction()
    {
        $id                = $this->getRequest()->getParam('id');
        $occurrence        = $this->getRequest()->getParam('occurrence');
        $sendNotifications = $this->getRequest()->getParam('sendNotification', 'false');
        $userId            = $this->getRequest()->getParam('userId', Phprojekt_Auth_Proxy::getEffectiveUserId());

        if (!Cleaner::validate('int', $id, true)
                && 'null'      !== $id
                && 'undefined' !== $id) {
            throw new Zend_Controller_Action_Exception("Invalid id '$id'", 400);
        }
        $id = (int) $id;

        if (!empty($id) && !self::_validateTimestamp($occurrence)) {
            throw new Zend_Controller_Action_Exception("Invalid occurrence '$occurrence'", 400);
        }

        if (!Cleaner::validate('int', $userId)) {
            throw new Zend_Controller_Action_Exception("Invalid userId '$userId'", 400);
        }

        $userId = (int) $userId;

        $this->getRequest()->setParam('userId', $userId);

        if ('1' === $sendNotifications) {
            $sendNotifications = 'true';
        } else if (!Cleaner::validate('boolean', $sendNotifications)) {
            throw new Zend_Controller_Action_Exception("Invalid value for sendNotification '$sendNotifications'", 400);
        }

        $sendNotifications = ($sendNotifications == 'true') ? true : false;

        if (!Phprojekt_Auth_Proxy::hasProxyRightForUserById($userId)) {
            throw new Zend_Controller_Action_Exception("Current user has no proxy rights for this user $userId", 403);
        } else {
            Phprojekt_Auth_Proxy::switchToUserById($userId);
        }

        // Note that all function this gets passed to must validate the
        // parameters they use.
        $params = $this->getRequest()->getParams();

        $model   = new Calendar2_Models_Calendar2();
        $message = Phprojekt::getInstance()->translate(self::ADD_TRUE_TEXT);

        if (!empty($id)) {
            $start = new Datetime($occurrence, new DateTimeZone('UTC'));
            $model->findOccurrence($id, $start);
            $message = Phprojekt::getInstance()->translate(self::EDIT_TRUE_TEXT);
        }

        if (!empty($id) && $model->ownerId != Phprojekt_Auth_Proxy::getEffectiveUserId()) {
            $newId = $this->_updateConfirmationStatusAction($model, $params);
        } else if ($model->ownerId == Phprojekt_Auth_Proxy::getEffectiveUserId()) {
            $newId = $this->_saveAction($model, $params);
        } else {
            throw new Zend_Controller_Action_Exception("User did not have the right to modify this item.", 400);
        }

        if ($sendNotifications) {
            $model->getNotification()->saveFrontendMessage();
            $model->getNotification()->send(Phprojekt_Notification::TRANSPORT_MAIL_TEXT);
        }

        Phprojekt_Converter_Json::echoConvert(
            array(
                'type'    => 'success',
                'message' => $message,
                'id'      => $newId
            )
        );
    }

    /**
     * Save some fields for many items.
     * Only edit existing items.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - array <b>data</b> Array with itemId and field as index, and the value.
     *    ($data[2]['title'] = 'new tittle')
     * </pre>
     *
     * The return is a string in JSON format with:
     * <pre>
     *  - type    => 'success' or 'error'.
     *  - message => Success or error message.
     *  - id      => Comma separated ids of the items.
     * </pre>
     *
     * @return void
     */
    public function jsonSaveMultipleAction()
    {
        $data    = (array) $this->getRequest()->getParam('data');
        $showId  = array();
        $model   = new Calendar2_Models_Calendar2();
        $success = true;
        $this->setCurrentProjectId();

        $changedOccurrences = array();

        foreach ($data as $id => $occurrences) {
            foreach ($occurrences as $recurrenceId => $fields) {
                if ($recurrenceId == 'undefined') {
                    throw new Zend_Controller_Action_Exception('\'undefined\' given as recurrence id!', 400);
                }

                $model->findWithRecurrenceId($id, $recurrenceId);

                foreach ($fields as $key => $value) {
                    $model->$key = $value;
                }
                $model->saveSingleEvent();
                $model->getNotification()->saveFrontendMessage();
                $model->getNotification()->send(Phprojekt_Notification::TRANSPORT_MAIL_TEXT);
                $showId[] = $id;
                $changedOccurrences[$id] = $model->occurrence;
            }
        }

        if ($success) {
            $message    = Phprojekt::getInstance()->translate(self::EDIT_MULTIPLE_TRUE_TEXT);
            $resultType = 'success';
        } else {
            $resultType = 'error';
        }

        $return = array(
            'type'               => $resultType,
            'message'            => $message,
            'id'                 => implode(',', $showId),
            'changedOccurrences' => $changedOccurrences
        );

        Phprojekt_Converter_Json::echoConvert($return);
    }

    /**
     * Returns the detail (fields and data) of one item from the model.
     *
     * The return have:
     *  - The metadata of each field.
     *  - The data of one item.
     *  - The number of rows.
     *
     * If the request parameter "id" is null or 0, the data will be all values of a "new item",
     * if the "id" is an existing item, the data will be all the values of the item.
     *
     * The function use Phprojekt_ModelInformation_Default::ORDERING_FORM for get and sort the fields.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - integer <b>id</b> id of the item to consult.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonDetailAction()
    {
        $id         = $this->getRequest()->getParam('id');
        $occurrence = $this->getRequest()->getParam('occurrence');
        $userId     = $this->getRequest()->getParam('userId', Phprojekt_Auth_Proxy::getEffectiveUserId());

        if (!Cleaner::validate('int', $id)
                && 'null'      !== $id
                && 'undefined' !== $id) {
            throw new Zend_Controller_Action_Exception("Invalid id '$id'", 400);
        }

        $id = (int) $id;

        if (!Cleaner::validate('int', $userId)) {
            throw new Zend_Controller_Action_Exception("Invalid userId '$userId'", 400);
        }

        $userId = (int) $userId;

        if (in_array($occurrence, array('null', '0', 'undefined'))) {
            $occurrence = null;
        } else {
            try {
                $occurrence = new Datetime($occurrence, new DateTimeZone('UTC'));
            } catch (Exception $e) {
                throw new Zend_Controller_Action_Exception("Invalid occurrence timestamp '$occurrence'", 400);
            }
        }
        $this->setCurrentProjectId();

        if (Phprojekt_Auth_Proxy::hasProxyRightForUserById($userId)) {
            Phprojekt_Auth_Proxy::switchToUserById($userId);
        }

        $record = new Calendar2_Models_Calendar2();

        if (!empty($id)) {
            if (empty($occurrence)) {
                $record = $record->find($id);
            } else {
                $record = $record->findOccurrence($id, $occurrence);
            }
        }

        Phprojekt_Converter_Json::echoConvert(
            $record,
            Phprojekt_ModelInformation_Default::ORDERING_FORM
        );
    }

    /**
     * Deletes a certain item.
     *
     * REQUIRED request parameters:
     * <pre>
     *  - integer <b>id</b> id of the item to delete.
     * </pre>
     *
     * Optional request parameters:
     * <pre>
     *  - timestamp <b>start</b>           The start date of the occurrence
     *                                     to delete.
     *  - boolean <b>sendNotifications</b> Whether Notifications should be send.
     *  - boolean <b>multipleEvents</b>    Whether all events in this series
     *                                     beginning with this one should be
     *                                     deleted or just this
     *                                     single occurrence.
     * </pre>
     *
     * The return is a string in JSON format with:
     * <pre>
     *  - type    => 'success' or 'error'.
     *  - message => Success or error message.
     *  - id      => id of the deleted item.
     * </pre>
     *
     * @throws Zend_Controller_Action_Exception On wrong parameters.
     *
     * @return void
     */
    public function jsonDeleteAction()
    {
        $id                = $this->getRequest()->getParam('id');
        $occurrence        = $this->getRequest()->getParam('occurrence');
        $multiple          = $this->getRequest()->getParam(
            'multipleEvents',
            'true'
        );
        $sendNotifications = $this->getRequest()->getParam(
            'sendNotification',
            'true'
        );

        if (!Cleaner::validate('int', $id)) {
            throw new Zend_Controller_Action_Exception("Invalid id '$id'", 400);
        }
        if (!self::_validateTimestamp($occurrence)) {
            throw new Zend_Controller_Action_Exception("Invalid occurrence timestamp '$occurrence'", 400);
        }
        if (!Cleaner::validate('boolean', $multiple)) {
            throw new Zend_Controller_Action_Exception("Invalid multiple '$multiple'", 400);
        }
        if ('1' === $sendNotifications) {
            $sendNotifications = 'true';
        } else if (!Cleaner::validate('boolean', $sendNotifications)) {
            throw new Zend_Controller_Action_Exception("Invalid value for sendNotification '$sendNotifications'", 400);
        }
        $sendNotifications = ($sendNotifications == 'true') ? true : false;

        $id = (int) $id;
        $multiple = ('true' == strtolower($multiple));

        $model = new Calendar2_Models_Calendar2;

        if (empty($occurrence)) {
            $model = $model->find($id);
        } else {
            $occurrence = new Datetime($occurrence, new DateTimeZone('UTC'));
            $model      = $model->findOccurrence($id, $occurrence);
        }

        if ($multiple || empty($model->rrule)) {
            if ($sendNotifications) {
                $notification = $model->getNotification();
                $notification->setControllProcess(Phprojekt_Notification::LAST_ACTION_DELETE);
                $notification->send(Phprojekt_Notification::TRANSPORT_MAIL_TEXT);
            }
            $model->delete();
        } else {
            $model->deleteSingleEvent();
            $model->getNotification()->saveFrontendMessage();
            $model->getNotification()->send(Phprojekt_Notification::TRANSPORT_MAIL_TEXT);
        }

        Phprojekt_Converter_Json::echoConvert(
            array(
                'type'    => 'success',
                'message' => Phprojekt::getInstance()->translate(
                    self::DELETE_TRUE_TEXT
                ),
                'id'      => $id
            )
        );
    }

    /**
     * Checks if a given user is available.
     * Takes and returns datetimes based on the user's timezone.
     *
     * Request parameters:
     *  int         user    => The id of the user
     *  datetime    start   => The start of the period to check.
     *  datetime    end     => The end of the period to check.
     *
     * Response
     *  boolean     available   => Whether the participant is available.
     */
    public function jsonCheckAvailabilityAction()
    {
        $user  = $this->getRequest()->getParam('user', Phprojekt_Auth_Proxy::getEffectiveUserId());
        $start = $this->getRequest()->getParam('start');
        $end   = $this->getRequest()->getParam('end');

        if (!Cleaner::validate('int', $user)) {
            throw new Zend_Controller_Action_Exception("Invalid user id '$id'", 400);
        }
        $user = (int) $user;
        if (!self::_validateTimestamp($start)) {
            throw new Zend_Controller_Action_Exception("Invalid start timestamp '$start'", 400);
        }
        if (!self::_validateTimestamp($end)) {
            throw new Zend_Controller_Action_Exception("Invalid end timestamp '$start'", 400);
        }
        $start = new Datetime($start, Phprojekt_User_User::getUserDateTimeZone());
        $end   = new Datetime($end, Phprojekt_User_User::getUserDateTimeZone());

        $model  = new Calendar2_Models_Calendar2();
        $events = $model->fetchAllForPeriod($start, $end, $user);

        $available = true;
        foreach ($events as $event) {
            // For availability purposes, we ignore events that the user has rejected.
            if ($event->getConfirmationStatus($user) != Calendar2_Models_Calendar2::STATUS_REJECTED) {
                $available = false;
                break;
            }
        }

        Phprojekt_Converter_Json::echoConvert(
            array(
                'available' => $available
            )
        );
    }

    /**
     * Return ical busy times for the given time period and user. If no
     * user is given, default to the currently logged in user.
     *
     * Request parameters:
     *  int         user    => The id of the user. May be null.
     *  datetime    start   => The start of the period to check.
     *  datetime    end     => The end of the period to check.
     *
     * Response
     *  Array of {
     *      datetime start => The start of the busy period.
     *      datetime end   => The end of the busy period.
     *  }
     */
    public function jsonBusyTimesAction()
    {
        $user  = $this->getRequest()->getParam('user', Phprojekt_Auth_Proxy::getEffectiveUserId());
        $start = $this->getRequest()->getParam('start');
        $end   = $this->getRequest()->getParam('end');

        if (!Cleaner::validate('int', $user)) {
            throw new Zend_Controller_Action_Exception("Invalid id '$id'", 400);
        }
        $user = (int) $user;
        if (!self::_validateTimestamp($start)) {
            throw new Zend_Controller_Action_Exception("Invalid start timestamp '$start'", 400);
        }
        if (!self::_validateTimestamp($end)) {
            throw new Zend_Controller_Action_Exception("Invalid end timestamp '$start'", 400);
        }
        $start = new Datetime($start, Phprojekt_User_User::getUserDateTimeZone());
        $end   = new Datetime($end, Phprojekt_User_User::getUserDateTimeZone());

        $model  = new Calendar2_Models_Calendar2();
        $events = $model->fetchAllForPeriod($start, $end);

        $busyPeriods = array();
        foreach ($events as $event) {
            $busyPeriods[] = array(
                'start' => new Datetime($event->start, new DateTimeZone('UTC')),
                'end' => new Datetime($event->end, new DateTimeZone('UTC'))
            );
        }

        Phprojekt_Converter_Json::echoConvert(
            Calendar2_Helper_Time::compactPeriods($busyPeriods)
        );
    }

    /**
     * Updates the current user's confirmation status on the given event.
     *
     * @param Calendar2_Models_Calendar2 $model  The model to update.
     * @param Array                      $params The Request's parameters. All
     *                                           values taken from this array
     *                                           will be validated.
     *
     * @return int The id of the (new) model object.
     *
     * @throws Zend_Controller_Action_Exception On malformed $params content.
     */
    private function _updateConfirmationStatusAction($model, $params)
    {
        $status   = $params['confirmationStatus'];
        $multiple = array_key_exists('multipleEvents', $params)
            ? $params['multipleEvents']
            : 'true';

        if (!Cleaner::validate('int', $status)
                || !Calendar2_Models_Calendar2::isValidStatus((int) $status)) {
            throw new Zend_Controller_Action_Exception("Invalid confirmationStatus '$status'", 400);
        }
        if (!Cleaner::validate('boolean', $multiple)) {
            throw new Zend_Controller_Action_Exception("Invalid multiple '$multiple'", 400);
        }

        $status   = (int) $status;
        $multiple = ('true' == strtolower($multiple));

        $model->setConfirmationStatus(Phprojekt_Auth_Proxy::getEffectiveUserId(), $status);

        if ($multiple) {
            $model->save();
        } else {
            $model->saveSingleEvent();
        }

        return $model->id;
    }

    /**
     * Saves the model.
     *
     * @param Calendar2_Models_Calendar2 $model  The model object
     * @param Array                      $params The request's parameters. All
     *                                           values taken from this array
     *                                           will be validated.
     *
     * @return int The id of the (new) model object.
     */
    private function _saveAction($model, $params)
    {
        $participants = array_key_exists('newParticipants', $params)
            ? $params['newParticipants']
            : array();
        $location    = trim($params['location']);
        $start       = $params['start'];
        $end         = $params['end'];
        $summary     = trim($params['summary']);
        $description = trim($params['description']);
        $comments    = trim($params['comments']);
        $visibility  = $params['visibility'];
        $rrule       = array_key_exists('rrule', $params)
            ? trim($params['rrule'])
            : null;
        $multiple = array_key_exists('multipleEvents', $params)
            ? $params['multipleEvents']
            : 'true';

        if (!is_array($participants)) {
            throw new Zend_Controller_Action_Exception("Invalid newParticipants '$participants'", 400);
        }
        foreach ($participants as $p) {
            if (!Cleaner::validate('int', $p)) {
                //TODO: Check if the participant exists? Many db calls, little
                //      gain...
                throw new Zend_Controller_Action_Exception("Invalid participant $p", 400);
            }
        }
        if (!self::_validateTimestamp($start)) {
            throw new Zend_Controller_Action_Exception("Invalid start '$start'", 400);
        }
        if (!self::_validateTimestamp($end)) {
            throw new Zend_Controller_Action_Exception("Invalid end '$end'", 400);
        }
        if (!Cleaner::validate('int', $visibility)
                || !Calendar2_Models_Calendar2::isValidVisibility(
                    (int) $visibility
                )) {
            throw new Zend_Controller_Action_Exception("Invalid visibility '$visibility'", 400);
        }

        $visibility = (int) $visibility;

        if (!Cleaner::validate('int', $params['userId'])) {
            throw new Zend_Controller_Action_Exception("Invalid userId " . $params['userId'], 400);
        }

        $params['userId'] = (int) $params['userId'];

        if (!Cleaner::validate('boolean', $multiple)) {
            throw new Zend_Controller_Action_Exception("Invalid multiple '$multiple'", 400);
        }

        $multiple = ('true' == strtolower($multiple));

        $model->ownerId = $params['userId'];
        $model->setParticipants($participants);

        if ($model->id
                && ($model->location !== $location
                    || $model->start !== $start
                    || $model->end   !== $end)) {
            $model->setParticipantsConfirmationStatuses(
                Calendar2_Models_Calendar2::STATUS_PENDING
            );
        }

        $model->summary     = $summary;
        $model->description = $description;
        $model->location    = $location;
        $model->comments    = $comments;
        $model->visibility  = $visibility;

        // Using Datetime would be much nicer here.
        // But Phprojekt doesn't really support Datetime yet.
        // (Dates will automatically be converted from Usertime to UTC)
        $model->start = $start;
        $model->end   = $end;
        $model->rrule = $rrule;

        if ($multiple) {
            $model->save();
        } else {
            $model->saveSingleEvent();
        }

        return $model->id;
    }

    /**
     * Wrapper around Cleaner::validate('timestamp', $value) because the client
     * sends timestamps without seconds. Set emptyOk if null values are
     * permitted.
     */
    private static function _validateTimestamp($value, $emptyOk = false)
    {
        if (preg_match('/\d{4}-\d\d-\d\d \d\d:\d\d/', $value)) {
            return true;
        } else {
            return Cleaner::validate('timestamp', $value, $emptyOk);
        }
    }
}
