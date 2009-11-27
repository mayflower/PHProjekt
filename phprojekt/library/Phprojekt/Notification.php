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
    const LAST_ACTION_DELETE  = 'delete';
    const LAST_ACTION_LOGOUT  = 'logout';
    const LAST_ACTION_LOGIN   = 'login';
    const LAST_ACTION_REMIND  = 'remind';
    const TRANSPORT_MAIL_TEXT = 0;
    const TRANSPORT_MAIL_HTML = 1;

    protected $_lastHistory;
    protected $_frontendMessage;
    protected $_controllProcess = null;
    protected $_model           = null;
    protected $_validUntil      = null;
    protected $_validFrom       = null;

    /**
     * Initialize new object
     */
    public function __construct()
    {
        $this->_frontendMessage = new Phprojekt_Notification_FrontendMessage();
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

    /**
     * Sends an notification through the indicated transport,
     * the contents are made according to a specific module and a specific event.
     * Previous to this function it has to be called setModel
     * so that the internal variable _model has the model where to obtain the data from.
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
        $recipients = $this->getTo();
        if (!empty($recipients)) {
            $adapter->setTo($recipients);
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
     * Gets only the recipients with at least a 'read' right.
     * If no recipient is given, returns an empty array.
     * Exclude the current user
     *
     * @return array
     */
    public function getTo()
    {
        $userIds    = $this->_model->getRights();
        $recipients = array();

        if (is_array($userIds) && !empty($userIds)) {
            foreach ($userIds as $right) {
                if (($right['userId'] == Phprojekt_Auth::getUserId()) || $right['none']) {
                    continue;
                }
                $recipients[] = $right['userId'];
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

    /**
     * Calls the saveFrontendMessage of the FrontendMessage class
     * to save a message to the corresponding table.
     *
     * @return boolean
     */
    public function saveFrontendMessage()
    {
        $bodyChanges = array();
        $recipients  = $this->getRecipients();

        if (null !== $this->_model) {
            // This is only possible if $this->model is not null
            $history            = Phprojekt_Loader::getLibraryClass('Phprojekt_History');
            $this->_lastHistory = $history->getLastHistoryData($this->_model);

            $bodyChanges = (false === empty($this->_controllProcess)) ? array() : $this->getBodyChanges();

            // If no recipients were added, return immediately
            // allthough a check in FrontendMessage saveFrontendMessage is performed too,
            // but this is earlier and avoids all the set.
            if (true === empty($recipients)) {
                return;
            }
        }

        $process     = $this->getProcess();
        $description = $this->getDescription();

        $this->_frontendMessage->setCustomModuleId($this->getModuleId());
        $this->_frontendMessage->setCustomProjectId($this->getProjectId());
        $this->_frontendMessage->setCustomItemId($this->getItemId());
        $this->_frontendMessage->setCustomValidUntil($this->getValidUntil());
        $this->_frontendMessage->setCustomValidFrom($this->getValidFrom());
        $this->_frontendMessage->setCustomRecipients($recipients);
        $this->_frontendMessage->setCustomProcess($process);
        $this->_frontendMessage->setCustomDescription($description);
        $this->_frontendMessage->setCustomDetails($bodyChanges);

        $return = $this->_frontendMessage->saveFrontendMessage();

        return $return;
    }

    /**
     * Delivers the description to a message.
     *
     * @return string
     */
    public function getDescription()
    {
        // Map process
        $process     = $this->getProcess();
        $project     = Phprojekt_Loader::getModel('Project', 'Project');
        $projectName = $project->find($this->getProjectId())->title;

        switch ($process) {
            case (self::LAST_ACTION_ADD):
                $description = ' has created the new ' . $this->_model->getModelName() . ' entry "'
                    . $this->_model->{$this->_model->searchFirstDisplayField} . '" in Project "' . $projectName .'"';
                break;
            case (self::LAST_ACTION_DELETE):
                $description = ' has delete the ' . $this->_model->getModelName() . ' entry "'
                    . $this->_model->{$this->_model->searchFirstDisplayField} . '" in Project "' . $projectName . '"';
                break;
            case (self::LAST_ACTION_EDIT):
                $description = ' has edit the existing ' . $this->_model->getModelName() . ' entry "'
                    . $this->_model->{$this->_model->searchFirstDisplayField} . '" in Project "' . $projectName . '"';
                break;
            case (self::LAST_ACTION_LOGIN):
                $description = ' has logged in.';
                break;
            case (self::LAST_ACTION_LOGOUT):
                $description = ' has logged out.';
                break;
            case (self::LAST_ACTION_REMIND):
                $description = ' your event "' . $this->_model->{$this->_model->searchFirstDisplayField}
                    . '" starts in ' . Phprojekt::getInstance()->getConfig()->remindBefore . ' minutes.';
                break;
            default:
                $description = ' has executed a not defined process.';
                break;
        }

        return $description;
    }

    /**
     * Setter for the controll process,
     *
     * @param string $customProcess
     *
     * @return void
     */
    public function setControllProcess($customProcess)
    {
        $this->_controllProcess = $customProcess;
    }

    /**
     * Getting the standard process like 'add', 'delete' or 'update'.
     *
     * @return string
     */
    public function getProcess()
    {
        if (false === empty($this->_controllProcess)) {
            return $this->_controllProcess;
        }

        if ($this->_lastHistory[0]['action'] === self::LAST_ACTION_ADD) {
            return self::LAST_ACTION_ADD;
        } else {
            return self::LAST_ACTION_EDIT;
        }
    }

    /**
     * Gets the project id.
     * Returns the default value for the root project if no model was instanciated.
     *
     * @return int
     */
    public function getProjectId()
    {
        $projectId = IndexController::INVISIBLE_ROOT;
        if ($this->_model instanceof Phprojekt_Tree_Node_Database) {
            $projectId = $this->_model->id;
        } elseif ($this->_model instanceof Phprojekt_Model_Interface) {
            $projectId = $this->_model->projectId;
        }

        return $projectId;
    }

    /**
     * Gets the module id.
     *
     * @return int
     */
    public function getModuleId()
    {
        $moduleId = 0;

        if (($this->_model instanceof Phprojekt_Tree_Node_Database) ||
            ($this->_model instanceof Phprojekt_Model_Interface)) {
            $moduleName = $this->_model->getModelName();
            $moduleId   = Phprojekt_Module::getId($moduleName);
        }

        return $moduleId;
    }

    /**
     * Gets the item id.
     *
     * @return int
     */
    public function getItemId()
    {
        $itemId = 0;

        if (($this->_model instanceof Phprojekt_Tree_Node_Database) ||
            ($this->_model instanceof Phprojekt_Model_Interface)) {
            $itemId = $this->_model->id;
        }

        return $itemId;
    }

    /**
     * Gets the datetime from which the frontend message is valid,
     * typically the moment where the message will be created.
     *
     * @return string
     */
    public function getValidFrom()
    {
        if (false === empty($this->_validFrom)) {
            return $this->_validFrom;
        }

        return date("Y-m-d H:i:s", time());
    }

    /**
     * Gets the datetime until a frontend message is valid.
     * This is from special interest in the calendar module,
     * where the user can add meetings and other time relevant events.
     * This time is needed to calculate the 'alarm' before an event starts.
     *
     * @return string
     */
    public function getValidUntil()
    {
        if (false === empty($this->_validUntil)) {
            return $this->_validUntil;
        }

        $validPeriod = Phprojekt::getInstance()->getConfig()->validPeriod;
        $validUntil  = time() + (60 * $validPeriod);

        return date("Y-m-d H:i:s", $validUntil);
    }

    /**
     * Gets only the recipients with at least a 'read' right
     * and checks if the user has disabled/enabled the settings for saving the messages.
     * If no recipient is given, returns an empty array.
     *
     * @return array
     */
    public function getRecipients()
    {
        $recipients = array();

        if (($this->_model instanceof Phprojekt_Tree_Node_Database) ||
            ($this->_model instanceof Phprojekt_Model_Interface)) {

            $userIds = $this->_model->getRights();

            if (is_array($userIds) && !empty($userIds)) {
                foreach ($userIds as $right) {
                    if (($right['userId'] == Phprojekt_Auth::getUserId()) || true === $right['none']) {
                        //continue;
                    }

                    $recipients[] = $right['userId'];
                }
            }
        } else {
            $user    = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
            $userIds = $user->fetchAll();

            foreach ($userIds as $user) {
                if ($user->id == Phprojekt_Auth::getUserId()) {
                    continue;
                }
                $recipients[] = $user->id;
            }
        }

        // Last but not least filter the recipients with their settings.
        $settingKey = $this->mapProcessToSettings();

        foreach ($recipients as $recipient) {
            $setting = $this->getSetting($settingKey, $recipient);
            // Since the checkboxes doesn´t have a propper default value,
            // only users who realy have disabled the setting will be removed from the recipients array.
            // Users with no saved setting will be stay on the recipients list too,
            // because a user has to uncheck the setting to disable the frontend message.
            if ($setting === 0) {
                if (false !== ($key = array_search($recipient,$recipients))){
                    unset($recipients[$key]);
                }
            }
        }

        return $recipients;
    }

    /**
     * Returns the key from the settings regarding to the given process.
     *
     *  @return string
     */
    public function mapProcessToSettings()
    {
        $settingKey = '';
        $process    = $this->getProcess();

        switch ($process) {
            case (self::LAST_ACTION_ADD):
            case (self::LAST_ACTION_DELETE):
            case (self::LAST_ACTION_EDIT):
                $settingKey = 'datarecords';
                break;
            case (self::LAST_ACTION_LOGIN):
            case (self::LAST_ACTION_LOGOUT):
                $settingKey = 'loginlogout';
                break;
            case (self::LAST_ACTION_REMIND):
                $settingKey = 'alerts';
                break;
            default:
                $settingKey = '';
                break;
        }

        return $settingKey;
    }

    /**
     * Returns the setting value from the notification tab.
     *
     * @return mixed Returns the setting value as integer or null if setting is not saved to the database yet.
     */
    public function getSetting($settingName, $userId)
    {
        $setting = Phprojekt_Loader::getLibraryClass('Phprojekt_Setting');
        $setting->setModule('Notification');

        $value = $setting->getSetting($settingName, $userId);
        $value = (true === is_null($value)) ? $value : (int) $value;

        return $value;
    }

    /**
     * Disables all types of frontend messages.
     *
     * @return void
     */
    public function disableFrontendMessages()
    {
        $defaultSettings = array(Core_Models_Notification_Setting::FIELD_LOGIN_LOGOUT  => 0,
                                 Core_Models_Notification_Setting::FIELD_DATARECORDS   => 0,
                                 Core_Models_Notification_Setting::FIELD_USERGENERATED => 0,
                                 Core_Models_Notification_Setting::FIELD_ALERTS        => 0);

        $setting = Phprojekt_Loader::getLibraryClass('Phprojekt_Setting');
        $setting->setModule('Notification');
        $setting->setSettings($defaultSettings);
    }
}
