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
    const EMERG   = 0;  // Emergency: system is unusable
    const ALERT   = 1;  // Alert: action must be taken immediately
    const CRIT    = 2;  // Critical: critical conditions
    const ERR     = 3;  // Error: error conditions
    const WARN    = 4;  // Warning: warning conditions
    const NOTICE  = 5;  // Notice: normal but significant condition
    const INFO    = 6;  // Informational: informational messages
    const DEBUG   = 7;  // Debug: debug messages

    /**
     * @var array of Log objects
     */
    private $_loguers = array();

    /**
     * @var array of priorities where the keys are the
     * priority numbers and the values are the priority names
     */
    private $_priorities = array();

    /**
     * Get the constants for use later
     *
     * @param  void
     */
    public function __construct()
    {
        $r = new ReflectionClass($this);
        $this->_priorities = array_flip($r->getConstants());
    }

    /**
     * Add a new log object
     *
     * @param  string  $priority  priority name
     * @return void
     */
    public function addLog($priority = 'debug')
    {
        $config = Zend_Registry::get('config');

        if (isset($config->log->$priority->filename)) {
            $oWriter = new Zend_Log_Writer_Stream($config->log->$priority->filename);
        } else {
            $oWriter = new Zend_Log_Writer_Null;
        }

        $priority = strtoupper($priority);
        $this->_loguers[$priority] = new Zend_Log($oWriter);
    }

    /**
     * Undefined method handler allows a shortcut:
     *   $log->priorityName('message')
     *     instead of
     *   $log->log('message', Zend_Log::PRIORITY_NAME)
     *
     * @param  string  $method  priority name
     * @param  string  $params  message to log
     * @return void
     * @throws Zend_Log_Exception
     */
    public function __call($method, $params)
    {
        $priority = strtoupper($method);

        /* Add the new log if is not started */
        if (!isset($this->_loguers[$priority])) {
            $this->addLog($method);
        }

        if (($iPriority = array_search($priority, $this->_priorities)) !== false) {
            $this->_loguers[$priority]->log(array_shift($params), $iPriority);
        } else {
            throw new Zend_Log_Exception('Bad log priority');
        }
    }

    /**
     * Add a writer per each log object
     *
     * @param  Zend_Log_Writer_Abstract $writer
     * @return void
     */
    public function addWriter($writer,$priority)
    {
        $priority = strtoupper($priority);
        $_loguers[$priority]->_writers[] = $writer;
    }
}