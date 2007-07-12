<?php
/**
 * Bootstrap file.
 *
 * @category   Htdocs
 * @package    Htdocs
 * @copyright  2007 Mayflower GmbH
 * @version    CVS: $Id$
 * @author     David Soria Parra <david.soria_parra@mayflower.de>
 */

require_once 'Zend/Session.php';

/* start zend session to handle all session stuff */
Zend_Session::start();

$front = Zend_Controller_Front::getInstance();

$front->addModuleDirectory(APP_PATH.'/application/modules');

$front->setDefaultModule ('Default')
      ->setDefaultController ('Default')
      ->setDefaultAction ('default');