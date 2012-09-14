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
 * Tests Logs
 *
 * @group      phprojekt
 * @group      log
 * @group      phprojekt-log
 */
class Phprojekt_LogTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test wrong call
     */
    public function testWrongLog()
    {
        $config = Phprojekt::getInstance()->getConfig();
        $log = new Phprojekt_Log($config);

        $this->setExpectedException('Zend_Log_Exception');
        $log->nothing('TEST');
    }

    /**
     * Test wrong priority
     */
    public function testWrongPriority()
    {
        $config = Phprojekt::getInstance()->getConfig();
        $log = new Phprojekt_Log($config);

        $this->setExpectedException('Zend_Log_Exception');
        $log->log('TEST', 'NOTHING');
    }

    /**
     * Test wrong priority
     */
    public function testBiggestPriority()
    {
        $config = Phprojekt::getInstance()->getConfig();
        $log = new Phprojekt_Log($config);

        $this->setExpectedException('Zend_Log_Exception');
        $log->log('TEST', 8);
    }

    /**
     * Normal cal
     */
    public function testLog()
    {
        $config = Phprojekt::getInstance()->getConfig();
        $log = new Phprojekt_Log($config);

        $log->log('TEST', Zend_Log::DEBUG);
        $log->log('TEST', Zend_Log::CRIT);
        $log->log('TEST', Zend_Log::INFO);
    }
}
