<?php
/**
 * Helper for set the rights of the user in one item
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
 * @author     Gustavo Solt <solt@mayflower.de>
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
 * @author     Gustavo Solt <solt@mayflower.de>
 */
final class Default_Helpers_Right
{
    /**
     * Add the user into the dataAccess array
     *
     * @param array   $params The Post values
     * @param integer $user   The user to add
     *
     * @return array
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
     * Set the none access for the user
     *
     * @param array   $params The post values
     * @param integer $user   The user to set
     *
     * @return array
     */
    public static function allowNone($params, $user)
    {
        return self::_addRight($params, $user, 'checkNoneAccess');
    }

    /**
     * Set the read access for the user
     *
     * @param array   $params The post values
     * @param integer $user   The user to set
     *
     * @return array
     */
    public static function allowRead($params, $user)
    {
        return self::_addRight($params, $user, 'checkReadAccess');
    }

    /**
     * Set the write access for the user
     *
     * @param array   $params The post values
     * @param integer $user   The user to set
     *
     * @return array
     */
    public static function allowWrite($params, $user)
    {
        return self::_addRight($params, $user, 'checkWriteAccess');
    }

    /**
     * Set the access access for the user
     *
     * @param array   $params The post values
     * @param integer $user   The user to set
     *
     * @return array
     */
    public static function allowAccess($params, $user)
    {
        return self::_addRight($params, $user, 'checkAccessAccess');
    }

    /**
     * Set the create access for the user
     *
     * @param array   $params The post values
     * @param integer $user   The user to set
     *
     * @return array
     */
    public static function allowCreate($params, $user)
    {
        return self::_addRight($params, $user, 'checkCreateAccess');
    }

    /**
     * Set the copy access for the user
     *
     * @param array   $params The post values
     * @param integer $user   The user to set
     *
     * @return array
     */
    public static function allowCopy($params, $user)
    {
        return self::_addRight($params, $user, 'checkCopyAccess');
    }

    /**
     * Set the delete access for the user
     *
     * @param array   $params The post values
     * @param integer $user   The user to set
     *
     * @return array
     */
    public static function allowDelete($params, $user)
    {
        return self::_addRight($params, $user, 'checkDeleteAccess');
    }

    /**
     * Set the download access for the user
     *
     * @param array   $params The post values
     * @param integer $user   The user to set
     *
     * @return array
     */
    public static function allowDownload($params, $user)
    {
        return self::_addRight($params, $user, 'checkDownloadAccess');
    }

    /**
     * Set the admin access for the user
     *
     * @param array   $params The post values
     * @param integer $user   The user to set
     *
     * @return array
     */
    public static function allowAdmin($params, $user)
    {
        return self::_addRight($params, $user, 'checkAdminAccess');
    }

    /**
     * Set read, write and delete access for the user
     *
     * @param array   $params The post values
     * @param integer $user   The user to set
     *
     * @return array
     */
    public static function allowReadWriteDelete($params, $user)
    {
        $params = self::allowRead($params, $user);
        $params = self::allowWrite($params, $user);
        $params = self::allowDelete($params, $user);

        return $params;
    }

    /**
     * Set full access for the user
     *
     * @param array   $params The post values
     * @param integer $user   The user to set
     *
     * @return array
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
     * Parse the rights for all the users and return it into a bitmask per user
     *
     * @param array   $params  The post values
     * @param boolean $newItem If is a new item or not
     * @param integer $ownerId The owner id or 0 for the current user
     *
     * @return array
     */
    public static function getRights($params, $newItem, $ownerId = 0)
    {
        $right  = array();
        $rights = array();

        $right['none']     = true;
        $right['read']     = true;
        $right['write']    = true;
        $right['access']   = true;
        $right['create']   = true;
        $right['copy']     = true;
        $right['delete']   = true;
        $right['download'] = true;
        $right['admin']    = true;

        // Only set the full access if is a new item
        if ($newItem) {
            if ($ownerId == 0) {
                $ownerId = Phprojekt_Auth::getUserId();
            }
            $rights[$ownerId] = Phprojekt_Acl::convertArrayToBitmask($right);
        }

        if (isset($params['dataAccess'])) {
            $ids = array_keys($params['dataAccess']);
            foreach ($ids as $accessId) {
                $right = array();
                $right['none']     = (isset($params['checkNoneAccess'][$accessId])) ? true : false;
                $right['read']     = (isset($params['checkReadAccess'][$accessId])) ? true : false;
                $right['write']    = (isset($params['checkWriteAccess'][$accessId])) ? true : false;
                $right['access']   = (isset($params['checkAccessAccess'][$accessId])) ? true : false;
                $right['create']   = (isset($params['checkCreateAccess'][$accessId])) ? true : false;
                $right['copy']     = (isset($params['checkCopyAccess'][$accessId])) ? true : false;
                $right['delete']   = (isset($params['checkDeleteAccess'][$accessId])) ? true : false;
                $right['download'] = (isset($params['checkDownloadAccess'][$accessId])) ? true : false;
                $right['admin']    = (isset($params['checkAdminAccess'][$accessId])) ? true : false;
                $rights[$accessId] = Phprojekt_Acl::convertArrayToBitmask($right);
            }
        }

        return $rights;
    }

    /**
     * Adds specific right to the params
     *
     * @param array   $params The post value
     * @param string  $right  The right to add
     * @param integer $user   The user for the right
     *
     * @return array
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
     * Add read, write and delete access to the assigned user in an item
     *
     * @param string                    $key     The name of the user field
     * @param array                     $params  The post values
     * @param Phprojekt_Model_Interface $model   The current module to save
     * @param boolean                   $newItem If is new item or not
     *
     * @return array
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
                $currentRights = $model->getRights();
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
                $params = self::allowReadWriteDelete($params, $assignedUser);
            }
        }

        return $params;
    }
}
