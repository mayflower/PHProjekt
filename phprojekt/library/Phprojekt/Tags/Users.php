<?php
/**
 * Tags-User relation class
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
 * @version    $Id:$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * The class provide the functions for manage the relation between
 * tags and users
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @subpackage Core
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_Tags_Users extends Zend_Db_Table_Abstract
{
    /**
     * Table name
     *
     * @var string
     */
    protected $_name = 'TagsUsers';

    /**
     * User ID, Use the current userId
     *
     * @var integer
     */
    private $_user = 0;

    /**
     * Constructs a Phprojekt_Tags_Users
     */
    public function __construct()
    {
        $this->_user = Phprojekt_Auth::getUserId();
        parent::__construct(array('db' => Zend_Registry::get('db')));
    }

    /**
     * Save a relation current user <-> tagId
     *
     * This function use the Zend_DB insert
     * First check if the pair don�t exist
     *
     * @param integer $tagId  The Tagid
     *
     * @return integer
     */
    public function saveTags($tagId)
    {
        $where = array();
        $where[] = 'userId = '. $this->getAdapter()->quote($this->_user);
        $where[] = 'tagId  = '. $this->getAdapter()->quote($tagId);

        $record = $this->fetchAll($where);
        if ($record->count() == 0) {
            $data['userId'] = $this->_user;
            $data['tagId']  = $tagId;
            return $this->insert($data);
        } else {
            $record = array_shift(current((array) $record));
            return $record['id'];
        }
    }

    /**
     * Get all the user-tags relation for the current user
     *
     * If the $tagId is seted
     * only return the relation between the current user and this tagId
     *
     * @param integer $tagId Optional tagId
     *
     * @return array
     */
    public function getUserTagIds($userId = 0, $tagId = 0)
    {
        $where = array();

        if ($userId == 0) {
            $userId = $this->_user;
        }
        $where[]   = 'userId = '. $this->getAdapter()->quote($userId);
        if ($tagId > 0) {
            $where[]   = 'tagId = '. $this->getAdapter()->quote($tagId);
        }
        $tmpResult = $this->fetchAll($where)->toArray();

        // Convert result to array
        $foundResults = array();
        foreach ($tmpResult as $data) {
            $foundResults[$data['id']] = $data['tagId'];
        }

        return $foundResults;
    }

    /**
     * Return if one relation id is from the current user
     *
     * @param integer $id Relation Id
     *
     * @return array
     */
    public function isFromUser($id)
    {
        $record = array_shift(current($this->find($id)));
        return ($record['userId'] == $this->_user);
    }

    /**
     * Return the tagId for on id
     *
     * @param integer $id Id of the relation
     *
     * @return integer
     */
    public function getTagId($id)
    {
        $record = array_shift(current($this->find($id)));
        return $record['tagId'];
    }

    /**
     * Delete all the entries for one userId
     *
     * @param integer $userId Id of user to delete all tags
     *
     * @return void
     */
    public function deleteUserTags($userId)
    {
        $clone   = clone($this);
        $where   = array();
        $where[] = 'userId = '. $clone->getAdapter()->quote($userId);
        $clone->delete($where);
    }
}
