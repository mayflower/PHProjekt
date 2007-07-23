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

/** Zend_Log */
require_once 'Zend/Log.php';

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
    protected $_loggers = array();

    /**
     * Get the constants for use later
     *
     * @param  void
     */
    public function __construct(Zend_Config $config)
    {
        parent::__construct();

        foreach ($config->log as $key => $val) {
            $constant = "self::".strtoupper($key);
            if (defined($constant)) {

                $priority = constant($constant);
                $logger   = new Zend_Log(new Zend_Log_Writer_Stream($val->filename));
                $logger->addFilter(new Zend_Log_Filter_Priority($priority));
                $this->_loggers[] = $logger;
            }
        }
    }

    public function log($message, $priority)
    {
        /* @var Zend_Log $logger */
        foreach ($this->_loggers as $logger) {
            $logger->log($message, $priority);
        }
    }
}