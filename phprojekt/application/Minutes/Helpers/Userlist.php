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
 * Helper for creating user lists with username mapping.
 */
final class Minutes_Helpers_Userlist
{
    /**
     * Helper to create an array of users.
     *
     * @param string $idList   Comma-separated list of user ids.
     * @param string $idListNN Optional additional lists of comma-separated user ids.
     *
     * @return array Array with 'id' and 'display'
     */
    public static function expandIdList()
    {
        $addArray = array();
        $num = func_num_args();
        for ($i = 0; $i < $num; $i++) {
            $addList = (string) func_get_arg($i);
            if ("" != $addList) {
                $addArray[] = $addList;
            }
        }

        $idList = implode(",", $addArray);

        $data = array();
        if (!empty($idList)) {
            $user     = new Phprojekt_User_User();
            $userList = $user->fetchAll(sprintf('id IN (%s)', $idList));

            foreach ($userList as $record) {
                $data[] = array('id'      => (int) $record->id,
                                'display' => $record->displayName);
            }
        }

        return $data;
    }
}
