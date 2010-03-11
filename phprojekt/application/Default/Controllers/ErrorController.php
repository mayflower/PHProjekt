<?php
/**
 * Default error handling.
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
 * @subpackage Default
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */

/**
 * Default error handling.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Default
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class ErrorController extends Zend_Controller_Action
{
    /**
     * Initialize our error controller and disable the viewRenderer.
     *
     * @return void
     */
    public function init()
    {
        $this->_helper->viewRenderer->setNoRender();
    }

    /**
     * Default error action.
     *
     * On Phprojekt_PublishedException, return an error string in JSON format.
     * <pre>
     *  - type    => 'error'.
     *  - message => Error message.
     *  - code    => Error code.
     * </pre>
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
        switch ($error->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
                $message  = "The url " . Cleaner::sanitize('xss', urldecode($error->request->getRequestUri())) . " do not exists";
                Phprojekt::getInstance()->getLog()->err($message);
                die($message);
                break;
            default:
                $exception = $error->exception;
                // We only forward exception with type PublishedException
                if ($exception instanceof Phprojekt_PublishedException) {
                    $error = array('type'    => 'error',
                                   'message' => $exception->getMessage(),
                                   'code'    => $exception->getCode());
                    echo '{}&&(' . Zend_Json_Encoder::encode($error) . ')';
                } else {
                    $logger = Phprojekt::getInstance()->getLog();
                    $logger->err($exception->getMessage() . "\n" . $exception->getTraceAsString());
                }
                break;
        }
    }
}
