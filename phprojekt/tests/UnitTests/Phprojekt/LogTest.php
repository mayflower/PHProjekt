<?php
/**
 * Unit test
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
*/
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/Extensions/ExceptionTestCase.php';

/**
 * Tests Logs
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_LogTest extends PHPUnit_Extensions_ExceptionTestCase
{
    /**
     * Test wrong call
     *
     */
    public function testWrongLog()
    {
        $session = new Zend_Session_Namespace();
        $log = new Phprojekt_Log($session->config);

        $this->setExpectedException('Exception');
        $log->nothing('TEST');
    }

    /**
     * Test wrong priority
     *
     */
    public function testWrongPriority()
    {
        $session = new Zend_Session_Namespace();
        $log = new Phprojekt_Log($session->config);

        $this->setExpectedException('Zend_Log_Exception');
        $log->log('TEST','NOTHING');
    }

    /**
     * Test wrong priority
     *
     */
    public function testBigestPriority()
    {
        $session = new Zend_Session_Namespace();
        $log = new Phprojekt_Log($session->config);

        $this->setExpectedException('Zend_Log_Exception');
        $log->log('TEST',8);
    }

    /**
     * Normal cal
     *
     */
    public function testLog()
    {
        $session = new Zend_Session_Namespace();
        $log = new Phprojekt_Log($session->config);

        $log->log('TEST',Zend_Log::DEBUG);
        $log->log('TEST',Zend_Log::CRIT);
        $log->log('TEST',Zend_Log::INFO);
    }

}