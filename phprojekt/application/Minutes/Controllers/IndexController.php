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
    
    const MAIL_FAIL_TEXT = 'The mail could not be sent.';
    const MAIL_SUCCESS_TEXT = 'The mail was sent successfully.';
    
    /**
     * Get a user list in JSON
     *
     * Produces a list of users that should be selectable in the frontend.
     * First implementation returns the list of users invited to the meeting.
     *
     * @return void
     */
    public function jsonListUserAction ()
    {
        $minutes = Phprojekt_Loader::getModel('Minutes', 'Minutes');
        $minutes->find($this->getRequest()->getParam('id'));

        if (!empty($minutes->id)) {
            $idList = array();
            $idList = array_merge($idList, 
                              explode(',', $minutes->participantsInvited),
                              explode(',', $minutes->participantsExcused),
                              explode(',', $minutes->participantsAttending),
                              explode(',', $minutes->recipients));
            $data['data']    = Minutes_Helpers_Userlist::expandIdList(implode(',', $idList));
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
    public function jsonDeleteAction () 
    {
        $id = (int) $this->getRequest()->getParam('id');

        if (empty($id)) {
            throw new Phprojekt_PublishedException(self::ID_REQUIRED_TEXT);
        }

        $minutes = Phprojekt_Loader::getModel('Minutes', 'Minutes')->find($id);
        $minutesItems = Phprojekt_Loader::getModel('Minutes', 'MinutesItem')->init($id)->fetchAll();
        
        $success = true;
        
        if ($minutes instanceof Phprojekt_Model_Interface) {
            foreach ($minutesItems as $item) {
                Phprojekt::getInstance()->getLog()->debug('Deleting minutesItem' . $item->id);
                $success = $success && (false !== Default_Helpers_Delete::delete($item));
                Phprojekt::getInstance()->getLog()->debug('Deletion was successful:' . ($success?'yes':'no'));
            }
            $success = $success && (false !== Default_Helpers_Delete::delete($minutes));
            Phprojekt::getInstance()->getLog()->debug('Main Deletion was successful:' . ($success?'yes':'no'));
            
            
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
     * Action to provide an HTML table of the whole minutes.
     */
    public function htmlListAction()
    {
        $this->getHtmlList((int) $this->getRequest()->getParam('minutesId', 0));
        $this->render('table');
    }
    
    /**
     * Sends a mail containing the Minutes protocol.
     */
    public function jsonSendMailAction()
    {
        $log = Phprojekt::getInstance()->getLog();
        $log->debug('Entering jsonSendMailAction... ');
        $errors = array();
        
        /**
         * @todo Change Phprojekt_Mail_Notification::_setTransport() to public,
         *       maybe even static (singleton), so the transport object can be 
         *       fetched and configured globally. Maybe even think about using
         *       Phprojekt_Mail_Notification instead of Zend_Mail. Benefits?
         */
        $config     = Phprojekt::getInstance()->getConfig();
        $eol        = (int) $config->get('mailEndOfLine');
        $smtpServer = $config->get('smtpServer');
        $smtpUser   = $config->get('smtpUser');
        $smtpPasswd = $config->get('smtpPassword');
        
        // The next 9 lines are copied over from Phprojekt_Mail_Notification::_setTransport(),
        // refactoring is needed!!
        if (empty($smtpServer)) {
            $smtpServer = 'localhost';
        }
        if (empty($smtpUser)) {
            $smtpTransport = new Zend_Mail_Transport_Smtp($smtpServer);
        } else {
            $smtpTransport = new Zend_Mail_Transport_Smtp($smtpServer, array('auth'     => 'login',
                                                                             'username' => $smtpUser,
                                                                             'password' => $smtpPasswd));
        }
        
        $params     = $this->getRequest()->getParams();
        $log->debug('Request params: ' . print_r($params, true));
        
        // sanity check
        if (empty($params['id']) || !is_numeric($params['id'])) {
            throw new Phprojekt_PublishedException(self::ID_REQUIRED_TEXT);
        }
        
        $minutesId     = (int) $params['id'];
        $minutes       = Phprojekt_Loader::getModel('Minutes', 'Minutes');
        $minutes->find($minutesId);
        
        // was the id provided a valid one?
        if (!$minutes->id) {
            // invalid ID
            throw new Phprojekt_PublishedException(self::ID_REQUIRED_TEXT);
        }
        
        // security check: is the current user owner of this minutes entry?
        if ($minutes->ownerId != PHprojekt_Auth::getUserId()) {
            throw new Phprojekt_PublishedException(self::USER_IS_NOT_OWNER);
        }
        
        $hasRecipients = false;
        $mail          = new Zend_Mail();
        $validator     = new Zend_Validate_EmailAddress();
        
        // Add regular recipients:
        $idList = array();
        if (!empty($params['recipients']) && is_array($params['recipients'])) {
            foreach ($params['recipients'] as $recipientId) {
                if (is_numeric($recipientId)) {
                    $idList[] = (int) $recipientId;
                }
            }
        }
        $log->debug('idList: ' . print_r($idList, true));
        if (count($idList)) {
            $userModel = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
            $userList  = $userModel->fetchAll(sprintf('id IN (%s)', implode(',', $idList)));
            $setting   = Phprojekt_Loader::getModel('Setting', 'Setting');
            foreach ($userList as $record) {
                $address = $setting->getSetting('email', (int) $record->id);
                
                if ($validator->isValid($address)) {
                    $log->debug('Adding mail to: ' . $address . ' (' .
                                $record->firstname . ' ' . $record->lastname . ')');
                    $mail->addTo($address, $record->firstname . ' ' . $record->lastname);
                    $hasRecipients = true;
                } else {
                    $errors[] = array('message' => 'Invalid email address detected: %s',
                                      'value'   => $address) ;
                }
            }
        }
        
        // Add additional recipients:
        if (!empty($params['additional'])) {
            $additional = explode(',', $params['additional']);
            foreach ($additional as $recipient) {
                $address = trim($recipient);
                if ($validator->isValid($address)) {
                    $log->debug('Adding mail to: ' . $address);
                    $mail->addTo($address);
                    $hasRecipients = true;
                } else {
                    $errors[] = array('message' => 'Invalid email address detected: %s',
                                      'value'   => $address);
                }
            }
        }
        
        // sanity check
        if (!$hasRecipients) {
            $errors[] = array('message' => self::MISSING_MAIL_RECIPIENTS,
                              'value'   => null);
        }
        
        // handle PDF attachment if needed
        if (!count($errors)) {
            if (!empty($params['options']) && is_array($params['options'])) {
                if (in_array('pdf', $params['options'])) {
                    $log->debug('Creating PDF attachment...');
                    /* @todo use PDF report creator here as soon as it's ready */
                    
                    $pdf = Minutes_Helpers_Pdf::getPdf($minutesId);
                    $mail->createAttachment($pdf,
                                            'application/x-pdf',
                                            Zend_Mime::DISPOSITION_ATTACHMENT,
                                            Zend_Mime::ENCODING_8BIT,
                                            'minutes_' . $minutesId . '.pdf');
                }
            }
            
            // set sender address
            $ownerModel = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
            $ownerModel->find($minutes->ownerId);
            $ownerEmail = $ownerModel->getSetting('email');    
            $mail->setFrom($ownerEmail, $ownerModel->firstname . ' ' . $ownerModel->lastname);
            $log->debug('Setting FROM: ' . $ownerEmail . ' (' .
                        $ownerModel->firstname . ' ' . $ownerModel->lastname . ')');
            
            // set subject
            $subject = sprintf(Phprojekt::getInstance()->translate('Meeting minutes for "%s", %s'), 
                               $minutes->title, 
                               $minutes->startTime);
            $mail->setSubject($subject);
            
            // set mail content
            $mail->setBodyText($subject, 'utf-8');
            $mail->setBodyHtml($this->getHtmlList($minutesId), 'utf-8');
            
            // keep send() commented out until test phase is over
            //$mail->send($smtpTransport);
            $return = array('type'    => 'success',
                            'message' => Phprojekt::getInstance()->translate(self::MAIL_SUCCESS_TEXT),
                            'code'    => 0,
                            'id'      => $minutesId);
        } else {
            $message = Phprojekt::getInstance()->translate(self::MAIL_FAIL_TEXT);
            foreach($errors as $error) {
                $message .= "\n";
                $message .= sprintf(Phprojekt::getInstance()->translate($error['message']), 
                                    $error['value']);
            }
            $return = array('type'    => 'error',
                            'message' => nl2br($message),
                            'code'    => -1,
                            'id'      => $minutesId);
        }
        
        Phprojekt_Converter_Json::echoConvert($return);
    }
    
    protected function getHtmlList($minutesId)
    {
        $this->view->addScriptPath(PHPR_CORE_PATH . '/Minutes/Views/dojo/');
        
        $items = Phprojekt_Loader::getModel('Minutes', 'MinutesItem')
                             ->init($minutesId)
                             ->fetchAll();
                             
        $newitem = array();
        foreach ($items as $item) {
            $content = array();
            $content['topicId'] = $item->topicId;
            $content['title']   = $item->title;
            $content['topicType'] = $item->topicType;
            $content['comment'] = $item->comment;
            $newitem[] = $content;
        }
        
        $this->view->items = $newitem; 
        
        return $this->view->render('table.phtml');
    }
    
    /**
     * Completely bogus action to create demo pdf file
     * 
     * @todo Remove this function or refactor to final action
     */
    public function pdfAction()
    {
        $id = (int) $this->getRequest()->getParam('id');

        if (empty($id)) {
            throw new Phprojekt_PublishedException(self::ID_REQUIRED_TEXT);
        }

        $minutes = Phprojekt_Loader::getModel('Minutes', 'Minutes')->find($id);

        if ($minutes instanceof Phprojekt_Model_Interface) {
            header("Content-Disposition: inline; filename=result.pdf");
            header("Content-type: application/x-pdf; charset=utf-8");
            echo Minutes_Helpers_Pdf::getPdf($minutes);
        } else {
            throw new Phprojekt_PublishedException(self::NOT_FOUND);
        }
    }
}
