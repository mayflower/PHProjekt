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
 * @subpackage Default
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class ErrorController extends Zend_Controller_Action
{
    /**
     * Initialize our error controller and disable the viewRenderer
     */
    public function init() 
    {
        $this->_helper->viewRenderer->setNoRender();
    }
	   
    /**
     * Default error action
     *
     * @return void
     */
    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
    
        $this->getResponse()->clearBody();
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
                // 404 error -- controller or action not found
                $this->getResponse()->setRawHeader('HTTP/1.1 404 Not Found');
                break;
            default:                
                $exception = $errors->exception;
                
                $logger = Zend_Registry::get('log');
                $logger->err($exception->getMessage() . "\n"
                           . $exception->getTraceAsString());
                           
                /* we only forward exception with type PublishedException */
                if ($exception instanceof Phprojekt_PublishedException) {
	                echo '/* '. Zend_Json_Encoder::encode($exception) . ' */';
                }
                
                break;
        }
    }
}