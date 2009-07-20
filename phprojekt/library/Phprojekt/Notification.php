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
class Phprojekt_Notification extends Phprojekt_Mail
{
    const LAST_ACTION_ADD   = 'add';
    const LAST_ACTION_EDIT  = 'edit';
    const LAST_ACTION_NONE  = 'none';

    protected $_tableName;
    protected $_lastHistory;
    protected $_customFrom;
    protected $_customTo = Array();
    protected $_customSubject;
    protected $_bodyMode;
    protected $_view;
    protected $_model;
    protected $_customBody;

    /**
     * Sends an email notification in HTML mode, with the contents according to a
     * specific module and a specific event.
     *
     * The sender, recipients, subject and body are generated dynamically depending
     * on the module received in the $model parameter.
     * To send a notification in Text mode, use function sendNotificationText()
     *
     * @param Phprojekt_Model_Interface    $model E.g.: A object of the type
     *                                            Todo_Models_Todo
     *
     * @uses    $mailNotif = new Phprojekt_Mail_Notification()
     *          $mailNotif->sendNotificationHtml($model);
     *
     * @see _sendNotification()
     *
     * @return void
     */
    public function sendNotificationHtml(Phprojekt_Model_Interface $model = null)
    {
        if ($model != null) {
            $this->_model = $model;
        }
        $this->_bodyMode = self::MODE_HTML;
        $this->_sendNotification();
    }

    /**
     * Sends an email notification in Text mode, with the contents according to a
     * specific module and a specific event.
     *
     * The sender, recipients, subject and body are generated dynamically depending
     * on the module received in the $model parameter.
     * To send a notification in Html mode, use function sendNotificationHtml()
     *
     * @param Phprojekt_Model_Interface $model E.g.: A object of the type Todo_Models_Todo
     *
     * @uses $mailNotif = new Phprojekt_Mail_Notification();
     *       $mailNotif->sendNotificationText($model);
     *
     * @see _sendNotification()
     *
     * @return void
     */
    public function sendNotificationText(Phprojekt_Model_Interface $model = null)
    {
        if ($model != null) {
            $this->_model = $model;
        }
        $this->_bodyMode = self::MODE_TEXT;
        $this->_sendNotification();
    }

    /**
     * Sends an email notification in Html/Text mode, with
     * the contents according to a specific module and a specific event.
     *
     * The function is called by both sendNotificationHtml() and sendNotificationText()
     * It calls several functions to set the sender, the recipients, the subject
     * and the body. Then calls _mailNotifSend() to send the email.
     *
     * @see _sendNotification()
     *
     * @return void
     */
    private function _sendNotification()
    {
        // Sometimes, the user may try to modify an existing item and presses Save without having modified even one
        // field. In that case, no mail should be sent.
        $history            = Phprojekt_Loader::getLibraryClass('Phprojekt_History');
        $this->_lastHistory = $history->getLastHistoryData($this->_model);
        if (empty($this->_lastHistory)) {
            return;
        }
        $this->_tableName     = trim($this->_model->getModelName());
        $this->_customFrom    = $this->setFrom();
        $this->_customTo      = $this->setTo();
        $this->_customSubject = $this->setCustomSubject();
        $this->_customBody    = $this->setCustomBody();
        $this->_mailNotifSend();
    }

    /**
     * Fills and return an array with the name and email of the logued user.
     *
     * @see Phprojekt_User_User()
     *
     * @return array
     */
    public function setFrom()
    {
        $phpUser = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
        $phpUser->find(Phprojekt_Auth::getUserId());

        $from = array();
        // Email assignment
        $from[0] = $phpUser->getSetting('email');

        // Name assignment
        $fullname = trim($phpUser->firstname . ' ' . $phpUser->lastname);
        if (!empty($fullname)) {
            $from[1] = $fullname . ' (' . $phpUser->username . ')';
        } else {
            $from[1] = $phpUser->username;
        }

        return $from;
    }

    /**
     * Fills and returns a variable with recipients obtained from $this->_model through class Phprojekt_Item_Rights()
     *
     * @return array
     */
    public function setTo()
    {
        $phpUser = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
        $setting = Phprojekt_Loader::getModel('Setting', 'Setting');

        // The recipients are all the users with at least 'read' access to the item
        $rights     = $this->_model->getRights();
        $recipientsIds = Array();
        foreach ($rights as $userId => $userRights) {
            if ($userRights['read']) {
                $recipientsIds[] = $userId;
            }
        }

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
     * Returns the subject of the email according to the current module, stored in $this->_model.
     *
     * @return string
     */
    public function setCustomSubject()
    {
        $mailTitle = "";
        if (isset($this->_model->searchFirstDisplayField)) {
            $mailTitle = $this->_model->{$this->_model->searchFirstDisplayField};
        }
        $subject = trim('[' . $this->_tableName . ' #' . $this->_model->id . '] ' . $mailTitle);
        return $subject;
    }

    /**
     * Returns the body of the email according to the current module and the event we are informing to the users.
     * It obtains all the data dinamically from the $this->_model object.
     *
     * @return string
     */
    public function setCustomBody()
    {
        $this->_view             = Phprojekt::getInstance()->getView();
        $action                  = $this->_lastHistory[0]['action'];
        $this->_view->mainFields = $this->setBodyFields();

        // Is it an ADD or EDIT action?
        switch ($action) {
            case self::LAST_ACTION_ADD:
                $actionLabel          = "created";
                $this->_view->changes = "";
                break;
            case self::LAST_ACTION_EDIT:
            default:
                $action               = self::LAST_ACTION_EDIT;
                $this->_view->changes = $this->setBodyChanges();
                $actionLabel          = "modified";
        }

        $this->_view->title = Phprojekt::getInstance()->translate('A ')
            . $this->_tableName
            . Phprojekt::getInstance()->translate(' item has been ')
            . Phprojekt::getInstance()->translate($actionLabel);


        $this->_view->url       = $this->_setUrl();
        $this->_view->translate = Phprojekt::getInstance()->getTranslate();

        if ($this->_bodyMode == self::MODE_TEXT) {
            $this->_view->endOfLine = $this->getEndOfLine();
        }

        Phprojekt_Loader::loadViewScript();
        return $this->_view->render('mail' . $this->_bodyMode . '.phtml');
    }

    /**
     * Returns the fields part of the Notification body (for internal variable _bodyFields)
     *
     * @return array
     */
    public function setBodyFields()
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
     * Goes into the contents of the 'changes' part of the Notification body (internal variable _lastHistory) and checks
     * for contents that have to be translated, then returns the final array
     *
     * @return array
     */
    public function setBodyChanges()
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

    /**
     * Sends an email notification using the inherited method send().
     *
     * The function sends an email to the users listed in the $_customTo array.
     * There are many private properties that must have been defined previously:
     * _customFrom, _customTo, _customSubject, _bodyMode and _customBody.
     *
     * @return void
     */
    private function _mailNotifSend()
    {
        // Has the name been set?
        if (sizeof($this->_customFrom) == 2) {
            // Address, Name
            $this->setFrom($this->_customFrom[0], $this->_customFrom[1]);
        } else {
            // Address
            $this->setFrom($this->_customFrom[0]);
        }

        // Iterates on the array to fill every recipient
        foreach ($this->_customTo as $recipient) {
            // Has the name been set?
            if (sizeof($recipient) == 2) {
                // Address, Name
                $this->addTo($recipient[0], $recipient[1]);
            } else {
                // Address
                $this->addTo($recipient[0]);
            }
        }

        $this->setSubject($this->_customSubject);

        switch ($this->_bodyMode) {
            case self::MODE_TEXT:
                $this->setBodyText($this->_customBody);
                break;
            case self::MODE_HTML:
            default:
                $this->setBodyHtml($this->_customBody);
                break;
        }

        // Creates the Zend_Mail_Transport_Smtp object
        $smtpTransport = $this->setTransport();
        try {
            $this->send($smtpTransport);
        } catch(Exception $e){
            throw new Phprojekt_PublishedException('SMTP error: ' . $e->getMessage());
        }
    }

    /**
     * Sets the url link to access to the created/modified item.
     *
     * @return string The url
     */
    protected function _setUrl()
    {
        $url      = Phprojekt::getInstance()->getConfig()->webpath . "index.php#" . $this->_model->getModelName();
        $saveType = Phprojekt_Module::getSaveType(Phprojekt_Module::getId($this->_model->getModelName()));
        if ($saveType == 0) {
            $url .= "," . $this->_model->projectId;
        }
        $url .= ",id," . $this->_model->id;

        return $url;
    }

    /**
     * Stores in the private variable $_model the received model
     *
     * @return void
     */
    public function setModel(Phprojekt_Model_Interface $model)
    {
        $this->_model = $model;
    }
}
