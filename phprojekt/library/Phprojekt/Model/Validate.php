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
 * @subpackage Core
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
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
 * @subpackage Core
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

    public function __construct()
    {
        $this->error = Phprojekt_Loader::getLibraryClass('Phprojekt_Error');
    }

    /**
     * Define the clone function for prevent the same point to same object.
     *
     * @return void
     */
    public function __clone()
    {
        $this->error = Phprojekt_Loader::getLibraryClass('Phprojekt_Error');
    }

    /**
     * Return if the values are valid or not
     *
     * @return boolean
     */
    public function recordValidate($class, $data, $fields)
    {
        $valid = true;

        foreach ($data as $varname => $value) {
            if (isset($class->$varname)) {
                /* Validate with the database_manager stuff */
                foreach ($fields as $field) {
                    if ($field['key'] == $varname) {
                        $validations = $field;

                        if (true === $validations['required']) {
                            $error = $this->validateIsRequired($value);
                            if (null !== $error) {
                                $valid = false;
                                $this->error->addError(array(
                                    'field'   => $varname,
                                    'label'   => Phprojekt::getInstance()->translate($field['label']),
                                    'message' => Phprojekt::getInstance()->translate($error)));
                                break;
                            }
                        }

                        $error = $this->validateValue($class, $varname, $value, $field['length']);
                        if (null !== $error) {
                            $valid = false;
                            $this->error->addError(array(
                                'field'   => $varname,
                                'label'   => Phprojekt::getInstance()->translate($field['label']),
                                'message' => $error));
                        }
                        break;
                    }
                }

                /* Validate an special fieldName */
                $validator  = 'validate' . ucfirst($varname);
                if ($validator != 'validateIsRequired') {
                    if (method_exists($class, $validator)) {
                        $error = call_user_func(array($class, $validator), $value);
                        if (null !== $error) {
                            $valid = false;
                            $this->error->addError(array(
                                'field'   => $varname,
                                'label'   => Phprojekt::getInstance()->translate($field['label']),
                                'message' => Phprojekt::getInstance()->translate($error)));
                        }
                    }
                }
            }
        }
        return $valid;
    }

    /**
     * Validates a value using the database type of the field
     *
     * @param Phprojekt_Model_Interface  $class      Model object
     * @param string                     $varname    Name of the field
     * @param mix                        $value      Value to validate
     * @param int                        $maxLength  Maximum length allowed
     *
     * @return string Error message or null if is valid
     */
    public function validateValue(Phprojekt_Model_Interface $class, $varname, $value, $maxLength)
    {
        $info  = $class->info();
        $valid = true;
        $error = null;

        if (isset($info['metadata'][$class->convertVarToSql($varname)]) && !empty($value)) {
            $type = $info['metadata'][$class->convertVarToSql($varname)]['DATA_TYPE'];
            switch ($type) {
                case 'int':
                    $valid = Cleaner::validate('integer', $value, false);
                    break;
                case 'float':
                    $valid = Cleaner::validate('float', $value, false);
                    break;
                case 'date':
                    $valid = Cleaner::validate('date', $value, false);
                    break;
                case 'time':
                    // $valid = Cleaner::validate('timestamp', $value, false);
                    break;
                case 'timestamp':
                    $valid = Cleaner::validate('timestamp', $value, false);
                    break;
                case 'varchar':
                    $valid = Cleaner::validate('string', $value, true);
                    if (strlen($value) > $maxLength) {
                        $error = Phprojekt::getInstance()->translate("Maximum length exceeded for field.");
                    }
                    break;
                case 'text':
                default:
                    $valid = Cleaner::validate('string', $value, true);
                    break;
                    break;
            }
        }
        if ($valid == false && $error === null) {
            $error = Phprojekt::getInstance()->translate("Invalid Format");
        }
        return $error;
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
