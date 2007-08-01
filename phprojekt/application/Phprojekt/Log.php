<?php
/**
 * Manage Logs
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Manage an array with Log objects
 * for log each type of log in one distinct file
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_Log extends Zend_Log
{
    /**
     * An array of Zend_Logs with priority filtering
     *
     * @var array
     */
    protected $_loggers = array();

    /**
     * Get the constants for use later
     *
     * @param Zend_Config $config Object contain the user configuration
     */
    public function __construct(Zend_Config $config)
    {
        parent::__construct();

        foreach ($config->log as $key => $val) {
            $constant = "self::".strtoupper($key);
            if (defined($constant)) {

                $priority = constant($constant);
                $logger   = new Zend_Log(
                                   new Zend_Log_Writer_Stream($val->filename)
                                   );
                $logger->addFilter(new Zend_Log_Filter_Priority($priority));
                $this->_loggers[] = $logger;
            }
        }
    }

    /**
     * Write the text into the file.
     *
     * @param string $message  Text to write
     * @param string $priority Type of log
     *
     * @return void
     */
    public function log($message, $priority)
    {
		if ($priority >= Zend_Log::DEBUG) {
			$btrace  = debug_backtrace();
			$message = sprintf("%d %s::%s:\n %s\n",
							   $btrace[3]['line'],
							   $btrace[3]['class'],
							   $btrace[3]['function'],
							   $message);
		}
        /*
         * Pass the message to all Zend_Log instances saved in _loggers,
         * but they have priority filtering and therefore decide themself
         * if they pass the message to the file
         */
        foreach ($this->_loggers as $logger) {
            $logger->log($message, $priority);
        }
    }
}
