<?php
/**
 * Cage class
 * 
 * @author     Peter Voringer <peter.voringer@mayflower.de>
 * @copyright  2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    CVS: $Id$
 * @license    
 * @package    Inspector
 * @link       http://www.thinkforge.org/projects/inspector
 * @since      File available since Release 1.0
 * 
 */

/**
 * Abstraction/Wrapper of some scope ($_GET, $_POST, ...)
 * 
 * Wrapped some scope to ensure right security usage of parameters
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: <package_version>
 * @license    
 * @package    Inspector
 * @link       http://www.thinkforge.org/projects/inspector
 * @author     Peter Voringer <peter.voringer@mayflower.de>
 * @since      File available since Release 1.0
 */
class Inspector_Cage
{
    /**
     * Key/Identifier of scope
     *
     * @var string
     */
    protected $_key;
    
    /**
     * Wrapped scope
     *
     * @var array
     */
    protected $_scope;
    
    /**
     * Get a wrapped Scope
     *
     * @param array  &$scope Scope to wrap
     * @param string $key    Name of Scope
     * 
     * @return Inspector_Cage Wrapped Scope
     */
    public static function getInstance(&$scope, $key)
    {
        return new Inspector_Cage($scope, $key);
    }
    
    /**
     * Creates Instance of a wrapped scope
     *
     * @param array  &$scope Scope to wrap
     * @param string $key    Name of Scope
     * 
     * @return void
     * 
     */
    protected function __construct(&$scope, $key)
    {
        $this->_key   = $key;
        $this->_scope = $scope;
    }
    
    /**
     * Validates an item in the wrapped scope for a certain type
     *
     * @param string $type      Type against parameter/item should be validated
     * @param string $name      Name of parameter / Name of item in scope
     * @param object &$messages Messages generated while validation
     * @param bool   $empty     Must parameter/item be not null or empty
     * 
     * @return bool true=>valid, false=>invalid
     */
    public function validate($type, $name, &$messages, $empty = false)
    {
        $value = isset($this->_scope[$name]) ? $this->_scope[$name] : null;
        return Inspector::validate($type, $value, $messages, $empty);
    }
    
    /**
     * Sanitizes an item in the wrapped scope to a certain type
     *
     * @param string $type      Type of parameter/item to sanitize
     * @param string $name      Name of parameter / Name of item in scope
     * @param object &$messages Messages generated while sanitizing
     * @param mixed  $default   Return value, if parameter/item is null/empty/..
     * @param bool   $empty     Must parameter/item be not null or empty
     * 
     * @return mixed
     */
    public function sanitize($type, $name, &$messages, $default = null, 
    $empty = false)
    {
        $value = isset($this->_scope[$name]) ? $this->_scope[$name] : null;
        return Inspector::sanitize($type, $value, $messages, $default, $empty);
    }
    
    /**
     * Validates an item in the wrapped scope for a certain type and if 
     * invalid sanitizes the value of the parameter/item
     *
     * @param string $type     Type of parameter/item to validate and/or 
     *                         sanitize
     * @param string $name     Name of parameter / Name of item in scope
     * @param bool   $empty    Must parameter/item be not null or empty
     * @param mixed  $default  Return value, if parameter/item and/or sanitizes 
     *                         parameter/item is null/empty/notset/..
     * @param bool   $sanitize Wheather sanitize value of parameter/item, 
     *                         when value is invalid
     * 
     * @return Inspector_Parameter Instance, representing selected 
     *                             parameter/item in Scope
     */
    public function getParameter($type, $name, $empty = false, $default = null,
    $sanitize = true)
    {
        $messages = Inspector_Engine::getMessages();
        
        if (!isset($this->_scope[$name]) || 
            Inspector_Util::isBlank($this->_scope[$name])) {
            
            if (!$empty) {
                $messages->add('INVALID_REQUIRED');
            }
            
            return new Inspector_Parameter(null, null, $default, 
                                           $empty, false, true, 
                                           $messages, 
                                           $type, $name, $this->_key);
        }
    
        $engine = Inspector_Engine::getInstance();

        $value = $this->_scope[$name];
        
        if ($engine->validate($type, $value, $messages)) {
            return new Inspector_Parameter(null, $value, $default, 
                                           true, false, false, 
                                           $messages, 
                                           $type, $name, $this->_key);
        }
        
        if (!$sanitize) {
            return new Inspector_Parameter(null, $value, $default, 
                                           false, false, false, 
                                           $messages, 
                                           $type, $name, $this->_key);
        }
        
        $clean = $engine->sanitze($type, $value, $messages);
        
        if (is_null($clean)) {
            
            if (!$empty) {
                $messages->add('SANITIZE_DEFAULT');
            }
            
            return new Inspector_Parameter(null, null, $default, 
                                           $empty, true, true, 
                                           $messages, 
                                           $type, $name, $this->_key);
        }
        
        
        return new Inspector_Parameter($clean, $value, $default, 
                                       false, true, false, 
                                       $messages, 
                                       $type, $name, $this->_key);
        
    }
    
}

