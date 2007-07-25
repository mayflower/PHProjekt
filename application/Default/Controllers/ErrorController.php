<?php
/**
 * Default error handling
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @author     David Soria Parra <soria_parra@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Default error handling
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class ErrorController extends Zend_Controller_Action
{
    /**
     * Default error action
     *
     * @return void
     */
    public function errorAction()
    {
        $config = Zend_Registry::get('config');
        $logger = Zend_Registry::get('log');

        $logger->err('Error handler called');


        $this->getResponse()->clearHeaders();
        $this->getResponse()->clearBody();

        $errors = $this->_getParam('error_handler');
        $exception = $errors->exception;
        $logger->debug($exception->getMessage() . "\n"
                     . $exception->getTraceAsString());

        if ($config->debug) {
            $this->view->message = $exception->getMessage();
        }

    }
}