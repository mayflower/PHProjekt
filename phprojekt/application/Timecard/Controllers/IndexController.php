<?php
/**
 * Timecard Module Controller.
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
 * @subpackage Timecard
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
 * Timecard Module Controller.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Timecard
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Timecard_IndexController extends IndexController
{
    /**
     * Keep in the session the current project id
     *
     * @return void
     */
    public function setCurrentProjectId()
    {
        Phprojekt::setCurrentProjectId(self::INVISIBLE_ROOT);
    }

    /**
     * Returns a list of the days in the month with the sum of bookings per day.
     *
     * For each day in the return, the data have:
     * <pre>
     *  - date         => Iso date.
     *  - week         => Number of day in the week (0-6).
     *  - sumInMinutes => Sum of bookings in the day in minutes.
     *  - sumInHours   => Sum of bookings in the day in HH:mm format.
     * </pre>
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - integer <b>year</b>  Year to consult.
     *  - integer <b>month</b> Month to consult.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonMonthListAction()
    {
        $year    = (int) $this->getRequest()->getParam('year', date("Y"));
        $month   = (int) $this->getRequest()->getParam('month', date("m"));
        $records = $this->getModelObject()->getMonthRecords($year, $month);

        Phprojekt_Converter_Json::echoConvert($records, Phprojekt_ModelInformation_Default::ORDERING_LIST);
    }

    /**
     * Returns a list of the bookings in a day.
     *
     * For each booking, the data have:
     * <pre>
     *  - id        => id of the booking record.
     *  - projectId => id of the booking project.
     *  - startTime => HH:mm:ss of the start time.
     *  - endTime   => HH:mm:ss of the end time.
     *  - display   => Display for the booking project.
     * </pre>
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - date <b>date</b> Iso date to consult.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonDayListAction()
    {
        $date    = Cleaner::sanitize('date', $this->getRequest()->getParam('date', date("Y-m-d")));
        $records = $this->getModelObject()->getDayRecords($date);

        Phprojekt_Converter_Json::echoConvert($records, Phprojekt_ModelInformation_Default::ORDERING_FORM);
    }

    /**
     * Return a list of Project (Ids and Names) saved as "favorites"
     *
     * For each one, the data have:
     * <pre>
     *  - id      => id of the project.
     *  - display => Display for the project.
     *  - name    => Real name of the project.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonGetFavoritesProjectsAction()
    {
        $setting = Phprojekt_Loader::getLibraryClass('Phprojekt_Setting');
        $setting->setModule('Timecard');

        $favorites = $setting->getSetting('favorites');
        if (!empty($favorites)) {
            $favorites = unserialize($favorites);
        } else {
            $favorites = array();
        }

        $activeRecord = Phprojekt_Loader::getModel('Project', 'Project');
        $tree         = new Phprojekt_Tree_Node_Database($activeRecord, 1);
        $tree         = $tree->setup();

        $datas = array();
        if (is_array($favorites)) {
            foreach ($favorites as $projectId) {
                foreach ($tree as $node) {
                    if ($node->id == $projectId) {
                        $data            = array();
                        $data['id']      = $projectId;
                        $data['display'] = $node->getDepthDisplay('title');
                        $data['name']    = $node->title;

                        $datas[$projectId] = $data;
                    }
                }
            }
        }

        Phprojekt_Converter_Json::echoConvert($datas);
    }

    /**
     * Checks if there are open bookings at the moment.
     *
     * It returns a string in JSON format with:
     * <pre>
     *  - type   => 'success'.
     *  - status => True or false if there are open bookings.
     *  - date   => startTime and date of the open booking or null.
     *  - code   => 0.
     *  - id     => 0.
     * </pre>
     *
     * @return void
     */
    public function jsonHasRunningBookingsAction()
    {
        $records = $this->getModelObject()->getRunningBookings(Phprojekt_Auth::getUserId());
        if (count($records) > 0) {
            $record = end($records);
            $date   = $record->startDatetime;
        } else {
            $date = null;
        }

        $return = array('type'    => 'success',
                        'status'  => (count($records) > 0) ? 'true' : 'false',
                        'date'    => $date,
                        'code'    => 0,
                        'id'      => 0);

        Phprojekt_Converter_Json::echoConvert($return);
    }

    /**
     * Saves a booking.
     *
     * If the request parameter "id" is null or 0, the function will add a new booking,
     * if the "id" is an existing booking, the function will update it.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - integer <b>id</b>                      id of the booking to save.
     *  - mixed   <b>all other module fields</b> All the fields values to save.
     * </pre>
     *
     * If there is an error, the save will return a Phprojekt_PublishedException,
     * if not, it returns a string in JSON format with:
     * <pre>
     *  - type    => 'success'.
     *  - message => Success message.
     *  - code    => 0.
     *  - id      => Id of the booking.
     * </pre>
     *
     * @throws Phprojekt_PublishedException On error in the action save or wrong id.
     *
     * @return void
     */
    public function jsonSaveAction()
    {
        $id = (int) $this->getRequest()->getParam('id');
        $this->setCurrentProjectId();

        if (empty($id)) {
            $model   = $this->getModelObject();
            $message = Phprojekt::getInstance()->translate(self::ADD_TRUE_TEXT);
        } else {
            $model   = $this->getModelObject()->find($id);
            $message = Phprojekt::getInstance()->translate(self::EDIT_TRUE_TEXT);
        }

        $params = $this->setParams($this->getRequest()->getParams(), $model);
        Default_Helpers_Save::save($model, $params);

        $return = array('type'    => 'success',
                        'message' => $message,
                        'code'    => 0,
                        'id'      => $model->id);

        Phprojekt_Converter_Json::echoConvert($return);
    }

    /**
     * Save the favorites projects for the current user.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - array <b>favorites</b> Array with ids of the projects.
     * </pre>
     *
     * The return is a string in JSON format with:
     * <pre>
     *  - type    => 'success'.
     *  - message => Success message.
     *  - code    => 0.
     *  - id      => 0.
     * </pre>
     *
     * @return void
     */
    public function jsonFavoritesSaveAction()
    {
        $setting = Phprojekt_Loader::getLibraryClass('Phprojekt_Setting');
        $setting->setModule('Timecard');

        $setting->setSettings($this->getRequest()->getParams());

        $message = Phprojekt::getInstance()->translate(self::EDIT_TRUE_TEXT);
        $return  = array('type'    => 'success',
                         'message' => $message,
                         'code'    => 0,
                         'id'      => 0);

        Phprojekt_Converter_Json::echoConvert($return);
    }

    /**
     * Returns the list of the bookings in the month.
     *
     * The function use Phprojekt_ModelInformation_Default::ORDERING_LIST for get and sort the fields.
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - integer <b>year</b>  Year to consult.
     *  - integer <b>month</b> Month to consult.
     * </pre>
     *
     * The return is in CSV format.
     *
     * @return void
     */
    public function csvListAction()
    {
        $db     = Phprojekt::getInstance()->getDb();
        $userId = Phprojekt_Auth::getUserId();
        $year   = (int) $this->getRequest()->getParam('year', date("Y"));
        $month  = (int) $this->getRequest()->getParam('month', date("m"));
        if (strlen($month) == 1) {
            $month = '0' . $month;
        }
        $where = sprintf('(owner_id = %d AND DATE(start_datetime) LIKE %s)', (int) $userId,
            $db->quote($year . '-' . $month . '-%'));
        $this->setCurrentProjectId();
        $records = $this->getModelObject()->fetchAll($where, 'start_datetime ASC');

        Phprojekt_Converter_Csv::echoConvert($records);
    }

    /**
     * Set some values deppend on the params
     *
     * Sanitize some values and calculate the minutes value.
     *
     * @return array
     */
    public function setParams()
    {
        $args = func_get_args();

        $params = $args[0];
        $model  = $args[1];

        $params['startDatetime'] = Cleaner::sanitize('datetime', $params['startDatetime']);
        if (isset($params['endTime'])) {
            $params['endTime'] = Cleaner::sanitize('time', $params['endTime']);
            if ($params['endTime'] == '') {
                unset($params['endTime']);
            }
        }
        $params['projectId'] = (int) $params['projectId'];
        $params['notes']     = Cleaner::sanitize('string', $params['notes']);

        if (isset($params['endTime']) && isset($params['startDatetime'])) {
            $params['minutes'] = Timecard_Models_Timecard::getDiffTime($params['endTime'],
                substr($params['startDatetime'], 11));
        } else if (!isset($params['endTime'])) {
            $params['minutes'] = 0;
        } else {
            $params['minutes'] = Timecard_Models_Timecard::getDiffTime($params['endTime'],
                substr($model->startDatetime, 11));
        }

        return $params;
    }
}
