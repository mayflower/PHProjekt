<?php
/**
 * Calendar Module Controller for PHProjekt 6.0
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @author     Eduardo Polidor <polidor@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Default Calendar Module Controller for PHProjekt 6.0
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */
class Calendar_IndexController extends IndexController
{
    /**
     * Saves the current item
     * Save if you are add one or edit one.
     * Use the model module for get the data
     *
     * If there is an error, the save will return a Phprojekt_PublishedException
     * If not, the return is a string with the same format than the Phprojekt_PublishedException
     * but with success type
     *
     * @requestparam integer id              Current item id or null for new
     * @requestparam string  startDate       Start Date for the item or recurring
     * @requestparam string  rrule           Rule for aply the recurring
     * @requestparam array   dataParticipant Array with usersId involved in the event
     * @requestparam bool    multipleEvents  Aply the save for one item or multiple events
     *
     * @return void
     */
    public function jsonSaveAction()
    {
        $message        = Phprojekt::getInstance()->translate(self::ADD_TRUE_TEXT);
        $id             = (int) $this->getRequest()->getParam('id');
        $startDate      = Cleaner::sanitize('date', $this->getRequest()->getParam('startDate', date("Y-m-d")));
        $endDate        = Cleaner::sanitize('date', $this->getRequest()->getParam('endDate', date("Y-m-d")));
        $startTime      = Cleaner::sanitize('time', $this->getRequest()->getParam('startTime', date("H-i-s")));
        $endTime        = Cleaner::sanitize('time', $this->getRequest()->getParam('endTime', date("H-i-s")));
        $rrule          = (string) $this->getRequest()->getParam('rrule', null);
        $participants   = (array) $this->getRequest()->getParam('dataParticipant');
        $multipleEvents = Cleaner::sanitize('boolean', $this->getRequest()->getParam('multipleEvents'));

        $this->getRequest()->setParam('endTime', $endTime);
        $this->getRequest()->setParam('startTime', $startTime);

        if (!empty($id)) {
            $message = Phprojekt::getInstance()->translate(self::EDIT_TRUE_TEXT);
        }

        $record  = $this->getModelObject();
        $request = $this->getRequest()->getParams();
        $id      = $record->saveEvent($request, $id, $startDate, $endDate, $rrule, $participants, $multipleEvents);

        $return = array('type'    => 'success',
                        'message' => $message,
                        'code'    => 0,
                        'id'      => $id);

        Phprojekt_Converter_Json::echoConvert($return);
    }

    /**
     * Returns all the participants for one item
     * (Check the recurrent and return all the users involved)
     *
     * @requestparam integer id The event id
     *
     * @return void
     */
    public function jsonGetParticipantsAction()
    {
        $id   = (int) $this->getRequest()->getParam('id');
        $data = array('data' => array());

        if ($id > 0) {
            $record = $this->getModelObject()->find($id);
            if (isset($record->id)) {
                $data = array('data' => $record->getAllParticipants());
            }
        }

        Phprojekt_Converter_Json::echoConvert($data);
    }

    /**
     * Deletes an event.
     * If the multipleEvents is true, all the related events will be deleted too
     *
     * @requestparam integer id             The event id
     * @requestparam bool    multipleEvents Aply the save for one item or multiple events
     *
     * @return void
     */
    public function jsonDeleteAction()
    {
        $id             = (int) $this->getRequest()->getParam('id');
        $multipleEvents = Cleaner::sanitize('boolean', $this->getRequest()->getParam('multipleEvents'));

        if (empty($id)) {
            throw new Phprojekt_PublishedException(self::ID_REQUIRED_TEXT);
        }

        $model = $this->getModelObject()->find($id);

        if ($model instanceof Phprojekt_Model_Interface) {
            $model->deleteEvents($multipleEvents);
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
     * Returns the list for a model in JSON.
     *
     * For further information see the chapter json exchange
     * in the internals documentantion
     *
     * @requestparam integer count ...
     * @requestparam integer start ...
     *
     * @return void
     */
    public function jsonListAction()
    {
        // Every dojox.data.QueryReadStore has to (and does) return "start" and "count" for paging,
        // so lets apply this to the query set. This is also used for loading a
        // grid on demand (initially only a part is shown, scrolling down loads what is needed).
        $count  = (int) $this->getRequest()->getParam('count', null);
        $offset = (int) $this->getRequest()->getParam('start', null);
        $itemId = (int) $this->getRequest()->getParam('id', null);

        if (!empty($itemId)) {
            $records = $this->getModelObject()->fetchAll('id = ' . $itemId, null, $count, $offset);
        } else {
            $where   = 'deleted IS NULL AND participant_id = ' . PHprojekt_Auth::getUserId();
            $records = $this->getModelObject()->fetchAll($where, null, $count, $offset);
        }

        Phprojekt_Converter_Json::echoConvert($records, Phprojekt_ModelInformation_Default::ORDERING_LIST);
    }

    /**
     * Returns the Day List for the logged user in JSON.
     *
     * For further information see the chapter json exchange
     * in the internals documentantion
     *
     * @requestparam integer count ...
     * @requestparam integer start ...
     * @requestparam string  date ...
     *
     * @return void
     */
    public function jsonDayListSelfAction()
    {
        // Every dojox.data.QueryReadStore has to (and does) return "start" and "count" for paging,
        // so lets apply this to the query set.
        $count   = (int) $this->getRequest()->getParam('count', null);
        $offset  = (int) $this->getRequest()->getParam('start', null);
        $date    = Cleaner::sanitize('date', $this->getRequest()->getParam('date', date("Y-m-d")));
        $userId  = PHprojekt_Auth::getUserId();
        $where   = 'deleted IS NULL AND participant_id = ' . $userId . ' AND start_date <= "' . $date . '"'
            . ' AND end_date >= "' . $date . '"';
        $records = $this->getModelObject()->fetchAll($where, null, $count, $offset);

        Phprojekt_Converter_Json::echoConvert($records, Phprojekt_ModelInformation_Default::ORDERING_FORM);
    }

    /**
     * Returns the Day List for a specific selection of users in JSON.
     *
     * For further information see the chapter json exchange
     * in the internals documentantion
     *
     * @requestparam integer count ...
     * @requestparam integer start ...
     * @requestparam string  date ...
     * @requestparam string  usersId ...
     *
     * @return void
     */
    public function jsonDayListSelectAction()
    {
        // Every dojox.data.QueryReadStore has to (and does) return "start" and "count" for paging,
        // so lets apply this to the query set.
        $count   = (int) $this->getRequest()->getParam('count', null);
        $offset  = (int) $this->getRequest()->getParam('start', null);
        $date    = Cleaner::sanitize('date', $this->getRequest()->getParam('date', date("Y-m-d")));
        $usersId = $this->getRequest()->getParam('users', null);
        $records = $this->getModelObject()->getUserSelectionRecords($usersId, $date, $count, $offset);

        Phprojekt_Converter_Json::echoConvert($records, Phprojekt_ModelInformation_Default::ORDERING_FORM);
    }

    /**
     * Returns a List for a specific period (like week or month) in JSON.
     *
     * For further information see the chapter json exchange
     * in the internals documentantion
     *
     * @requestparam integer count ...
     * @requestparam integer start ...
     * @requestparam string  dateStart ...
     * @requestparam string  dateEnd ...
     *
     * @return void
     */
    public function jsonPeriodListAction()
    {
        // Every dojox.data.QueryReadStore has to (and does) return "start" and "count" for paging,
        // so lets apply this to the query set.
        $count     = (int) $this->getRequest()->getParam('count', null);
        $offset    = (int) $this->getRequest()->getParam('start', null);
        $dateStart = Cleaner::sanitize('date', $this->getRequest()->getParam('dateStart', date("Y-m-d")));
        $dateEnd   = Cleaner::sanitize('date', $this->getRequest()->getParam('dateEnd', date("Y-m-d")));
        $userId    = PHprojekt_Auth::getUserId();
        $where     = 'deleted IS NULL AND participant_id = ' . $userId . ' AND start_date <= "' . $dateEnd
            . '" AND end_date >= "' . $dateStart . '"';
        $records   = $this->getModelObject()->fetchAll($where, "start_date", $count, $offset);

        Phprojekt_Converter_Json::echoConvert($records, Phprojekt_ModelInformation_Default::ORDERING_FORM);
    }

    /**
     * Returns the Day List for the logged user in CSV format.
     *
     * @return void
     */
    public function csvDayListSelfAction()
    {
        $count   = (int) $this->getRequest()->getParam('count', null);
        $offset  = (int) $this->getRequest()->getParam('start', null);
        $date    = Cleaner::sanitize('date', $this->getRequest()->getParam('date', date("Y-m-d")));
        $userId  = PHprojekt_Auth::getUserId();
        $where   = 'deleted IS NULL AND participant_id = ' . $userId . ' AND start_date <= "' . $date . '"'
            . ' AND end_date >= "' . $date . '"';
        $records = $this->getModelObject()->fetchAll($where, null, $count, $offset);

        Phprojekt_Converter_Csv::echoConvert($records, Phprojekt_ModelInformation_Default::ORDERING_LIST);
    }

    /**
     * Returns the Day List for a specific selection of users in CSV format.
     *
     * @return void
     */
    public function csvDayListSelectAction()
    {
        // Every dojox.data.QueryReadStore has to (and does) return "start" and "count" for paging,
        // so lets apply this to the query set.
        $count   = (int) $this->getRequest()->getParam('count', null);
        $offset  = (int) $this->getRequest()->getParam('start', null);
        $date    = Cleaner::sanitize('date', $this->getRequest()->getParam('date', date("Y-m-d")));
        $usersId = $this->getRequest()->getParam('users', null);
        $where   = 'deleted IS NULL AND participant_id IN (' . $usersId . ') AND start_date <= "' . $date . '"'
            . ' AND end_date >= "' . $date . '"';
        $records = $this->getModelObject()->fetchAll($where, null, $count, $offset);

        Phprojekt_Converter_Csv::echoConvert($records, Phprojekt_ModelInformation_Default::ORDERING_FORM);
    }

    /**
     * Returns a List for a specific period (like week or month) in CSV format.
     *
     * @return void
     */
    public function csvPeriodListAction()
    {
        // Every dojox.data.QueryReadStore has to (and does) return "start" and "count" for paging,
        // so lets apply this to the query set.
        $count     = (int) $this->getRequest()->getParam('count', null);
        $offset    = (int) $this->getRequest()->getParam('start', null);
        $dateStart = Cleaner::sanitize('date', $this->getRequest()->getParam('dateStart', date("Y-m-d")));
        $dateEnd   = Cleaner::sanitize('date', $this->getRequest()->getParam('dateEnd', date("Y-m-d")));
        $userId    = PHprojekt_Auth::getUserId();
        $where     = 'deleted IS NULL AND participant_id = ' . $userId . ' AND start_date <= "' . $dateEnd
            . '" AND end_date >= "' . $dateStart . '"';
        $records   = $this->getModelObject()->fetchAll($where, "start_date", $count, $offset);

        Phprojekt_Converter_Csv::echoConvert($records, Phprojekt_ModelInformation_Default::ORDERING_FORM);
    }

    /**
     * Return a specific list of users
     *
     * @requestparam string users   The users id list. E.g.: '1,3,5'
     *
     * @return void
     */
    public function jsonGetSpecificUsersAction()
    {
        $usersId = $this->getRequest()->getParam('users', null);
        $where   = "status = 'A' AND id IN (" . $usersId . ")";
        $order   = "lastname";
        $user    = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
        $records = $user->fetchAll($where, $order);

        Phprojekt_Converter_Json::echoConvert($records, Phprojekt_ModelInformation_Default::ORDERING_LIST);
    }
}
