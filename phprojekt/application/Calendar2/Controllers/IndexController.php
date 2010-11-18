<?php
/**
 * Calendar2 Module Controller.
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
 * @subpackage Calendar2
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: @package_version@
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 */

/**
 * Calendar2 Module Controller.
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
class Calendar2_IndexController extends IndexController
{
    /**
     * Returns all events in the given period of time that the user is
     * involved in.
     *
     * Request parameters:
     * <pre>
     *  - datetime Start
     *  - datetime End
     * </pre>
     */
    public function jsonPeriodListAction()
    {
        $timezone = $this->_getUserTimezone();
        $start = new Datetime(
            Cleaner::sanitize('date', $this->getRequest()->getParam('dateStart')),
            $timezone
        );
        $end = new Datetime(
            Cleaner::sanitize('date', $this->getRequest()->getParam('dateEnd')),
            $timezone
        );

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
     *  - datetime date
     * </pre>
     */
    public function jsonDayListSelfAction()
    {
        $start = new Datetime(
            Cleaner::sanitize('date', $this->getRequest()->getParam('date')),
            $this->_getUserTimezone()
        );

        $start->setTime(0, 0, 0);
        $end = clone $start;
        $end->setTime(23, 59, 59);

        $model  = new Calendar2_Models_Calendar2();
        $events = $model->fetchAllForPeriod($start, $end);

        Phprojekt_Converter_Json::echoConvert(
            $events,
            Phprojekt_ModelInformation_Default::ORDERING_FORM
        );
    }

    /**
     * Saves the current item.
     *
     * If the request parameter "id" is null or 0, the function will add a new item,
     * if the "id" is an existing item, the function will update it.
     *
     * Request parameters:
     * <pre>
     *  - integer <b>id</b>                      id of the item to save.
     *  - string  <b>start</b>                   Start datetime of the item or recurring.
     *  - string  <b>end</b>                     End datetime of the item or recurring.
     *  - string  <b>rrule</b>                   Recurrence rule.
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
        //TODO: Input validation
        $id = (int) $this->getRequest()->getParam('id');

        $params = $this->getRequest()->getParams();
        $model = new Calendar2_Models_Calendar2();
        if (!empty($id)) {
            $model->find($id);
        }

        $model->summary     = trim($params['summary']);
        $model->description = trim($params['description']);
        $model->location    = trim($params['location']);
        $model->comments    = trim($params['comments']);
        $model->ownerId     = Phprojekt_Auth::getUserId();
        $model->visibility  = $params['visibility'];

        if (array_key_exists('newParticipants', $params)) {
            $model->setParticipants($params['newParticipants']);
        } else {
            $model->setParticipants(array());
        }

        // Using Datetime would be much nicer here.
        // But Phprojekt doesn't really support Datetime yet.
        // (Dates will automatically be converted from Usertime to UTC)
        $model->start = $params['start'];
        $model->end   = $params['end'];

        if (array_key_exists('rrule', $params)) {
            $model->rrule = $params['rrule'];
        }

        $model->save();

        Phprojekt_Converter_Json::echoConvert(array(
            'type'    => 'success',
            'message' => Phprojekt::getInstance()->translate(self::ADD_TRUE_TEXT),
            'code'    => 0,
            'id'      => $model->id
        ));
    }

    /**
     * This function wraps around the phprojekt setting for the user timezone
     * to return a DateTimeZone object.
     *
     * @return DateTimeZone The timezone of the user.
     */
    private function _getUserTimezone()
    {
        $tz = Phprojekt_User_User::getSetting('timezone', '0');
        $tz = explode('_', $tz);
        $hours = (int) $tz[0];
        if ($hours >= 0) {
            $hours = '+' . $hours;
        }
        $minutes = '00';
        if (array_key_exists(1, $tz)) {
            // We don't need the minus sign
            $minutes = abs($tz[1]);
        }
        $datetime = new Datetime($hours . ':' . $minutes);
        return $datetime->getTimezone();
    }
}
