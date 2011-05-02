<?php
/**
 * Helper for creating user lists with username mapping.
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
 * @subpackage Minutes
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Sven Rautenberg <sven.rautenberg@mayflower.de>
 */

/**
 * Helper for creating user lists with username mapping.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Minutes
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Sven Rautenberg <sven.rautenberg@mayflower.de>
 */
final class Minutes_Helpers_Userlist
{
    /**
     * Helper to create an array of users.
     *
     * @param string $idList   Comma-separated list of user ids.
     * @param string $idListNN Optional additional lists of comma-separated user ids.
     *
     * @return array Array with 'id' and 'name'
     */
    public static function expandIdList($idList = '')
    {
        if (1 < ($num = func_num_args())) {
            for ($i = 1; $i < $num; $i++) {
                $addList = (string) func_get_arg($i);
                if ("" != $addList) {
                    $idList .= ',' . $addList;
                }
            }
        }

        $data = array();
        if (!empty($idList)) {
            $user     = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
            $display  = $user->getDisplay();
            $userList = $user->fetchAll(sprintf('id IN (%s)', $idList), $display);

            foreach ($userList as $record) {
                $data[] = array('id'   => (int) $record->id,
                                'name' => $record->applyDisplay($display, $record));
            }
        }

        return $data;
    }
}
