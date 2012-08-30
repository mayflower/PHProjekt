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
 * @copyright  Copyright (c) 2011 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * WebDAV Auth
 *
 * This class implements an authentication backend for sabredav
 */
class WebDAV_Helper_Auth extends Sabre_DAV_Auth_Backend_AbstractBasic
{
    public function validateUserPass($username, $password)
    {
        return Phprojekt_Auth::checkCredentials($username, $password);
    }
}
