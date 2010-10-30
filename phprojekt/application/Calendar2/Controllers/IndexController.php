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
     *
     * Both datetimes must include timezone information.
     */
    public function jsonPeriodListAction()
    {
        $start = new Datetime(
            Cleaner::sanitize('date', $this->getRequest()->getParam('dateStart'))
        );
        $end   = new Datetime(
            Cleaner::sanitize('date', $this->getRequest()->getParam('dateEnd'))
        );

        $model = new Calendar2_Models_Calendar2();
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
        if (!empty($id)) {
            throw new Exception('Updating not implemented yet');
        }

        $params = $this->getRequest()->getParams();
        $model = new Calendar2_Models_Calendar2();

        $model->title          = trim($params['title']);
        $model->subject        = trim($params['subject']);
        $model->place          = trim($params['place']);
        $model->notes          = trim($params['notes']);
        $model->ownerId        = Phprojekt_Auth::getUserId();
        $model->visibility     = $params['visibility'];

        if (array_key_exists('participants', $params)) {
            $model->setParticipants($params['participants']);
        }
        $model->addParticipant(Phprojekt_Auth::getUserId());
        $model->setConfirmationStatus(
            $model->ownerId,
            Calendar2_Models_Calendar2::STATUS_ACCEPTED
        );

        // Using Datetime would be much nicer here.
        // But Phprojekt doesn't support Datetime in any way yet.
        $model->start = Phprojekt_Converter_Time::userToUtc($params['start']);
        $model->end   = Phprojekt_Converter_Time::userToUtc($params['end']);

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

}
