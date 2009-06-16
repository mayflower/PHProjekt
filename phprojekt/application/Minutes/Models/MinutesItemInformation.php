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
class Minutes_Models_MinutesItemInformation extends EmptyIterator implements Phprojekt_ModelInformation_Interface
{
    /**
     * @var array List of available topic types. Keywords need to be translated.
     */
    protected static $_topicTypeListTemplate = array(
            1 => 'TOPIC',
            2 => 'STATEMENT',
            3 => 'TODO',
            4 => 'DECISION',
            5 => 'DATE',
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
                self::$_topicTypeList[$key] = Phprojekt::getInstance()->translate($value);
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
     * Lazy load the projectList list
     *
     * @return array _projectList
     */
    protected function _getProjectList()
    {
        if (array() === self::$_projectList) {
            /* @var $projectModel Project_Models_Project */
            $projectModel = Phprojekt_Loader::getModel('Project', 'Project');
            $tree         = new Phprojekt_Tree_Node_Database($projectModel, 1);
            $tree->setup();
            foreach ($tree as $node) {
                self::$_projectList[$node->id] = str_repeat('....', $node->getDepth()) . $node->title;
            }
        }
        return self::$_projectList;
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
            'integer'  => false);
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

        $result['key']   = $key;
        $result['label'] = Phprojekt::getInstance()->translate($label);
        $result['type']  = $type;
        $result['hint']  = Phprojekt::getInstance()->getTooltip($hint);
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
        $converted[] = $this->_fillTemplate('projectId', 'Project', 'selectbox', 'projectId', 2, array(
            'range'    => $this->convertArray($this->_getProjectList()),
            'required' => true,
            'readOnly' => true,
            'integer'  => true));

        // minutesId
        $converted[] = $this->_fillTemplate('minutesId', 'minutesId', 'display', 'minutesId', 3, array(
            'required' => true,
            'readOnly' => true,
            'integer'  => true));

        // topicId
        $converted[] = $this->_fillTemplate('topicId', 'topicId', 'display', 'topicId', 4, array(
            'readOnly' => true,
            'integer'  => true));

        // topicType
        $converted[] = $this->_fillTemplate('topicType', 'topicType', 'selectbox', 'topicType', 5, array(
            'range'    => $this->convertArray($this->_getTopicTypeList()),
            'required' => true,
            'integer'  => true));

        // sortOrder
        $converted[] = $this->_fillTemplate('sortOrder', 'sortOrder', 'integer', 'sortOrder', 6);

        // title
        $converted[] = $this->_fillTemplate('title', 'title', 'text', 'title', 7, array('required' => true));

        // comment
        $converted[] = $this->_fillTemplate('comment', 'comment', 'textarea', 'comment', 8);

        // topicDate
        $converted[] = $this->_fillTemplate('topicDate', 'topicDate', 'date', 'topicDate', 9);

        // userId
        $converted[] = $this->_fillTemplate('userId', 'userId', 'selectbox', 'userId', 10, array(
            'range'    => $this->convertArray($this->_getUserIdList()),
            ));

        return $converted;
    }

    /**
     * Return an array with titles to simplify things
     *
     * @param integer $ordering An ordering constant (ORDERING_DEFAULT, etc)
     *
     * @return array
     */
    public function getTitles($ordering = Phprojekt_ModelInformation_Default::ORDERING_DEFAULT)
    {
        return array();
    }
}
