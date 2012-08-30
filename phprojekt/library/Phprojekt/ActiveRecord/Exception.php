<?php
/**
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
 */

/**
 * Exception class for ActiveRecord pattern.
 */
class Phprojekt_ActiveRecord_Exception extends Exception
{
    /**
     * New instance.
     *
     * @param string  $message Fault string.
     * @param integer $code    Fault code.
     *
     * @return void
     */
    function __construct($message, $code = null)
    {
        parent::__construct($message, $code);
    }
}
