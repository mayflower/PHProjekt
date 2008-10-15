<?php
/**
 * Sanitizing class
 * 
 * @author     Peter Voringer <peter.voringer@mayflower.de>
 * @copyright  2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    CVS: $Id$
 * @license    
 * @package    Cleaner
 * @link       http://www.thinkforge.org/projects/Cleaner
 * @since      File available since Release 1.0
 * 
 */

/**
 * Contains sanitizing methods
 * 
 * Contains sanitizing methods
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: <package_version>
 * @license    
 * @package    Cleaner
 * @link       http://www.thinkforge.org/projects/Cleaner
 * @author     Peter Voringer <peter.voringer@mayflower.de>
 * @since      File available since Release 1.0
 */
class Cleaner_Sanitizer
{
    /**
     * Registered Types for Sanitizing
     *
     * @var unknown_type
     */
    public $sanitizers = array(
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
        'word'         => 'Word',
        'html'         => 'Html'
    );
    
    /**
     * Sanitize value to 'Word'
     *
     * @param mixed $value     Value to sanitizes
     * @param mixed &$messages Messages generated while sanitizing
     * 
     * @return mixed sanitized value
     */
    public function sanitizeWord($value, &$messages)
    {
        return preg_replace('/\W/', '', $value);
    }
    
    /**
     * Sanitize value to 'Word'
     *
     * @param mixed $value     Value to sanitizes
     * @param mixed &$messages Messages generated while sanitizing
     * 
     * @return mixed sanitized value
     */
    public function sanitizeString($value, &$messages)
    {
        return (string) $value;
    }
    
    /**
     * Sanitize value to 'Numeric'
     *
     * @param mixed $value     Value to sanitizes
     * @param mixed &$messages Messages generated while sanitizing
     * 
     * @return mixed sanitized value
     */
    public function sanitizeNumeric($value, &$messages)
    {
        $instance       = Cleaner_Engine::getInstance();
        $floatSanitizer = $instance->getSanitizer('float');
        
        return (string) $floatSanitizer->sanitize($value, $messages);
    }
    
    /**
     * Sanitize value to 'IsoTimestamp'
     *
     * @param mixed $value     Value to sanitizes
     * @param mixed &$messages Messages generated while sanitizing
     * 
     * @return mixed sanitized value
     */
    public function sanitizeIsoTimestamp($value, &$messages)
    {
        $format = 'Y-m-d H:i:s';
        if (is_int($value)) {
            return date($format, $value);
        } else {
            $time = strtotime($value);
            if ($time === false) {
                return null;
            }
            return date($format, $time);
        }        
    }
    
    /**
     * Sanitize value to 'IsoTime'
     *
     * @param mixed $value     Value to sanitizes
     * @param mixed &$messages Messages generated while sanitizing
     * 
     * @return mixed sanitized value
     */    
    public function sanitizeIsoTime($value, &$messages)
    {       
        $format = 'H:i:s'; 
        if (is_int($value)) {
            return date($format, $value);
        } else {
            if (ereg("([A-Za-z0-9 \r\t])?([0-9]{1,2}):([0-9]{1,2}):?([0-9]{1,2})?(A-Za-z0-9 \r\t])?", $value, $regs)) {
                return date($format, mktime($regs[2], $regs[3], 0, date("m"), date("d"), date("Y")));                  
            } else {
                return null;
            }
        }
    }
    
    /**
     * Sanitize value to 'IsoDate'
     *
     * @param mixed $value     Value to sanitizes
     * @param mixed &$messages Messages generated while sanitizing
     * 
     * @return mixed sanitized value
     */
    public function sanitizeIsoDate($value, &$messages)
    {
        $format = 'Y-m-d';
        
        if (is_int($value)) {
            return date($format, $value);
        } else {
            $time = strtotime($value);
            if ($time === false) {
                return null;
            }
            return date($format, $time);
        }
    }
    
    /**
     * Sanitize value to 'Ipv4'
     *
     * @param mixed $value     Value to sanitizes
     * @param mixed &$messages Messages generated while sanitizing
     * 
     * @return mixed sanitized value
     */
    public function sanitizeIpv4($value, &$messages)
    {
        $long = ip2long($value);
        
        if ($long === false) {
            return null;
        }
        
        $result = long2ip($long);
    }
    
    /**
     * Sanitize value to 'Int'
     *
     * @param mixed $value     Value to sanitizes
     * @param mixed &$messages Messages generated while sanitizing
     * 
     * @return mixed sanitized value
     */
    public function sanitizeInt($value, &$messages)
    {
        // sanitize numerics and non-strings
        if ((! is_string($value)) || (is_numeric($value))) {
            // we double-cast here to honor scientific notation.
            // (int) 1E5 == 1, but (int) (float) 1E5 == 100000
            return (int) (float) $value;
        }
        
        // it's a non-numeric string, attempt to extract an integer from it.
        
        // remove all chars except digit and minus.
        // this removes all + signs; any - sign takes precedence because ...
        //     0 + -1 = -1
        //     0 - +1 = -1
        // ... at least it seems that way to me now.
        $value = preg_replace('/[^0-9-]/', '', $value);
        
        // remove all trailing minuses
        $value = rtrim($value, '-');
        
        // pre-empt further checks if already empty
        if ($value == '') {
            return null;
        }
        
        // remove all minuses not at the front
        $isNegative = ($value[0] == '-');
        $value = str_replace('-', '', $value);
        if ($isNegative) {
            $value = '-' . $value;
        }
        
        // looks like we're done
        return (int) $value;
    }
    
    /**
     * Sanitize value to 'HTML' (Purifier)
     *
     * @param mixed $value     Value to sanitizes
     * @param mixed &$messages Messages generated while sanitizing
     * 
     * @return mixed sanitized value
     */
    public function sanitizeHtml($value, &$messages)
    {
         return HTMLPurifier::getInstance()->purify($value);
    }
    
    /**
     * Sanitize value to 'Float'
     *
     * @param mixed $value     Value to sanitizes
     * @param mixed &$messages Messages generated while sanitizing
     * 
     * @return mixed sanitized value
     */
    public function sanitizeFloat($value, &$messages)
    {
        // normal sanitize.  non-string, or already numeric, get converted in
        // place.
        if (! is_string($value) || is_numeric($value)) {
            return (float) $value;
        }
        
        // it's a non-numeric string, attempt to extract a float from it.
        
        // remove all + signs; any - sign takes precedence because ...
        //     0 + -1 = -1
        //     0 - +1 = -1
        // ... at least it seems that way to me now.
        $value = str_replace('+', '', $value);
        
        // reduce multiple decimals and minuses
        $value = preg_replace('/[\.-]{2,}/', '.', $value);
        
        // remove all decimals without a digit or minus next to them
        $value = preg_replace('/([^0-9-]\.[^0-9])/', '', $value);
        
        // remove all chars except digit, decimal, and minus
        $value = preg_replace('/[^0-9\.-]/', '', $value);
        
        // remove all trailing decimals and minuses
        $value = rtrim($value, '.-');
        
        // pre-empt further checks if already empty
        if ($value == '') {
            return null;
        }
        
        // remove all minuses not at the front
        $isNegative = ($value[0] == '-');
        $value      = str_replace('-', '', $value);
        if ($isNegative) {
            $value = '-' . $value;
        }
        
        // remove all decimals but the first
        $pos   = strpos($value, '.');
        $value = str_replace('.', '', $value);
        if ($pos !== false) {
            $value = substr($value, 0, $pos)
                   . '.'
                   . substr($value, $pos);
        }
        
        // looks like we're done
        return (float) $value;
    }

    /**
     * Sanitize value to 'Boolean'
     *
     * @param mixed $value     Value to sanitizes
     * @param mixed &$messages Messages generated while sanitizing
     * 
     * @return mixed sanitized value
     */
    public function sanitizeBool($value, &$messages)
    {
        // PHP booleans
        if ($value === true || $value === false) {
            return $value;
        }
        
        $true  = array('1', 'on', 'true', 't', 'yes', 'y');
        $false = array('0', 'off', 'false', 'f', 'no', 'n');
        
        // "string" booleans
        $value = strtolower(trim($value));
        if (in_array($value, $true)) {
            return true;
        }
        if (in_array($value, $false)) {
            return false;
        }
        
        return null;
    }
    
    /**
     * Sanitize value to 'Alpha'
     *
     * @param mixed $value     Value to sanitizes
     * @param mixed &$messages Messages generated while sanitizing
     * 
     * @return mixed sanitized value
     */
    public function sanitizeAlpha($value, &$messages)
    {
        $result = preg_replace('/[^a-z]/i', '', $value);
        
        if ($result == '') {
            return null;
        } else {
            return $result;
        }
    }
    
    /**
     * Sanitize value to 'Alnum'
     *
     * @param mixed $value     Value to sanitizes
     * @param mixed &$messages Messages generated while sanitizing
     * 
     * @return mixed sanitized value
     */
    public function sanitizeAlnum($value, &$messages)
    {
        $result = preg_replace('/[^a-z0-9]/i', '', $value);
        
        if ($result == '') {
            return null;
        } else {
            return $result;
        }
    }
}