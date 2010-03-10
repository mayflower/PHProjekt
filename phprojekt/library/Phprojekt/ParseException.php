<?php
/**
 * Represents an Parse error
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
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @version    CVS: $Id$
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */

/**
 * Represents an Parse error
 *
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL v3 (See LICENSE file)
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class Phprojekt_ParseException extends Exception
{
    protected $_parsedString;

    /**
     *
     * @param string  $message
     * @param integer $code
     * @param string  $parsedString
     */
    public function __construct($message, $code = null, $parsedString = null)
    {
        parent::__construct($message, $code);
        if (null !== $parsedString) {
            $this->_parsedString = $parsedString;
        }
    }

    /**
     *
     * @return string
     */
    public function getParsedString()
    {
        return $this->_parsedString;
    }
}
