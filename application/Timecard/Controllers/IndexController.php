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
 * @version    $Id:$
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
     * Returns the list for a model in JSON.
     *
     * For further information see the chapter json exchange
     * in the internals documentantion
     *
     * Only return data for the current user.
     * User the params for set the month and year
     *
     * @requestparam integer count ...
     * @requestparam integer start ...
     * @requestparam integer year  Year for the list view
     * @requestparam integer month Month for the list view
     * @requestparam string  view  Type of the view for the list
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
        $year   = (int) $this->getRequest()->getParam('year', date("Y"));
        $month  = (int) $this->getRequest()->getParam('month', date("m"));
        $view   = Cleaner::sanitize('alpha', $this->getRequest()->getParam('view', 'month'));

        $records = $this->getModelObject()->getRecords($view, $year, $month, $count, $offset);

        echo Phprojekt_Converter_Json::convert($records, Phprojekt_ModelInformation_Default::ORDERING_LIST);
    }

    /**
     * Save the timecard hours
     * IF the start is empty, looking for an open time and close it.
     *
     * @requestparam integer id ...
     *
     * @return void
     */
    public function jsonSaveAction()
    {
        $translate = Phprojekt::getInstance()->getTranslate();
        $id        = (int) $this->getRequest()->getParam('id');

        if (empty($id)) {
            $model   = $this->getModelObject();
            $message = $translate->translate(self::ADD_TRUE_TEXT);
        } else {
            $model   = $this->getModelObject()->find($id);
            $message = $translate->translate(self::EDIT_TRUE_TEXT);
        }

        if (null == $this->getRequest()->getParam('startTime', null)) {
            // Date filter to find the open register
            $dateFilter = array();

            $dateFilter[] = 'date = "'.date("Y-m-d").'"';
            $dateFilter[] = '(endTime = "" OR endTime is null)';
            $dateFilter = implode($dateFilter, " AND ");

            $records = $this->getModelObject()->fetchAll($dateFilter, null, 1);

            if (isset($records[0])) {
                $model = $records[0];
                $this->getRequest()->setParam('startTime', $model->startTime);
                Default_Helpers_Save::save($model, $this->getRequest()->getParams());
                $type    = 'success';
                $message = $translate->translate(self::ADD_TRUE_TEXT);
                $showId  = $model->id;
            } else {
                $type    = 'error';
                $message = $translate->translate(self::NOT_FOUND);
                $showId  = null;
            }
        } else if (null == $this->getRequest()->getParam('endTime', null)) {
            $params = $this->getRequest()->getParams();
            unset($params['endTime']);
            Default_Helpers_Save::save($model, $params);
            $type   = 'success';
            $showId = $model->id;
        } else {
            Default_Helpers_Save::save($model, $this->getRequest()->getParams());
            $type   = 'success';
            $showId = $model->id;
        }

        $return    = array('type'    => $type,
                           'message' => $message,
                           'code'    => 0,
                           'id'      => $showId);

        echo Phprojekt_Converter_Json::convert($return);
    }

    /**
     * Returns the detail for a model in JSON.
     *
     * For further information see the chapter json exchange
     * in the internals documentantion
     *
     * @requestparam string date
     *
     * @return void
     */
    public function jsonDetailAction()
    {
        $date = Cleaner::sanitize('date', $this->getRequest()->getParam('date', date("Y-m-d")));

        $where = sprintf('(ownerId = %d AND date = "%s")', Phprojekt_Auth::getUserId(), $date);

        $records = $this->getModelObject()->fetchAll($where);

        echo Phprojekt_Converter_Json::convert($records, Phprojekt_ModelInformation_Default::ORDERING_FORM);
    }

    /**
     * Returns the detail for the bookings in JSON.
     *
     * For further information see the chapter json exchange
     * in the internals documentantion
     *
     * @requestparam string date
     *
     * @return void
     */
    public function jsonBookingDetailAction()
    {
        $date = Cleaner::sanitize('date', $this->getRequest()->getParam('date', date("Y-m-d")));

        $model = Phprojekt_Loader::getModel('Timecard', 'Timeproj');

        $records = $model->getRecords($date);

        echo Phprojekt_Converter_Json::convert($records, Phprojekt_ModelInformation_Default::ORDERING_FORM);
    }

   /**
     * Deletes a certain item
     *
     * If the item are already deleted or do not exist
     * return a Phprojekt_PublishedException
     * If the item is deleted, the return is a string with the same format than the Phprojekt_PublishedException
     * but with success type
     *
     * @requestparam integer id ...
     *
     * @return void
     */
    public function jsonBookingDeleteAction()
    {
        $translate = Phprojekt::getInstance()->getTranslate();
        $id        = (int) $this->getRequest()->getParam('id');

        if (empty($id)) {
            throw new Phprojekt_PublishedException(self::ID_REQUIRED_TEXT);
        }

        $model = Phprojekt_Loader::getModel('Timecard', 'Timeproj')->find($id);

        if ($model instanceof Phprojekt_Model_Interface) {
            $tmp = $model->delete();
            if ($tmp === false) {
                $message = $translate->translate(self::DELETE_FALSE_TEXT);
            } else {
                $message = $translate->translate(self::DELETE_TRUE_TEXT);
            }
            $return  = array('type'    => 'success',
                             'message' => $message,
                             'code'    => 0,
                             'id'      => $id);

            echo Phprojekt_Converter_Json::convert($return);
        } else {
            throw new Phprojekt_PublishedException(self::NOT_FOUND);
        }
    }

    /**
     * Creates a new timecard record with the current date and time.
     *
     * @return void
     */
    public function jsonStartAction()
    {
        $translate = Phprojekt::getInstance()->getTranslate();
        $model     = $this->getModelObject();
        $message   = $translate->translate(self::ADD_TRUE_TEXT);

        $this->getRequest()->setParam('date', date("Y-m-d"));
        $this->getRequest()->setParam('startTime', date("H:i:s"));

        Default_Helpers_Save::save($model, $this->getRequest()->getParams());

        $return    = array('type'    => 'success',
                           'message' => $message,
                           'code'    => 0,
                           'id'      => $model->id);

        echo Phprojekt_Converter_Json::convert($return);
    }

    /**
     * Closes the first timecard record of the current date.
     *
     * @return void
     */
    public function jsonStopAction()
    {
        $translate = Phprojekt::getInstance()->getTranslate();
        $offset    = (int) $this->getRequest()->getParam('start', null);

        // Date filter to find the open register
        $dateFilter = array();

        $dateFilter[] = 'date = "'.date("Y-m-d").'"';
        $dateFilter[] = '(endTime = "" OR endTime is null)';
        $dateFilter = implode($dateFilter, " AND ");

        $this->getRequest()->setParam('endTime', date("H:i:s"));

        $records = $this->getModelObject()->fetchAll($dateFilter, null, 1, $offset);

        if (isset($records[0])) {
            $model = $records[0];
            Default_Helpers_Save::save($model, $this->getRequest()->getParams());
            $type    = 'success';
            $message = $translate->translate(self::ADD_TRUE_TEXT);
            $showId  = $model->id;
        } else {
            $type    = 'error';
            $message = $translate->translate(self::NOT_FOUND);
            $showId  = null;
        }

        $return    = array('type'    => $type,
                           'message' => $message,
                           'code'    => 0,
                           'id'      => $showId);

        echo Phprojekt_Converter_Json::convert($return);
    }

    /**
     * Save a booking project
     *
     * @return void
     */
    public function jsonBookingSaveAction()
    {
        $translate = Phprojekt::getInstance()->getTranslate();
        $id        = (int) $this->getRequest()->getParam('id');

        if (empty($id)) {
            $model   = Phprojekt_Loader::getModel('Timecard', 'Timeproj');
            $message = $translate->translate(self::ADD_TRUE_TEXT);
        } else {
            $model   = Phprojekt_Loader::getModel('Timecard', 'Timeproj')->find($id);
            $message = $translate->translate(self::EDIT_TRUE_TEXT);
        }

        Default_Helpers_Save::save($model, $this->getRequest()->getParams());

        $return    = array('type'    => 'success',
                           'message' => $message,
                           'code'    => 0,
                           'id'      => $model->id);

        echo Phprojekt_Converter_Json::convert($return);
    }

    /**
     * Return a list of Project Ids saved as "favorites" for th
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
        echo Phprojekt_Converter_Json::convert($favorites);
    }

    /**
     * Save the favorties projects for the current user
     *
     * @return void
     */
    public function jsonFavortiesSaveAction()
    {
        $setting = Phprojekt_Loader::getModel('Setting', 'Setting');
        $setting->setModule('Timecard');

        $setting->setSettings($this->getRequest()->getParams());

        $translate = Phprojekt::getInstance()->getTranslate();
        $message = $translate->translate(self::EDIT_TRUE_TEXT);
        $return    = array('type'    => 'success',
                           'message' => $message,
                           'code'    => 0,
                           'id'      => 0);

        echo Phprojekt_Converter_Json::convert($return);
    }
}
