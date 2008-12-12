<?php
/**
 * Class for valid the data of each field
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
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    CVS: $Id: Abstract.php 809 2008-06-16 15:49:23Z polidor $
 * @link       http://www.phprojekt.com
 * @author     Gustavao Solt <solt@mayflower.de>
 * @since      File available since Release 6.0
 */

/**
 * This class recive some fields, the data and one class for check,
 * and will validate each field deppend on the type and other restrictions
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavao Solt <solt@mayflower.de>
 */
class Phprojekt_Model_Validate
{
    /**
     * Error object
     *
     * @var Phprojekt_Error
     */
    public $error = null;

    /**
     * Translate class
     *
     * @var Phprojekt_Language
     */
    private $_translate = null;

    public function __construct()
    {
        $this->error      = new Phprojekt_Error();
        $this->_translate = Zend_Registry::get('translate');
    }

    /**
     * Return if the values are valid or not
     *
     * @return boolean
     */
    public function recordValidate($class, $data, $fields)
    {
        $validated = true;

        foreach ($data as $varname => $value) {
            if (isset($class->$varname)) {
                /* Validate with the database_manager stuff */
                foreach ($fields as $field) {
                    if ($field['key'] == $varname) {
                        $validations = $field;

                        if (true === $validations['required']) {
                            $error = $this->validateIsRequired($value);
                            if (null !== $error) {
                                $validated = false;
                                $this->error->addError(array(
                                'field'   => $this->_translate->translate($varname),
                                'message' => $this->_translate->translate($error)));
                                break;
                            }
                        }

                        $error = $this->validateValue($class, $varname, $value);
                        if (false === $error) {
                            $validated = false;
                            $this->error->addError(array(
                            'field'   => $this->_translate->translate($varname),
                            'message' => $this->_translate->translate("Invalid Format")));
                        }
                        break;
                    }
                }

                /* Validate an special fieldName */
                $validater  = 'validate' . ucfirst($varname);
                if ($validater != 'validateIsRequired') {
                    if (in_array($validater, get_class_methods($class))) {
                        $error = call_user_method($validater, $class, $value);
                        if (null !== $error) {
                            $validated = false;
                            $this->error->addError(array(
                            'field'   => $this->_translate->translate($varname),
                            'message' => $this->_translate->translate($error)));
                        }
                    }
                }
            }
        }
        return $validated;
    }

   /**
     * Validate a value use the database type of the field
     *
     * @param string $varname Name of the field
     * @param mix    $value   Value to validate
     *
     * @return string Error message or null if is valid
     */
    public function validateValue($class, $varname, $value)
    {
        $info  = $class->info();
        $valid = true;
        $messages = null;
        if (isset($info['metadata'][$varname]) && !empty($value)) {

            $type = $info['metadata'][$varname]['DATA_TYPE'];

            switch ($type) {
                case 'int':
                    $valid = Cleaner::validate('integer', $value, $messages, false);
                    break;
                case 'float':
                    $valid = Cleaner::validate('float', $value, $messages, false);
                    break;
                case 'date':
                    $valid = Cleaner::validate('date', $value, $messages, false);
                    break;
                case 'time':
                    // $valid = Cleaner::validate('timestamp', $value, $messages, false);
                    break;
                case 'timestamp':
                    $valid = Cleaner::validate('timestamp', $value, $messages, false);
                    break;
                default:
                    $valid = Cleaner::validate('string', $value, $messages, true);
                    break;
            }
        }

        return $valid !== false;
    }

    /**
     * Validate required fields
     * return the msg error if exists
     *
     * @param mix $value The value to check
     *
     * @return string Error string or null
     */
    public function validateIsRequired($value)
    {
        $error = null;
        if (empty($value)) {
            $error = 'Is a required field';
        }
        return $error;
    }
}
