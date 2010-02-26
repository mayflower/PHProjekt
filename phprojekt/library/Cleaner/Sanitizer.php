<?php
/**
 * Sanitizing class
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
 * Contains sanitizing methods
 *
 * Contains sanitizing methods
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license
 * @package    Cleaner
 * @link       http://www.thinkforge.org/projects/Cleaner
 * @author     Peter Voringer <peter.voringer@mayflower.de>
 * @since      File available since Release 6.0
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
        'datetime'     => 'IsoTimestamp',
        'timestamp'    => 'IsoTimestamp',
        'isotimestamp' => 'IsoTimestamp',
        'numeric'      => 'Numeric',
        'string'       => 'String',
        'word'         => 'Word',
        'html'         => 'Html',
        'xss'          => 'Xss',
        'filter'       => 'Filter'
    );

    /**
     * Sanitize value to 'Word'
     *
     * @param mixed $value Value to sanitizes
     *
     * @return mixed sanitized value
     */
    public function sanitizeWord($value)
    {
        return preg_replace('/\W/', '', $value);
    }

    /**
     * Sanitize value to 'Word'
     *
     * @param mixed $value Value to sanitizes
     *
     * @return mixed sanitized value
     */
    public function sanitizeString($value)
    {
        return (string) $value;
    }

    /**
     * Sanitize value to 'Numeric'
     *
     * @param mixed $value Value to sanitizes
     *
     * @return mixed sanitized value
     */
    public function sanitizeNumeric($value)
    {
        $instance       = Cleaner_Engine::getInstance();
        $floatSanitizer = $instance->getSanitizer('float');

        return (string) $floatSanitizer->sanitize($value);
    }

    /**
     * Sanitize value to 'IsoTimestamp'
     *
     * @param mixed $value Value to sanitizes
     *
     * @return mixed sanitized value
     */
    public function sanitizeIsoTimestamp($value)
    {
        $format = 'Y-m-d H:i:s';
        if (is_int($value)) {
            return date($format, $value);
        } else {
            // Remove GMT declaration if exists
            $value = preg_replace('/ GMT([-+0-9])+ \((\D)*\)/', '', $value);
            $time  = strtotime($value);
            if ($time === false) {
                return null;
            }
            return date($format, $time);
        }
    }

    /**
     * Sanitize value to 'IsoTime'
     *
     * @param mixed $value Value to sanitizes
     *
     * @return mixed sanitized value
     */
    public function sanitizeIsoTime($value)
    {
        $format = 'H:i:s';
        if (is_int($value)) {
            return date($format, $value);
        } else {
            $expr = "/([A-Za-z0-9 \r\t])?([0-9]{2}):([0-9]{2}):?([0-9]{2})?(A-Za-z0-9 \r\t])?/";
            if (preg_match($expr, $value, $regs)) {
                return date($format, mktime($regs[2], $regs[3], 0, date("m"), date("d"), date("Y")));
            } else {
                return null;
            }
        }
    }

    /**
     * Sanitize value to 'IsoDate'
     *
     * @param mixed $value Value to sanitizes
     *
     * @return mixed sanitized value
     */
    public function sanitizeIsoDate($value)
    {
        $format = 'Y-m-d';

        if (is_int($value)) {
            return gmdate($format, $value);
        } else {
            // Remove GMT declaration if exists
            $value = preg_replace('/ GMT([-+0-9])+ \((\D)*\)/', '', $value);
            $time  = strtotime($value);
            if ($time === false || $time === -1) {
                return null;
            }
            return date($format, $time);
        }
    }

    /**
     * Sanitize value to 'Ipv4'
     *
     * @param mixed $value Value to sanitizes
     *
     * @return mixed sanitized value
     */
    public function sanitizeIpv4($value)
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
     * @param mixed $value Value to sanitizes
     *
     * @return mixed sanitized value
     */
    public function sanitizeInt($value)
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
     * @param mixed $value Value to sanitizes
     *
     * @return mixed sanitized value
     */
    public function sanitizeHtml($value)
    {
        require_once PHPR_LIBRARY_PATH . DIRECTORY_SEPARATOR .
            'HTMLPurifier' . DIRECTORY_SEPARATOR . 'HTMLPurifier.standalone.php';

        $config = HTMLPurifier_Config::createDefault();
        $config->set('Core', 'Encoding', 'UTF-8');
        $config->set('HTML', 'Doctype', 'XHTML 1.0 Transitional');
        $config->set('HTML', 'AllowedAttributes', '*.style, *.size, *.href, *.alt, *.src');
        $allowedProperties = 'font-weight, font-style, text-align, text-decoration, color, font-size, '
            . 'background-color, font-family';
        $config->set('CSS','AllowedProperties', $allowedProperties);
        $config->set('Cache', 'SerializerPath', PHPR_TEMP_PATH);
        $purifier = new HTMLPurifier($config);

        if (get_magic_quotes_gpc()) {
            $value = stripslashes($value);
        }

        return addslashes($purifier->purify($value));
    }

    /**
     * Sanitize value to 'Float'
     *
     * @param mixed $value Value to sanitizes
     *
     * @return mixed sanitized value
     */
    public function sanitizeFloat($value)
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
     * @param mixed $value Value to sanitizes
     *
     * @return mixed sanitized value
     */
    public function sanitizeBool($value)
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
     * @param mixed $value Value to sanitizes
     *
     * @return mixed sanitized value
     */
    public function sanitizeAlpha($value)
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
     * @param mixed $value Value to sanitizes
     *
     * @return mixed sanitized value
     */
    public function sanitizeAlnum($value)
    {
        $result = preg_replace('/[^a-z0-9]/i', '', $value);

        if ($result == '') {
            return null;
        } else {
            return $result;
        }
    }

    /**
     * Sanitize value to 'Xss'
     *
     * @param mixed $value Value to sanitizes
     *
     * @return mixed sanitized value
     */
    public function sanitizeXss($value)
    {
        return htmlentities(strip_tags((string) $value));
    }


    /**
     * Sanitize value for use in a filter like search
     *
     * @param mixed $value Value to sanitizes
     *
     * @return mixed sanitized value
     */
    public function sanitizeFilter($value)
    {
        // Allow letters, numbers, '-', ':' and '_'
        $result = preg_replace('/[^\w\s\-\:]/', '', $value);

        if ($result == '') {
            return null;
        } else {
            return $result;
        }
    }
}
