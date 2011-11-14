<?php
/**
 * This class manage the rights for each item per user.
 * Return and save the rights using the moduleId-itemId relation.
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
 * @package    Phprojekt
 * @subpackage Item
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
 * This class manage the rights for each item per user.
 * Return and save the rights using the moduleId-itemId relation.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Item
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_Item_Rights extends Zend_Db_Table_Abstract
{
    /**
     * Name of the table.
     *
     * @var string
     */
    protected $_name = 'item_rights';

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $config = array('db' => Phprojekt::getInstance()->getDb());

        parent::__construct($config);
    }

    /**
     * Save all the access for each user.
     *
     * The function will re-order the user and access for save it.
     *
     * @param string  $moduleId The module ID to store.
     * @param integer $itemId   The item ID.
     * @param array   $rights   Array of user IDs with the bitmask access.
     *
     * @return void
     */
    public function saveRights($moduleId, $itemId, $rights)
    {
        // Delete the entries for this moduleId-itemId and re-inserted the changes
        $this->_delete($moduleId, $itemId);

        foreach ($rights as $userId => $access) {
            $this->_saveRight($moduleId, $itemId, $userId, $access);
            // Reset cache
            $sessionName = 'Phprojekt_Item_Rights-getItemRight' . '-' . $moduleId . '-' . $itemId . '-' . $userId;
            $rightPerUserNamespace = new Zend_Session_Namespace($sessionName);
            $rightPerUserNamespace->unsetAll();
        }

        // Reset access by module-item
        $sessionName    = 'Phprojekt_Item_Rights-getUsersRights' . '-' . $moduleId . '-' . $itemId;
        $rightNamespace = new Zend_Session_Namespace($sessionName);
        $rightNamespace->unsetAll();

        // Reset users by module-item
        $sessionName    = 'Phprojekt_Item_Rights-getUsersWithRight' . '-' . $moduleId . '-' . $itemId;
        $rightNamespace = new Zend_Session_Namespace($sessionName);
        $rightNamespace->unsetAll();
    }

    /**
     * Save an access right.
     *
     * This function use the Zend_Db insert.
     *
     * @param string  $moduleId The module ID to store.
     * @param integer $itemId   The item ID.
     * @param integer $userId   The user to save.
     * @param integer $access   Bitmask of the access.
     *
     * @return void
     */
    private function _saveRight($moduleId, $itemId, $userId, $access)
    {
        $data['module_id'] = (int) $moduleId;
        $data['item_id']   = (int) $itemId;
        $data['user_id']   = (int) $userId;
        $data['access']    = (int) $access;
        $this->insert($data);
    }

    /**
     * Delete all the users for one object.
     *
     * @param string  $moduleId The module ID to delete.
     * @param integer $itemId   The item ID.
     *
     * @return void
     */
    private function _delete($moduleId, $itemId)
    {
        $where = array();
        $clone = clone($this);

        $where[] = 'module_id = ' . $clone->getAdapter()->quote($moduleId);
        $where[] = 'item_id = ' . $clone->getAdapter()->quote($itemId);
        $clone->delete($where);
    }

    /**
     * Return the right for a individual module-item-user pair.
     *
     * @param string  $moduleId The module ID.
     * @param integer $itemId   The item ID.
     * @param integer $userId   The user ID.
     *
     * @return integer Bitmask for the module-item-user pair.
     */
    public function getItemRight($moduleId, $itemId, $userId)
    {
        // Cache the query
        $sessionName           = 'Phprojekt_Item_Rights-getItemRight' . '-' . $moduleId . '-' . $itemId . '-' . $userId;
        $rightPerUserNamespace = new Zend_Session_Namespace($sessionName);
        if (!isset($rightPerUserNamespace->right)) {
            $row = $this->find((int) $moduleId, (int) $itemId, (int) $userId)->toArray();
            if (isset($row[0])) {
                $value = $row[0]['access'];
            } else {
                $value = 0;
            }
            $rightPerUserNamespace->right = $value;
        }

        return $rightPerUserNamespace->right;
    }

    /**
     * Returns the rights for the current user of a moduleId-ItemId pair.
     *
     * @param string  $moduleId The module ID.
     * @param integer $itemId   The item ID.
     *
     * @return array Array with 'moduleId', 'itemId', 'userId' and all the access key.
     */
    public function getRights($moduleId, $itemId)
    {
        $values        = array();
        $currentUserId = (int) Phprojekt_Auth_Proxy::getEffectiveUserId();

        $access = $this->getItemRight($moduleId, $itemId, $currentUserId);
        if (null === $access) {
            // Use for an empty rights
            $access = (int) Phprojekt_Acl::NONE;
        }
        $values['currentUser']['moduleId'] = (int) $moduleId;
        $values['currentUser']['itemId']   = (int) $itemId;
        $values['currentUser']['userId']   = $currentUserId;
        $access                            = Phprojekt_Acl::convertBitmaskToArray($access);
        $values['currentUser']             = array_merge($values['currentUser'], $access);

        return $values;
    }

    /**
     * Returns the rights for the current user of each moduleId-ItemId pair.
     *
     * @param string $moduleId The module ID.
     * @param array  $ids      An array with all the IDs.
     *
     * @return array Array with 'moduleId', 'itemId', 'userId' and all the access key.
     */
    public function getMultipleRights($moduleId, $ids)
    {
        $values        = array();
        $currentUserId = (int) Phprojekt_Auth_Proxy::getEffectiveUserId();

        $where = sprintf('module_id = %d AND user_id = %d AND item_id IN (%s)', (int) $moduleId,
            $currentUserId, implode(",", $ids));
        $rows = $this->fetchAll($where)->toArray();

        foreach ($ids as $id) {
            // Set the current User
            // Use for an empty rights, if not, will be re-write
            $values[$id]['currentUser']['moduleId'] = (int) $moduleId;
            $values[$id]['currentUser']['itemId']   = (int) $id;
            $values[$id]['currentUser']['userId']   = $currentUserId;
            $access                                 = Phprojekt_Acl::convertBitmaskToArray((int) Phprojekt_Acl::ALL);
            $values[$id]['currentUser']             = array_merge($values[$id]['currentUser'], $access);
        }

        foreach ($rows as $row) {
            $access                                 = Phprojekt_Acl::convertBitmaskToArray($row['access']);
            $values[$row['item_id']]['currentUser'] = array_merge($values[$row['item_id']]['currentUser'], $access);
        }

        return $values;
    }

    /**
     * Returns the rights for all the users of a moduleId-ItemId pair.
     *
     * @param string  $moduleId The module ID.
     * @param integer $itemId   The item ID.
     *
     * @return array Array with 'moduleId', 'itemId', 'userId' and all the access key.
     */
    public function getUsersRights($moduleId, $itemId)
    {
        // Cache the query
        $sessionName    = 'Phprojekt_Item_Rights-getUsersRights' . '-' . $moduleId . '-' . $itemId;
        $rightNamespace = new Zend_Session_Namespace($sessionName);

        if (!isset($rightNamespace->right)) {
            $values        = array();
            $currentUserId = (int) Phprojekt_Auth_Proxy::getEffectiveUserId();

            $where = sprintf('module_id = %d AND item_id = %d', (int) $moduleId, (int) $itemId);
            $rows  = $this->fetchAll($where)->toArray();
            foreach ($rows as $row) {
                $access  = Phprojekt_Acl::convertBitmaskToArray($row['access']);
                $values[$row['user_id']]['moduleId'] = (int) $moduleId;
                $values[$row['user_id']]['itemId']   = (int) $itemId;
                $values[$row['user_id']]['userId']   = (int) $row['user_id'];
                $values[$row['user_id']]             = array_merge($values[$row['user_id']], $access);
            }
            $rightNamespace->right = $values;
        }

        return $rightNamespace->right;
    }

    /**
     * Save default permission for the provided user in root project.
     *
     * @param integer $userId The user to save default permission.
     *
     * @return void
     */
    public function saveDefaultRights($userId)
    {
        $data              = array();
        $data['module_id'] = Phprojekt_Module::getId('Project');
        $data['item_id']   = 1;
        $data['user_id']   = (int) $userId;
        $data['access']    = (int) Phprojekt_Acl::WRITE;
        $this->insert($data);
    }

    /**
     * Return all the users with at least one right for a moduleId-ItemId pair.
     *
     * @param string  $moduleId The module ID.
     * @param integer $itemId   The item ID.
     *
     * @return array Array of user IDs.
     */
    public function getUsersWithRight($moduleId, $itemId)
    {
        // Cache the query
        $sessionName    = 'Phprojekt_Item_Rights-getUsersWithRight' . '-' . $moduleId . '-' . $itemId;
        $rightNamespace = new Zend_Session_Namespace($sessionName);

        if (!isset($rightNamespace->right)) {
            $values = array();
            $where  = sprintf('module_id = %d AND item_id = %d AND access > 0', (int) $moduleId, (int) $itemId);
            $rows   = $this->fetchAll($where)->toArray();
            foreach ($rows as $row) {
                $values[] = $row['user_id'];
            }
            $rightNamespace->right = $values;
        }

        return $rightNamespace->right;
    }
}
