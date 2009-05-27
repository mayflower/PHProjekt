<?php
/**
 * Helper for creating user lists with username mapping
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @link       http://www.phprojekt.com
 * @author     Sven Rautenberg <sven.rautenberg@mayflower.de>
 * @since      File available since Release 6.0
 */

/**
 * Helper for set the rights of the user in one item
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Sven Rautenberg <sven.rautenberg@mayflower.de>
 */
final class Minutes_Helpers_Userlist
{
    public static function expandIdList($idList)
    {
        $data    = array();
        if (!empty($idList)) {
            $user = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
            $userList = $user->fetchAll(sprintf('id IN (%s)', $idList));

            $display = $user->getDisplay();
            foreach ($userList as $record) {
                $data[] = array('id'      => $record->id,
                                'display' => $record->applyDisplay($display, $record));
            }
        }
        return $data;
    }
}