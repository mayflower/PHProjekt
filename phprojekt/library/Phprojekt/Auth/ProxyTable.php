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
 * Table mappings for the proxy table.
 *
 * Class to abstract the access to the proxy table.
 */
class Phprojekt_Auth_ProxyTable
{
    protected $_proxyTable = null;

    /**
     * Constructor
     */
    public function __construct($db = null)
    {
        if (is_null($db)) {
            $db = Phprojekt::getInstance()->getDb();
        }

        $this->_proxyTable = new Zend_Db_Table(array(
            'db' => $db,
            'name' => 'user_proxy'
        ));
    }

    /**
     * Sets the user ids that have proxy rights for the proxided user id.
     *
     * @param array     $proxyIds   Array of userIds that should be allowed to impersonate the provided user id.
     * @param integer   $user       Id of the impersonated user.
     *                              If not set, the current userId is used.
     *
     * @return void
     */
    public function setProxyIdsForUserId(Array $proxyIds, $userId = null)
    {
        $userId = $this->_currentUserIdIfNull($userId);

        $this->_throwOnInvalidUserId($userId);

        $this->_clearProxiesForUserId($userId);
        $values  = array();

        foreach ($proxyIds as $proxyUserId) {
            if ($this->_isValidUserId($proxyUserId)) {
                $values[] = array(  'proxying_id' => (int) $proxyUserId,
                    'proxyed_id' => (int) $userId);
            }
        }

        $this->_insertRowsIntoProxyTable($values);
    }

    /**
     * Returns a list of user ids the given user is capable of impersonating.
     *
     * @param integer $userId   The user to get the proxyables user ids from.
     *                          If not set, the current userId is used.
     *
     * @return array            Array of userIds the user can impersonate.
     */
    public function getProxyableUserIdsForUserId($userId = null)
    {
        $userId = $this->_currentUserIdIfNull($userId);
        $this->_throwOnInvalidUserId($userId);

        $userIdList = array();

        $select = $this->_proxyTable->select();
        $select->where('proxying_id = ?', (int) $userId);

        $rows = $this->_proxyTable->fetchAll($select);

        foreach ($rows as $row) {
            $userIdList[] = $row['proxyed_id'];
        }

        return $userIdList;
    }

    /**
     * Returns a list of users the given user is capable of impersonating.
     *
     * @param integer $userId   The user to get the proxyables user ids from.
     *                          If not set, the current userId is used.
     *
     * @return array            Array of users the user can impersonate.
     */
    public function getProxyableUsersForUserId($userId = null)
    {
        $userIds   = $this->getProxyableUserIdsForUserId($userId);

        $userList = array();

        foreach ($userIds as $userId) {
            $userClass  = new Phprojekt_User_User();
            $userList[] = $userClass->find($userId);
        }

        return $userList;
    }

    /**
     * Returns a list of user ids that can impersonate the current user.
     *
     * @param integer $userId   Id of the impersonated user
     *                          If not set, the current userId is used.
     *
     * @return array    Array of userids that can impersonate the current user.
     */
    public function getProxyIdsForUserId($userId = null)
    {
        $userId = $this->_currentUserIdIfNull($userId);

        $this->_throwOnInvalidUserId($userId);

        $select = $this->_proxyTable->select();
        $select->where('proxyed_id = ?', (int) $userId);

        $rows = $this->_proxyTable->fetchAll($select);

        $userlist = array();

        foreach ($rows as $row) {
            $userlist[] = $row['proxying_id'];
        }

        return $userlist;
    }

    /**
     * Checks whether the source user id has proxy rights for the target userid.
     *
     * @param integer $sourceUserId     The user requesting the right.
     * @param integer $targetUserId     The user which will be proxyed
     *
     * @return Boolean  True if the source has the right, false otherwise
     */
    public function hasProxyRights($sourceUserId, $targetUserId)
    {
        $this->_throwOnInvalidUserId($sourceUserId);
        $this->_throwOnInvalidUserId($targetUserId);

        $select = $this->_proxyTable->select();
        $select->where('proxyed_id = ?', (int) $targetUserId)
                ->where('proxying_id = ?', (int) $sourceUserId);

        $row = $this->_proxyTable->fetchRow($select);

        return !is_null($row);
    }

    protected function _currentUserIdIfNull($userId)
    {
        if (is_null($userId)) {
            $userId = Phprojekt_Auth::getUserId();
        }

        return $userId;
    }

    protected function _throwOnInvalidUserId($userId)
    {
        if (!$this->_isValidUserId($userId)) {
            throw new Phprojekt_Auth_Exception("Invalid UserId $userId");
        }
    }

    protected function _isValidUserId($userId)
    {
        if (!is_numeric($userId) || $userId < 1) {
            return false;
        }

        $validUser = false;
        $user = new Phprojekt_User_User();

        if ($user->find((int) $userId)) {
            $validUser =  true;
        }

        return $validUser;

    }

    protected function _clearProxiesForUserId($userId)
    {
        $where = $this->_proxyTable->getAdapter()->quoteInto('proxyed_id = ?', $userId);
        $this->_proxyTable->delete($where);
    }

    protected function _insertRowsIntoProxyTable($rows = array()) {
        foreach ($rows as $row) {
            $this->_proxyTable->insert($row);
        }
    }
}
