<?php
/**
 * Notification class of Calendar model for PHProjekt 6.0
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
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 */

/**
 * Notification class for Calendar module
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Calendar
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 */
class Calendar_Models_Notification extends Phprojekt_Notification
{
    /**
     * Returns the rrule in descriptive mode
     *
     * @param string      $rrule String with the recurrence 'rrule' field, as it is saved in the DB.
     * @param Zend_Locale $lang Locale for use in translations
     *
     * @return array
     */
    public function getRruleDescriptive($rrule, $lang)
    {
        $tmp1     = explode(";", $rrule);
        $tmp2     = explode("=", $tmp1[0]);
        $freq     = $tmp2[1];
        $tmp2     = explode("=", $tmp1[1]);
        $until    = $tmp2[1];
        $tmp2     = explode("=", $tmp1[2]);
        $interval = (int) $tmp2[1];
        $tmp2     = explode("=", $tmp1[3]);
        $byday    = $tmp2[1];
        $freq     = ucfirst(strtolower($freq));

        $rruleFields[] = array('label' => Phprojekt::getInstance()->translate('Repeats', $lang),
                               'value' => Phprojekt::getInstance()->translate($freq, $lang));
        $rruleFields[] = array('label' => Phprojekt::getInstance()->translate('Interval', $lang),
                               'value' => $interval);

        if (false === empty($until)) {
            $year      = substr($until, 0, 4);
            $month     = substr($until, 4, 2);
            $dayNum    = substr($until, 6, 2);
            $untilDate = mktime(0, 0, 0, $month, $dayNum, $year);
            $dayDesc   = date("D", $untilDate);
            $monthDesc = date("M", $untilDate);
            $untilDesc = $dayDesc . " " . $monthDesc . " " . $dayNum . " " . $year;

            $rruleFields[] = array('label' => Phprojekt::getInstance()->translate('Until', $lang),
                                   'value' => $this->translateDate($untilDesc, $lang));
        }

        if (false === empty($byday)) {
            switch ($byday) {
                case 'MO':
                    $weekDay = "Monday";
                    break;
                case 'TU':
                    $weekDay = "Tuesday";
                    break;
                case 'WE':
                    $weekDay = "Wednesday";
                    break;
                case 'TH':
                    $weekDay = "Thursday";
                    break;
                case 'FR':
                    $weekDay = "Friday";
                    break;
                case 'SA':
                    $weekDay = "Saturday";
                    break;
                case 'SU':
                    $weekDay = "Sunday";
                    break;

                $rruleFields[] = array('label' => 'Weekdays',
                                       'value' => Phprojekt::getInstance()->translate($weekDay, $lang));
            }
        }

        return $rruleFields;
    }

    /**
     * Converts the date format and language, from an english '2009-04-25' or 'Sat Apr 25 2009' to
     * 'Wednesday - March 24 2009' in the according language.
     *
     * @param string      $date String with the original date in english
     * @param Zend_Locale $lang Locale for use in translations*
     *
     * @return string
     */
    public function translateDate($date, $lang)
    {
        if (strlen($date) == 10) {
            // '2009-04-25' style
            $year  = (int) substr($date, 0, 4);
            $month = (int) substr($date, 5, 2);
            $day   = (int) substr($date, 8, 2);
        } else {
            // 'Sat Apr 25 2009' style
            $monthShort = substr($date, 4, 3);
            $day        = (int) substr($date, 8, 2);
            $year       = (int) substr($date, 11, 4);

            switch ($monthShort) {
                case 'Jan':
                    $month = 1;
                    break;
                case 'Feb':
                    $month = 2;
                    break;
                case 'Mar':
                    $month = 3;
                    break;
                case 'Apr':
                    $month = 4;
                    break;
                case 'May':
                    $month = 5;
                    break;
                case 'Jun':
                    $month = 6;
                    break;
                case 'Jul':
                    $month = 7;
                    break;
                case 'Ago':
                    $month = 8;
                    break;
                case 'Sep':
                    $month = 9;
                    break;
                case 'Oct':
                    $month = 10;
                    break;
                case 'Nov':
                    $month = 11;
                    break;
                case 'Dec':
                    $month = 12;
                    break;
            }
        }
        $dateUnix   = mktime(0, 0, 0, $month, $day, $year);
        $dayDesc    = Phprojekt::getInstance()->translate(date("l", $dateUnix), $lang);
        $monthDesc  = Phprojekt::getInstance()->translate(date("F", $dateUnix), $lang);
        $dateString = $dayDesc . " - " . $monthDesc . " " . $day . " " . $year;

        return $dateString;
    }

    /**
     * Returns the recipients for this Calendar item
     *
     * @return array
     */
    public function getTo()
    {
        return $this->_model->notifParticipants;
    }

    /**
     * Returns the fields part of the Notification body using a custom criterion for the Calendar module.
     *
     * @param Zend_Locale $lang Locale for use in translations
     *
     * @return array
     */
    public function getBodyFields($lang)
    {
        $bodyFields   = array();
        $bodyFields[] = array('label' => Phprojekt::getInstance()->translate('Title', $lang),
                              'value' => $this->_model->title);
        $bodyFields[] = array('label' => Phprojekt::getInstance()->translate('Place', $lang),
                              'value' => $this->_model->place);
        $bodyFields[] = array('label' => Phprojekt::getInstance()->translate('Notes', $lang),
                              'value' => $this->_model->notes);
        $bodyFields[] = array('label' => Phprojekt::getInstance()->translate('Start', $lang),
                              'value' => $this->translateDate($this->_model->startDateNotif, $lang) . ' '
                              . substr($this->_model->startDatetime, 11, 5));
        $bodyFields[] = array('label' => Phprojekt::getInstance()->translate('End', $lang),
                              'value' => $this->translateDate($this->_model->endDateNotif, $lang) . ' '
                              . substr($this->_model->startDatetime, 11, 5));

        $phpUser           = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
        $participants      = $this->_model->notifParticipants;
        $participantsValue = "";
        $i                 = 0;
        $lastItem          = count($participants);

        // Participants field
        foreach ($participants as $participant) {
            $i++;
            $phpUser->find((int) $participant);
            $fullname = trim($phpUser->firstname . ' ' . $phpUser->lastname);
            if (!empty($fullname)) {
                $participantsValue .= $fullname . ' (' . $phpUser->username . ')';
            } else {
                $participantsValue .= $phpUser->username;
            }
            if ($i < $lastItem) {
                $participantsValue .= ", ";
            }
        }
        $bodyFields[] = array('label' => Phprojekt::getInstance()->translate('Participants', $lang),
                              'value' => $participantsValue);

        if ($this->_model->rrule !== null) {
            $bodyFields = array_merge($bodyFields, $this->getRruleDescriptive($this->_model->rrule, $lang));
        }

        return $bodyFields;
    }

    /**
     * Goes into the contents of the 'changes' part of the Notification body (internal variable _lastHistory)
     * and parses, translates and orders it using a custom criterion for the Calendar Notification.
     * Then returns the final array.
     *
     * @param Zend_Locale $lang Locale for use in translations
     * @param boolean     $translate Translate the fields or not
     *
     * @return array
     */
    public function getBodyChanges($lang = null, $translate = true)
    {
        $order           = Phprojekt_ModelInformation_Default::ORDERING_FORM;
        $fieldDefinition = $this->_model->getInformation()->getFieldDefinition($order);
        $bodyChanges     = $this->_lastHistory;

        // Iterate in every change done
        for ($i = 0; $i < count($bodyChanges); $i++) {
            // Translate the name of the field
            foreach ($fieldDefinition as $field) {
                // Find the field definition for the field that has been modified
                if ($field['key'] == $bodyChanges[$i]['field']) {
                    $bodyChanges[$i]['field'] = $field['key'];
                    $bodyChanges[$i]['label'] = Phprojekt::getInstance()->translate($field['originalLabel'], $lang);
                    $bodyChanges[$i]['type']  = $field['type'];
                }
            }

            // Recurrence
            if (strtolower($bodyChanges[$i]['field']) == 'rrule') {
                $oldRruleEmpty = false;
                $newRruleEmpty = false;
                if (false === empty($bodyChanges[$i]['oldValue'])) {
                    $oldRrule = $this->getRruleDescriptive($bodyChanges[$i]['oldValue'], $lang);
                } else {
                    $oldRruleEmpty = true;
                }
                if (false === empty($bodyChanges[$i]['newValue'])) {
                    $newRrule = $this->getRruleDescriptive($bodyChanges[$i]['newValue'], $lang);
                } else {
                    $newRruleEmpty = true;
                }

                // FIELDS: Repeats, Interval and Until
                for ($k = 0; $k < 3; $k++) {
                    if (!$oldRruleEmpty) {
                        $fieldName = Phprojekt::getInstance()->translate('Recurrence', $lang)
                            . " - " . $oldRrule[$k]['label'];
                    } else {
                        $fieldName = Phprojekt::getInstance()->translate('Recurrence', $lang)
                            . " - " . $newRrule[$k]['label'];
                    }
                    if (!$oldRruleEmpty) {
                        $fieldOldValue = $oldRrule[$k]['value'];
                    } else {
                        $fieldOldValue = "";
                    }
                    if (!$newRruleEmpty) {
                        $fieldNewValue = $newRrule[$k]['value'];
                    } else {
                        $fieldNewValue = "";
                    }
                    if ($fieldOldValue != $fieldNewValue) {
                        $bodyChanges[] = array('label'    => $fieldName,
                                               'field'    => $fieldName,
                                               'type'     => 'hidden',
                                               'oldValue' => $fieldOldValue,
                                               'newValue' => $fieldNewValue);
                    }
                }

                // FIELD: Weekday (optional)
                $oldWeekDayExists = false;
                if (!$oldRruleEmpty) {
                    if (count($oldRrule) == 4) {
                        $oldWeekDayExists = true;
                    }
                }
                $newWeekDayExists = false;
                if (!$newRruleEmpty) {
                    if (count($newRrule) == 4) {
                        $newWeekDayExists = true;
                    }
                }
                if ($oldWeekDayExists || $newWeekDayExists) {
                    if (!$oldRruleEmpty) {
                        $fieldName = Phprojekt::getInstance()->translate('Recurrence', $lang)
                            . " - " . $oldRrule[3]['label'];
                        $fieldOldValue = $oldRrule[3]['value'];
                    } else {
                        $fieldOldValue = "";
                    }
                    if (!$newRruleEmpty) {
                        $fieldName = Phprojekt::getInstance()->translate('Recurrence', $lang)
                            . " - " . $newRrule[3]['label'];
                        $fieldNewValue = $newRrule[3]['value'];
                    } else {
                        $fieldOldValue = "";
                    }

                    if ($fieldOldValue != $fieldNewValue) {
                        $bodyChanges[] = array('label'    => $fieldName,
                                               'field'    => $fieldName,
                                               'type'     => 'hidden',
                                               'oldValue' => $fieldOldValue,
                                               'newValue' => $fieldNewValue);
                    }
                }
            } else if ($bodyChanges[$i]['field'] == 'startDatetime') {
                // Doing the date translation only if something has changed
                if (false === empty($bodyChanges[$i]['oldValue'])) {
                    $bodyChanges[$i]['oldValue'] = $this->translateDate(
                        $this->_model->getDate($bodyChanges[$i]['oldValue']), $lang)
                        . ' ' . substr($bodyChanges[$i]['oldValue'], 11, 5);
                }
                $bodyChanges[$i]['newValue'] = $this->translateDate(
                    $this->_model->getDate($bodyChanges[$i]['newValue']), $lang)
                    . ' ' . substr($bodyChanges[$i]['newValue'], 11, 5);
            }
        }

        // Take out the original confusing 'rrule' element, if it is there
        for ($i = 0; $i < count($bodyChanges); $i++) {
            if ($bodyChanges[$i]['field'] == 'rrule') {
                unset($bodyChanges[$i]);
            }
        }

        return $bodyChanges;
    }

    /**
     * Define the datetime until the frontend message is valid
     *
     * @return string
     */
    public function getCalendarValidUntil()
    {
        return date("Y-m-d H:i:s", Phprojekt_Converter_Time::userToUtc($this->_model->endDatetime));
    }

    /**
     * Defines the datetime from which the generated frontend message is valid.
     *
     * @return string
     */
    public function getCalendarValidFrom()
    {
        $date           = Phprojekt_Converter_Time::userToUtc($this->_model->startDatetime);
        $configMin      = Phprojekt::getInstance()->getConfig()->remindBefore;
        $configMinInSec = ($configMin * 60);

        return date("Y-m-d H:i:s", ($date - $configMinInSec));
    }

    /**
     * Gets the participants of a calendar item.
     * Does a distinction between the processes 'delete' and 'add' or 'edit' because for the 'delete' process an
     * additional query to the database is needed. Checks if the recipient has the right settings and if the recipient
     * is the owner of the calendar item. Returns at least an empty array.
     *
     * @return array
     */
    public function getRecipients()
    {
        if ($this->_controllProcess == Phprojekt_Notification::LAST_ACTION_REMIND) {
            $recipients = array($this->_model->participantId);
        } else {
            $recipients = parent::getRecipients();
        }

        return $this->filterRecipientsToSettings($recipients);
    }

    /**
     * Overwrites the existing saveFrontendMessage.
     * Runs a regular save first and if successful, runs a second save for the alert before a meeting starts.
     *
     * @return boolean
     */
    public function saveFrontendMessage()
    {
        $this->_validFrom       = null;
        $this->_validUntil      = null;
        $this->_controllProcess = null;
        if (null === $this->_model->rrule || empty($this->_model->rrule)) {
            // Single events
            parent::saveFrontendMessage();
        } else {
            // Multiple events
            // Check if is already saved
            if (Zend_Registry::isRegistered('keepSavedIds')) {
                $keepSavedIds = Zend_Registry::get('keepSavedIds');
                $isSaved = (isset($keepSavedIds[$this->_model->participantId]));
            } else {
                $isSaved      = false;
                $keepSavedIds = array();
            }

            // Only save the first one
            if ($this->_model->parentId != 0 && !$isSaved) {
                $keepSavedIds[$this->_model->participantId] = true;
                Zend_Registry::set('keepSavedIds', $keepSavedIds);
                parent::saveFrontendMessage();
            }
        }

        $this->_validFrom       = $this->getCalendarValidFrom();
        $this->_validUntil      = $this->getCalendarValidUntil();
        $this->_controllProcess = Phprojekt_Notification::LAST_ACTION_REMIND;

        return parent::saveFrontendMessage();
    }
}
