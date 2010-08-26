<?php
/**
 * Notification Mail class.
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
 * @package    Phprojekt
 * @subpackage Notification
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 */

/**
 * Notification Mail class.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Notification
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Mariano La Penna <mariano.lapenna@mayflower.de>
 */
class Phprojekt_Notification_Mail extends Phprojekt_Mail
{
    /**
     * HTML mode.
     */
    const MODE_HTML = 'Html';

    /**
     * Text mode.
     */
    const MODE_TEXT = 'Text';

    /**
     * Index in the option for Charset.
     */
    const PARAMS_CHARSET = 0;

    /**
     * Index in the option for body type.
     */
    const PARAMS_BODYMODE = 2;

    /**
     * Constructor.
     *
     * @param array $params Array with parameters.
     *
     * @return void
     */
    public function __construct($params)
    {
        parent::__construct($params[self::PARAMS_CHARSET]);

        $this->_bodyMode = $params[self::PARAMS_BODYMODE];
    }

    /**
     * Sets the name and email of the sender.
     *
     * @see Phprojekt_User_User()
     *
     * @param integer $from ID of the user who send the mail.
     *
     * @return void
     */
    public function setCustomFrom($from)
    {
        $phpUser = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
        $phpUser->find($from);

        $email = $phpUser->getSetting('email');

        $name = trim($phpUser->firstname . ' ' . $phpUser->lastname);
        if (!empty($name)) {
            $name .= ' (' . $phpUser->username . ')';
        } else {
            $name = $phpUser->username;
        }

        $this->setFrom($email, $name);
    }

    /**
     * Sets the recipients according to the received IDs.
     *
     * @param array $recipients Array with user IDs.
     *
     * @return void
     */
    public function setTo($recipients)
    {
        $phpUser = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
        $setting = Phprojekt_Loader::getLibraryClass('Phprojekt_Setting');

        foreach ($recipients as $recipient) {
            $email = $setting->getSetting('email', (int) $recipient);

            if (!empty($email)) {
                if ((int) $recipient) {
                    $phpUser->find($recipient);
                } else {
                    $phpUser->find(Phprojekt_Auth::getUserId());
                }

                $name = trim($phpUser->firstname . ' ' . $phpUser->lastname);
                if (!empty($name)) {
                    $name = $name . ' (' . $phpUser->username . ')';
                } else {
                    $name = $phpUser->username;
                }
                $this->addTo($email, $name);
            }
        }
    }

    /**
     * Sets the subject of the email according to the string received.
     *
     * @param string $subject The subject to use in the mail.
     *
     * @return void
     */
    public function setCustomSubject($subject)
    {
        $this->setSubject($subject);
    }

    /**
     * Sets the body of the email according to the data received from Notification class
     *
     * @param array       $params  Array with options.
     * @param array       $fields  Array with the fields of the model.
     * @param array       $changes Array with changes done in the model.
     * @param Zend_Locale $lang Locale for use in translations.
     *
     * @return void
     */
    public function setCustomBody($params, $fields, $changes, $lang)
    {
        $phproject        = Phprojekt::getInstance();
        $view             = $phproject->getView();
        $view->mainFields = $fields;

        if ($changes !== null) {
            $view->changes = $changes;
        }

        $view->title = $phproject->translate('A', $lang) . " "
            . '"' . $phproject->translate($params['moduleTable'], $lang) . '" '
            . $phproject->translate('item has been', $lang) . " "
            . $phproject->translate($params['actionLabel'], $lang);

        $view->currentData = $phproject->translate('Current data', $lang);
        $view->changesDone = $phproject->translate('Changes done', $lang);
        $view->field       = $phproject->translate('Field', $lang);
        $view->oldValue    = $phproject->translate('Old value', $lang);
        $view->copyright   = Phprojekt::COPYRIGHT;
        $view->url         = $params['url'];

        if ($this->_bodyMode == self::MODE_TEXT) {
            $view->endOfLine = $this->getEndOfLine();
        }

        Phprojekt_Loader::loadViewScript();

        $body = $view->render('mail' . $this->_bodyMode . '.phtml');

        switch ($this->_bodyMode) {
            case self::MODE_TEXT:
            default:
                $this->setBodyText($body);
                break;
            case self::MODE_HTML:
                $this->setBodyHtml($body);
                break;
        }
    }

    /**
     * Sends an email notification in Html/Text mode using the inherited method send(),
     * with the contents according to a specific module and a specific event.
     * Previous to calling this function, there has to be called all the set* methods.
     *
     * @return void
     */
    public function sendNotification()
    {
        // Creates the Zend_Mail_Transport_<Smtp/SendMail> object
        $smtpTransport = $this->setTransport();

        try {
            $this->send($smtpTransport);
        } catch(Exception $e) {
            throw new Phprojekt_PublishedException('SMTP error: ' . $e->getMessage());
        }
    }
}
