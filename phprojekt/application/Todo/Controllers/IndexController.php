<?php
/**
 * Todo Module Controller.
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
 * @package    Application
 * @subpackage Todo
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
 * Todo Module Controller.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Todo
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Todo_IndexController extends IndexController
{
    /**
     * Sets some values depending on the parameters.
     *
     * Set the rights for each user (owner, userId and the normal access tab).
     *
     * @return array POST values with some changes.
     */
    public function setParams()
    {
        $args    = func_get_args();
        $params  = $args[0];
        $model   = $args[1];
        $newItem = (isset($args[2])) ? $args[2] : false;

        return Default_Helpers_Right::addRightsToAssignedUser('userId', $params, $model, $newItem);
    }
}
