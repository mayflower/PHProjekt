<?php
/**
 * Escaper class
 *
 * @author     Peter Voringer <peter.voringer@mayflower.de>
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    CVS: $Id$
 * @license
 * @package    Cleaner
 * @link       http://www.thinkforge.org/projects/inspector
 * @since      File available since Release 6.0
 *
 */

/**
 * Contains escaping methods
 *
 * Contains escaping methods
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: <package_version>
 * @license
 * @package    Cleaner
 * @link       http://www.thinkforge.org/projects/inspector
 * @author     Peter Voringer <peter.voringer@mayflower.de>
 * @since      File available since Release 6.0
 */
class Cleaner_Escaper
{
    /**
     * Registered Types of Escapers
     *
     * @var array
     */
    public $escapers = array(
        'html'       => array('url'   => 'HtmlUrl',
                              'value' => 'HtmlValue'),
        'css'        => array(),
        'javascript' => array(),
        'sql'        => array('value' => 'SqlValue')
    );

    /**
     * Escapes html url value
     *
     * @param string $value Value to escape
     *
     * @return string Escaped value
     */
    public function escapeHtmlUrl($value)
    {
        return urlencode(utf8_encode($value));
    }

    /**
     * Escapes HTML Values
     *
     * @param string $value Value to escape
     *
     * @return string Escaped value
     */
    public function escapeHtmlValue($value)
    {
        return htmlentities($value, ENT_QUOTES);
    }

    /**
     * Escapes SQL Value
     *
     * @param string $value Value to escape
     *
     * @return string Escaped value
     */
    public function escapeSqlValue($value)
    {
        return addslashes($value);
    }
}