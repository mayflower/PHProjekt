<?php
/**
 * Cage class.
 *
 * Abstraction/Wrapper of some scope ($_GET, $_POST, ...).
 *
 * Wrapped some scope to ensure right security usage of parameters.
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
 * @category  PHProjekt
 * @package   Cleaner
 * @copyright Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license   LGPL v3 (See LICENSE file)
 * @link      http://www.thinkforge.org/projects/Cleaner
 * @since     File available since Release 6.0
 * @version   Release: 6.1.0
 * @author    Peter Voringer <peter.voringer@mayflower.de>
 */

/**
 * Cage class.
 *
 * Abstraction/Wrapper of some scope ($_GET, $_POST, ...).
 *
 * Wrapped some scope to ensure right security usage of parameters.
 *
 * @category  PHProjekt
 * @package   Cleaner
 * @copyright Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license   LGPL v3 (See LICENSE file)
 * @link      http://www.thinkforge.org/projects/Cleaner
 * @since     File available since Release 6.0
 * @version   Release: 6.1.0
 * @author    Peter Voringer <peter.voringer@mayflower.de>
 */
class Cleaner_Cage
{
    /**
     * Key/Identifier of scope.
     *
     * @var string
     */
    protected $_key;

    /**
     * Wrapped scope.
     *
     * @var array
     */
    protected $_scope;

    /**
     * Get a wrapped Scope.
     *
     * @param array  &$scope Scope to wrap.
     * @param string $key    Name of Scope.
     *
     * @return Cleaner_Cage Wrapped Scope.
     */
    public static function getInstance(&$scope, $key)
    {
        return new Cleaner_Cage($scope, $key);
    }

    /**
     * Creates Instance of a wrapped scope.
     *
     * @param array  &$scope Scope to wrap.
     * @param string $key    Name of Scope.
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
     * Validates an item in the wrapped scope for a certain type.
     *
     * @param string  $type     Type against parameter/item should be validated.
     * @param string  $name     Name of parameter / Name of item in scope.
     * @param boolean $empty    Must parameter/item be not null or empty.
     * @param object  $messages Messages generated while validation.
     *
     * @return boolean True for valid.
     */
    public function validate($type, $name, $empty = false, $messages = null)
    {
        $value = isset($this->_scope[$name]) ? $this->_scope[$name] : null;
        return Cleaner::validate($type, $value, $empty, $messages);
    }

    /**
     * Sanitizes an item in the wrapped scope to a certain type.
     *
     * @param string  $type     Type of parameter/item to sanitize.
     * @param string  $name     Name of parameter / Name of item in scope.
     * @param mixed   $default  Return value, if parameter/item is null/empty/...
     * @param boolean $empty    Must parameter/item be not null or empty.
     * @param object  $messages Messages generated while sanitizing.
     *
     * @return mixed Sanitized value.
     */
    public function sanitize($type, $name, $default = null, $empty = false, $messages = null)
    {
        $value = isset($this->_scope[$name]) ? $this->_scope[$name] : null;
        return Cleaner::sanitize($type, $value, $default, $empty, $messages);
    }

    /**
     * Validates an item in the wrapped scope for a certain type and if
     * invalid sanitizes the value of the parameter/item.
     *
     * @param string  $type     Type of parameter/item to validate and/or sanitize.
     * @param string  $name     Name of parameter / Name of item in scope.
     * @param boolean $empty    Must parameter/item be not null or empty.
     * @param mixed   $default  Return value, if parameter/item and/or sanitizesparameter/item is null/empty/notset/...
     * @param boolean $sanitize Wheather sanitize value of parameter/item, when value is invalid.
     *
     * @return Cleaner_Parameter Instance, representing selected parameter/item in Scope.
     */
    public function getParameter($type, $name, $empty = false, $default = null, $sanitize = true)
    {
        $messages = Cleaner_Engine::getMessages();

        if (!isset($this->_scope[$name]) ||
            Cleaner_Util::isBlank($this->_scope[$name])) {

            if (!$empty) {
                $messages->add('INVALID_REQUIRED');
            }

            return new Cleaner_Parameter(null, null, $default, $empty, false, true, $messages,
                $type, $name, $this->_key);
        }

        $engine = Cleaner_Engine::getInstance();

        $value = $this->_scope[$name];

        if ($engine->validate($type, $value, $messages)) {
            return new Cleaner_Parameter(null, $value, $default, true, false, false, $messages,
                $type, $name, $this->_key);
        }

        if (!$sanitize) {
            return new Cleaner_Parameter(null, $value, $default, false, false, false, $messages,
                $type, $name, $this->_key);
        }

        $clean = $engine->sanitze($type, $value, $messages);

        if (is_null($clean)) {
            if (!$empty) {
                $messages->add('SANITIZE_DEFAULT');
            }

            return new Cleaner_Parameter(null, null, $default, $empty, true, true, $messages,
                $type, $name, $this->_key);
        }

        return new Cleaner_Parameter($clean, $value, $default, false, true, false, $messages,
            $type, $name, $this->_key);
    }
}
