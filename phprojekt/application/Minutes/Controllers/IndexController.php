<?php
/**
 * Minutes Module Controller for PHProjekt 6.0
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
 * @author     Sven Rautenberg <sven.rautenberg@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Default Minutes Module Controller for PHProjekt 6.0
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Sven Rautenberg <sven.rautenberg@mayflower.de>
 */
class Minutes_IndexController extends IndexController
{
    /**
     * Constant for error message displayed when sending mail without
     * specifying any recipient addresses.
     * @see Minutes_IndexController::jsonSendMailAction()
     */
    const MISSING_MAIL_RECIPIENTS = 'No recipient addresses have been specified.';

    /**
     * Constant for error message displayed when trying to send mail
     * without being the owner of the requested Minutes entry.
     * @see Minutes_IndexController::jsonSendMailAction()
     */
    const USER_IS_NOT_OWNER = 'The currently logged-in user is not owner of the given minutes entry.';

    const MAIL_FAIL_TEXT    = 'The mail could not be sent.';
    const MAIL_SUCCESS_TEXT = 'The mail was sent successfully.';

    /**
     * Get a user list in JSON
     *
     * Produces a list of users that should be selectable in the frontend.
     * First implementation returns the list of users invited to the meeting.
     *
     * @return void
     */
    public function jsonListUserAction()
    {
        $id      = (int) $this->getRequest()->getParam('id');
        $minutes = $this->getModelObject();
        $minutes->find($id);

        if (!empty($minutes->id)) {
            $data         = array();
            $data['data'] = Minutes_Helpers_Userlist::expandIdList($minutes->participantsInvited,
                $minutes->participantsExcused, $minutes->participantsAttending, $minutes->recipients);
            $data['numRows'] = count($data['data']);
            Phprojekt_Converter_Json::echoConvert($data);
        } else {
            throw new Phprojekt_PublishedException(self::NOT_FOUND);
        }
    }

    /*
     * Deleting minutes also deletes all minutes items belonging to this minutes.
     *
     * @return void
     */
    public function jsonDeleteAction()
    {
        $id = (int) $this->getRequest()->getParam('id');

        if (empty($id)) {
            throw new Phprojekt_PublishedException(self::ID_REQUIRED_TEXT);
        }

        $minutes      = $this->getModelObject()->find($id);
        $minutesItems = Phprojekt_Loader::getModel('Minutes', 'MinutesItem')->init($id)->fetchAll();
        $success      = true;

        if ($minutes instanceof Phprojekt_Model_Interface) {
            foreach ($minutesItems as $item) {
                $success = $success && (false !== Default_Helpers_Delete::delete($item));
            }
            $success = $success && (false !== Default_Helpers_Delete::delete($minutes));

            if ($success === false) {
                $message = Phprojekt::getInstance()->translate(self::DELETE_FALSE_TEXT);
            } else {
                $message = Phprojekt::getInstance()->translate(self::DELETE_TRUE_TEXT);
            }

            $return = array('type'    => 'success',
                            'message' => $message,
                            'code'    => 0,
                            'id'      => $id);

            Phprojekt_Converter_Json::echoConvert($return);
        } else {
            throw new Phprojekt_PublishedException(self::NOT_FOUND);
        }
    }

    /**
     * Collects all mail addresses from user ids
     *
     * @param array                  $userIdList Array of user ids to be fetched
     * @param Zend_Validate_Abstract $validator  Validator to be used for the mail addresses
     *
     * @return array Array of arrays with either 'mail'/'name' pairs or 'message'/'value' errors.
     */
    protected function _getMailFromUserIds($userIdList, Zend_Validate_Abstract $validator)
    {
        // Add regular recipients:
        $idList = array();
        if (!empty($userIdList) && is_array($userIdList)) {
            foreach ($userIdList as $recipientId) {
                if (is_numeric($recipientId)) {
                    $idList[] = (int) $recipientId;
                }
            }
        }

        $userMailList = array();
        if (count($idList)) {
            /* @var $userModel Phprojekt_User_User */
            $userModel = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
            $userList  = $userModel->fetchAll(sprintf('id IN (%s)', implode(',', $idList)));
            $setting   = Phprojekt_Loader::getModel('Setting', 'Setting');
            $display   = $userModel->getDisplay();
            /* @var $record Phprojekt_User_User */
            foreach ($userList as $record) {
                $address = $setting->getSetting('email', (int) $record->id);

                if ($validator->isValid($address)) {
                    $userMailList[] = array('mail' => $address,
                                            'name' => $record->applyDisplay($display, $record));
                } else {
                    $userMailList[] = array('message' => 'Invalid email address detected:',
                                            'value'   => $address) ;
                }
            }
        }

        return $userMailList;
    }

    /**
     * Collects all mail addresses from a comma separated string
     *
     * @param string                 $csvString String with mail addresses
     * @param Zend_Validate_Abstract $validator Validator to be used for the mail addresses
     *
     * @return array Array of arrays with either 'mail'/'name' pairs or 'message'/'value' errors.
     */
    protected function _getMailFromCsvString($csvString, Zend_Validate_Abstract $validator)
    {
        $mailList = array();
        // Add additional recipients:
        if (!empty($csvString)) {
            $additional = explode(',', $csvString);
            foreach ($additional as $recipient) {
                $address = trim($recipient);
                if ($validator->isValid($address)) {
                    $mailList[] = array('mail' => $address,
                                        'name' => '');
                } else {
                    $mailList[] = array('message' => 'Invalid email address detected:',
                                        'value'   => $address);
                }
            }
        }

        return $mailList;
    }

    /**
     * Add recipients to the Zend_Mail object if valid, or put error message into return array
     *
     * @param Zend_Mail $mail     Zend_Mail object to be used
     * @param array     $mailList Array of mail addresses to be added, or error messages to be returned
     * @param array     $errors   Array of errors that new errors should be added to.
     *
     * @return array Array of errors encountered
     */
    protected function _addRecipients(Zend_Mail $mail, array $mailList, array $errors)
    {
        foreach ($mailList as $mailUser) {
            if (isset($mailUser['mail'])) {
                $mail->addTo($mailUser['mail'], $mailUser['name']);
            } else {
                $errors[] = $mailUser;
            }
        }

        return $errors;
    }

    /**
     * Sends a mail containing the Minutes protocol.
     */
    public function jsonSendMailAction()
    {
        $errors = array();
        $params = $this->getRequest()->getParams();

        // Sanity check
        if (empty($params['id']) || !is_numeric($params['id'])) {
            throw new Phprojekt_PublishedException(self::ID_REQUIRED_TEXT);
        }

        $minutesId = (int) $params['id'];
        $minutes   = $this->getModelObject()->find($minutesId);

        // Was the id provided a valid one?
        if (!($minutes instanceof Phprojekt_Model_Interface) || !$minutes->id) {
            // Invalid ID
            throw new Phprojekt_PublishedException(self::ID_REQUIRED_TEXT);
        }

        // Security check: is the current user owner of this minutes entry?
        if ($minutes->ownerId != PHprojekt_Auth::getUserId()) {
            throw new Phprojekt_PublishedException(self::USER_IS_NOT_OWNER);
        }

        $mail = new Phprojekt_Mail_Notification(); // @todo Refactor mail classes and use base class here!
        /* @var $mail Zend_Mail */
        $smtpTransport = $mail->setTransport();
        $validator     = new Zend_Validate_EmailAddress();

        $emailsListed  = $this->getRequest()->getParam('recipients', array());
        $emailsListed  = $this->_getMailFromUserIds($emailsListed, $validator);
        $emailsWritten = $this->getRequest()->getParam('additional', '');
        $emailsWritten = $this->_getMailFromCsvString($emailsWritten, $validator);
        $userMails     = array_merge($emailsListed, $emailsWritten);
        $errors        = $this->_addRecipients($mail, $userMails, $errors);

        // Sanity check
        if (array() === $mail->getRecipients()) {
            $errors[] = array('message' => self::MISSING_MAIL_RECIPIENTS,
                              'value'   => null);
        }

        if (!count($errors)) {
            // Handle PDF attachment if needed
            if (!empty($params['options']) && is_array($params['options'])) {
                if (in_array('pdf', $params['options'])) {
                    $pdf = (string) Minutes_Helpers_Pdf::getPdf($minutes);
                    $mail->createAttachment($pdf, 'application/x-pdf', Zend_Mime::DISPOSITION_ATTACHMENT,
                        Zend_Mime::ENCODING_8BIT, 'minutes_' . $minutesId . '.pdf');
                }
            }

            // Set sender address
            $ownerModel = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
            $ownerModel->find($minutes->ownerId);
            $ownerEmail = $ownerModel->getSetting('email');
            $display    = $ownerModel->getDisplay();
            $mail->setFrom($ownerEmail, $ownerModel->applyDisplay($display, $ownerModel));

            // Set subject
            $subject = sprintf('%s "%s", %s', Phprojekt::getInstance()->translate('Meeting minutes for'),
                $minutes->title, $minutes->startTime);
            $mail->setSubject($subject);

            // Set mail content
            $mail->setBodyText($subject, 'utf-8');
            $mail->setBodyHtml($this->_getHtmlList($minutes), 'utf-8');

            // Keep send() commented out until test phase is over
            $mail->send($smtpTransport);

            $return = array('type'    => 'success',
                            'message' => Phprojekt::getInstance()->translate(self::MAIL_SUCCESS_TEXT),
                            'code'    => 0,
                            'id'      => $minutesId);
        } else {
            $message = Phprojekt::getInstance()->translate(self::MAIL_FAIL_TEXT);
            foreach ($errors as $error) {
                $message .= "\n";
                $message .= sprintf("%s %s", Phprojekt::getInstance()->translate($error['message']), $error['value']);
            }

            $return = array('type'    => 'error',
                            'message' => nl2br($message), // @todo Converting to BR should be done in the view!
                            'code'    => -1,
                            'id'      => $minutesId);
        }

        Phprojekt_Converter_Json::echoConvert($return);
    }

    /**
     * Returns a string with HTML representing the minutes data
     *
     * @param Minutes_Models_Minutes $minutes Minutes object to use for data
     *
     * @return string HTML representation of minutes data
     */
    protected function _getHtmlList(Phprojekt_Model_Interface $minutes)
    {
        $items     = $minutes->items->fetchAll();
        $translate = Phprojekt::getInstance()->getTranslate();
        $newitem   = array();

        foreach ($items as $item) {
            $data              = array();
            $data['topicId']   = $this->view->escape($item->topicId);
            $data['topicType'] = $this->view->escape($item->information->getTopicType($item->topicType));
            $data['display']   = nl2br($this->view->escape($item->getDisplay()));
            $newitem[] = $data;
        }

        $this->view->items       = $newitem;
        $this->view->title       = $this->view->escape($minutes->title);
        $this->view->description = nl2br($this->view->escape($minutes->description));
        $this->view->txtNo       = $translate->translate('No.');
        $this->view->txtType     = $translate->translate('Type');
        $this->view->txtItem     = $translate->translate('Item');

        Phprojekt_Loader::loadViewScript($this->view);
        return $this->view->render('table.phtml');
    }

    /**
     * Create pdf file and stream to client
     *
     * @return void
     */
    public function pdfAction()
    {
        $id = (int) $this->getRequest()->getParam('id');

        if (empty($id)) {
            throw new Phprojekt_PublishedException(self::ID_REQUIRED_TEXT);
        }

        $minutes = $this->getModelObject()->find($id);

        if ($minutes instanceof Phprojekt_Model_Interface) {
            $this->getResponse()->setHeader("Content-Disposition", "inline; filename=minutes-" . $minutes->id . ".pdf");
            $this->getResponse()->setHeader("Content-type", "application/x-pdf; charset=utf-8");
            echo Minutes_Helpers_Pdf::getPdf($minutes);
        } else {
            throw new Phprojekt_PublishedException(self::NOT_FOUND);
        }
    }

    /**
     * Final minutes only allow write access to status field
     * @todo This should really be placed inside the model itself
     *
     * @return array
     */
    public function setParams()
    {
        $args   = func_get_args();
        $params = $args[0];
        $model  = $args[1];

        if (4 == $model->itemStatus) {
            if (isset($params['itemStatus'])) {
                return array('itemStatus' => (int) $params['itemStatus']);
            } else {
                return array();
            }
        } else {
            return $params;
        }
    }
}
