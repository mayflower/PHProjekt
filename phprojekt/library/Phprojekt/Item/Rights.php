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
 * @version    $Id$
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
    /**
     * Name of the table
     *
     * @var string
     */
    protected $_name = 'item_rights';

    /**
     * Change the tablename for use with the Zend db class
     *
     * @param array $config The config array for the database
     */
    public function __construct()
    {
        $config = array('db' => Phprojekt::getInstance()->getDb());

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

        // Reset cache
        $sessionName    = 'Phprojekt_Item_Rights-getRights' . '-' . $moduleId . '-' . $itemId;
        $rightNamespace = new Zend_Session_Namespace($sessionName);
        $rightNamespace->unsetAll();
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
        $data['module_id'] = (int) $moduleId;
        $data['item_id']   = (int) $itemId;
        $data['user_id']   = (int) $userId;
        $data['access']    = (int) $access;
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

        $where[] = 'module_id = '. $clone->getAdapter()->quote($moduleId);
        $where[] = 'item_id = '. $clone->getAdapter()->quote($itemId);
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
        $sessionName           = 'Phprojekt_Item_Rights-getItemRight' . '-' . $moduleId . '-' . $itemId . '-' . $userId;
        $rightPerUserNamespace = new Zend_Session_Namespace($sessionName);
        if (!isset($rightPerUserNamespace->right)) {
            $row = $this->find($moduleId, $itemId, $userId)->toArray();
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
        $sessionName    = 'Phprojekt_Item_Rights-getRights' . '-' . $moduleId . '-' . $itemId;
        $rightNamespace = new Zend_Session_Namespace($sessionName);

        if (!isset($rightNamespace->right)) {
            $values = array();

            // Set the current User
            // Use for an empty rights, if not, will be re-write
            $values['currentUser']['moduleId'] = (int) $moduleId;
            $values['currentUser']['itemId']   = (int) $itemId;
            $values['currentUser']['userId']   = (int) Phprojekt_Auth::getUserId();
            $access                            = Phprojekt_Acl::convertBitmaskToArray((int) Phprojekt_Acl::ALL);
            $values['currentUser']             = array_merge($values['currentUser'], $access);

            $where = sprintf('module_id = %d AND item_id = %d', (int) $moduleId, (int) $itemId);
            $rows  = $this->fetchAll($where)->toArray();
            foreach ($rows as $row) {
                // Convert index
                foreach ($row as $k => $v) {
                    unset($row[$k]);
                    $row[Phprojekt_ActiveRecord_Abstract::convertVarFromSql($k)] = (int) $v;
                }
                $row = array_merge($row, Phprojekt_Acl::convertBitmaskToArray($row['access']));
                if (Phprojekt_Auth::getUserId() == $row['userId']) {
                    $values['currentUser'] = $row;
                } else {
                    $values[$row['userId']] = $row;
                }
            }
            $rightNamespace->right = $values;
        }

        return $rightNamespace->right;
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
        $data              = array();
        $data['module_id'] = Phprojekt_Module::getId('Project');
        $data['item_id']   = 1;
        $data['user_id']   = (int) $userId;
        $data['access']    = (int) Phprojekt_Acl::WRITE;
        $this->insert($data);
    }
}
