<?php
/**
 * Represents an Parse error.
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
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Core
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */

/**
 * Represents an Parse error.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Core
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class Phprojekt_ParseException extends Exception
{
    /**
     * Internal cache.
     *
     * @var string
     */
    protected $_parsedString;

    /**
     * Constructor.
     *
     * @param string  $message      Message.
     * @param integer $code         Code.
     * @param string  $parsedString Parsed string.
     *
     * @return void
     */
    public function __construct($message, $code = null, $parsedString = null)
    {
        parent::__construct($message, $code);
        if (null !== $parsedString) {
            $this->_parsedString = $parsedString;
        }
    }

    /**
     * Get the last parsed string.
     *
     * @return string Parsed string.
     */
    public function getParsedString()
    {
        return $this->_parsedString;
    }
}
