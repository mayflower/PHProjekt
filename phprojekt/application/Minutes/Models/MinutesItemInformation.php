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
    
    protected $_userIdList = array();

    /**
     * Constructor fills metadata
     */
    public function __construct()
    {
        // Fill variable metadata into structure.
        $user = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
        $users = $user->fetchAll();
        $displayname = $user->getDisplay();
        foreach ($users as $node) {
            $this->_userIdList[$node->id] = $node->applyDisplay($displayname, $node);
        }

    }
    
    public function getTopicType($topicType)
    {
        return (isset($this->_topicTypeList[$topicType])? $this->_topicTypeList[$topicType] : NULL);
    }

    public function getUserName($userId)
    {
        return (isset($this->_userIdList[$userId])? $this->_userIdList[$userId] : NULL);
    }
    
    public function convertArray($array, $keyname = 'id', $valuename = 'name')
    {
        $result = array();
        foreach ($array as $key => $value) {
            $result[] = array($keyname => $key, $valuename => $value);
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
        $data = array();
        $data['key']      = 'projectId';
        $data['label']    = Phprojekt::getInstance()->translate('Project');
        $data['type']     = 'selectbox';
        $data['hint']     = Phprojekt::getInstance()->getTooltip('projectId');
        $data['order']    = 0;
        $data['position'] = 2;
        $data['fieldset'] = '';
        $data['range']    = array();
        $activeRecord = Phprojekt_Loader::getModel('Project', 'Project');
        $tree = new Phprojekt_Tree_Node_Database($activeRecord, 1);
        $tree->setup();
        foreach ($tree as $node) {
            $key   = $node->id;
            $value = str_repeat('....', $node->getDepth()) . $node->title;
            $data['range'][] = array('id'   => $key,
                                     'name' => $value);
        }
        $data['required'] = true;
        $data['readOnly'] = true;
        $data['tab']      = 1;

        $converted[] = $data;

        // minutesId
        $data = array();
        $data['key']      = 'minutesId';
        $data['label']    = Phprojekt::getInstance()->translate('minutesId');
        $data['type']     = 'display';
        $data['hint']     = Phprojekt::getInstance()->getTooltip('minutesId');
        $data['order']    = 0;
        $data['position'] = 3;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
                                  'name' => '');
        $data['required'] = true;
        $data['readOnly'] = true;
        $data['tab']      = 1;

        $converted[] = $data;

        // topicId
        $data = array();
        $data['key']      = 'topicId';
        $data['label']    = Phprojekt::getInstance()->translate('topicId');
        $data['type']     = 'display';
        $data['hint']     = Phprojekt::getInstance()->getTooltip('topicId');
        $data['order']    = 0;
        $data['position'] = 4;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
                                  'name' => '');
        $data['required'] = false;
        $data['readOnly'] = false;
        $data['tab']      = 1;

        $converted[] = $data;

        // topicId
        $data = array();
        $data['key']      = 'topicType';
        $data['label']    = Phprojekt::getInstance()->translate('topicType');
        $data['type']     = 'selectbox';
        $data['hint']     = Phprojekt::getInstance()->getTooltip('topicType');
        $data['order']    = 0;
        $data['position'] = 5;
        $data['fieldset'] = '';
        $data['range']    = $this->convertArray($this->_topicTypeList);
        $data['required'] = true;
        $data['readOnly'] = false;
        $data['tab']      = 1;

        $converted[] = $data;

        // sortOrder
        $data = array();
        $data['key']      = 'sortOrder';
        $data['label']    = Phprojekt::getInstance()->translate('sortOrder');
        $data['type']     = 'integer';
        $data['hint']     = Phprojekt::getInstance()->getTooltip('sortOrder');
        $data['order']    = 0;
        $data['position'] = 6;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
                                  'name' => '');
        $data['required'] = false;
        $data['readOnly'] = false;
        $data['tab']      = 1;

        $converted[] = $data;


        // title
        $data = array();
        $data['key']      = 'title';
        $data['label']    = Phprojekt::getInstance()->translate('title');
        $data['type']     = 'text';
        $data['hint']     = Phprojekt::getInstance()->getTooltip('title');
        $data['order']    = 0;
        $data['position'] = 6;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
                                  'name' => '');
        $data['required'] = true;
        $data['readOnly'] = true;
        $data['tab']      = 1;

        $converted[] = $data;

        // comment
        $data = array();
        $data['key']      = 'comment';
        $data['label']    = Phprojekt::getInstance()->translate('comment');
        $data['type']     = 'textarea';
        $data['hint']     = Phprojekt::getInstance()->getTooltip('comment');
        $data['order']    = 0;
        $data['position'] = 7;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
                                  'name' => '');
        $data['required'] = false;
        $data['readOnly'] = true;
        $data['tab']      = 1;

        $converted[] = $data;

        // topicDate
        $data = array();
        $data['key']      = 'topicDate';
        $data['label']    = Phprojekt::getInstance()->translate('topicDate');
        $data['type']     = 'date';
        $data['hint']     = Phprojekt::getInstance()->getTooltip('topicDate');
        $data['order']    = 0;
        $data['position'] = 8;
        $data['fieldset'] = '';
        $data['range']    = array('id'   => '',
                                  'name' => '');
        $data['required'] = false;
        $data['readOnly'] = true;
        $data['tab']      = 1;

        $converted[] = $data;

        // userId
        $data = array();
        $data['key']      = 'userId';
        $data['label']    = Phprojekt::getInstance()->translate('userId');
        $data['type']     = 'selectbox';
        $data['hint']     = Phprojekt::getInstance()->getTooltip('userId');
        $data['order']    = 0;
        $data['position'] = 9;
        $data['fieldset'] = '';
        $data['range']    = $this->convertArray($this->_userIdList);
        $data['required'] = false;
        $data['readOnly'] = true;
        $data['tab']      = 1;

        $converted[] = $data;

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
