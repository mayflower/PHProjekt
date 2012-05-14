<?php
/**
 * The file contains the log functions.
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
 * @package    Phprojekt
 * @subpackage Core
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
 * Manage an array with Zend_Log objects for loging each type of log in one distinct file.
 *
 * Since the Zend_Log use only one file for log everything in one big file,
 * we create an array with various Zend_Log objects,
 * each one, defined with a own log file and a own filter.
 *
 * The path to the log file is defined in the configuration.php file in the way:
 * log.debug.filename is for log DEBUG stuffs.
 * log.crit.filename  is for log CRIT stuffs.
 * etc.
 *
 * The type defined for use are:
 * EMERG   = Emergency: system is unusable.
 * ALERT   = Alert: action must be taken immediately.
 * CRIT    = Critical: critical conditions.
 * ERR     = Error: error conditions.
 * WARN    = Warning: warning conditions.
 * NOTICE  = Notice: normal but significant condition.
 * INFO    = Informational: informational messages.
 * DEBUG   = Debug: debug messages.
 *
 * You can add in the configuration.php all of these types.
 * If the path to a log file is not defined, the class just drop the log.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Core
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_Log extends Zend_Log
{
    /**
     * An array of Zend_Log with priority filtering.
     *
     * @var array
     */
    protected $_loggers = array();

    /**
     * Constructor function.
     *
     * For all the defined filenames for log constant,
     * will create a Zend_Log object
     * with the path to the filename and a filter for these log.
     *
     * @param Zend_Config $config Object contain the user configuration.
     *
     * @return void
     */
    public function __construct(Zend_Config $config)
    {
        parent::__construct();

        $this->_loggers = array();

        if (isset($config->log)) {
            foreach ($config->log as $key => $val) {
                $constant = "self::" . strtoupper($key);
                if (defined($constant)) {
                    $priority = constant($constant);
                    $logger = new Zend_Log(new Zend_Log_Writer_Stream($val->filename));
                    $logger->addFilter(new Zend_Log_Filter_Priority($priority));
                    $this->_loggers[] = $logger;
                }
            }
        }
    }

    /**
     * Write the text into the file.
     *
     * For DEBUG log, is defined a special format.
     *
     * The message is passed to all Zend_Log instances saved in _loggers,
     * but they have priority filtering and therefore decide themself
     * if they pass the message to the file.
     *
     * @param string $message  Text to write.
     * @param string $priority Type of log.
     *
     * @return void
     */
    public function log($message, $priority, $extras = null)
    {
        if ($priority >= Zend_Log::DEBUG) {
            $btrace = debug_backtrace();
            if (isset($btrace[3])) {
                if (!isset($btrace[3]['line'])) {
                    $btrace[3]['line'] = '';
                }
                if (!isset($btrace[3]['class'])) {
                    $btrace[3]['class'] = '';
                }
                $message = sprintf("%d %s::%s:\n %s\n", $btrace[3]['line'],
                               $btrace[3]['class'], $btrace[3]['function'], $message);
            }
        }
        foreach ($this->_loggers as $logger) {
            $logger->log($message, $priority, $extras);
        }
    }
}
