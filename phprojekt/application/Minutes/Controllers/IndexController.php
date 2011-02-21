<?php
/**
 * Minutes Module Controller.
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
 * @subpackage Minutes
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Sven Rautenberg <sven.rautenberg@mayflower.de>
 */

/**
 * Minutes Module Controller.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Minutes
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Sven Rautenberg <sven.rautenberg@mayflower.de>
 */
class Minutes_IndexController extends IndexController
{
    /**
     * String to use on error when send an mail
     * without specifying any recipient addresses.
     */
    const MISSING_MAIL_RECIPIENTS = 'No recipient addresses have been specified';

    /**
     * String to use on error when trying to send mail
     * without being the owner of the requested Minutes entry.
     */
    const USER_IS_NOT_OWNER = 'The currently logged-in user is not owner of the given minutes entry';

    /**
     * String to use on error in the action sendMail.
     */
    const MAIL_FAIL_TEXT = 'The mail could not be sent';

    /**
     * String to use on success in the action sendMail.
     */
    const MAIL_SUCCESS_TEXT = 'The mail was sent successfully';

    /**
     * Deletes minutes and also all minutes items belonging to this minutes.
     *
     * REQUIRES request parameters:
     * <pre>
     *  - integer <b>id</b> id of the minute to delete.
     * </pre>
     *
     * The return is a string in JSON format with:
     * <pre>
     *  - type    => 'success' or 'error'.
     *  - message => Success or error message.
     *  - code    => 0.
     *  - id      => id of the deleted item.
     * </pre>
     *
     * @throws Phprojekt_PublishedException On missing or wrong id, or on error in the action delete.
     *
     * @return void
     */
    public function jsonDeleteAction()
    {
        $id = (int) $this->getRequest()->getParam('id');

        if (empty($id)) {
            throw new Phprojekt_PublishedException(self::ID_REQUIRED_TEXT);
        } else {
            $minutes = $this->getModelObject()->find($id);
            if (empty($minutes->id)) {
                throw new Phprojekt_PublishedException(self::NOT_FOUND);
            }
        }
        $minutesItems = $minutes->items->fetchAll();
        $success      = true;

        if ($minutes instanceof Phprojekt_ActiveRecord_Abstract) {
            foreach ($minutesItems as $item) {
                $item->setParent($id);
                $success = $success && (false !== Default_Helpers_Delete::delete($item));
            }
            $success = $success && (false !== Default_Helpers_Delete::delete($minutes));

            if ($success === false) {
                $message = Phprojekt::getInstance()->translate(self::DELETE_FALSE_TEXT);
                $type    = 'error';
            } else {
                $message = Phprojekt::getInstance()->translate(self::DELETE_TRUE_TEXT);
                $type    = 'success';
            }

            $return = array('type'    => $type,
                            'message' => $message,
                            'code'    => 0,
                            'id'      => $id);

            Phprojekt_Converter_Json::echoConvert($return);
        } else {
            throw new Phprojekt_PublishedException(self::NOT_FOUND);
        }
    }

    /**
     * Returns a list of users.
     *
     * Produces a list of users that should be selectable in the frontend.
     *
     * First implementation returns the list of users invited to the meeting.
     *
     * Returns a list of all the users with:
     * <pre>
     *  - id   => id of user.
     *  - name => Display for the user.
     * </pre>
     *
     * REQUIRES request parameters:
     * <pre>
     *  - integer <b>id</b> id of the minute to consult.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @throws Phprojekt_PublishedException On wrong id.
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
                $minutes->participantsExcused, $minutes->participantsAttending);
            $data['numRows'] = count($data['data']);
            Phprojekt_Converter_Json::echoConvert($data);
        } else {
            throw new Phprojekt_PublishedException(self::NOT_FOUND);
        }
    }

    /**
     * Sends a mail containing the Minutes protocol.
     *
     * A pdf can be also attached to the mail.
     *
     * REQUIRES request parameters:
     * <pre>
     *  - integer <b>id</b> id of the minute to send.
     * </pre>
     *
     * OPTIONAL request parameters:
     * <pre>
     *  - array <b>options</b> If contain 'pdf', a pdf is attached to the mail.
     * </pre>
     *
     * The return is a string in JSON format with:
     * <pre>
     *  - type    => 'success' or 'error'.
     *  - message => Success or error message.
     *  - code    => 0 for success, -1 for error.
     *  - id      => id of the minute.
     * </pre>
     *
     * @throws Phprojekt_PublishedException On error in the send action or wrong id.
     *
     * @return void
     */
    public function jsonSendMailAction()
    {
        $errors = array();
        $params = $this->getRequest()->getParams();
        $this->setCurrentProjectId();

        print_r($params);
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

        $mail = new Phprojekt_Mail();
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
            if (isset($params['pdf']) && $params['pdf'] == 1) {
                $pdf = (string) Minutes_Helpers_Pdf::getPdf($minutes);
                $mail->createAttachment($pdf, 'application/x-pdf', Zend_Mime::DISPOSITION_ATTACHMENT,
                    Zend_Mime::ENCODING_8BIT, 'minutes_' . $minutesId . '.pdf');
            }

            // Set sender address
            $ownerModel = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
            $ownerModel->find($minutes->ownerId);
            $ownerEmail = $ownerModel->getSetting('email');
            $display    = $ownerModel->getDisplay();
            $mail->setFrom($ownerEmail, $ownerModel->applyDisplay($display, $ownerModel));

            // Set subject
            $subject = sprintf('%s "%s", %s', Phprojekt::getInstance()->translate('Meeting minutes for'),
                $minutes->title, $minutes->meetingDatetime);
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
     * Creates a pdf file and stream ite to the client.
     *
     * REQUIRES request parameters:
     * <pre>
     *  - integer <b>id</b> id of the minute to send.
     * </pre>
     *
     * The return is a string in JSON format with:
     * <pre>
     *  - type    => 'success' or 'error'.
     *  - message => Success or error message.
     *  - code    => 0 for success, -1 for error.
     *  - id      => id of the minute.
     * </pre>
     *
     * @throws Phprojekt_PublishedException On error in the pdf creation action or wrong id.
     *
     * @return void
     */
    public function pdfAction()
    {
        $id = (int) $this->getRequest()->getParam('id');
        $this->setCurrentProjectId();

        if (empty($id)) {
            throw new Phprojekt_PublishedException(self::ID_REQUIRED_TEXT);
        }

        $minutes = $this->getModelObject()->find($id);

        if ($minutes instanceof Phprojekt_Model_Interface) {
            $outputString = Minutes_Helpers_Pdf::getPdf($minutes);

            if (!headers_sent()) {
                header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
                header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
                header("Cache-Control: must-revalidate");
                header("Cache-Control: post-check=0, pre-check=0", false);
                header("Pragma: no-cache");
                header('Content-Length: ' . strlen($outputString));
                header("Content-Disposition: attachment; filename=\"minutes-" . $minutes->id . ".pdf\"");
                header("Content-type: application/x-pdf; charset=utf-8");
            }

            echo $outputString;
        } else {
            throw new Phprojekt_PublishedException(self::NOT_FOUND);
        }
    }

    /**
     * Collects all mail addresses from user ids.
     *
     * @param array                  $userIdList Array of user ids to be fetched.
     * @param Zend_Validate_Abstract $validator  Validator to be used for the mail addresses.
     *
     * @return array Array of arrays with either 'mail'/'name' pairs or 'message'/'value' errors.
     */
    private function _getMailFromUserIds($userIdList, Zend_Validate_Abstract $validator)
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
            $setting   = Phprojekt_Loader::getLibraryClass('Phprojekt_Setting');
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
     * Collects all mail addresses from a comma separated string.
     *
     * @param string                 $csvString String with mail addresses.
     * @param Zend_Validate_Abstract $validator Validator to be used for the mail addresses.
     *
     * @return array Array of arrays with either 'mail'/'name' pairs or 'message'/'value' errors.
     */
    private function _getMailFromCsvString($csvString, Zend_Validate_Abstract $validator)
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
     * Adds recipients to the Zend_Mail object if valid,
     * or put error message into return array.
     *
     * @param Zend_Mail $mail     Zend_Mail object to be used.
     * @param array     $mailList Array of mail addresses to be added, or error messages to be returned.
     * @param array     $errors   Array of errors that new errors should be added to.
     *
     * @return array Array of errors encountered.
     */
    private function _addRecipients(Zend_Mail $mail, array $mailList, array $errors)
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
     * Returns a string with HTML representing the minutes data.
     *
     * @param Minutes_Models_Minutes $minutes Minutes object to use for data.
     *
     * @return string HTML representation of minutes data.
     */
    private function _getHtmlList(Phprojekt_Model_Interface $minutes)
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
     * Sets some values depending on the parameters.
     *
     * Final minutes only allow write access to status field.
     *
     * @return array POST values with some changes.
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
