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
 * Timecard model class
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
     * Overwrite save to set the uri if it's not already set and recalculate the minutes.
     */
    public function save()
    {
        // Prevent http://jira.opensource.mayflower.de/jira/browse/PHPROJEKT-450
        if (is_null($this->notes)) {
            $this->notes = '';
        }

        if (empty($this->endTime)) {
            $this->minutes = null;
        } else {
            $start         = new Datetime($this->startDatetime);
            $end           = new Datetime(substr($this->startDatetime, 0, 11) . $this->endTime);
            $this->minutes = floor (($end->getTimestamp() - $start->getTimestamp()) / 60);
        }

        if (empty($this->uid)) {
            $this->uid = Phprojekt::generateUniqueIdentifier();
        }

        if (empty($this->uri)) {
            $this->uri = $this->uid;
        }

        if (!$this->projectId) {
            $this->projectId = 1;
        }

        return parent::save();
    }
    /**
     * Constructor initializes additional Infomanager.
     *
     * @param array $db Configuration for Zend_Db_Table.
     *
     * @return void
     */
    public function __construct($db = null)
    {
        if (null === $db) {
            $db = Phprojekt::getInstance()->getDb();
        }
        parent::__construct($db);

        $this->_validate           = new Phprojekt_Model_Validate();
        $this->_informationManager = new Timecard_Models_Information();
    }

    /**
     * Define the clone function for prevent the same point to same object.
     *
     * @return void
     */
    public function __clone()
    {
        parent::__clone();
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

        if (isset($data['startDatetime'])) {
            $startTime = str_replace(":", "", substr($data['startDatetime'], 11));
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

            if (!empty($data['startDatetime'])) {
                $overlaps  = $this->_hasOverlappingTime();
                if ($this->id != 0 && $overlaps) {
                    // Stop Working Times button pressed, or it is being saved an existing period
                    // Check if end time overlaps any existing period but the current one
                    $this->_validate->error->addError(array(
                        'field'   => 'Time period',
                        'label'   => Phprojekt::getInstance()->translate('Time period'),
                        'message' => Phprojekt::getInstance()->translate('Can not End Working Time because this'
                        . ' moment is occupied by an existing period')));
                    return false;
                } else if ($overlaps && $this->id == 0) {
                    $this->_validate->error->addError(array(
                        'field'   => 'Time period',
                        'label'   => Phprojekt::getInstance()->translate('Time period'),
                        'message' => Phprojekt::getInstance()->translate('The entry overlaps with an existing one')));
                    return false;
                }
            }

            if (!isset($data['endTime'])) {
                // Start Hours button pressed - Check if new start time overlaps any existing period
                if ($this->_hasOverlappingTime()) {
                    $this->_validate->error->addError(array(
                        'field'   => 'Time period',
                        'label'   => Phprojekt::getInstance()->translate('Time period'),
                        'message' => Phprojekt::getInstance()->translate('Can not Start Working Time because this '
                            . 'moment is occupied by an existing period or an open one')));
                    return false;
                }
            }
        }

        if (isset($data['endTime']) && !empty($data['endTime'])) {
            if ($this->getDiffTime($data['endTime'], substr($data['startDatetime'], 11)) < 0) {
                if (($data['endTime'] != "00:00:00") && ($data['endTime'] != "00:00")) {
                    $this->_validate->error->addError(array(
                        'field'   => 'Hours',
                        'label'   => Phprojekt::getInstance()->translate('Hours'),
                        'message' => Phprojekt::getInstance()->translate('The end time must be after the start time')));
                    return false;
                }
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

            if (empty($data['startDatetime']) || $data['startDatetime'] == ':') {
                $this->_validate->error->addError(array(
                    'field'   => 'Hours',
                    'label'   => Phprojekt::getInstance()->translate('Hours'),
                    'message' => Phprojekt::getInstance()->translate('The start time is invalid')));
                return false;
            }
        }

        return $this->_validate->recordValidate($this, $data, $fields);
    }

    /**
     * Checks for overlapping times
     *
     * @return Boolean
     */
    private function _hasOverlappingTime()
    {
        $startTime = substr($this->startDatetime, 11);
        $date      = substr($this->startDatetime, 0, 10);
        $select    = Phprojekt::getInstance()->getDb()->select();

        $select->from("timecard", "COUNT(*) > 0")
            ->where("owner_id = ?", Phprojekt_Auth_Proxy::getEffectiveUserId())
            ->where("DATE(start_datetime) = ?", $date);

        if ($this->id) {
            $select->where("id != ?", $this->id);
        }

        $binding = array('startTime' => $startTime);

        if (null !== $this->endTime) {
            $select->where("(TIME(start_datetime) <= :startTime AND end_time > :startTime) "
                    . " OR (TIME(start_datetime) < :endTime AND end_time >= :endTime) "
                    . " OR (TIME(start_datetime) <= :startTime AND end_time >= :endTime) "
                    . " OR (TIME(start_datetime) >= :startTime AND end_time <= :endTime)");
            $binding['endTime'] = $this->endTime;
        } else {
            $select->where("(TIME(start_datetime) <= :startTime AND end_time > :startTime )"
                    . " OR (TIME(start_datetime) <= :startTime AND end_time IS NULL)");
        }

        $ret = $select->query(null, $binding)->fetchColumn();

        return $ret != "0";
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
        $userId = (int) Phprojekt_Auth_Proxy::getEffectiveUserId();

        $select = Phprojekt::getInstance()->getDb()->select();
        $select->from("timecard")
            ->where("owner_id = ?", $userId)
            ->where("YEAR(start_datetime) = ?", $year)
            ->where("MONTH(start_datetime) = ?", $month)
            ->order("start_datetime ASC");
        $records = $select->query()->fetchAll();

        // Get all the hours for this month
        $sortRecords = array();
        foreach ($records as $record) {
            $date = date('Y-m-d', strtotime($record['start_datetime']));
            if (!isset($sortRecords[$date])) {
                $sortRecords[$date]['sum']        = 0;
                $sortRecords[$date]['openPeriod'] = 0;
            }
            if ($record['minutes'] == 0 && null === $record['end_time']) {
                $sortRecords[$date]['openPeriod'] = 1;
            }
            $sortRecords[$date]['sum'] += (int) $record['minutes'];
        }

        $endDayofTheMonth = date("t", mktime(0, 0, 0, $month, 1, $year));
        $datas            = array();
        for ($i = 1; $i <= $endDayofTheMonth; $i++) {
            $day   = $i;
            $day   = str_pad($day, 2, "0", STR_PAD_LEFT);
            $month = str_pad($month, 2, "0", STR_PAD_LEFT);
            $date  = $year . '-' . $month . '-' . $day;

            $data         = array();
            $data['date'] = $date;
            $data['week'] = date("w", strtotime($date));
            if (isset($sortRecords[$date])) {
                $data['sumInMinutes'] = $sortRecords[$date]['sum'];
                $data['sumInHours']   = self::convertTime($sortRecords[$date]['sum']);
                $data['openPeriod']   = $sortRecords[$date]['openPeriod'];
            } else {
                $data['sumInMinutes'] = 0;
                $data['sumInHours']   = 0;
                $data['openPeriod']   = 0;
            }
            $datas[] = $data;
        }

        return array('data' => $datas);
    }

    /**
     * Return an array with all the bookings in the day
     *
     * @param string $date Date for the request
     *
     * @return array
     */
    public function getDayRecords($date)
    {
        $db    = Phprojekt::getInstance()->getDb();
        $where = sprintf('(owner_id = %d AND DATE(start_datetime) = %s)', (int) Phprojekt_Auth_Proxy::getEffectiveUserId(),
            $db->quote($date));
        $records = $this->fetchAll($where, 'start_datetime ASC');
        $datas   = array();

        $activeRecord = new Project_Models_Project();
        $tree         = new Phprojekt_Tree_Node_Database($activeRecord, 1);
        $tree         = $tree->setup();

        foreach ($records as $record) {
            $data    = array();
            $display = $tree->getNodeById($record->projectId)->getDepthDisplay('title');
            if (!empty($record->notes)) {
                if (strlen($record->notes) > 50) {
                    $record->notes = substr($record->notes, 0, 50) . '...';
                }
            }

            $data['id']        = $record->id;
            $data['projectId'] = $record->projectId;
            $data['startTime'] = substr($record->startDatetime, 11);
            $data['endTime']   = $record->endTime;
            $data['display']   = $display;
            $data['note']      = $record->notes;

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

        if ($hoursEnd == 0 && $minutesEnd == 0) {
            $hoursEnd = 24;
        }

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
     * Fetch the currently running booking for the given date
     * for the effective user.
     *
     * @param integer $year Year of the bookings
     * @param integer $month Month of the bookings
     * @param integer $date Date of the bookings
     *
     * @return array
     */
    static public function getRunningBooking($year, $month, $date)
    {
        $ownerId = Phprojekt_Auth_Proxy::getEffectiveUserId();
        $db = Phprojekt::getInstance()->getDb();
        $select = $db->select();
        $select->from("timecard")
            ->where("owner_id = ?", $ownerId)
            ->where("end_time IS NULL")
            ->where("YEAR(start_datetime) = ?", $year)
            ->where("MONTH(start_datetime) = ?", $month)
            ->where("DAY(start_datetime) = ?", $date)
            ->order("start_datetime ASC");
        return $db->fetchRow($select);
    }

    /**
     * Delete only the own records
     *
     * @return boolean
     */
    public function delete()
    {
        if ($this->ownerId == Phprojekt_Auth_Proxy::getEffectiveUserId()) {
            return parent::delete();
        } else {
            return false;
        }
    }

    /**
     * Retrieves the timecard entry with the given uri
     */
    public function findByUri($uri)
    {
        $fetch = $this->fetchAll(Phprojekt::getInstance()->getDb()->quoteInto('uri = ?', $uri));
        if (!$fetch) {
            return false;
        }
        return $fetch[0];
    }
}
