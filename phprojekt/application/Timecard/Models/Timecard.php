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
 * @version    $Id$
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
            $db = Phprojekt::getInstance()->getDb();
        }
        parent::__construct($db);

        $this->_validate           = Phprojekt_Loader::getLibraryClass('Phprojekt_Model_Validate');
        $this->_informationManager = Phprojekt_Loader::getModel('Timecard', 'Information');
    }

    /**
     * Define the clone function for prevent the same point to same object.
     *
     * @return void
     */
    public function __clone()
    {
        parent::__clone();
        $this->_validate           = Phprojekt_Loader::getLibraryClass('Phprojekt_Model_Validate');
        $this->_informationManager = Phprojekt_Loader::getModel('Timecard', 'Information');
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
        $data   = $this->_data;
        $fields = $this->_informationManager->getFieldDefinition(Phprojekt_ModelInformation_Default::ORDERING_FORM);

        if (isset($data['startTime'])) {
            $startTime = str_replace(":", "", $data['startTime']);
            if (strlen($startTime) == 6) {
                $startTime = substr($startTime, 0, 4);
            }
            $startTime = (int) $startTime;
            if (($startTime >= 2400) || ($startTime < 0)) {
                $this->_validate->error->addError(array(
                    'field'   => 'Hours',
                    'label'   => Phprojekt::getInstance()->translate('Hours'),
                    'message' => Phprojekt::getInstance()->translate('Start time has to be between 0:00 and 24:00')));
                return false;
            } else {
                $startMinutes = substr($startTime, strlen($startTime) - 2, 2);
                if ($startMinutes > 59 || $startMinutes < 0) {
                    $this->_validate->error->addError(array(
                        'field'   => 'Hours',
                        'label'   => Phprojekt::getInstance()->translate('Hours'),
                        'message' => Phprojekt::getInstance()->translate('The start time is invalid')));
                    return false;
                }
            }

            if (!isset($data['endTime'])) {
                // Start Hours button pressed - Check if new start time overlaps any existing period
                $records = $this->fetchAll($this->_getWhereForTimes());
                if (count($records) > 0) {
                    $this->_validate->error->addError(array(
                        'field'   => 'Time period',
                        'label'   => Phprojekt::getInstance()->translate('Time period'),
                        'message' => Phprojekt::getInstance()->translate('Can not Start Working Time because this '
                            . 'moment is occupied by an existing period or a open one')));
                    return false;
                }
            }
        }

        if (isset($data['endTime']) && !empty($data['endTime'])) {
            if ($this->getDiffTime($data['endTime'], $data['startTime']) < 0) {
                $this->_validate->error->addError(array(
                    'field'   => 'Hours',
                    'label'   => Phprojekt::getInstance()->translate('Hours'),
                    'message' => Phprojekt::getInstance()->translate('The end time must be after the start time')));
                return false;
            }

            $endTime = str_replace(":", "", $data['endTime']);
            if (strlen($endTime) == 6) {
                $endTime = substr($endTime, 0, 4);
            }
            $endTime = (int) $endTime;
            if (($endTime > 2400) || ($endTime < 0)) {
                $this->_validate->error->addError(array(
                    'field'   => 'Hours',
                    'label'   => Phprojekt::getInstance()->translate('Hours'),
                    'message' => Phprojekt::getInstance()->translate('End time has to be between 0:00 and 24:00')));
                return false;
            } else {
                $endMinutes = substr($endTime, strlen($endTime) - 2, 2);
                if ($endMinutes > 59 || $endMinutes < 0) {
                    $this->_validate->error->addError(array(
                        'field'   => 'Hours',
                        'label'   => Phprojekt::getInstance()->translate('Hours'),
                        'message' => Phprojekt::getInstance()->translate('The end time is invalid')));
                    return false;
                }
            }

            if (empty($data['startTime']) || $data['startTime'] == ':') {
                $this->_validate->error->addError(array(
                    'field'   => 'Hours',
                    'label'   => Phprojekt::getInstance()->translate('Hours'),
                    'message' => Phprojekt::getInstance()->translate('The start time is invalid')));
                return false;
            }

            if (!empty($data['startTime'])) {
                $startTime = str_replace(":", "", $data['startTime']);
                if (strlen($startTime) == 6) {
                    $startTime = substr($startTime, 0, 4);
                }
                $startTime = (int) $startTime;

                $showError = false;
                $records   = $this->fetchAll($this->_getWhereForTimes());
                if ($this->id != 0) {
                    // Stop Working Times button pressed, or it is being saved an existing period
                    // Check if end time overlaps any existing period but the current one
                    if (count($records) > 0) {
                        foreach ($records as $record) {
                            if ($record->id != $this->id) {
                                $showError = true;
                                break;
                            }
                        }
                        if ($showError) {
                            $this->_validate->error->addError(array(
                                'field'   => 'Time period',
                                'label'   => Phprojekt::getInstance()->translate('Time period'),
                                'message' => Phprojekt::getInstance()->translate('Can not End Working Time because this'
                                    . ' moment is occupied by an existing period')));
                            return false;
                        }
                    }
                } else {
                    if (count($records) > 0 && $this->id == 0) {
                        $showError = true;
                    }
                    if ($showError) {
                        $this->_validate->error->addError(array(
                            'field'   => 'Time period',
                            'label'   => Phprojekt::getInstance()->translate('Time period'),
                            'message' => Phprojekt::getInstance()->translate('Can not save it because it overlaps '
                                . 'existing one')));
                        return false;
                    }
                }
            }
        }

        return $this->_validate->recordValidate($this, $data, $fields);
    }

    /**
     * Make the where to check date and times
     *
     * @return string
     */
    private function _getWhereForTimes()
    {
        $startTime = $this->getAdapter()->quote($this->startTime);
        $date      = $this->getAdapter()->quote($this->date);
        if (null !== $this->endTime) {
            $endTime = $this->getAdapter()->quote($this->endTime);
            $where   = sprintf(" owner_id = %d AND date = %s AND "
                . " ((start_time <= %s AND end_time > %s) OR (start_time < %s AND end_time >= %s) "
                . " OR (start_time <= %s AND end_time >= %s) OR (start_time >= %s AND end_time <= %s) ) ",
                (int) Phprojekt_Auth::getUserId(), $date,
                $startTime, $startTime, $endTime, $endTime, $startTime, $endTime, $startTime, $endTime);
        } else {
            $where = sprintf(" owner_id = %d AND date = %s AND ((start_time <= %s AND end_time > %s )"
                . " OR (start_time <= %s AND end_time IS NULL)) ", (int) Phprojekt_Auth::getUserId(), $date,
                $startTime, $startTime, $startTime);
        }

        return $where;
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
     * Return an array with all the days in the month and the sum of bookings per each
     *
     * @param integer $year   Year for the request
     * @param integer $month  Month for the request
     *
     * @return array
     */
    public function getMonthRecords($year, $month)
    {
        $userId = (int) Phprojekt_Auth::getUserId();

        if (strlen($month) == 1) {
            $month = '0' . $month;
        }

        $db    = Phprojekt::getInstance()->getDb();
        $where = sprintf('(owner_id = %d AND date LIKE %s)', $userId, $db->quote($year . '-' . $month . '-%'));
        $records = $this->fetchAll($where, 'date ASC');

        // Get all the hours for this month
        $sortRecords = array();
        foreach ($records as $record) {
            if (!isset($sortRecords[$record->date])) {
                $sortRecords[$record->date] = array('sum' => (int) $record->minutes);
            } else {
                $sortRecords[$record->date]['sum'] += (int) $record->minutes;
            }
        }

        $endDayofTheMonth = date("t", mktime(0, 0, 0, $month, 1, $year));
        $datas            = array();
        for ($i = 1; $i <= $endDayofTheMonth; $i++) {
            $day = $i;
            if (strlen($day) == 1) {
                $day = '0' . $i;
            }
            $date = $year . '-' . $month . '-' . $day;

            $data         = array();
            $data['date'] = $date;
            $data['week'] = date("w", strtotime($date));
            if (isset($sortRecords[$date])) {
                $data['sumInMinutes'] = $sortRecords[$date]['sum'];
                $data['sumInHours']   = self::convertTime($sortRecords[$date]['sum']);
            } else {
                $data['sumInMinutes'] = 0;
                $data['sumInHours']   = 0;
            }
            $datas[] = $data;
        }

        return array('data' => $datas);
    }


    /**
     * Return an array with all the bookings in the day
     *
     * @param string  $date Date for the request
     *
     * @return array
     */
    public function getDayRecords($date)
    {
        $db      = Phprojekt::getInstance()->getDb();
        $where   = sprintf('(owner_id = %d AND date = %s)', (int) Phprojekt_Auth::getUserId(), $db->quote($date));
        $records = $this->fetchAll($where, 'start_time ASC');
        $datas   = array();

        $activeRecord = Phprojekt_Loader::getModel('Project', 'Project');
        $tree         = new Phprojekt_Tree_Node_Database($activeRecord, 1);
        $tree         = $tree->setup();

        foreach ($records as $record) {
            $data = array();
            $data['id']        = $record->id;
            $data['projectId'] = $record->projectId;
            $data['startTime'] = $record->startTime;
            $data['endTime']   = $record->endTime;
            $data['display']   = $tree->getNodeById($record->projectId)->title;

            $datas[] = $data;
        }

        return array('data' => $datas);
    }

    /**
     * Get the diff of minutes between 2 times
     *
     * @param string $end   endTime
     * @param string $start startTime
     *
     * @return integer
     */
    static public function getDiffTime($end, $start)
    {
        $hoursEnd   = substr($end, 0, 2);
        $minutesEnd = substr($end, 3, 2);

        $hoursStart   = substr($start, 0, 2);
        $minutesStart = substr($start, 3, 2);

        return (($hoursEnd - $hoursStart) * 60) + ($minutesEnd - $minutesStart);
    }

    /**
     * Convert a number of minuts into hours:inutes
     *
     * @param integer $time
     *
     * @return string
     */
    static public function convertTime($time)
    {
        $hoursDiff   = floor($time / 60);
        $minutesDiff = $time - ($hoursDiff * 60);

        if (strlen($hoursDiff) == 1) {
            $hoursDiff = '0' . $hoursDiff;
        }
        if (strlen($minutesDiff) == 1) {
            $minutesDiff = '0' . $minutesDiff;
        }

        return $hoursDiff . ':' . $minutesDiff;
    }

    /**
     * Get running bookings and return an array
     * of currently running for the given userid
     *
     * @param integer $ownerId Owner of the bookings
     *
     * @return array
     */
    public function getRunningBookings($ownerId)
    {
        $where = sprintf('date = %s AND (end_time = "" OR end_time IS NULL) AND owner_id = %d',
            $this->getAdapter()->quote(date('Y-m-d')), $ownerId);
        $records = $this->fetchAll($where, null, 1);
        return $records;
    }

    /**
     * Delete only the own records
     *
     * @return boolean
     */
    public function delete()
    {
        if ($this->ownerId == Phprojekt_Auth::getUserId()) {
            return parent::delete();
        } else {
            return false;
        }
    }
}
