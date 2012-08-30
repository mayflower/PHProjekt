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
 *
 */
final class Phprojekt_Right
{
    public static function getRightsForItems($moduleId, $projectId, $userId, array $itemIds)
    {
        $acl = Phprojekt_Item_Rights::getItemRights($moduleId, $itemIds, $userId);
        return self::mergeWithRole($moduleId, $projectId, $userId, $acl);
    }

    public static function mergeWithRole($moduleId, $projectId, $userId, $itemRights)
    {
        /* there is currently only an implementation for standard modules with
         * save type NORMAL */
        if (Phprojekt_Module::getSaveType($moduleId) == Phprojekt_Module::TYPE_NORMAL) {
            $roleRights      = new Phprojekt_RoleRights($projectId, $moduleId, 0, $userId);
            $roleRightRead   = $roleRights->hasRight('read');
            $roleRightWrite  = $roleRights->hasRight('write');
            $roleRightCreate = $roleRights->hasRight('create');
            $roleRightAdmin  = $roleRights->hasRight('admin');

            // Map roles with item rights and make one array
            foreach ($itemRights as $itemId => $accessMask) {
                $access = Phprojekt_Acl::NONE;

                if ($roleRightAdmin) {
                    $access |= $accessMask & Phprojekt_Acl::ADMIN;
                }

                if ($roleRightRead || $roleRightWrite || $roleRightAdmin) {
                    $access |= $accessMask & Phprojekt_Acl::DOWNLOAD;
                }

                if ($roleRightWrite || $roleRightAdmin) {
                    $access |= $accessMask & Phprojekt_Acl::DELETE;
                }

                if ($roleRightWrite || $roleRightCreate || $roleRightAdmin) {
                    $access |= $accessMask & Phprojekt_Acl::COPY;
                }

                if ($roleRightWrite || $roleRightCreate || $roleRightAdmin) {
                    $access |= $accessMask & Phprojekt_Acl::CREATE;
                }

                if ($roleRightRead || $roleRightWrite || $roleRightCreate || $roleRightAdmin) {
                    $access |= $accessMask & Phprojekt_Acl::ACCESS;
                }

                if ($roleRightWrite || $roleRightCreate || $roleRightAdmin) {
                    $access |= $accessMask & Phprojekt_Acl::WRITE;
                }

                if ($roleRightRead || $roleRightWrite || $roleRightAdmin) {
                    $access |= $accessMask & Phprojekt_Acl::READ;
                }

                $itemRights[$itemId] = $access;
            }
        }

        return $itemRights;
    }
}
