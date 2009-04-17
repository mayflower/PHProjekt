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
     * Return an array of field information.
     *
     * @todo The meta info has to be refactored to fit the db table
     * 
     * @param string $ordering Sort
     *
     * @return array
     */
    public function getFieldDefinition($ordering = Phprojekt_ModelInformation_Default::ORDERING_DEFAULT)
    {
        $converted = array();

        switch ($ordering) {
            default:
                // ownerId
                $data = array();
                $data['key']      = 'ownerId';
                $data['label']    = Phprojekt::getInstance()->translate('ownerId');
                $data['type']     = 'display';
                $data['hint']     = Phprojekt::getInstance()->getTooltip('ownerId');
                $data['order']    = 0;
                $data['position'] = 1;
                $data['fieldset'] = '';
                $data['range']    = array('id'   => '',
                                          'name' => '');
                $data['required'] = true;
                $data['readOnly'] = true;
                $data['tab']      = 1;

                $converted[] = $data;

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
                $data['required'] = true;
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
                $data['range']    = array( array('id'   => '1',
                                                 'name' => 'Topic'),
                                           array('id'   => '2',
                                                 'name' => 'Comment'),
                                          );
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
                $data['required'] = true;
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
                $data['range']    = array();
                
                $user = Phprojekt_Loader::getLibraryClass('Phprojekt_User_User');
                $users = $user->fetchAll();
                foreach ($users as $node) {
                    $data['range'][] = array('id'   => $node->id,
                                             'name' => $node->username);
                }
                
                $data['required'] = false;
                $data['readOnly'] = true;
                $data['tab']      = 1;
                
                $converted[] = $data;
                break;
        }
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
