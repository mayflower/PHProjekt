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
 * Exception thrown when trying to save an entry that overlaps
 */
class Phprojekt_Exception_Validation extends Phprojekt_Exception_Published
{
    protected $_type     = 'validation';
    protected $_httpCode = 422;

    protected $_field;
    protected $_label;

    function __construct(array $error)
    {
        if (!array_key_exists('error', $error) || empty($error['error'])) {
            $error['error'] = null;
        }

        parent::__construct("{$error['field']}: {$error['message']}", $error['error']);

        $this->_field = $error['field'];
        $this->_label = $error['label'];
    }

    public function toArray()
    {
        $ret = parent::toArray();

        $ret['field'] = $this->_field;
        $ret['label'] = $this->_label;

        return $ret;
    }
}
