<?php
/**
 * Notification class of Calendar model for PHProjekt 6.0
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
 * @package    PHProjekt
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @link       http://www.phprojekt.com
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 * @since      File available since Release 6.0
 */

/**
 * Notification class for Calendar module
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 */
class Calendar_Models_Notification extends Phprojekt_Notification
{
    /**
     * Fills and returns a variable with recipients using a custom criterion for Calendar class
     *
     * @return array
     */
    public function setTo()
    {
        $phpUser       = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
        $setting       = Phprojekt_Loader::getModel('Setting', 'Setting');
        $recipientsIds = $this->_model->notifParticipants;

        // All the recipients IDs are inside $recipientsIds, now add emails and descriptive names to $recipients
        $recipients = array();
        foreach ($recipientsIds as $recipient) {
            $email = $setting->getSetting('email', (int) $recipient);

            if ((int) $recipient) {
                $phpUser->find($recipient);
            } else {
                $phpUser->find(Phprojekt_Auth::getUserId());
            }

            $recipients[]             = array();
            $lastItem                 = count($recipients) - 1;
            $recipients[$lastItem][0] = $email;

            $fullname = trim($phpUser->firstname . ' ' . $phpUser->lastname);
            if (!empty($fullname)) {
                $recipients[$lastItem][1] = $fullname . ' (' . $phpUser->username . ')';
            } else {
                $recipients[$lastItem][1] = $phpUser->username;
            }
        }

        return $recipients;
    }

    /**
     * Returns the fields part of the Notification body (for internal variable _bodyFields) using a custom criterion for
     * the Calendar module.
     *
     * @return array
     */
    public function setBodyFields()
    {
        $bodyFields   = array();
        $bodyFields[] = array('label' => Phprojekt::getInstance()->translate('Title'),
                              'value' => $this->_model->title);
        $bodyFields[] = array('label' => Phprojekt::getInstance()->translate('Place'),
                              'value' => $this->_model->place);
        $bodyFields[] = array('label' => Phprojekt::getInstance()->translate('Notes'),
                              'value' => $this->_model->notes);
        $bodyFields[] = array('label' => Phprojekt::getInstance()->translate('Start date'),
                              'value' => $this->_model->translateDate($this->_model->startDateNotif));
        $bodyFields[] = array('label' => Phprojekt::getInstance()->translate('Start time'),
                              'value' => substr($this->_model->startTime, 0, 5));
        $bodyFields[] = array('label' => Phprojekt::getInstance()->translate('End date'),
                              'value' => $this->_model->translateDate($this->_model->endDateNotif));
        $bodyFields[] = array('label' => Phprojekt::getInstance()->translate('End time'),
                              'value' => substr($this->_model->endTime, 0, 5));

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
        $bodyFields[] = array('label' => Phprojekt::getInstance()->translate('Participants'),
                              'value' => $participantsValue);

        if ($this->_model->rrule !== null) {
            $bodyFields = array_merge($this->_bodyFields,
                $this->_model->_getRruleDescriptive($this->_model->rrule));
        }

        return $bodyFields;
    }

    /**
     * Goes into the contents of the 'changes' part of the Notification body (internal variable _lastHistory) and
     * parses, translates and orders it using a custom criterion for the Calendar Notification. Then returns the final
     * array.
     *
     * @return array
     */
    public function setBodyChanges()
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
                    $bodyChanges[$i]['field'] = $field['label'];
                }
            }

            // Recurrence
            if (strtolower($bodyChanges[$i]['field']) == 'rrule') {
                $oldRruleEmpty = false;
                $newRruleEmpty = false;
                if ($bodyChanges[$i]['oldValue'] !== null) {
                    $oldRrule = $this->_model->_getRruleDescriptive($bodyChanges[$i]['oldValue']);
                } else {
                    $oldRruleEmpty = true;
                }
                if ($bodyChanges[$i]['newValue'] !== null) {
                    $newRrule = $this->_model->_getRruleDescriptive($bodyChanges[$i]['newValue']);
                } else {
                    $newRruleEmpty = true;
                }

                // FIELDS: Repeats, Interval and Until
                for ($i=0; $i < 3; $i++) {
                    if (!$oldRruleEmpty) {
                        $fieldName = Phprojekt::getInstance()->translate('Recurrence') . " - " . $oldRrule[$i]['label'];
                    } else {
                        $fieldName = Phprojekt::getInstance()->translate('Recurrence') . " - " . $newRrule[$i]['label'];
                    }
                    if (!$oldRruleEmpty) {
                        $fieldOldValue = $oldRrule[$i]['value'];
                    } else {
                        $fieldOldValue = "";
                    }
                    if (!$newRruleEmpty) {
                        $fieldNewValue = $newRrule[$i]['value'];
                    } else {
                        $fieldNewValue = "";
                    }
                    if ($fieldOldValue != $fieldNewValue) {
                        $bodyChanges[] = array('field'    => $fieldName,
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
                        $fieldName = Phprojekt::getInstance()->translate('Recurrence')
                            . " - " . $oldRrule[3]['label'];
                        $fieldOldValue = $oldRrule[3]['value'];
                    } else {
                        $fieldOldValue = "";
                    }
                    if (!$newRruleEmpty) {
                        $fieldName = Phprojekt::getInstance()->translate('Recurrence')
                            . " - " . $newRrule[3]['label'];
                        $fieldNewValue = $newRrule[3]['value'];
                    } else {
                        $fieldOldValue = "";
                    }

                    if ($fieldOldValue != $fieldNewValue) {
                        $bodyChanges[] = array('field'    => $fieldName,
                                               'oldValue' => $fieldOldValue,
                                               'newValue' => $fieldNewValue);
                    }
                }
            } else if ($bodyChanges[$i]['field'] == 'startDate') {
                $bodyChanges[$i]['oldValue'] = $this->_model->translateDate($bodyChanges[$i]['oldValue']);
                $bodyChanges[$i]['newValue'] = $this->_model->translateDate($bodyChanges[$i]['newValue']);
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
}
