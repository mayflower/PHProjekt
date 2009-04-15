<?php
/**
 * Mail notification class for PHProjekt 6.0
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
 * Mail notification class for PHProjekt 6.0
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
class Phprojekt_Mail_Notification extends Zend_Mail
{
    const MODE_HTML         = 'Html';
    const MODE_TEXT         = 'Text';
    const MAIL_LINEEND_RN   = 0; //External use (configuration.ini)
    const MAIL_LINEEND_N    = 1; //External use (configuration.ini)
    const LAST_ACTION_ADD   = 'add';
    const LAST_ACTION_EDIT  = 'edit';
    const LAST_ACTION_NONE  = 'none';

    private $_tableName;
    private $_customFrom;
    private $_customTo = Array();
    private $_customSubject;
    private $_bodyMode;
    private $_view;
    private $_model;
    private $_customBody;
    private $_changes;

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
    public function sendNotificationHtml(Phprojekt_Model_Interface $model)
    {
        $this->_model    = $model;
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
    public function sendNotificationText(Phprojekt_Model_Interface $model)
    {
        $this->_model    = $model;
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
        $history = Phprojekt_Loader::getLibraryClass('Phprojekt_History');
        $this->_changes = $history->getLastHistoryData($this->_model);
        if (empty($this->_changes)) {
            return;
        }
        $this->_tableName = trim($this->_model->getModelName());
        if (!isset($this->_customFrom)) {
            $this->_setFromUserLogued();
        }
        $this->_setTo();
        $this->_setCustomSubject();
        $this->_setCustomBody();
        $this->_mailNotifSend();
    }

    /**
     * Sets the sender name and address. If not called, then, when sending the email
     * through sendNotificationHtml/Text(), it is automatically called _setFromUserLogued()
     * that sets the sender to the logued user.
     *
     * @param array $from An array with two positions: the first value contains
     *                    the email and the second one, the name (optional).
     *
     * @uses $mailNotif = new Phprojekt_Mail_Notification();
     *       $mailNotif->setCustomFrom(array("mariano.lapenna@mayflower.de",
     *                                       "Mariano"));
     *       $mailNotif->sendNotificationHtml($model);
     *
     * @return void
     */
    public function setCustomFrom(array $from)
    {
        //Email
        $this->_customFrom[0] = $from[0];

        // Has the name been set?
        if (sizeof($from) == 2) {
            //Name
            $this->_customFrom[1] = $from[1];
        }
    }

    /**
     * Fills the _customFrom property with the name and email of the logued user.
     * This function is called by default if an email is tryed to be sent with
     * not sender data specified via setCustomFrom.
     *
     * @see Phprojekt_User_User()
     *
     * @return void
     */
    private function _setFromUserLogued()
    {
        $phpUser = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
        $phpUser->find(Phprojekt_Auth::getUserId());

        // Email assignment
        $this->_customFrom[0] = $phpUser->getSetting('email');

        // Name assignment
        $fullname = trim($phpUser->firstname . ' ' . $phpUser->lastname);
        if (!empty($fullname)) {
            $this->_customFrom[1] = $fullname . ' (' . $phpUser->username . ')';
        } else {
            $this->_customFrom[1] = $phpUser->username;
        }
    }

    /**
     * Fills the variable $_customTo with the recipients obtained from $this->_model
     * through class Phprojekt_Item_Rights()
     *
     * @return void
     */
    private function _setTo()
    {
        $phpUser = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
        $setting = Phprojekt_Loader::getModel('Setting', 'Setting');

        // The model has an exception to define the recipients of the notification?
        if (!method_exists($this->_model, 'getNotificationRecipients')) {
            // No - The recipients will be all the users with at least 'read' access to the item
            $rights     = $this->_model->getRights();
            $recipients = Array();
            foreach ($rights as $userId => $userRights) {
                if ($userRights['read']) {
                    $recipients[] = $userId;
                }
            }
        } else {
            // Yes - The model defines its own recipients
            $recipients = split(',', $this->_model->getNotificationRecipients());
        }

        // All the recipients IDs are inside $recipients, now add emails and descriptive names to _customTo
        foreach ($recipients as $recipient) {
            $email = $setting->getSetting('email', (int) $recipient);

            if ((int) $recipient) {
                $phpUser->find($recipient);
            } else {
                $phpUser->find(Phprojekt_Auth::getUserId());
            }

            $this->_customTo[]             = array();
            $lastItem                      = count($this->_customTo) - 1;
            $this->_customTo[$lastItem][0] = $email;

            $fullname = trim($phpUser->firstname . ' ' . $phpUser->lastname);
            if (!empty($fullname)) {
                $this->_customTo[$lastItem][1] = $fullname . ' (' . $phpUser->username . ')';
            } else {
                $this->_customTo[$lastItem][1] = $phpUser->username;
            }
        }
    }

    /**
     * Sets the subject of the email according to the current module, stored in $this->_model.
     *
     * @return void
     */
    private function _setCustomSubject()
    {
        $mailTitle = "";
        if (isset($this->_model->searchFirstDisplayField)) {
            $mailTitle = $this->_model->{$this->_model->searchFirstDisplayField};
        }
        $this->_customSubject = trim('[' . $this->_tableName . ' #' . $this->_model->id . '] ' . $mailTitle);
    }

    /**
     * Sets the body of the email according to the current module and the event
     * we are informing to the users.
     * It obtains all the data dinamically from the $this->_model object.
     *
     * @return void
     */
    private function _setCustomBody()
    {
        $this->_view     = Phprojekt::getInstance()->getView();
        $order           = Phprojekt_ModelInformation_Default::ORDERING_FORM;
        $fieldDefinition = $this->_model->getInformation()->getFieldDefinition($order);
        $action          = $this->_changes[0]['action'];

        // The model has an exception to define the body 'Current data' contents?
        if (!method_exists($this->_model, 'getNotificationBodyData')) {
            // No
            foreach ($fieldDefinition as $key => $field) {
                $value        = Phprojekt_Converter_Text::convert($this->_model, $field);
                $fieldsView[] = array('label' => $field['label'],
                                      'value' => $value);
            }
        } else {
            // Yes
            $fieldsView = $this->_model->getNotificationBodyData();
        }
        $this->_view->mainFields = $fieldsView;

        if ($action == self::LAST_ACTION_EDIT) {
            // The model has an exception to define the body 'Changes done' contents?
            if (!method_exists($this->_model, 'getNotificationBodyChanges')) {
                // No

                // The following algorithm loops inside $this->_changes and does the following:
                // * Translates the name of the field
                // * Searches Integer values that should be converted into Strings and converts them

                // Iterate in every change done
                for ($i = 0; $i < count($this->_changes); $i++) {
                    foreach ($fieldDefinition as $field) {
                        // Find the field definition for the field that has been modified
                        if ($field['key'] == $this->_changes[$i]['field']) {

                            $this->_changes[$i]['field'] = $field['label'];

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
                                    if ($range['id'] == $this->_changes[$i]['oldValue']) {
                                        $this->_changes[$i]['oldValue'] = trim($range['name']);
                                    }
                                    // Try to replace newValue Integer with the String
                                    if ($range['id'] == $this->_changes[$i]['newValue']) {
                                        $this->_changes[$i]['newValue'] = trim($range['name']);
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                // Yes
                $this->_changes = $this->_model->getNotificationBodyChanges($this->_changes);
            }
        }

        // Is it an ADD or EDIT action?
        switch ($action) {
            case self::LAST_ACTION_ADD:
                $actionLabel          = "created";
                $this->_view->changes = "";
                break;
            case self::LAST_ACTION_EDIT:
            default:
                $action               = self::LAST_ACTION_EDIT;
                $this->_view->changes = $this->_changes;
                $actionLabel          = "modified";
        }

        $this->_view->title = Phprojekt::getInstance()->translate('A ')
            . $this->_tableName
            . Phprojekt::getInstance()->translate(' item has been ')
            . Phprojekt::getInstance()->translate($actionLabel);

        $this->_view->translate = Phprojekt::getInstance()->getTranslate();

        if ($this->_bodyMode == self::MODE_TEXT) {
            switch (Phprojekt::getInstance()->getConfig()->mailEndOfLine) {
                case self::MAIL_LINEEND_N:
                    $this->_view->endOfLine = "\n";
                    break;
                case self::MAIL_LINEEND_RN:
                default:
                    $this->_view->endOfLine = "\r\n";
                    break;
            }
        }

        $this->_customBody = $this->_view->render('mail' . $this->_bodyMode . '.phtml');
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
        $smtpTransport = $this->_setTransport();
        $this->send($smtpTransport);
    }

    /**
     * Sets the SMTP server. The data is obtained from the configuration.ini file.
     *
     * @return Zend_Mail_Transport_Smtp object
     */
    private function _setTransport()
    {
        $smtpServer   = Phprojekt::getInstance()->getConfig()->smtpServer;
        $smtpUser     = Phprojekt::getInstance()->getConfig()->smtpUser;
        $smtpPassword = Phprojekt::getInstance()->getConfig()->smtpPassword;

        if (empty($smtpServer)) {
            $smtpServer = 'localhost';
        }

        if (empty($smtpUser)) {
            $smtpTransport = new Zend_Mail_Transport_Smtp($smtpServer);
        } else {
            $smtpTransport = new Zend_Mail_Transport_Smtp($smtpServer, array('auth'     => 'login',
                                                                             'username' => $smtpUser,
                                                                             'password' => $smtpPassword));
        }

        return $smtpTransport;
    }
}
