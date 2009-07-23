<?php
/**
 * Notification class for PHProjekt 6.0
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
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Notification class for PHProjekt 6.0
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 */
class Phprojekt_Notification
{
    const LAST_ACTION_ADD     = 'add';
    const LAST_ACTION_EDIT    = 'edit';
    const TRANSPORT_MAIL_TEXT = 0;
    const TRANSPORT_MAIL_HTML = 1;

    protected $_lastHistory;

    /**
     * Stores in the private variable $_model the received model
     *
     * @return void
     */
    public function setModel(Phprojekt_Model_Interface $model)
    {
        $this->_model = $model;
    }

    /**
     * Sends an notification through the indicated transport, the contents are made according to a specific module and a
     * specific event.
     * Previous to this function it has to be called setModel so that the internal variable _model has the model where
     * to obtain the data from.
     * Depending on the indicated transport the notification will be sent currently via text or html email.
     *
     * @return void
     */
    public function send($transport)
    {
        // Sometimes, the user may try to modify an existing item and presses Save without having modified even one
        // field. In that case, no mail should be sent.
        $history            = Phprojekt_Loader::getLibraryClass('Phprojekt_History');
        $this->_lastHistory = $history->getLastHistoryData($this->_model);
        if (empty($this->_lastHistory)) {
            return;
        }

        $params = array();
        switch ($transport) {
            case self::TRANSPORT_MAIL_TEXT:
            case self::TRANSPORT_MAIL_HTML:
            default:
                $adapterName = "Phprojekt_Notification_Mail";
                $showSubject = true;
                $params[Phprojekt_Notification_Mail::PARAMS_CHARSET] = "UTF-8";
                if ($transport == self::TRANSPORT_MAIL_TEXT) {
                    $params[Phprojekt_Notification_Mail::PARAMS_BODYMODE] = Phprojekt_Notification_Mail::MODE_TEXT;
                } else {
                    $params[Phprojekt_Notification_Mail::PARAMS_BODYMODE] = Phprojekt_Notification_Mail::MODE_HTML;
                }
                break;
        }
        $adapter = new $adapterName($params);
        $adapter->setCustomFrom($this->getFrom());
        $adapter->setTo($this->getTo());
        if ($showSubject) {
            $adapter->setCustomSubject($this->getSubject());
        }
        if ($this->_lastHistory[0]['action'] == self::LAST_ACTION_EDIT) {
            $changes = $this->getBodyChanges();
            $adapter->setCustomBody($this->getBodyParams(), $this->getBodyFields(), $changes);
        } else {
            $adapter->setCustomBody($this->getBodyParams(), $this->getBodyFields());
        }
        $adapter->sendNotification();
    }

    /**
     * Return the id of the sender, that's the logged user
     *
     * @return int
     */
    public function getFrom()
    {
        return Phprojekt_Auth::getUserId();
    }

    /**
     * Returns an array with recipients obtained from $this->_model through class Phprojekt_Item_Rights()
     *
     * @return array
     */
    public function getTo()
    {
        // The recipients are all the users with at least 'read' access to the item
        $rights     = $this->_model->getRights();
        $recipients = Array();
        foreach ($rights as $userId => $userRights) {
            if ($userRights['read']) {
                $recipients[] = $userId;
            }
        }

        return $recipients;
    }

    /**
     * Returns the subject of the notification according to the current module, stored in $this->_model.
     *
     * @return string
     */
    public function getSubject()
    {
        $mailTitle = "";
        if (isset($this->_model->searchFirstDisplayField)) {
            $mailTitle = $this->_model->{$this->_model->searchFirstDisplayField};
        }
        $subject = trim('[' . $this->_model->getModelName() . ' #' . $this->_model->id . '] ' . $mailTitle);

        return $subject;
    }

    /**
     * Returns some params for the body of the notification according to the current module and the event we are
     * informing to the users.
     *
     * @return array
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
        }

        // Module
        $bodyParams['moduleTable'] = $this->_model->getModelName();

        // Url
        $url      = Phprojekt::getInstance()->getConfig()->webpath . "index.php#" . $this->_model->getModelName();
        $saveType = Phprojekt_Module::getSaveType(Phprojekt_Module::getId($this->_model->getModelName()));
        if ($saveType == 0) {
            $url .= "," . $this->_model->projectId;
        }
        $url              .= ",id," . $this->_model->id;
        $bodyParams['url'] = $url;

        return $bodyParams;
    }

    /**
     * Returns the fields part of the Notification body
     *
     * @return array
     */
    public function getBodyFields()
    {
        $order           = Phprojekt_ModelInformation_Default::ORDERING_FORM;
        $fieldDefinition = $this->_model->getInformation()->getFieldDefinition($order);
        $bodyFields      = array();

        foreach ($fieldDefinition as $key => $field) {
            $value        = Phprojekt_Converter_Text::convert($this->_model, $field);
            $bodyFields[] = array('label' => $field['label'],
                                  'value' => $value);
        }

        return $bodyFields;
    }

    /**
     * Goes into the contents of the 'changes' part of the Notification body (from internal variable _lastHistory) and
     * checks for contents that have to be translated, then returns the final array.
     *
     * @return array
     */
    public function getBodyChanges()
    {
        // The following algorithm loops inside $this->_lastHistory and prepares $bodyChanges while:
        // * Translates the name of the fields
        // * Searches Integer values that should be converted into Strings and converts them
        $order           = Phprojekt_ModelInformation_Default::ORDERING_FORM;
        $fieldDefinition = $this->_model->getInformation()->getFieldDefinition($order);
        $bodyChanges     = $this->_lastHistory;

        // Iterate in every change done
        for ($i = 0; $i < count($bodyChanges); $i++) {
            foreach ($fieldDefinition as $field) {
                // Find the field definition for the field that has been modified
                if ($field['key'] == $bodyChanges[$i]['field']) {

                    $bodyChanges[$i]['field'] = $field['label'];

                    // Is the field of a type that should be translated from an Id into a descriptive String?
                    $convertToString = false;
                    if ($field['type'] == 'selectbox') {
                        $convertToString = true;
                    } else if ($field['type'] == 'display' && is_array($field['range'])) {
                        foreach ($field['range'] as $range) {
                            if (is_array($range)) {
                                $convertToString = true;
                                break;
                            }
                        }
                    }

                    if ($convertToString) {
                        // Yes, so translate it into the appropriate meaning
                        foreach ($field['range'] as $range) {
                            // Try to replace oldValue Integer with the String
                            if ($range['id'] == $bodyChanges[$i]['oldValue']) {
                                $bodyChanges[$i]['oldValue'] = trim($range['name']);
                            }
                            // Try to replace newValue Integer with the String
                            if ($range['id'] == $bodyChanges[$i]['newValue']) {
                                $bodyChanges[$i]['newValue'] = trim($range['name']);
                            }
                        }
                    }
                }
            }
        }

        return $bodyChanges;
    }
}
