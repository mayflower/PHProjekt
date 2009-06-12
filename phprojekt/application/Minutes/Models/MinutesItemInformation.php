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
     * @var list of available topic types. Keywords need to be translated.
     */
    protected $_topicTypeList = array(
            1 => 'TOPIC',
            2 => 'STATEMENT',
            3 => 'TODO',
            4 => 'DECISION',
            5 => 'DATE',
        );

    protected $_userIdList  = array();
    protected $_projectList = array();

    /**
     * Constructor fills metadata
     */
    public function __construct()
    {
        // Fill variable metadata into structure.
        /* @var $user Phprojekt_User_User */
        $user        = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
        $displayname = $user->getDisplay();
        $users       = $user->fetchAll(null, $displayname);
        foreach ($users as $node) {
            $this->_userIdList[$node->id] = $node->applyDisplay($displayname, $node);
        }

        foreach ($this->_topicTypeList as $key => $value) {
            $this->_topicTypeList[$key] = Phprojekt::getInstance()->translate($value);
        }

        $activeRecord = Phprojekt_Loader::getModel('Project', 'Project');
        $tree = new Phprojekt_Tree_Node_Database($activeRecord, 1);
        $tree->setup();
        $this->_projectList = array();
        foreach ($tree as $node) {
            $this->_projectList[$node->id] = str_repeat('....', $node->getDepth()) . $node->title;
        }
    }

    public function getTopicType($topicTypeValue)
    {
        return (isset($this->_topicTypeList[$topicTypeValue])? $this->_topicTypeList[$topicTypeValue] : NULL);
    }

    public function getUserName($userId)
    {
        return (isset($this->_userIdList[$userId])? $this->_userIdList[$userId] : NULL);
    }

    /**
     * Converts array into form used by json
     */
    public function convertArray(Array $array, $keyname = 'id', $valuename = 'name')
    {
        $result = array();
        foreach ($array as $key => $value) {
            $result[] = array($keyname => $key, $valuename => $value);
        }
        return $result;
    }

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

    protected function _fillTemplate(Array $data)
    {
        $result = $this->_getFieldTemplate();
        foreach ($data as $key => $value) {
            if (isset($result[$key])) {
                $result[$key] = $value;
            }
        }
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
        $converted[] = $this->_fillTemplate(array(
            'key'      => 'projectId',
            'label'    => Phprojekt::getInstance()->translate('Project'),
            'type'     => 'selectbox',
            'hint'     => Phprojekt::getInstance()->getTooltip('projectId'),
            'position' => 2,
            'range'    => $this->convertArray($this->_projectList),
            'required' => true,
            'readOnly' => true,
            'integer'  => true));

        // minutesId
        $converted[] = $this->_fillTemplate(array(
            'key'      => 'minutesId',
            'label'    => Phprojekt::getInstance()->translate('minutesId'),
            'type'     => 'display',
            'hint'     => Phprojekt::getInstance()->getTooltip('minutesId'),
            'position' => 3,
            'required' => true,
            'readOnly' => true,
            'integer'  => true));

        // topicId
        $converted[] = $this->_fillTemplate(array(
            'key'      => 'topicId',
            'label'    => Phprojekt::getInstance()->translate('topicId'),
            'type'     => 'display',
            'hint'     => Phprojekt::getInstance()->getTooltip('topicId'),
            'position' => 4,
            'readOnly' => true,
            'integer'  => true));

        // topicType
        $converted[] = $this->_fillTemplate(array(
            'key'      => 'topicType',
            'label'    => Phprojekt::getInstance()->translate('topicType'),
            'type'     => 'selectbox',
            'hint'     => Phprojekt::getInstance()->getTooltip('topicType'),
            'position' => 5,
            'range'    => $this->convertArray($this->_topicTypeList),
            'required' => true,
            'integer'  => true));

        // sortOrder
        $converted[] = $this->_fillTemplate(array(
            'key'      => 'sortOrder',
            'label'    => Phprojekt::getInstance()->translate('sortOrder'),
            'type'     => 'integer',
            'hint'     => Phprojekt::getInstance()->getTooltip('sortOrder'),
            'position' => 6));

        // title
        $converted[] = $this->_fillTemplate(array(
            'key'      => 'title',
            'label'    => Phprojekt::getInstance()->translate('title'),
            'type'     => 'text',
            'hint'     => Phprojekt::getInstance()->getTooltip('title'),
            'position' => 6,
            'required' => true));

        // comment
        $converted[] = $this->_fillTemplate(array(
            'key'      => 'comment',
            'label'    => Phprojekt::getInstance()->translate('comment'),
            'type'     => 'textarea',
            'hint'     => Phprojekt::getInstance()->getTooltip('comment'),
            'position' => 7));

        // topicDate
        $converted[] = $this->_fillTemplate(array(
            'key'      => 'topicDate',
            'label'    => Phprojekt::getInstance()->translate('topicDate'),
            'type'     => 'date',
            'hint'     => Phprojekt::getInstance()->getTooltip('topicDate'),
            'position' => 8));

        // userId
        $converted[] = $this->_fillTemplate(array(
            'key'      => 'userId',
            'label'    => Phprojekt::getInstance()->translate('userId'),
            'type'     => 'selectbox',
            'hint'     => Phprojekt::getInstance()->getTooltip('userId'),
            'position' => 9,
            'range'    => $this->convertArray($this->_userIdList)));

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
        switch ($ordering) {
            default:
                $result = array();
                break;
        }

        return $result;
    }
}
