<?php
/**
 * Calendar Module Controller.
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
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
 * Calendar Module Controller.
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
class Calendar_IndexController extends IndexController
{
    /**
     * Returns the list of events where the logged user is involved.
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
     *  - integer <b>count</b>  Use for SQL LIMIT count.
     *  - integer <b>offset</b> Use for SQL LIMIT offset.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonListAction()
    {
        $count  = (int) $this->getRequest()->getParam('count', null);
        $offset = (int) $this->getRequest()->getParam('start', null);
        $itemId = (int) $this->getRequest()->getParam('id', null);
        $this->setCurrentProjectId();

        if (!empty($itemId)) {
            $where = 'id = ' . (int) $itemId;
        } else {
            $where = 'participant_id = ' . (int) PHprojekt_Auth::getUserId();
        }
        $where   = $this->getFilterWhere($where);
        $records = $this->getModelObject()->fetchAll($where, null, $count, $offset);

        Phprojekt_Converter_Json::echoConvert($records, Phprojekt_ModelInformation_Default::ORDERING_LIST);
    }

    /**
     * Returns the list of events where the logged user is involved,
     * only for one date.
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
     *  - date    <b>date</b>   Date for consult.
     *  - integer <b>count</b>  Use for SQL LIMIT count.
     *  - integer <b>offset</b> Use for SQL LIMIT offset.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonDayListSelfAction()
    {
        $count  = (int) $this->getRequest()->getParam('count', null);
        $offset = (int) $this->getRequest()->getParam('start', null);
        $db     = Phprojekt::getInstance()->getDb();
        $date   = $db->quote(Cleaner::sanitize('date', $this->getRequest()->getParam('date', date("Y-m-d"))));

        $where = sprintf('participant_id = %d AND DATE(start_datetime) <= %s AND DATE(end_datetime) >= %s',
            (int) PHprojekt_Auth::getUserId(), $date, $date);
        $records = $this->getModelObject()->fetchAll($where, null, $count, $offset);

        Phprojekt_Converter_Json::echoConvert($records, Phprojekt_ModelInformation_Default::ORDERING_FORM);
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
     * The function use Phprojekt_ModelInformation_Default::ORDERING_LIST for get and sort the fields.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - date    <b>date</b>   Date for consult.
     *  - users   <b>users</b>  Comma separated ids of the users.
     *  - integer <b>count</b>  Use for SQL LIMIT count.
     *  - integer <b>offset</b> Use for SQL LIMIT offset.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonDayListSelectAction()
    {
        $count   = (int) $this->getRequest()->getParam('count', null);
        $offset  = (int) $this->getRequest()->getParam('start', null);
        $date    = Cleaner::sanitize('date', $this->getRequest()->getParam('date', date("Y-m-d")));
        $usersId = Cleaner::sanitize('arrayofint', $this->getRequest()->getParam('users', array()));

        $records = $this->getModelObject()->getUserSelectionRecords($usersId, $date, $count, $offset);

        Phprojekt_Converter_Json::echoConvert($records, Phprojekt_ModelInformation_Default::ORDERING_FORM);
    }

    /**
     * Returns the list of events where the logged user is involved,
     * for a specific period (like week or month).
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
     *  - date    <b>dateStart</b> Start date for filter.
     *  - date    <b>dateEnd</b>   End date for filter.
     *  - integer <b>count</b>     Use for SQL LIMIT count.
     *  - integer <b>offset</b>    Use for SQL LIMIT offset.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonPeriodListAction()
    {
        $count     = (int) $this->getRequest()->getParam('count', null);
        $offset    = (int) $this->getRequest()->getParam('start', null);
        $db        = Phprojekt::getInstance()->getDb();
        $dateStart = $db->quote(Cleaner::sanitize('date', $this->getRequest()->getParam('dateStart', date("Y-m-d"))));
        $dateEnd   = $db->quote(Cleaner::sanitize('date', $this->getRequest()->getParam('dateEnd', date("Y-m-d"))));

        $where     = sprintf('participant_id = %d AND DATE(start_datetime) <= %s AND DATE(end_datetime) >= %s',
            (int) PHprojekt_Auth::getUserId(), $dateEnd, $dateStart);
        $records = $this->getModelObject()->fetchAll($where, "start_datetime", $count, $offset);

        Phprojekt_Converter_Json::echoConvert($records, Phprojekt_ModelInformation_Default::ORDERING_FORM);
    }

    /**
     * Saves the current item.
     *
     * If the request parameter "id" is null or 0, the function will add a new item,
     * if the "id" is an existing item, the function will update it.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - integer <b>id</b>                      id of the item to save.
     *  - string  <b>startDatetime</b>           Start datetime of the item or recurring.
     *  - string  <b>endDatetime</b>             End datetime of the item or recurring.
     *  - string  <b>rrule</b>                   Recurring rule.
     *  - array   <b>dataParticipant</b>         Array with users id involved in the event.
     *  - boolean <b>multipleEvents</b>          Aply the save for one item or multiple events.
     *  - mixed   <b>all other module fields</b> All the fields values to save.
     * </pre>
     *
     * If there is an error, the save will return a Phprojekt_PublishedException,
     * if not, it returns a string in JSON format with:
     * <pre>
     *  - type    => 'success'.
     *  - message => Success message.
     *  - code    => 0.
     *  - id      => Id of the item.
     * </pre>
     *
     * @throws Phprojekt_PublishedException On error in the action save or wrong id.
     *
     * @return void
     */
    public function jsonSaveAction()
    {
        $message       = Phprojekt::getInstance()->translate(self::ADD_TRUE_TEXT);
        $id            = (int) $this->getRequest()->getParam('id');
        $startDatetime = Cleaner::sanitize('datetime', $this->getRequest()->getParam('startDatetime',
            date("Y-m-d H:i:s")));
        $endDatetime = Cleaner::sanitize('datetime', $this->getRequest()->getParam('endDatetime',
            date("Y-m-d H:i:s")));
        $rrule                = (string) $this->getRequest()->getParam('rrule', null);
        $participants         = (array) $this->getRequest()->getParam('dataParticipant');
        $multipleEvents       = Cleaner::sanitize('boolean', $this->getRequest()->getParam('multipleEvents'));
        $multipleParticipants = Cleaner::sanitize('boolean', $this->getRequest()->getParam('multipleParticipants'));
        $modification         = false;
        $startDate            = date('Y-m-d', strtotime($startDatetime));
        $endDate              = date('Y-m-d', strtotime($endDatetime));

        $this->getRequest()->setParam('startDate', $startDate);
        $this->getRequest()->setParam('startTime', date('H:i:s', strtotime($startDatetime)));
        $this->getRequest()->setParam('endDate', $endDate);
        $this->getRequest()->setParam('endTime', date('H:i:s', strtotime($endDatetime)));
        $this->getRequest()->setParam('startDatetime', $startDatetime);
        $this->getRequest()->setParam('endDatetime', $endDatetime);
        $this->setCurrentProjectId();

        if (!empty($id)) {
            $message      = Phprojekt::getInstance()->translate(self::EDIT_TRUE_TEXT);
            $modification = true;
        }

        $model   = $this->getModelObject();
        $request = $this->getRequest()->getParams();
        $id      = $model->saveEvent($request, $id, $startDate, $endDate, $rrule, $participants, $multipleEvents,
            $multipleParticipants);

        $return = array('type'    => 'success',
                        'message' => $message,
                        'code'    => 0,
                        'id'      => $id);

        Phprojekt_Converter_Json::echoConvert($return);
    }

    /**
     * Deletes a certain event.
     *
     * If the multipleEvents is true, all the related events will be deleted too.
     * If the multipleParticipants is true, the action delete also the events to the other participants.
     *
     * REQUIRES request parameters:
     * <pre>
     *  - integer <b>id</b>                   id of the event to delete.
     *  - boolean <b>multipleEvents</b>       Deletes one item or multiple events.
     *  - boolean <b>multipleParticipants</b> Deletes for multiple participants or just the logged one.
     * </pre>
     *
     * The return is a string in JSON format with:
     * <pre>
     *  - type    => 'success' or 'error'.
     *  - message => Success or error message.
     *  - code    => 0.
     *  - id      => id of the deleted event.
     * </pre>
     *
     * @throws Phprojekt_PublishedException On missing or wrong id, or on error in the action delete.
     *
     * @return void
     */
    public function jsonDeleteAction()
    {
        $id                   = (int) $this->getRequest()->getParam('id');
        $multipleEvents       = Cleaner::sanitize('boolean', $this->getRequest()->getParam('multipleEvents'));
        $multipleParticipants = Cleaner::sanitize('boolean', $this->getRequest()->getParam('multipleParticipants'));

        if (empty($id)) {
            throw new Phprojekt_PublishedException(self::ID_REQUIRED_TEXT);
        }

        $model = $this->getModelObject()->find($id);

        if ($model instanceof Phprojekt_ActiveRecord_Abstract) {
            $model->deleteEvents($multipleEvents, $multipleParticipants);
            $message = Phprojekt::getInstance()->translate(self::DELETE_TRUE_TEXT);
            $return  = array('type'    => 'success',
                             'message' => $message,
                             'code'    => 0,
                             'id'      => $id);

            Phprojekt_Converter_Json::echoConvert($return);
        } else {
            throw new Phprojekt_PublishedException(self::NOT_FOUND);
        }
    }

    /**
     * Returns the relations for one event.
     *
     * Returns a data array with:
     * <pre>
     *  - participants  => All the participants for one item
     *                     (checks the recurrence and returns all the users involved).
     *  - relatedEvents => All the related events to the current one.
     * </pre>
     *
     * REQUIRES request parameters:
     * <pre>
     *  - integer <b>id</b> id of the event to consult.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonGetRelatedDataAction()
    {
        $id   = (int) $this->getRequest()->getParam('id');
        $data = array('data' => array());

        if ($id > 0) {
            $record = $this->getModelObject()->find($id);
            if (isset($record->id)) {
                $participants  = $record->getAllParticipants();
                $relatedEvents = implode(",", $record->getRelatedEvents());
                $data['data']  = array('participants'  => $participants,
                                       'relatedEvents' => $relatedEvents);
            }
        }

        Phprojekt_Converter_Json::echoConvert($data);
    }

    /**
     * Returns a specific list of users.
     *
     * Returns a list of all the selected users with:
     * <pre>
     *  - id      => id of user.
     *  - display => Display for the user.
     * </pre>
     *
     * REQUIRES request parameters:
     * <pre>
     *  - string <b>users</b> Comma separated ids of the users.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonGetSpecificUsersAction()
    {
        $ids = Cleaner::sanitize('arrayofint', $this->getRequest()->getParam('users', array()));

        if (empty($ids)) {
            $ids[] = (int) PHprojekt_Auth::getUserId();
        }

        $db      = Phprojekt::getInstance()->getDb();
        $where   = sprintf('status = %s AND id IN (%s)', $db->quote('A'), implode(", ", $ids));
        $user    = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
        $display = $user->getDisplay();
        $records = $user->fetchAll($where, $display);

        $data = array();
        foreach ($records as $record) {
            $data['data'][] = array('id'      => (int) $record->id,
                                    'display' => $record->applyDisplay($display, $record));
        }

        Phprojekt_Converter_Json::echoConvert($data, Phprojekt_ModelInformation_Default::ORDERING_LIST);
    }

    /**
     * Returns the list of events where the logged user is involved,
     * only for one date.
     *
     * The function use Phprojekt_ModelInformation_Default::ORDERING_LIST for get and sort the fields.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - date    <b>date</b>   Date for consult.
     *  - integer <b>count</b>  Use for SQL LIMIT count.
     *  - integer <b>offset</b> Use for SQL LIMIT offset.
     * </pre>
     *
     * The return is in CSV format.
     *
     * @return void
     */
    public function csvDayListSelfAction()
    {
        $count   = (int) $this->getRequest()->getParam('count', null);
        $offset  = (int) $this->getRequest()->getParam('start', null);
        $db      = Phprojekt::getInstance()->getDb();
        $date    = $db->quote(Cleaner::sanitize('date', $this->getRequest()->getParam('date', date("Y-m-d"))));
        $this->setCurrentProjectId();

        $where = sprintf('participant_id = %d AND DATE(start_datetime) <= %s AND DATE(end_datetime) >= %s',
            (int) PHprojekt_Auth::getUserId(), $date, $date);
        $records = $this->getModelObject()->fetchAll($where, null, $count, $offset);

        Phprojekt_Converter_Csv::echoConvert($records, Phprojekt_ModelInformation_Default::ORDERING_LIST);
    }

    /**
     * Returns the list of events where some users are involved,
     * only for one date.
     *
     * The function use Phprojekt_ModelInformation_Default::ORDERING_LIST for get and sort the fields.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - date    <b>date</b>   Date for consult.
     *  - users   <b>users</b>  Comma separated ids of the users.
     *  - integer <b>count</b>  Use for SQL LIMIT count.
     *  - integer <b>offset</b> Use for SQL LIMIT offset.
     * </pre>
     *
     * The return is in CSV format.
     *
     * @return void
     */
    public function csvDayListSelectAction()
    {
        $count  = (int) $this->getRequest()->getParam('count', null);
        $offset = (int) $this->getRequest()->getParam('start', null);
        $db     = Phprojekt::getInstance()->getDb();
        $date   = $db->quote(Cleaner::sanitize('date', $this->getRequest()->getParam('date', date("Y-m-d"))));
        $ids    = Cleaner::sanitize('arrayofint', $this->getRequest()->getParam('users', array()));
        $this->setCurrentProjectId();

        if (empty($ids)) {
            $ids[] = (int) PHprojekt_Auth::getUserId();
        }

        $where = sprintf('participant_id IN (%s) AND DATE(start_datetime) <= %s AND DATE(end_datetime) >= %s',
            implode(", ", $ids), $date, $date);
        $records = $this->getModelObject()->fetchAll($where, null, $count, $offset);

        // Hide the title, place and note from the private events
        $userId = Phprojekt_Auth::getUserId();
        foreach ($records as $key => $record) {
            if ($record->visibility == 1 && $record->participantId != $userId) {
                $record->title = "-";
                $record->notes = "-";
                $record->place = "-";
            }
        }

        Phprojekt_Converter_Csv::echoConvert($records, Phprojekt_ModelInformation_Default::ORDERING_FORM);
    }

    /**
     * Returns the list of events where the logged user is involved,
     * for a specific period (like week or month).
     *
     * The function use Phprojekt_ModelInformation_Default::ORDERING_LIST for get and sort the fields.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - date    <b>dateStart</b> Start date for filter.
     *  - date    <b>dateEnd</b>   End date for filter.
     *  - integer <b>count</b>     Use for SQL LIMIT count.
     *  - integer <b>offset</b>    Use for SQL LIMIT offset.
     * </pre>
     *
     * The return is in CSV format.
     *
     * @return void
     */
    public function csvPeriodListAction()
    {
        $count     = (int) $this->getRequest()->getParam('count', null);
        $offset    = (int) $this->getRequest()->getParam('start', null);
        $db        = Phprojekt::getInstance()->getDb();
        $dateStart = $db->quote(Cleaner::sanitize('date', $this->getRequest()->getParam('dateStart', date("Y-m-d"))));
        $dateEnd   = $db->quote(Cleaner::sanitize('date', $this->getRequest()->getParam('dateEnd', date("Y-m-d"))));
        $this->setCurrentProjectId();

        $where = sprintf('participant_id = %d AND DATE(start_datetime) <= %s AND DATE(end_datetime) >= %s',
            (int) PHprojekt_Auth::getUserId(), $dateEnd, $dateStart);
        $records = $this->getModelObject()->fetchAll($where, "start_datetime", $count, $offset);

        Phprojekt_Converter_Csv::echoConvert($records, Phprojekt_ModelInformation_Default::ORDERING_FORM);
    }
}
