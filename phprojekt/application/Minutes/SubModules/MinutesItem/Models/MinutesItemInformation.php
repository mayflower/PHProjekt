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
 * Meta information about the data provided by the MinutesItem model.
 *
 * The fields are hardcoded.
 */
class Minutes_SubModules_MinutesItem_Models_MinutesItemInformation extends Phprojekt_ModelInformation_Default
{
    /**
     * List of available topic types.
     *
     * @var array
     */
    protected static $_topicTypeListTemplate = array(
            1 => 'Topic',
            2 => 'Statement',
            3 => 'Todo',
            4 => 'Decision',
            5 => 'Date',
        );

    /**
     * Stores the translated list of topic types.
     *
     * @var array
     */
    protected static $_topicTypeList = array();

    /**
     * Stores the list of user names in display format.
     *
     * @var array
     */
    protected static $_userIdList = array();

    /**
     * Lazy load the topicType translation list.
     *
     * @return array $_topicTypeList
     */
    protected function _getTopicTypeList()
    {
        if (array() === self::$_topicTypeList) {
            foreach (self::$_topicTypeListTemplate as $key => $value) {
                self::$_topicTypeList[(int) $key] = $this->getFullRangeValues((int) $key, $value);
            }
        }
        return self::$_topicTypeList;
    }

    /**
     * Returns the translated text of a given topicType id, or NULL if undefined.
     *
     * @param integer $topicTypeValue Topic type.
     *
     * @return string|null Display of the topic type.
     */
    public function getTopicType($topicTypeValue)
    {
        $types = $this->_getTopicTypeList();

        return (isset($types[$topicTypeValue]['name'])? $types[$topicTypeValue]['name'] : null);
    }

    /**
     * Lazy load the userId list.
     *
     * @return array $_userIdList
     */
    protected function _getUserIdList()
    {
        if (empty(self::$_userIdList)) {
            /* @var $user Phprojekt_User_User */
            $user  = new Phprojekt_User_User();
            $users = $user->getAllowedUsers();
            foreach ($users as $node) {
                self::$_userIdList[$node['id']] = $this->getRangeValues($node['id'], $node['name']);
            }
        }

        return self::$_userIdList;
    }

    /**
     * Returns the displayName of a given user id, or null if undefined.
     *
     * @param integer $userId The user ID.
     *
     * @return string|null User display.
     */
    public function getUserName($userId)
    {
        $users = $this->_getUserIdList();

        return (isset($users[$userId]['name'])? $users[$userId]['name'] : null);
    }

    /**
     * Sets a fields definitions for each field.
     *
     * @return void
     */
    public function setFields()
    {
        // projectId
        $this->fillField('projectId', 'Project', 'hidden', 1, 0, array(
            'required' => true,
            'readOnly' => true,
            'integer'  => true));

        // minutesId
        $this->fillField('minutesId', 'minutesId', 'hidden', 2, 0, array(
            'required' => true,
            'readOnly' => true,
            'integer'  => true));

        // topicId
        $this->fillField('topicId', 'Id', 'hidden', 3, 0, array(
            'readOnly' => true,
            'integer'  => false));

        // sortOrder
        $this->fillField('sortOrder', 'Sort after', 'hidden', 4, 1, array(
            'integer'  => true));

        // title
        $this->fillField('title', 'Title', 'text', 5, 2, array(
            'length'   => 255,
            'required' => true));

        // topicType
        $this->fillField('topicType', 'Type', 'selectbox', 6, 3, array(
            'range'    => array_values($this->_getTopicTypeList()),
            'required' => true,
            'integer'  => true));

        // comment
        $this->fillField('comment', 'Comment', 'textarea', 7, 4);

        // topicDate
        $this->fillField('topicDate', 'Date', 'date', 8, 5);

        // userId
        $this->fillField('userId', 'Who', 'selectbox', 9, 6, array(
            'range' => array_values($this->_getUserIdList())));
    }
}
