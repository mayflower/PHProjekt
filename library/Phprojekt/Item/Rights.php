<?php
/**
 * Item Rights Class for PHProjekt 6.0
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
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    CVS: $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * This class manage the rights for each item per user.
 * Return and save the rights using the moduleId-itemId relation.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */
class Phprojekt_Item_Rights extends Zend_Db_Table_Abstract
{
    protected $_name = 'ItemRights';

    /**
     * Change the tablename for use with the Zend db class
     *
     * @param array $config The config array for the database
     */
    public function __construct()
    {
        $config = array('db' => Zend_Registry::get('db'));

        parent::__construct($config);
    }

    /**
     * Save all the access for each user
     * The function will re-order the user and access for save it
     *
     * @param string  $moduleId   The module Id to store
     * @param integer $itemId     The item Id
     * @param array   $rights     Array of userIds with the bitmask access
     *
     * @return void
     */
    public function _save($moduleId, $itemId, $rights)
    {
        // Delete the entries for this moduleId-itemId and re-inserted the changes
        $this->_delete($moduleId, $itemId);

        foreach ($rights as $userId => $access) {
            $this->_saveRight($moduleId, $itemId, $userId, $access);
        }
        // Reset cache
        $rightNamespace = new Zend_Session_Namespace('ItemRights'.'-'.$moduleId.'-'.$itemId);
        if (isset($rightNamespace->right) && !empty($rightNamespace->right)) {
            $rightNamespace->right = array();
        }
    }

    /**
     * Save an access right
     *
     * This function use the Zend_DB insert
     *
     * @param string  $moduleId The module Id to store
     * @param integer $itemId   The item ID
     * @param integer $userId   The user to save
     * @param integer $access   Bitmask of the access
     *
     * @return void
     */
    private function _saveRight($moduleId, $itemId, $userId, $access)
    {
        $data['moduleId']     = (int)$moduleId;
        $data['itemId']       = (int)$itemId;
        $data['userId']       = (int)$userId;
        $data['access']       = (int)$access;
        $this->insert($data);
    }

    /**
     * Delete all the users for one object
     *
     * @param string  $moduleId The moduleId to delete
     * @param integer $itemId   The item ID
     *
     * @return void
     */
    private function _delete($moduleId, $itemId)
    {
        $where = array();
        $clone = clone($this);

        $where[] = 'moduleId = '. $clone->getAdapter()->quote($moduleId);
        $where[] = 'itemId = '. $clone->getAdapter()->quote($itemId);
        $clone->delete($where);
    }

    /**
     * Return the right for a individual module-item-user pair
     *
     * @param string  $moduleId The module Id
     * @param integer $itemId   The item Id
     * @param integer $userId   The user Id
     *
     * @return integer
     */
    public function getItemRight($moduleId, $itemId, $userId)
    {
        // Cache the query
        $rightNamespace = new Zend_Session_Namespace('ItemRights'.'-'.$moduleId.'-'.$itemId.'-'.$userId);
        if (isset($rightNamespace->right) && !empty($rightNamespace->right)) {
            $value = $rightNamespace->right;
        } else {
            $row   = $this->find($moduleId, $itemId, $userId)->toArray();
            if (isset($row[0])) {
                $value = $row[0]['access'];
            } else {
                $value = 0;
            }

            $rightNamespace->right = $value;
        }
        return $value;
    }

    /**
     * Return all the rights for a moduleId-ItemId
     *
     * @param string  $moduleId The module Id
     * @param integer $itemId   The item Id
     *
     * @return array
     */
    public function getRights($moduleId, $itemId)
    {
        // Cache the query
        $rightNamespace = new Zend_Session_Namespace('ItemRights'.'-'.$moduleId.'-'.$itemId);
        if (isset($rightNamespace->right) && !empty($rightNamespace->right)) {
            $values = $rightNamespace->right;
        } else {
            $db     = Zend_Registry::get('db');
            $user   = new Phprojekt_User_User($db);
            $where  = array();
            $values = array();

            $where[] = 'moduleId = '. (int)$moduleId;
            $where[] = 'itemId = '. (int)$itemId;
            $where   = implode(' AND ', $where);
            $rows    = $this->fetchAll($where)->toArray();
            foreach ($rows as $row) {
                $row['userName'] = $user->findUserById($row['userId'])->username;
                $row = array_merge($row, Phprojekt_Acl::convertBitmaskToArray($row['access']));
                if (Phprojekt_Auth::getUserId() == $row['userId']) {
                    $values['currentUser'] = $row;
                } else {
                    $values[$row['userId']] = $row;
                }
            }
            $rightNamespace->right = $values;
        }
        return $values;
    }

    /**
     * Save default permission for the provided user in root project
     *
     * @param integer $userId   The user to save default permission
     *
     * @return void
     */
    public function saveDefaultRights($userId)
    {
        $data = array();
        $data['moduleId']     = Phprojekt_Module::getId('Project');
        $data['itemId']       = 1;
        $data['userId']       = (int)$userId;
        $data['access']       = (int)Phprojekt_Acl::WRITE;
        $this->insert($data);
    }
}
