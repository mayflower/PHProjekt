<?php
/**
 * Timecard model class
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
 * @version    CVS: $Id: Timecard.php 635 2008-04-02 19:32:05Z david $
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Timecard model class
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */
class Timecard_Models_Timecard extends Phprojekt_ActiveRecord_Abstract implements Phprojekt_Model_Interface
{
    /**
     * The standard information manager with hardcoded
     * field definitions
     *
     * @var Phprojekt_ModelInformation_Interface
     */
    protected $_informationManager;

    /**
     * Validate object
     *
     * @var Phprojekt_Model_Validate
     */
    protected $_validate = null;

    /**
     * Initialize new user
     * If is seted the user id in the session,
     * the class will get all the values of these user
     *
     * @param array $db Configuration for Zend_Db_Table
     *
     * @return void
     */
    public function __construct($db = null)
    {
        if (null === $db) {
            $db = Zend_Registry::get('db');
        }
        parent::__construct($db);

        $this->_validate           = new Phprojekt_Model_Validate();
        $this->_informationManager = new Timecard_Models_Information();
    }

    /**
     * Get the information manager
     *
     * @see Phprojekt_Model_Interface::getInformation()
     *
     * @return Phprojekt_ModelInformation_Interface
     */
    public function getInformation()
    {
        return $this->_informationManager;
    }

    /**
     * Get the rigths
     *
     * @return array
     */
    public function getRights()
    {
        return array();
    }

    /**
     * Save the rigths
     *
     * @return void
     */
    public function saveRights()
    {
    }

    /**
     * Validate the current record
     *
     * @return boolean
     */
    public function recordValidate()
    {
        $data      = $this->_data;
        $fields    = $this->_informationManager->getFieldDefinition(Phprojekt_ModelInformation_Default::ORDERING_FORM);
        $translate = Zend_Registry::get('translate');

        if (isset($data['startTime'])) {
            $startTime = ereg_replace(":", "", $data['startTime']);
            if (strlen($startTime) == 6) {
                $startTime = substr($startTime, 0, 4);
            }
            $startTime = intval($startTime);
            if (($startTime > 2359) || ($startTime < 0)) {
                $this->_validate->error->addError(array(
                    'field'   => $translate->translate('Hours'),
                    'message' => $translate->translate('The start time is invalid')));
                return false;
            }
        }

        if (isset($data['endTime']) && !empty($data['endTime'])) {
            if ($this->getDiffTime($data['endTime'], $data['startTime']) < 0) {
                $this->_validate->error->addError(array(
                    'field'   => $translate->translate('Hours'),
                    'message' => $translate->translate('The end time must be after the start time')));
                return false;
            }

            $endTime = ereg_replace(":", "", $data['endTime']);
            if (strlen($endTime) == 6) {
                $endTime = substr($endTime, 0, 4);
            }
            $endTime = intval($endTime);
            if (($endTime > 2359) || ($endTime < 0)) {
                $this->_validate->error->addError(array(
                    'field'   => $translate->translate('Hours'),
                    'message' => $translate->translate('The end time is invalid')));
                return false;
            }

            if (empty($data['startTime']) || $data['startTime'] == ':') {
                if (strlen($startTime) == 6) {
                    $startTime = substr($startTime, 0, 4);
                }
                $this->_validate->error->addError(array(
                    'field'   => $translate->translate('Hours'),
                    'message' => $translate->translate('The start time is invalid')));
                return false;
            }
        }

        $this->_validate = new Phprojekt_Model_Validate();
        return $this->_validate->recordValidate($this, $data, $fields);
    }

    /**
     * Return the error data
     *
     * @return array
     */
    public function getError()
    {
        return (array) $this->_validate->error->getError();
    }

    /**
     * Return an array with information about the records, the fields and some convinations
     * with other tables (timecard and timeproj)
     *
     * @param string  $view   Type of view
     * @param integer $year   Year for the request
     * @param integer $month  Month for the request
     * @param integer $count  Count  for the request
     * @param integer $offset Offset for the request
     *
     * @return array
     */
    public function getRecords($view, $year, $month, $count, $offset)
    {
        $sortRecords = array();
        $userId      = Phprojekt_Auth::getUserId();

        if (strlen($month) == 1) {
            $month = '0'.$month;
        }
        switch ($view) {
            case 'today':
                $where   = sprintf('(ownerId = %d AND date = "%s")', $userId, date("Y-m-d"));
                $order   = ' date ASC';
                $records = $this->fetchAll($where, $order, $count, $offset);
                $data= $records;
                break;
            case 'month':
                $where = sprintf('(ownerId = %d AND date LIKE "%s")', $userId, $year.'-'.$month.'-%');
                $order = ' date ASC';

                $records       = $this->fetchAll($where, $order, $count, $offset);
                $sortRecords   = array();
                $timeproj      = new Timecard_Models_Timeproj();

                $information     = $this->getInformation($order);
                $fieldDefinition = $information->getFieldDefinition($view);

                $datas   = array();
                $data    = array();
                $numRows = 0;

                // Get all the hours for this month
                foreach ($records as $record) {
                    $sum = $this->getDiffTime($record->endTime, $record->startTime);
                    if (!isset($sortRecords[$record->date])) {
                        $sortRecords[$record->date] = array('sum'      => 0,
                                                            'bookings' => 0);
                    }
                    if ($sum > 0) {
                        $sortRecords[$record->date]['sum'] += (int)$sum;
                    }
                }

                // Get the bookings for this month
                $bookingsResults = $timeproj->fetchAll($where);
                foreach ($bookingsResults as $booking) {
                    $bookings = 0;
                    if (!isset($sortRecords[$booking->date])) {
                        $sortRecords[$booking->date] = array('sum'      => 0,
                                                             'bookings' => 0);
                    }
                    $bookings += $this->getDiffTime($booking->amount, '00:00:00');
                    $sortRecords[$booking->date]['bookings'] += (int)$bookings;
                }

                $endDayofTheMonth = date("t");
                for ($i = 1; $i <= $endDayofTheMonth; $i++) {
                    $day = $i;
                    if (strlen($day) == 1) {
                        $day = '0'.$i;
                    }
                    $date = $year.'-'.$month.'-'.$day;
                    if (isset($sortRecords[$date])) {
                        $data['date']     = $date;
                        $data['sum']      = $this->convertTime($sortRecords[$date]['sum']);
                        $data['bookings'] = $this->convertTime($sortRecords[$date]['bookings']);
                        $data['rights']   = array();
                        $datas[] = $data;
                    } else {
                        $data['date']     = $date;
                        $data['sum']      = 0;
                        $data['bookings'] = 0;
                        $data['rights']   = array();
                        $datas[] = $data;
                    }
                }

                $numRows = count($datas);
                $data = array('metadata' => $fieldDefinition,
                              'data'     => $datas,
                              'numRows'  => (int)$numRows);
                break;
        }

        return $data;
    }

    /**
     * Get the diff of minutes between 2 times
     *
     * @param string $end   endTime
     * @param string $start startTime
     *
     * @return integer
     */
    public function getDiffTime($end, $start)
    {
        $hoursEnd   = substr($end, 0, 2);
        $minutesEnd = substr($end, 3, 2);

        $hoursStart   = substr($start, 0, 2);
        $minutesStart = substr($start, 3, 2);

        return (($hoursEnd - $hoursStart)*60) + ($minutesEnd - $minutesStart);
    }

    /**
     * Convert a number of minuts into hours:inutes
     *
     * @param integer $time
     *
     * @return string
     */
    public function convertTime($time)
    {
        $hoursDiff = floor($time / 60);
        $minutesDiff = $time - ($hoursDiff * 60);
        if (strlen($hoursDiff) == 1) {
            $hoursDiff = '0'.$hoursDiff;
        }
        if (strlen($minutesDiff) == 1) {
            $minutesDiff = '0'.$minutesDiff;
        }
        return $hoursDiff.':'.$minutesDiff;
    }
}
