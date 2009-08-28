<?php
/**
 * Timecard Module Controller for PHProjekt 6.0
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
 * Default Timecard Module Controller for PHProjekt 6.0
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */
class Timecard_IndexController extends IndexController
{
   /**
     * Returns a list of the days in the month with the sum of bookings per day
     *
     * @requestparam integer year  Year for the list view
     * @requestparam integer month Month for the list view
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
     * Returns a list of the bookings in a day
     *
     * @requestparam string date
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
     * @return void
     */
    public function jsonGetFavoritesProjectsAction()
    {
        $setting = Phprojekt_Loader::getModel('Setting', 'Setting');
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
        foreach ($favorites as $projectId) {
            $data            = array();
            $data['id']      = $projectId;
            $data['display'] = $tree->getNodeById($projectId)->title;

            $datas[] = $data;
        }

        Phprojekt_Converter_Json::echoConvert($datas);
    }

    /**
     * Checks if there are running bookings at the moment.
     * This method doesn't take any argument. It returns
     * a field 'status' with either true or false.
     *
     * @return void
     */
    public function jsonHasRunningBookingsAction()
    {
        $records = $this->getModelObject()->getRunningBookings(Phprojekt_Auth::getUserId());
        if (count($records) > 0) {
            /* TODO: make sure we use phprojekt's default date/time format */
            $record = end($records);
            $date   = $record->startTime . ' ' . $record->date;
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
     * Save a booking project
     *
     * @requestparam integer date ...
     * @requestparam integer startTime ...
     * @requestparam integer endTime ...
     * @requestparam integer projectId ...
     * @requestparam integer notes ...
     *
     * @return void
     */
    public function jsonSaveAction()
    {
        $id = (int) $this->getRequest()->getParam('id');

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
     * Save the favorites projects for the current user
     *
     * @return void
     */
    public function jsonFavoritesSaveAction()
    {
        $setting = Phprojekt_Loader::getModel('Setting', 'Setting');
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
     * Export the list of the bookings in the month
     *
     * @requestparam integer year  Current year
     * @requestparam integer month Current month
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
        $where   = sprintf('(owner_id = %d AND date LIKE %s)', (int) $userId, $db->quote($year . '-' . $month . '-%'));
        $records = $this->getModelObject()->fetchAll($where, 'date ASC');

        Phprojekt_Converter_Csv::echoConvert($records, 'export');
    }


    /**
     * Set some values deppend on the params
     *
     * @return array
     */
    public function setParams()
    {
        $args = func_get_args();

        $params = $args[0];
        $model  = $args[1];

        $params['date']      = Cleaner::sanitize('date', $params['date']);
        $params['startTime'] = Cleaner::sanitize('time', $params['startTime']);
        if ($params['startTime'] == '') {
            unset($params['startTime']);
        }
        $params['endTime'] = Cleaner::sanitize('time', $params['endTime']);
        if ($params['endTime'] == '') {
            unset($params['endTime']);
        }
        $params['projectId'] = (int) $params['projectId'];
        $params['notes']     = Cleaner::sanitize('string', $params['notes']);
        if (isset($params['endTime']) && isset($params['startTime'])) {
            $params['minutes'] = Timecard_Models_Timecard::getDiffTime($params['endTime'], $params['startTime']);
        } else if (!isset($params['endTime'])) {
            $params['minutes'] = 0;
        } else {
            $params['minutes'] = Timecard_Models_Timecard::getDiffTime($params['endTime'], $model->startTime);
        }

        return $params;
    }
}
