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
 * Helper for set the rights of the user in one item.
 */
final class Default_Helpers_Right
{
    /**
     * Type: item
     */
    const ITEM_TYPE   = "item";

    /**
     * Type: module
     */
    const MODULE_TYPE = "module";

    /**
     * Add the user into the dataAccess array.
     *
     * @param array   $params The Post values.
     * @param integer $user   The user ID to add.
     *
     * @return array Array with users IDs.
     */
    public static function addUser($params, $user)
    {
        if (!array_key_exists('dataAccess', $params)) {
            $params['dataAccess'] = array();
        }

        if (!isset($params['dataAccess'][$user])) {
            $params['dataAccess'][$user] = $user;
        }

        return $params;
    }

    /**
     * Set the none access for the user.
     *
     * @param array   $params The post values.
     * @param integer $user   The user ID to set.
     *
     * @return array Array with user IDs per access.
     */
    public static function allowNone($params, $user)
    {
        return self::_addRight($params, $user, 'checkNoneAccess');
    }

    /**
     * Set the read access for the user.
     *
     * @param array   $params The post values.
     * @param integer $user   The user ID to set.
     *
     * @return array Array with user IDs per access.
     */
    public static function allowRead($params, $user)
    {
        return self::_addRight($params, $user, 'checkReadAccess');
    }

    /**
     * Set the write access for the user.
     *
     * @param array   $params The post values.
     * @param integer $user   The user ID to set.
     *
     * @return array Array with user IDs per access.
     */
    public static function allowWrite($params, $user)
    {
        return self::_addRight($params, $user, 'checkWriteAccess');
    }

    /**
     * Set the access access for the user.
     *
     * @param array   $params The post values.
     * @param integer $user   The user ID to set.
     *
     * @return array Array with user IDs per access.
     */
    public static function allowAccess($params, $user)
    {
        return self::_addRight($params, $user, 'checkAccessAccess');
    }

    /**
     * Set the create access for the user.
     *
     * @param array   $params The post values.
     * @param integer $user   The user ID to set.
     *
     * @return array Array with user IDs per access.
     */
    public static function allowCreate($params, $user)
    {
        return self::_addRight($params, $user, 'checkCreateAccess');
    }

    /**
     * Set the copy access for the user.
     *
     * @param array   $params The post values.
     * @param integer $user   The user ID to set.
     *
     * @return array Array with user IDs per access.
     */
    public static function allowCopy($params, $user)
    {
        return self::_addRight($params, $user, 'checkCopyAccess');
    }

    /**
     * Set the delete access for the user.
     *
     * @param array   $params The post values.
     * @param integer $user   The user ID to set.
     *
     * @return array Array with user IDs per access.
     */
    public static function allowDelete($params, $user)
    {
        return self::_addRight($params, $user, 'checkDeleteAccess');
    }

    /**
     * Set the download access for the user.
     *
     * @param array   $params The post values.
     * @param integer $user   The user ID to set.
     *
     * @return array Array with user IDs per access.
     */
    public static function allowDownload($params, $user)
    {
        return self::_addRight($params, $user, 'checkDownloadAccess');
    }

    /**
     * Set the admin access for the user.
     *
     * @param array   $params The post values.
     * @param integer $user   The user ID to set.
     *
     * @return array Array with user IDs per access.
     */
    public static function allowAdmin($params, $user)
    {
        return self::_addRight($params, $user, 'checkAdminAccess');
    }

    /**
     * Set read, write, delete and download access for the user.
     *
     * @param array   $params The post values.
     * @param integer $user   The user ID to set.
     *
     * @return array Array with user IDs per access.
     */
    public static function allowReadWriteDownloadDelete($params, $user)
    {
        $params = self::allowRead($params, $user);
        $params = self::allowWrite($params, $user);
        $params = self::allowDelete($params, $user);
        $params = self::allowDownload($params, $user);

        return $params;
    }

    /**
     * Set full access for the user.
     *
     * @param array   $params The post values.
     * @param integer $user   The user ID to set.
     *
     * @return array Array with user IDs per access.
     */
    public static function allowAll($params, $user)
    {
        $params = self::allowRead($params, $user);
        $params = self::allowWrite($params, $user);
        $params = self::allowAccess($params, $user);
        $params = self::allowCreate($params, $user);
        $params = self::allowCopy($params, $user);
        $params = self::allowDelete($params, $user);
        $params = self::allowDownload($params, $user);
        $params = self::allowAdmin($params, $user);

        return $params;
    }

    /**
     * Parse the rights for all the users and return it into a bitmask per user.
     *
     * @param array   $params   The post values.
     * @param string  $type     Type of right, for users or modules.
     * @param string  $moduleId The module ID.
     * @param boolean $newItem  If is a new item or not.
     * @param integer $ownerId  The owner ID or 0 for the current user.
     *
     * @return array Array with user IDs per access.
     */
    public static function getRights($params)
    {
        $right  = array();
        $rights = array();

        if (isset($params['dataAccess'])) {
            $ids = array_keys($params['dataAccess']);
            foreach ($ids as $accessId) {
                $right = array();
                $right['none']     = self::_checked($params,'checkNoneAccess', $accessId);
                $right['read']     = self::_checked($params,'checkReadAccess', $accessId);
                $right['write']    = self::_checked($params,'checkWriteAccess', $accessId);
                $right['access']   = self::_checked($params,'checkAccessAccess', $accessId);
                $right['create']   = self::_checked($params,'checkCreateAccess', $accessId);
                $right['copy']     = self::_checked($params,'checkCopyAccess', $accessId);
                $right['delete']   = self::_checked($params,'checkDeleteAccess', $accessId);
                $right['download'] = self::_checked($params,'checkDownloadAccess', $accessId);
                $right['admin']    = self::_checked($params,'checkAdminAccess', $accessId);
                $rights[$accessId] = Phprojekt_Acl::convertArrayToBitmask($right);
            }
        }

        return $rights;
    }

    /**
     * Adds specific right to the params.
     *
     * @param array   $params The post value.
     * @param string  $right  The right to add.
     * @param integer $user   The user ID for the right.
     *
     * @return array Array with user IDs per access.
     */
    private static function _addRight($params, $user, $right)
    {
        // Add the user if don't exist
        $params = self::addUser($params, $user);

        // Adds the specific right
        if (!array_key_exists($right, $params)) {
            $params[$right] = array();
        }
        $params[$right][$user] = 1;

        return $params;
    }

    /**
     * Add read, write and delete access to the assigned user in an item.
     *
     * @param string                    $key     The name of the user field.
     * @param array                     $params  The post values.
     * @param Phprojekt_Model_Interface $model   The current module to save.
     * @param boolean                   $newItem If is new item or not.
     *
     * @return array Array with user IDs per access.
     */
    public static function addRightsToAssignedUser($key, $params, $model, $newItem)
    {
        // Add rights to the Assigned user, if any
        $assignedUser = (isset($params[$key])) ? $params[$key] : 0;

        // The assgined user is set
        if ($assignedUser != 0) {
            // Is an Existing item
            // The logged user don't have access to the 'Access' tab
            if (!$newItem && (!isset($params['dataAccess']))) {
                // The rights will be added to the Request Params, but also it needs to be added the
                // already existing rights for users on this item. Case else, the saving routine deletes all
                // other rights that the ones added for the assigned user

                // Add already existing rights of the item,
                // except for the new assignedUser
                // except for the old assignedUser
                $currentRights = $model->getUsersRights();
                $rightsType    = array('access', 'read', 'write', 'create', 'copy', 'delete', 'download', 'admin');
                foreach ($currentRights as $userRights) {
                    $userId = $userRights['userId'];
                    if ($userId != $assignedUser && $userId != $model->$key) {
                        $params = self::addUser($params, $userId);
                        foreach ($rightsType as $rightName) {
                            if (array_key_exists($rightName, $userRights)) {
                                if ($userRights[$rightName] == 1) {
                                    $rightCompleteName = 'check' . ucfirst($rightName) . 'Access';
                                    if (!array_key_exists($rightCompleteName, $params)) {
                                        $params[$rightCompleteName] = array();
                                    }
                                    $params[$rightCompleteName][$userId] = 1;
                                }
                            }
                        }
                    }
                }
            }

            // Add the assigned user basic write rights to $params
            // If is the owner, set full access
            if ($model->ownerId == $assignedUser) {
                $params = self::allowAll($params, $model->ownerId);
            } else {
                $params = self::allowReadWriteDownloadDelete($params, $assignedUser);
            }
        }

        return $params;
    }

    /**
     * Return true or false if the checkbox is checked.
     *
     * @param array   $params   The post value.
     * @param string  $string   The key of the field.
     * @param integer $accessId The user ID.
     *
     * @return boolean True for checked.
     */
    private static function _checked($params, $string, $accessId)
    {
        if (isset($params[$string][$accessId])) {
            if ($params[$string][$accessId] == 1) {
                return true;
            }
        }

        return false;
    }
}
