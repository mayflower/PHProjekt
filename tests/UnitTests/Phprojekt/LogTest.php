<?php
/**
 * Unit test
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
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    CVS: $Id:
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */
require_once 'PHPUnit/Framework.php';

/**
 * Tests Logs
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_LogTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test wrong call
     *
     */
    public function testWrongLog()
    {
        $config = Zend_Registry::get('config');
        $log = new Phprojekt_Log($config);

        $this->setExpectedException('Zend_Log_Exception');
        $log->nothing('TEST');
    }

    /**
     * Test wrong priority
     *
     */
    public function testWrongPriority()
    {
        $config = Zend_Registry::get('config');
        $log = new Phprojekt_Log($config);

        $this->setExpectedException('Zend_Log_Exception');
        $log->log('TEST', 'NOTHING');
    }

    /**
     * Test wrong priority
     *
     */
    public function testBiggestPriority()
    {
        $config = Zend_Registry::get('config');
        $log = new Phprojekt_Log($config);

        $this->setExpectedException('Zend_Log_Exception');
        $log->log('TEST', 8);
    }

    /**
     * Normal cal
     *
     */
    public function testLog()
    {
        $config = Zend_Registry::get('config');
        $log = new Phprojekt_Log($config);

        $log->log('TEST', Zend_Log::DEBUG);
        $log->log('TEST', Zend_Log::CRIT);
        $log->log('TEST', Zend_Log::INFO);
    }
}
