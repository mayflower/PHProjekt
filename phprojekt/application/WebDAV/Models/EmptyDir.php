<?php
/**
 * WebDAV collection model.
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage WebDAV
 * @copyright  Copyright (c) 2011 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: @package_version@
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 */
require_once 'SabreDAV/Sabre/VObject/includes.php';

/**
 * WebDAV collection model.
 *
 * A dummy that serves as an empty, read-only directory.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage WebDAV
 * @copyright  Copyright (c) 2011 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: @package_version@
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 */
class WebDAV_Models_EmptyDir extends Sabre_DAV_Directory
{
    public function construct()
    {
    }

    public function getName()
    {
        return "Empty";
    }

    public function getChildren()
    {
        return array();
    }

}
