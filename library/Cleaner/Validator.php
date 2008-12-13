<?php
/**
 * Validation class
 *
 * @author     Peter Voringer <peter.voringer@mayflower.de>
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    $Id$
 * @license
 * @package    Cleaner
 * @link       http://www.thinkforge.org/projects/Cleaner
 * @since      File available since Release 6.0
 *
 */

/**
 * Contains validation methods
 *
 * Contains validation methods
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license
 * @package    Cleaner
 * @link       http://www.thinkforge.org/projects/Cleaner
 * @author     Peter Voringer <peter.voringer@mayflower.de>
 * @since      File available since Release 6.0
 */
class Cleaner_Validator
{
    /**
     * Registered Validator types
     *
     * @var array
     */
    public $validators = array(
        'int'          => 'Int',
        'integer'      => 'Int',
        'alnum'        => 'Alnum',
        'alpha'        => 'Alpha',
        'bool'         => 'Bool',
        'boolean'      => 'Bool',
        'float'        => 'Float',
        'real'         => 'Float',
        'ipv4'         => 'Ipv4',
        'ip'           => 'Ipv4',
        'date'         => 'IsoDate',
        'isodate'      => 'IsoDate',
        'time'         => 'IsoTime',
        'isotime'      => 'IsoTime',
        'timestamp'    => 'IsoTimestamp',
        'isotimestamp' => 'IsoTimestamp',
        'numeric'      => 'Numeric',
        'string'       => 'String',
        'word'         => 'Word'
    );

    /**
     * Validates if value is of type 'Alnum'
     *
     * @param mixed $value     Value to validate
     * @param mixed &$messages Messages generated while validation
     *
     * @return bool sanitized value
     */
    public function validateAlnum($value, &$messages)
    {
        $valid = ctype_alnum((string)$value);

        if (!$valid) {
            $messages->add('INVALID_ALNUM');
        }

        return $valid;
    }

    /**
     * Validates if value is of type 'Alpha'
     *
     * @param mixed $value     Value to validate
     * @param mixed &$messages Messages generated while validation
     *
     * @return bool sanitized value
     */
    public function validateAlpha($value, &$messages)
    {
        $valid = ctype_alpha($value);

        if (!$valid) {
            $messages->add('INVALID_ALPHA');
        }

        return $valid;
    }

    /**
     * Validates if value is of type 'Bool'
     *
     * @param mixed $value     Value to validate
     * @param mixed &$messages Messages generated while validation
     *
     * @return bool sanitized value
     */
    public function validateBool($value, &$messages)
    {
        // PHP booleans
        if ($value === true || $value === false) {
            return true;
        }

        $true  = array('1', 'on', 'true', 't', 'yes', 'y');
        $false = array('0', 'off', 'false', 'f', 'no', 'n');

        // "string" booleans
        $value = strtolower(trim($value));
        if (in_array($value, $true, true) ||
            in_array($value, $false, true)) {
            return true;
        }

        $messages->add('INVALID_BOOL');

        return false;
    }

    /**
     * Validates if value is of type 'Float'
     *
     * @param mixed $value     Value to validate
     * @param mixed &$messages Messages generated while validation
     *
     * @return bool sanitized value
     */
    public function validateFloat($value, &$messages)
    {
        if (is_float($value)) {
            return true;
        }

        // otherwise, must be numeric, and must be same as when cast to float
        $valid = is_numeric($value) && $value == (float) $value;

        if (!$valid) {
            $messages->add('INVALID_FLOAT');
        }

        return $valid;
    }

    /**
     * Validates if value is of type 'Int'
     *
     * @param mixed $value     Value to validate
     * @param mixed &$messages Messages generated while validation
     *
     * @return bool sanitized value
     */
    public function validateInt($value, &$messages)
    {
        if (is_int($value)) {
            return true;
        }

        // otherwise, must be numeric, and must be same as when cast to int
        $valid = (is_numeric($value) && $value == (int) $value);

        if (!$valid) {
            $messages->add('INVALID_INT');
        }

        return $valid;
    }

    /**
     * Validates if value is of type 'Ipv4'
     *
     * @param mixed $value     Value to validate
     * @param mixed &$messages Messages generated while validation
     *
     * @return bool sanitized value
     */
    public function validateIpv4($value, &$messages)
    {
        $result = ip2long($value);

        if ($result == -1 || $result === false) {
            $messages->add('INVALID_IPV4');
            return false;
        } else {
            return true;
        }
    }

    /**
     * Validates if value is of type 'IsoDate'
     *
     * @param mixed $value     Value to validate
     * @param mixed &$messages Messages generated while validation
     *
     * @return bool sanitized value
     */
    public function validateIsoDate($value, &$messages)
    {
        // basic date format
        // yyyy-mm-dd
        $expr = '/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/D';

        // validate
        if (preg_match($expr, $value, $match) &&
            checkdate($match[2], $match[3], $match[1])) {
            return true;
        } else {
            $messages->add('INVALID_ISODATE');
            return false;
        }
    }

    /**
     * Validates if value is of type 'IsoTime'
     *
     * @param mixed $value     Value to validate
     * @param mixed &$messages Messages generated while validation
     *
     * @return bool sanitized value
     */
    public function validateIsoTime($value, &$messages)
    {
        $expr = '/^(([0-1][0-9])|(2[0-3])):[0-5][0-9]:[0-5][0-9]$/D';

        $valid = $this->_filter->validatePregMatch($value, $expr) ||
               ($value == '24:00:00');


        if (!$valid) {
            $messages->add('INVALID_ISOTIME');
        }

        return $valid;
    }

    /**
     * Validates if value is of type 'IsoTimestamp'
     *
     * @param mixed $value     Value to validate
     * @param mixed &$messages Messages generated while validation
     *
     * @return bool sanitized value
     */
    public function validateIsoTimestamp($value, &$messages)
    {

        // correct length?
        if (strlen($value) != 19) {
            $messages->add('INVALID_ISOTIMESTAMP');
            return false;
        }

        // valid date?
        $date = substr($value, 0, 10);
        if (! $this->_filter->validateIsoDate($date)) {
            $messages->add('INVALID_ISOTIMESTAMP');
            return false;
        }

        // valid separator?
        $sep = substr($value, 10, 1);
        if ($sep != 'T' && $sep != ' ') {
            $messages->add('INVALID_ISOTIMESTAMP');
            return false;
        }

        // valid time?
        $time = substr($value, 11, 8);
        if (! $this->_filter->validateIsoTime($time)) {
            $messages->add('INVALID_ISOTIMESTAMP');
            return false;
        }

        // must be ok
        return true;
    }

    /**
     * Validates if value is of type 'Numeric'
     *
     * @param mixed $value     Value to validate
     * @param mixed &$messages Messages generated while validation
     *
     * @return bool sanitized value
     */
    public function validateNumeric($value, &$messages)
    {
        $valid = is_numeric($value);

        if (!$valid) {
            $messages->add('INVALID_NUMERIC');
        }

        return $valid;
    }

    /**
     * Validates if value is of type 'String'
     *
     * @param mixed $value     Value to validate
     * @param mixed &$messages Messages generated while validation
     *
     * @return bool sanitized value
     */
    public function validateString($value, &$messages)
    {
        $valid = is_scalar($value);

        if (!$valid) {
            $messages->add('INVALID_NUMERIC');
        }

        return $valid;
    }

    /**
     * Validates if value is of type 'Word'
     *
     * @param mixed $value     Value to validate
     * @param mixed &$messages Messages generated while validation
     *
     * @return bool sanitized value
     */
    public function validateWord($value, &$messages)
    {
        $expr = '/^\w+$/D';
        $valid = $this->_filter->validatePregMatch($value, $expr);

        if (!$valid) {
            $messages->add('INVALID_WORD');
        }

        return $valid;
    }
}