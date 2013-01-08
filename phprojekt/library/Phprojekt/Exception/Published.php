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
 * @copyright  Copyright (c) 2012 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * General PHProjekt Exception Superclass
 */
class Phprojekt_Exception_Published extends Phprojekt_Exception
{
    /** A error string. Can be used to determine the error type in the client. */
    protected $_error;

    /** Can be set by subclasses to identify them in the client */
    protected $_type     = 'generic';
    protected $_httpCode = 500;

    public function __construct($message = "", $error = null, $previous = null)
    {
        parent::__construct(Phprojekt::getInstance()->translate($message), 0, $previous);
        $this->_error = $error;
    }

    public function getHttpCode()
    {
        return $this->_httpCode;
    }

    public function toArray()
    {
        $ret = array(
            'type'    => $this->_type,
            'message' => $this->getMessage()
        );

        if (!empty($this->_error)) {
            $ret['error'] = $this->_error;
        }

        return $ret;
    }
}
