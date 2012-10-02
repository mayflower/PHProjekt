<?php
/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details
 *
 * @copyright  Copyright (c) 2011 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * Calendar2 Notification class.
 *
 * This class extends PHProjekt's default notification class because of some special requirements for calendar2.
 */
class Calendar2_Models_Notification extends Phprojekt_Notification
{
    /**
     * Returns the subject of the notification
     * according to the current module, stored in $this->_model.
     *
     * @return string Subject.
     */
    public function getSubject()
    {
        $subject  = '[' . Phprojekt::getInstance()->translate('Calendar') . " #{$this->_model->id}] ";
        $subject .= trim($this->_model->summary);
        return $subject;
    }

    /**
     * Returns some params for the body of the notification
     * according to the current module and the event we are informing to the users.
     *
     * @return array Array with options.
     */
    public function getBodyParams()
    {
        $bodyParams = array();

        // Action
        switch ($this->_lastHistory[0]['action']) {
            case self::LAST_ACTION_ADD:
                $bodyParams['actionLabel'] = "created";
                break;
            case self::LAST_ACTION_EDIT:
            default:
                $bodyParams['actionLabel'] = "modified";
                break;
        }

        // Module
        $bodyParams['moduleTable'] = 'Calendar';

        // Url
        $params = array('moduleName' => 'Calendar2', 'id' => $this->_model->id);
        if ($this->_model->recurrenceId) {
            $params['occurrence'] = $this->_model->recurrenceId;
        }
        $bodyParams['url'] = 'index.php#' . http_build_query($params);

        return $bodyParams;
    }

    /**
     * Returns the fields part of the Notification body.
     *
     * @param Zend_Locale $lang Locale for use in translations.
     *
     * @return array Array with 'field', 'label' and 'value'.
     */
    public function getBodyFields($lang)
    {
        $fields = parent::getBodyFields($lang);

        foreach ($fields as $k => $f) {
            if ($f['field'] == 'occurrence' || $f['field'] == 'rrule' || $f['field'] == 'confirmationStatuses') {
                unset($fields[$k]);
            }

            if ($f['field'] == 'participants') {
                $fields[$k]['value'] = $this->_model->getParticipantsNames();
            }
        }
        $fields[] = array(
            'field' => 'recurrence',
            'label' => Phprojekt::getInstance()->translate('Recurrence'),
            'value' => $this->_model->recurrence
        );
        return $fields;
    }
}
