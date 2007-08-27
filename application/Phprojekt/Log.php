<?php
/**
 * The file contains the log functions
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Manage an array with Zend_Log objects
 * for loging each type of log in one distinct file.
 *
 * Since the Zend_Log use only one file for log everything in one big file,
 * we create an array with various Zend_Log objects,
 * each one, defined with a own log file and a own filter.
 *
 * The path to the log file is defined in the configuration.ini file in the way:
 * log.debug.filename is for log DEBUG stuffs
 * log.crit.filename  is for log CRIT stuffs
 * etc.
 *
 * The type defined for use are:
 * EMERG   = Emergency: system is unusable
 * ALERT   = Alert: action must be taken immediately
 * CRIT    = Critical: critical conditions
 * ERR     = Error: error conditions
 * WARN    = Warning: warning conditions
 * NOTICE  = Notice: normal but significant condition
 * INFO    = Informational: informational messages
 * DEBUG   = Debug: debug messages
 *
 * You can add in the configuration.ini all of these types.
 * If the path to a log file is not defined, the class just drop the log.
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_Log extends Zend_Log
{
    /**
     * An array of Zend_Log with priority filtering
     *
     * @var array
     */
    protected $_loggers = array();

    /**
     * Constructor function
     *
     * For all the defined filenames for log constant,
     * will create a Zend_Log object
     * with the path to the filename and a filter for these log.
     *
     * @param Zend_Config $config Object contain the user configuration
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
     * if they pass the message to the file
     *
     * @param string $message  Text to write
     * @param string $priority Type of log
     *
     * @return void
     */
    public function log($message, $priority)
    {
        if ($priority >= Zend_Log::DEBUG) {
            $btrace = debug_backtrace();
            if (true == isset($btrace[3]['line'])) {
                $message = sprintf("%d %s::%s:\n %s\n", $btrace[3]['line'], $btrace[3]['class'], $btrace[3]['function'], $message);
            }
        }
        foreach ($this->_loggers as $logger) {
            $logger->log($message, $priority);
        }
    }
}
