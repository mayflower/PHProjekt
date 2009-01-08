<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Wildfire
 * @subpackage Plugin
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/** Zend_Loader */
require_once 'Zend/Loader.php';

/** Zend_Wildfire_Exception */
require_once 'Zend/Wildfire/Exception.php';

/** Zend_Controller_Request_Abstract */
require_once('Zend/Controller/Request/Abstract.php');

/** Zend_Controller_Response_Abstract */
require_once('Zend/Controller/Response/Abstract.php');

/** Zend_Wildfire_Channel_HttpHeaders */
require_once 'Zend/Wildfire/Channel/HttpHeaders.php';

/** Zend_Wildfire_Protocol_JsonStream */
require_once 'Zend/Wildfire/Protocol/JsonStream.php';

/** Zend_Wildfire_Plugin_Interface */
require_once 'Zend/Wildfire/Plugin/Interface.php';

/**
 * Primary class for communicating with the FirePHP Firefox Extension.
 * 
 * @category   Zend
 * @package    Zend_Wildfire
 * @subpackage Plugin
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Wildfire_Plugin_FirePhp implements Zend_Wildfire_Plugin_Interface
{
    /**
     * Plain log style.
     */
    const LOG = 'LOG';
    
    /**
     * Information style.
     */
    const INFO = 'INFO';
    
    /**
     * Warning style.
     */
    const WARN = 'WARN';
    
    /**
     * Error style that increments Firebug's error counter.
     */
    const ERROR = 'ERROR';
    
    /**
     * Trace style showing message and expandable full stack trace.
     */
    const TRACE = 'TRACE';
    
    /**
     * Exception style showing message and expandable full stack trace.
     * Also increments Firebug's error counter.
     */
    const EXCEPTION = 'EXCEPTION';
    
    /**
     * Table style showing summary line and expandable table
     */
    const TABLE = 'TABLE';

    /**
     * Dump variable to Server panel in Firebug Request Inspector
     */
    const DUMP = 'DUMP';
  
    /**
     * The plugin URI for this plugin
     */
    const PLUGIN_URI = 'http://meta.firephp.org/Wildfire/Plugin/ZendFramework/FirePHP/0.1';
    
    /**
     * The protocol URI for this plugin
     */
    const PROTOCOL_URI = Zend_Wildfire_Protocol_JsonStream::PROTOCOL_URI;
    
    /**
     * The structure URI for the Dump structure
     */
    const STRUCTURE_URI_DUMP = 'http://meta.firephp.org/Wildfire/Structure/FirePHP/Dump/0.1';

    /**
     * The structure URI for the Firebug Console structure
     */
    const STRUCTURE_URI_FIREBUGCONSOLE = 'http://meta.firephp.org/Wildfire/Structure/FirePHP/FirebugConsole/0.1';
  
    /**
     * Singleton instance
     * @var Zend_Wildfire_Plugin_FirePhp
     */
    protected static $_instance = null;

    /**
     * Flag indicating whether FirePHP should send messages to the user-agent.
     * @var boolean
     */
    protected $_enabled = true;
    
    /**
     * The channel via which to send the encoded messages.
     * @var Zend_Wildfire_Channel_Interface
     */
    protected $_channel = null;
    
    /**
     * Messages that are buffered to be sent when protocol flushes
     * @var array
     */
    protected $_messages = array();
    
    /**
     * Create singleton instance.
     *
     * @param string $class OPTIONAL Subclass of Zend_Wildfire_Plugin_FirePhp
     * @return Zend_Wildfire_Plugin_FirePhp Returns the singleton Zend_Wildfire_Plugin_FirePhp instance
     * @throws Zend_Wildfire_Exception
     */
    public static function init($class = null)
    {
        if (self::$_instance!==null) {
            throw new Zend_Wildfire_Exception('Singleton instance of Zend_Wildfire_Plugin_FirePhp already exists!');
        }
        if ($class!==null) {
            if (!is_string($class)) {
                throw new Zend_Wildfire_Exception('Third argument is not a class string');
            }
            Zend_Loader::loadClass($class);
            self::$_instance = new $class();
            if (!self::$_instance instanceof Zend_Wildfire_Plugin_FirePhp) {
                self::$_instance = null;
                throw new Zend_Wildfire_Exception('Invalid class to third argument. Must be subclass of Zend_Wildfire_Plugin_FirePhp.');
            }
        } else {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    /**
     * Constructor
     * @return void
     */
    protected function __construct()
    {
        $this->_channel = Zend_Wildfire_Channel_HttpHeaders::getInstance();
        $this->_channel->getProtocol(self::PROTOCOL_URI)->registerPlugin($this);
    }

    /**
     * Get or create singleton instance
     * 
     * @param $skipCreate boolean True if an instance should not be created
     * @return Zend_Wildfire_Plugin_FirePhp
     */
    public static function getInstance($skipCreate=false)
    {  
        if (self::$_instance===null && $skipCreate!==true) {
            return self::init();               
        }
        return self::$_instance;
    }
    
    /**
     * Destroys the singleton instance
     *
     * Primarily used for testing.
     *
     * @return void
     */
    public static function destroyInstance()
    {
        self::$_instance = null;
    }    
    
    /**
     * Enable or disable sending of messages to user-agent.
     * If disabled all headers to be sent will be removed.
     * 
     * @param boolean $enabled Set to TRUE to enable sending of messages. 
     * @return boolean The previous value.
     */
    public function setEnabled($enabled)
    {
        $previous = $this->_enabled;
        $this->_enabled = $enabled;
        if (!$this->_enabled) {
            $this->_messages = array();
            $this->_channel->getProtocol(self::PROTOCOL_URI)->clearMessages($this);
        }
        return $previous;
    }
    
    /**
     * Determine if logging to user-agent is enabled.
     * 
     * @return boolean Returns TRUE if logging is enabled.
     */
    public function getEnabled()
    {
        return $this->_enabled;
    }
            
    /**
     * Logs variables to the Firebug Console
     * via HTTP response headers and the FirePHP Firefox Extension.
     *
     * @param  mixed  $var   The variable to log.
     * @param  string  $label OPTIONAL Label to prepend to the log event.
     * @param  string  $style  OPTIONAL Style of the log event.
     * @return boolean Returns TRUE if the variable was added to the response headers or buffered.
     * @throws Zend_Wildfire_Exception
     */
    public static function send($var, $label=null, $style=null)
    {
        if (self::$_instance===null) {
            self::getInstance();
        }

        if (!self::$_instance->_enabled) {
            return false; 
        }

        if ($var instanceof Zend_Wildfire_Plugin_FirePhp_Message) {

            if ($var->getBuffered()) {
                if (!in_array($var, self::$_instance->_messages)) {
                    self::$_instance->_messages[] = $var;
                }
                return true;              
            }
            
            if ($var->getDestroy()) {
                return false;  
            }

            $style = $var->getStyle();
            $label = $var->getLabel();
            $var = $var->getMessage();
        }
      
        if (!self::$_instance->_channel->isReady()) {
            return false; 
        }

        if ($var instanceof Exception) {

            $var = array('Class'=>get_class($var),
                         'Message'=>$var->getMessage(),
                         'File'=>$var->getFile(),
                         'Line'=>$var->getLine(),
                         'Type'=>'throw',
                         'Trace'=>$var->getTrace());
  
            $style = self::EXCEPTION;
          
        } else
        if ($style==self::TRACE) {
            
            $trace = debug_backtrace();
            if(!$trace) return false;

            for ( $i=0 ; $i<sizeof($trace) ; $i++ ) {
                if (isset($trace[$i]['class']) &&
                    substr($trace[$i]['class'],0,8)!='Zend_Log' &&
                    substr($trace[$i]['class'],0,13)!='Zend_Wildfire') {
                  
                    $i--;
                    break;
                }
            }

            if ($i==sizeof($trace)) {
                $i = 0;
            }

            $var = array('Class'=>$trace[$i]['class'],
                         'Type'=>$trace[$i]['type'],
                         'Function'=>$trace[$i]['function'],
                         'Message'=>(isset($trace[$i]['args'][0]))?$trace[$i]['args'][0]:'',
                         'File'=>(isset($trace[$i]['file']))?$trace[$i]['file']:'',
                         'Line'=>(isset($trace[$i]['line']))?$trace[$i]['line']:'',
                         'Args'=>$trace[$i]['args'],
                         'Trace'=>array_splice($trace,$i+1));
        } else {
            if ($style===null) {
                $style = self::LOG;
            }
        }

        switch ($style) {
            case self::LOG:
            case self::INFO:
            case self::WARN:
            case self::ERROR:
            case self::EXCEPTION:
            case self::TRACE:
            case self::TABLE:
            case self::DUMP:
                break;
            default:
                throw new Zend_Wildfire_Exception('Log style "'.$style.'" not recognized!');
                break;
        }
        
        if ($style == self::DUMP) {
          
          return self::$_instance->_recordMessage(self::STRUCTURE_URI_DUMP,
                                                  array('key'=>$label,
                                                        'data'=>$var));
          
        } else {
          
          if ($label!=null) {
            $var = array($label,$var);
          }
          
          return self::$_instance->_recordMessage(self::STRUCTURE_URI_FIREBUGCONSOLE,
                                                  array('data'=>$var,
                                                        'meta'=>array('Type'=>$style)));
        }
    }
    
    
    /**
     * Record a message with the given data in the given structure
     * 
     * @param string $structure The structure to be used for the data
     * @param array $data The data to be recorded
     * @return boolean Returns TRUE if message was recorded
     * @throws Zend_Wildfire_Exception
     */
    protected function _recordMessage($structure, $data)
    {
        switch($structure) {

            case self::STRUCTURE_URI_DUMP:
            
                if (!isset($data['key'])) {
                    throw new Zend_Wildfire_Exception('You must supply a key.');
                }
                if (!isset($data['data'])) {
                    throw new Zend_Wildfire_Exception('You must supply data.');
                }
                
                return $this->_channel->getProtocol(self::PROTOCOL_URI)->
                           recordMessage($this,
                                         $structure,
                                         array($data['key']=>$data['data']));
                
            case self::STRUCTURE_URI_FIREBUGCONSOLE:
            
                if (!isset($data['meta']) ||
                    !is_array($data['meta']) ||
                    !array_key_exists('Type',$data['meta'])) {
                      
                    throw new Zend_Wildfire_Exception('You must supply a "Type" in the meta information.');
                }
                if (!isset($data['data'])) {
                    throw new Zend_Wildfire_Exception('You must supply data.');
                }
              
                return $this->_channel->getProtocol(self::PROTOCOL_URI)->
                           recordMessage($this,
                                         $structure,
                                         array($data['meta'],
                                               $data['data']));

            default:
                throw new Zend_Wildfire_Exception('Structure of name "'.$structure.'" is not recognized.');
                break;  
        }
        return false;      
    }

    
    
    /*
     * Zend_Wildfire_Plugin_Interface
     */

    /**
     * Get the unique indentifier for this plugin.
     * 
     * @return string Returns the URI of the plugin.
     */
    public function getUri()
    {
        return self::PLUGIN_URI;
    }
    
    /**
     * Flush any buffered data.
     * 
     * @param string $protocolUri The URI of the protocol that should be flushed to
     * @return void
     */
    public function flushMessages($protocolUri)
    {
        if(!$this->_messages || $protocolUri!=self::PROTOCOL_URI) {
            return;
        }

        foreach( $this->_messages as $message ) {
            if (!$message->getDestroy()) {
                $this->send($message->getMessage(), $message->getLabel(), $message->getStyle());
            }
        }
        
        $this->_messages = array();
    }
}
