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
 * Filemanager Extension
 *
 * This makes Filemanager_Migration available to the Phprojekt library through the
 * extension api.
 */

class Filemanager_Extension extends Phprojekt_Extension_Abstract
{
    public function getVersion()
    {
        return '6.1.0';
    }

    public function getMigration()
    {
        return new Filemanager_Migration();
    }
}
