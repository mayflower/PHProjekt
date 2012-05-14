<?php
/**
 * Tags-User relation class.
 *
 * The class provide the functions for manage the relation between
 * tags and users.
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
 * @subpackage Tags
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
 * Tags-User relation class.
 *
 * The class provide the functions for manage the relation between
 * tags and users.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Tags
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_Tags_Users extends Zend_Db_Table_Abstract
{
    /**
     * Table name.
     *
     * @var string
     */
    protected $_name = 'tags_users';

    /**
     * User ID, Use the current userId.
     *
     * @var integer
     */
    private $_user = 0;

    /**
     * Constructs a Phprojekt_Tags_Users.
     *
     * @return void
     */
    public function __construct()
    {
        $this->_user = Phprojekt_Auth::getUserId();
        parent::__construct(array('db' => Phprojekt::getInstance()->getDb()));
    }

    /**
     * Save a relation current user <-> tagId.
     *
     * This function use the Zend_DB insert.
     * First check if the pair don´t exist.
     *
     * @param integer $tagId The Tag ID.
     *
     * @return integer ID of user-tag relation.
     */
    public function saveTags($tagId)
    {
        $id    = 0;
        $where = sprintf('user_id = %d AND tag_id = %d', (int) $this->_user, (int) $tagId);

        $record = $this->fetchAll($where);
        if ($record->count() == 0) {
            $data['user_id'] = $this->_user;
            $data['tag_id']  = $tagId;
            $id = $this->insert($data);
        } else {
            $records = current((array) $record);
            $record  = array_shift($records);
            $id      = $record['id'];
        }

        return $id;
    }

    /**
     * Get all the user-tags relation for the current user.
     *
     * If the $tagId is seted, only return the relation between the current user and this tagId.
     *
     * @param integer $userId User ID to check.
     * @param integer $tagId  Optional tag ID.
     *
     * @return array Array with results.
     */
    public function getUserTagIds($userId = 0, $tagId = 0)
    {
        $where = array();

        if ($userId == 0) {
            $userId = $this->_user;
        }

        $where = sprintf('user_id = %d', (int) $userId);
        if ($tagId > 0) {
            $where .= sprintf(' AND tag_id = %d', (int) $tagId);
        }
        $select = $this->select();
        $select->from($this->_name, array('id', 'tag_id'))
               ->where($where);
        $tmpResult = $this->fetchAll($select)->toArray();

        // Convert result to array
        $foundResults = array();
        foreach ($tmpResult as $data) {
            $foundResults[$data['id']] = $data['tag_id'];
        }

        return $foundResults;
    }

    /**
     * Return if one relation id is from the current user.
     *
     * @param integer $id Relation ID.
     *
     * @return boolean True if the relation is for the user.
     */
    public function isFromUser($id)
    {
        $records = current($this->find($id));
        $record  = array_shift($records);

        return ($record['user_id'] == $this->_user);
    }

    /**
     * Return the tagId for on ID.
     *
     * @param integer $id ID of the relation.
     *
     * @return integer Tag ID.
     */
    public function getTagId($id)
    {
        $records = current($this->find($id));
        $record  = array_shift($records);

        return $record['tag_id'];
    }

    /**
     * Delete all the entries for one userId.
     *
     * @param integer $userId ID of user to delete all tags.
     *
     * @return void
     */
    public function deleteUserTags($userId)
    {
        $where = sprintf('user_id = %d', (int) $userId);
        $this->delete($where);
    }
}
