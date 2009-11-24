<?php
/**
 * Meta information about the data provided by the MinutesItem model.
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
 * @author     Sven Rautenberg <sven.rautenberg@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Meta information about the data provided by the MinutesItem model.
 *
 * The fields are hardcoded.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @author     Sven Rautenberg <sven.rautenberg@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */
class Minutes_SubModules_MinutesItem_Models_MinutesItemInformation extends EmptyIterator implements Phprojekt_ModelInformation_Interface
{
    /**
     * @var array List of available topic types.
     */
    protected static $_topicTypeListTemplate = array(
            1 => 'Topic',
            2 => 'Statement',
            3 => 'Todo',
            4 => 'Decision',
            5 => 'Date',
        );

    /**
     * Stores the translated list of topic types
     *
     * @var array
     */
    protected static $_topicTypeList = array();

    /**
     * Stores the list of user names in display format
     *
     * @var array
     */
    protected static $_userIdList    = array();

    /**
     * Stores the list of projects
     *
     * @var array
     */
    protected static $_projectList   = array();

    /**
     * Lazy load the topicType translation list.
     *
     * @return array _topicTypeList
     */
    protected function _getTopicTypeList()
    {
        if (array() === self::$_topicTypeList) {
            foreach (self::$_topicTypeListTemplate as $key => $value) {
                self::$_topicTypeList[(int) $key] = Phprojekt::getInstance()->translate($value);
            }
        }
        return self::$_topicTypeList;
    }

    /**
     * Returns the translated text of a given topicType id, or NULL if undefined.
     *
     * @param int $topicTypeValue
     *
     * @return mixed
     */
    public function getTopicType($topicTypeValue)
    {
        $types = $this->_getTopicTypeList();
        return (isset($types[$topicTypeValue])? $types[$topicTypeValue] : NULL);
    }

    /**
     * Lazy load the userId list
     *
     * @return array _userIdList
     */
    protected function _getUserIdList()
    {
        if (array() === self::$_userIdList) {
            /* @var $user Phprojekt_User_User */
            $user        = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
            $displayname = $user->getDisplay();
            $users       = $user->fetchAll(null, $displayname);
            foreach ($users as $node) {
                self::$_userIdList[$node->id] = $node->applyDisplay($displayname, $node);
            }
        }
        return self::$_userIdList;
    }

    /**
     * Returns the displayName of a given user id, or NULL if undefined.
     *
     * @param int $userId
     *
     * @return mixed
     */
    public function getUserName($userId)
    {
        $users = $this->_getUserIdList();
        return (isset($users[$userId])? $users[$userId] : NULL);
    }

    /**
     * Converts array into form used by json
     *
     * @param array  $array     The array to be converted
     * @param string $keyname   The identifier to be used for key values
     * @param string $valuename The identifier to be used for value names
     *
     * @return array
     */
    public function convertArray(array $array, $keyname = 'id', $valuename = 'name')
    {
        $result = array();
        foreach ($array as $key => $value) {
            $result[] = array($keyname => $key, $valuename => $value);
        }
        return $result;
    }

    /**
     * Returns an array with empty default values for a model field
     *
     * @return array
     */
    protected function _getFieldTemplate()
    {
        return array(
            'key'      => '',
            'label'    => '',
            'type'     => '',
            'hint'     => '',
            'order'    => 0,
            'position' => 0,
            'fieldset' => '',
            'range'    => $this->convertarray(array('' => '')),
            'required' => false,
            'readOnly' => false,
            'tab'      => 1,
            'integer'  => false,
            'length'   => 0,
            'default'  => null);
    }

    /**
     * Returns an array filled with mandatory data and optional keys. Undefined keys are stripped.
     *
     * @param string $key      Name of the model property
     * @param string $label    Label of the form field (will get translated)
     * @param string $type     Type of the form control
     * @param string $hint     Tooltip text index (will get translated)
     * @param int    $position Position of the field in the form
     * @param array  $data     Optional additional keys
     *
     * @return array
     */
    protected function _fillTemplate($key, $label, $type, $hint, $position, array $data = array())
    {
        $result = $this->_getFieldTemplate();

        foreach ($data as $index => $value) {
            if (isset($result[$index])) {
                $result[$index] = $value;
            }
        }

        $result['key']      = $key;
        $result['label']    = Phprojekt::getInstance()->translate($label);
        $result['type']     = $type;
        $result['hint']     = Phprojekt::getInstance()->getTooltip($hint);
        $result['position'] = (int) $position;

        return $result;
    }

    /**
     * Return an array of field information.
     *
     * @return array
     */
    public function getFieldDefinition()
    {
        $converted = array();

        // projectId
        $converted[] = $this->_fillTemplate('projectId', 'Project', 'hidden', 'projectId', 0, array(
            'required' => true,
            'readOnly' => true,
            'integer'  => true));

        // minutesId
        $converted[] = $this->_fillTemplate('minutesId', 'minutesId', 'hidden', 'minutesId', 0, array(
            'required' => true,
            'readOnly' => true,
            'integer'  => true));

        // topicId
        $converted[] = $this->_fillTemplate('topicId', 'Id', 'hidden', 'topicId', 0, array(
            'readOnly' => true,
            'integer'  => false));

        // sortOrder
        $converted[] = $this->_fillTemplate('sortOrder', 'Sort after', 'hidden', 'sortOrder', 1, array(
            'integer'  => true));

        // title
        $converted[] = $this->_fillTemplate('title', 'Title', 'text', 'title', 2, array(
            'length'   => 255,
            'required' => true));

        // topicType
        $converted[] = $this->_fillTemplate('topicType', 'Type', 'selectbox', 'topicType', 3, array(
            'range'    => $this->convertArray($this->_getTopicTypeList()),
            'required' => true,
            'integer'  => true));

        // comment
        $converted[] = $this->_fillTemplate('comment', 'Comment', 'textarea', 'comment', 4);

        // topicDate
        $converted[] = $this->_fillTemplate('topicDate', 'Date', 'date', 'topicDate', 5);

        // userId
        $converted[] = $this->_fillTemplate('userId', 'Who', 'selectbox', 'userId', 6, array(
            'range' => $this->convertArray($this->_getUserIdList())));

        return $converted;
    }
}
