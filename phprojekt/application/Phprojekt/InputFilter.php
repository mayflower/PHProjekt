<?php
/**
 * Cleaner class for prevent injections in the input fields
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Filter class for prevent XSS and SQL injections
 *
 * The class is bassed on the Daniel Morris <dan@rootcube.com> class
 * with some tiny changes for adapt it to the Zend Framework
 *
 * The class have 4 parameters:
 * _tagsArray: User defined tags
 * _attrArray: User defined attributes
 * _tagsMethod: How to use the _tagsArray
 * _attrMethod: How to use the _attrArray
 * _xssAuto: Use or not the XSS cleaner
 *
 * You can process a simple value on an array of values.
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */

class Phprojekt_InputFilter
{
    /**
     * User defined tags
     * By default is empty
     *
     * @var array
     */
    protected $_tagsArray;

    /**
     * User defined attributes
     * By default is empty
     *
     * @var array
     */
    protected $_attrArray;

    /**
     * Type of use of the tags
     * 0 = remove ALL, BUT these tags don´t (default)
     * 1 = remove ONLY these tags
     *
     * @var integer
     */
    protected $_tagsMethod;

    /**
     * Type of use of the attributes
     * 0 = remove ALL, BUT these attributes don´t (default)
     * 1 = remove ONLY these attributes
     *
     * @var integer
     */
    protected $_attrMethod;

    /**
     * Type of use of xss filter
     * 1 = remove all identified problem tags (default)
     * 0 = turn this feature off
     *
     * @var integer
     */
    protected $_xssAuto;

    /**
     * Black list of tags that will be deleted
     *
     * @var array
     */
    protected $_tagBlacklist = array();

    /**
     * Black list of attributes that will be deleted
     *
     * Also will strip ALL event handlers
     *
     * @var array
     */
    protected $_attrBlacklist = array();

    /**
     * Constructor for inputFilter class.
     *
     * @param array $config Config for the functions
     *         tagsArray     => List of user-defined tags
     *         attrArray     => List of user-defined attributes
     *         tagsMethod    => 0 = allow just user-defined, 1 = allow all but user-defined
     *         attrMethod    => 0 = allow just user-defined, 1 = allow all but user-defined
     *         xssAuto       => 0 = only auto clean essentials, 1 = allow clean blacklisted tags/attr
     *         tagBlacklist  => Black list of tags that will be deleted<br>
     *         attrBlacklist => Black list of attributes that will be deleted
     */
    public function __construct($config)
    {
        $tagsArray = (isset($config['tagsArray'])) ? $config['tagsArray'] : array();
        $attrArray = (isset($config['attrArray'])) ? $config['attrArray'] : array();

        /* make sure user defined arrays are in lowercase */
        for ($i = 0; $i < count($tagsArray); $i++) {
            $tagsArray[$i] = strtolower($tagsArray[$i]);
        }
        for ($i = 0; $i < count($attrArray); $i++) {
            $attrArray[$i] = strtolower($attrArray[$i]);
        }

        $this->_tagsArray     = (array) $tagsArray;
        $this->_attrArray     = (array) $attrArray;
        $this->_tagsMethod    = (isset($config['tagsMethod'])) ? $config['tagsMethod'] : 0;
        $this->_attrMethod    = (isset($config['attrMethod'])) ? $config['attrMethod'] : 0;
        $this->_xssAuto       = (isset($config['xssAuto'])) ? $config['xssAuto'] : 1;
        $this->_tagBlacklist  = (isset($config['tagBlacklist'])) ? $config['tagBlacklist'] : array();
        $this->_attrBlacklist = (isset($config['attrBlacklist'])) ? $config['attrBlacklist'] : array();
	}

	/**
	 * Processes for XSS and specified bad code.
	 *
	 * Process an array of strings or a simple string
	 * filtering element for XSS and other bad code.
	 *
	 * @param mixed $source Input string/array-of-string to be 'cleaned'
	 *
	 * @return String Cleaned version of input parameter
	 */
    public function process($source)
    {
        if (is_array($source)) {
            foreach($source as $key => $value) {
                if (is_string($value)) {
                    $source[$key] = $this->_remove($this->_decode($value));
                }
            }
            return $source;
        } else if (is_string($source)) {
            return $this->_remove($this->_decode($source));
        } else {
            return $source;
        }
    }

    /**
     * Remove all unwanted tags and attributes
     *
     * @param string $source Tnput string to be cleaned
     *
     * @return string Cleaned version of input parameter
     */
    protected function _remove($source)
    {
        $loopCounter = 0;
        while ($source != $this->_filterTags($source)) {
            $source = $this->_filterTags($source);
            $loopCounter++;
        }
        return $source;
	}

	/**
	 * Strip a string of certain tags
	 *
	 * @param string $source Input string to be cleaned
	 *
	 * @return string Cleaned version of input parameter
	 */
    protected function _filterTags($source)
    {
        $preTag       = NULL;
        $postTag      = $source;
        $tagOpenStart = strpos($source, '<');

        /* interate through string until no tags left */
        while ($tagOpenStart !== false) {
            $preTag     .= substr($postTag, 0, $tagOpenStart);
            $postTag     = substr($postTag, $tagOpenStart);
            $fromTagOpen = substr($postTag, 1);
            $tagOpenEnd  = strpos($fromTagOpen, '>');

            if ($tagOpenEnd === false) {
                break;
            }

            $tagOpenNested = strpos($fromTagOpen, '<');
            if (($tagOpenNested !== false) && ($tagOpenNested < $tagOpenEnd)) {
                $preTag      .= substr($postTag, 0, ($tagOpenNested+1));
                $postTag      = substr($postTag, ($tagOpenNested+1));
                $tagOpenStart = strpos($postTag, '<');
                continue;
            }

            $tagOpenNested = (strpos($fromTagOpen, '<') + $tagOpenStart + 1);
            $currentTag    = substr($fromTagOpen, 0, $tagOpenEnd);
            $tagLength     = strlen($currentTag);

            if (!$tagOpenEnd) {
                $preTag      .= $postTag;
                $tagOpenStart = strpos($postTag, '<');
            }

            /* iterate through tag finding attribute pairs - setup */
            $tagLeft      = $currentTag;
            $attrSet      = array();
            $currentSpace = strpos($tagLeft, ' ');

            if (substr($currentTag, 0, 1) == "/") {
                $isCloseTag    = true;
                list($tagName) = explode(' ', $currentTag);
                $tagName       = substr($tagName, 1);
            } else {
                $isCloseTag    = false;
                list($tagName) = explode(' ', $currentTag);
            }

            /*
			 * excludes all "non-regular" tagnames
			 * OR no tagname OR remove if xssauto is on and tag is blacklisted
			 */
            if ((!preg_match("/^[a-z][a-z0-9]*$/i", $tagName))||
                (!$tagName) ||
                ((in_array(strtolower($tagName), $this->_tagBlacklist)) &&
                 ($this->_xssAuto))) {
                $postTag = substr($postTag, ($tagLength + 2));
                $tagOpenStart = strpos($postTag, '<');
                continue;
            }

            /* this while is needed to support attribute values with spaces in */
            while ($currentSpace !== false) {
                $fromSpace   = substr($tagLeft, ($currentSpace+1));
                $nextSpace   = strpos($fromSpace, ' ');
                $openQuotes  = strpos($fromSpace, '"');
                $closeQuotes = strpos(substr($fromSpace, ($openQuotes+1)), '"') + $openQuotes + 1;

                if (strpos($fromSpace, '=') !== false) {
                    if (($openQuotes !== false) &&
                        (strpos(substr($fromSpace, ($openQuotes+1)), '"') !== false)) {
                        $attr = substr($fromSpace, 0, ($closeQuotes+1));
                    } else {
                        $attr = substr($fromSpace, 0, $nextSpace);
                    }
                } else {
                    $attr = substr($fromSpace, 0, $nextSpace);
                }

                if (!$attr) {
                    $attr = $fromSpace;
                }
                $attrSet[]    = $attr;
                $tagLeft      = substr($fromSpace, strlen($attr));
                $currentSpace = strpos($tagLeft, ' ');
            }

            /* appears in array specified by user */
            /* remove this tag on condition */
            $tagFound = in_array(strtolower($tagName), $this->_tagsArray);
            if ((!$tagFound && $this->_tagsMethod) ||
                ($tagFound && !$this->_tagsMethod)) {
                if (!$isCloseTag) {
                    $attrSet = $this->_filterAttr($attrSet);
                    $preTag .= '<' . $tagName;
                    for ($i = 0; $i < count($attrSet); $i++) {
                        $preTag .= ' ' . $attrSet[$i];
                    }
                    /* reformat single tags to XHTML */
                    if (strpos($fromTagOpen, "</" . $tagName)) {
                        $preTag .= '>';
                    } else {
                        $preTag .= ' />';
                    }
                } else {
                    $preTag .= '</' . $tagName . '>';
                }
            }

            $postTag      = substr($postTag, ($tagLength + 2));
            $tagOpenStart = strpos($postTag, '<');
        }

        $preTag .= $postTag;
        return $preTag;
    }

    /**
     * Strip a tag of certain attributes
     *
     * @param array $attrSet Array with attributes to filter
     *
     * @return array cleanded array
     */
    protected function _filterAttr($attrSet)
    {
        $newSet = array();
        for ($i = 0; $i <count($attrSet); $i++) {
            /* skip blank spaces in tag */
            if (!$attrSet[$i]) {
                continue;
            }
            /* split into attr name and value */
            $attrSubSet = explode('=', trim($attrSet[$i]));
            list($attrSubSet[0]) = explode(' ', $attrSubSet[0]);
            /*
             * removes all "non-regular" attr names
             * AND also attr blacklisted
             */
            if ((!eregi("^[a-z]*$", $attrSubSet[0])) ||
                (($this->_xssAuto) &&
                 ((in_array(strtolower($attrSubSet[0]), $this->_attrBlacklist)) ||
                  (substr($attrSubSet[0], 0, 2) == 'on')))) {
                continue;
			}

			/* xss attr value filtering */
			if ($attrSubSet[1]) {
			    /* strips unicode, hex, etc */
			    $attrSubSet[1] = str_replace('&#', '', $attrSubSet[1]);
			    /* strip normal newline within attr value  */
			    $attrSubSet[1] = preg_replace('/\s+/', '', $attrSubSet[1]);
			    /* strip double quotes */
			    $attrSubSet[1] = str_replace('"', '', $attrSubSet[1]);
			    /* [requested feature] convert single quotes from either
			     * side to doubles (Single quotes
			     * shouldn't be used to pad attr value) */
			    if ((substr($attrSubSet[1], 0, 1) == "'") &&
			        (substr($attrSubSet[1], (strlen($attrSubSet[1]) - 1), 1) == "'")) {
                    $attrSubSet[1] = substr($attrSubSet[1], 1, (strlen($attrSubSet[1]) - 2));
			    }
			    /* strip slashes */
			    $attrSubSet[1] = stripslashes($attrSubSet[1]);
			}

			/* auto strip attr's with "javascript: */
			if (((strpos(strtolower($attrSubSet[1]), 'expression') !== false) &&
			    (strtolower($attrSubSet[0]) == 'style')) ||
			    (strpos(strtolower($attrSubSet[1]), 'javascript:') !== false) ||
			    (strpos(strtolower($attrSubSet[1]), 'behaviour:') !== false) ||
			    (strpos(strtolower($attrSubSet[1]), 'vbscript:') !== false) ||
			    (strpos(strtolower($attrSubSet[1]), 'mocha:') !== false) ||
			    (strpos(strtolower($attrSubSet[1]), 'livescript:') !== false)) {
                continue;
            }

            /* if matches user defined array */
            /* keep this attr on condition */
            $attrFound = in_array(strtolower($attrSubSet[0]), $this->_attrArray);
            if ((!$attrFound && $this->_attrMethod) ||
                ($attrFound && !$this->_attrMethod)) {
                if ($attrSubSet[1]) {
                    $newSet[] = $attrSubSet[0] . '="' . $attrSubSet[1] . '"';
                } else if ($attrSubSet[1] == "0") {
                    $newSet[] = $attrSubSet[0] . '="0"';
                } else {
                    $newSet[] = $attrSubSet[0] . '="' . $attrSubSet[0] . '"';
                }
            }
        }
        return $newSet;
    }

    /**
     * Try to convert to plaintext
     * conver url, decimal and hex
     *
     * @param string $source Text to parse
     *
     * @return string converted text
     */
    protected function _decode($source)
    {
        $source = html_entity_decode($source, ENT_QUOTES, "ISO-8859-1");
        $source = preg_replace('/&#(\d+);/me', "chr(\\1)", $source);
        $source = preg_replace('/&#x([a-f0-9]+);/mei', "chr(0x\\1)", $source);
        return $source;
    }
}