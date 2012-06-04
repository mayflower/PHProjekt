<?php
/**
 * Escaper class.
 *
 * Contains escaping methods.
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
 * Escaper class.
 *
 * Contains escaping methods.
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
class Cleaner_Escaper
{
    /**
     * Registered Types of Escapers.
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
     * Escapes html url value.
     *
     * @param string $value Value to escape.
     *
     * @return string Escaped value.
     */
    public function escapeHtmlUrl($value)
    {
        return urlencode(utf8_encode($value));
    }

    /**
     * Escapes HTML Values.
     *
     * @param string $value Value to escape.
     *
     * @return string Escaped value.
     */
    public function escapeHtmlValue($value)
    {
        return htmlentities($value, ENT_QUOTES);
    }

    /**
     * Escapes SQL Value.
     *
     * @param string $value Value to escape.
     *
     * @return string Escaped value.
     */
    public function escapeSqlValue($value)
    {
        return addslashes($value);
    }
}
