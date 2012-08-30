<?php
/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * Default error handling.
 */
class ErrorController extends Zend_Controller_Action
{

    /**
     * Init function to register the contexts
     *
     * Sets the json context for the error action.
     *
     * @return void
     */
    public function init() {
        $this->_helper->contextSwitch()
            ->addActionContext('error', 'json')
            ->setAutoJsonSerialization(false)
            ->initContext();
    }

    /**
     * Default error action.
     *
     * On Zend_Controller_Action_Exception, if the error code is 4xx return an error message matching the accepted type.
     *
     * On wrong controller name or action, terminates script execution.
     *
     * In all cases, the error is logged.
     *
     * @return void
     */
    public function errorAction()
    {
        $error = $this->_getParam('error_handler');

        $this->getResponse()->clearBody();
        $exception = $error->exception;

        $viewerror = array(
            'type'    => 'error',
            'message' => 'Internal Server Error'
        );

        switch ($error->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $message  = "The url " . Cleaner::sanitize('xss', urldecode($error->request->getRequestUri())) . " do not exists";
                Phprojekt::getInstance()->getLog()->err($message);
                die($message);
                break;
            default:
                // We only forward exception with 4xx code to the client
                if ($exception instanceof Zend_Controller_Action_Exception &&
                        $exception->getCode() >= 400 &&
                        $exception->getCode() < 500) {

                    $this->getResponse()->setHttpResponseCode($exception->getCode());
                    $viewerror['message'] = $exception->getMessage();
                } else {
                    $this->getResponse()->setHttpResponseCode(500);
                    $logger = Phprojekt::getInstance()->getLog();
                    $logger->err($exception->getMessage() . "\n" . $exception->getTraceAsString());
                }
                break;
        }
        $this->view->error = $viewerror;
    }
}
