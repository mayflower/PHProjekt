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
 * This class manage the rights for each item per user.
 * Return and save the rights using the moduleId-itemId relation.
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
        }
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
    public static function getItemRight($moduleId, $itemId, $userId)
    {
        $rights = self::getItemRights($moduleId, array($itemId), $userId);
        if (isset($rights[$itemId])) {
            return $rights[$itemId];
        }

        return Phprojekt_Acl::NONE;
    }

    /**
     * Returns the rights for the current user of each moduleId-ItemId pair.
     *
     * @param string $moduleId The module ID.
     * @param array  $ids      An array with all the IDs.
     *
     * @return array Array with 'moduleId', 'itemId', 'userId' and all the access key.
     */
    public static function getItemRights($moduleId, $itemIds, $userId)
    {
        $values = array_fill_keys($itemIds, array());
        $where  = sprintf('module_id = %d AND user_id = %d AND item_id IN (%s)',
            (int) $moduleId, (int) $userId, implode(",", $itemIds));
        $obj  = new self();
        $rows = $obj->fetchAll($where);

        foreach ($rows as $row) {
            // Set the current User
            // Use for an empty rights, if not, will be re-write
            $values[$row->item_id] = $row->access;
        }

        unset($obj);
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
        $values = array();
        $where  = sprintf('module_id = %d AND item_id = %d', (int) $moduleId, (int) $itemId);
        $rows   = $this->fetchAll($where)->toArray();
        foreach ($rows as $row) {
            $access = Phprojekt_Acl::convertBitmaskToArray($row['access']);
            $values[$row['user_id']] = array_merge($access, array(
                'moduleId' => (int) $moduleId,
                'itemId'   => (int) $itemId,
                'userId'   => (int) $row['user_id']
            ));
        }
        return $values;
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
        $data['access']    = (int) Phprojekt_Acl::WRITE | Phprojekt_Acl::CREATE | Phprojekt_Acl::READ;
        $this->insert($data);
    }

    /**
     * Returns all users that have a given right (or any right if none is given) on an item.
     *
     * @param string  $moduleId The module ID.
     * @param integer $itemId   The item ID.
     * @param int     $rights   A bitmask of rights (Constants in Phprojekt_Acl). All users with any rights will be
     *                              returned if null or omitted.
     * @param bool    $exact    Only get users with exact $rights instead of all users that have at least $rights.
     *                              Default is false.
     *
     * @return array Array of user IDs.
     */
    public function getUsersWithRight($moduleId, $itemId, $rights = null, $exact = false)
    {
        $db     = Phprojekt::getInstance()->getDb();
        $where  = $db->quoteInto('module_id = ? AND ', (int) $moduleId);
        $where .= $db->quoteInto('item_id = ?', (int) $itemId);

        if (is_null($rights)) {
            $where .= ' AND access > 0';
        } else if ($exact) {
            $where .= $db->quoteInto(' AND access = ?', (int) $rights);
        } else {
            $where .= $db->quoteInto(' AND (access & ?) = ?', (int) $rights, (int) $rights);
        }

        $user  = new Phprojekt_User_User();
        $users = $user->fetchAll($where, null, null, null, null, "JOIN item_rights ON item_rights.user_id = user.id");

        return $users;
    }
}
