<?php
/**
 * Convert a model into a json structure.
 * This is usually done by a controller to send data to the
 * client
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
 * Convert a model into a json structure.
 * This is usally done by a controller to send data to the client.
 * The Phprojekt_Convert_Json takes care that a apporpriate structure
 * is made from the given model.
 *
 * The fields are hardcore.
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
class Timecard_Models_Information extends EmptyIterator implements Phprojekt_ModelInformation_Interface
{
    /**
     * Return an array of field information.
     *
     * @param string $ordering Type of view
     *
     * @return array
     */
    public function getFieldDefinition($ordering = Phprojekt_ModelInformation_Default::ORDERING_DEFAULT)
    {
        $converted = array();

        switch ($ordering) {
            default:
                // start_datetime
                $data = array();

                $data['key']      = 'startDatetime';
                $data['label']    = Phprojekt::getInstance()->translate('Start');
                $data['type']     = 'datetime';
                $data['hint']     = Phprojekt::getInstance()->getTooltip('startDatetime');
                $data['order']    = 0;
                $data['position'] = 1;
                $data['fieldset'] = '';
                $data['range']    = array('id'   => '',
                                          'name' => '');
                $data['required'] = true;
                $data['readOnly'] = false;
                $data['tab']      = 1;
                $data['integer']  = false;
                $data['length']   = 0;
                $data['default']  = null;

                $converted[] = $data;

                // endTime
                $data = array();

                $data['key']      = 'endTime';
                $data['label']    = Phprojekt::getInstance()->translate('End');
                $data['type']     = 'time';
                $data['hint']     = Phprojekt::getInstance()->getTooltip('startTime');
                $data['order']    = 0;
                $data['position'] = 2;
                $data['fieldset'] = '';
                $data['range']    = array('id'   => '',
                                          'name' => '');
                $data['required'] = false;
                $data['readOnly'] = false;
                $data['tab']      = 1;
                $data['integer']  = false;
                $data['length']   = 0;
                $data['default']  = null;

                $converted[] = $data;

                // minutes
                $data = array();

                $data['key']      = 'minutes';
                $data['label']    = Phprojekt::getInstance()->translate('Minutes');
                $data['type']     = 'text';
                $data['hint']     = Phprojekt::getInstance()->getTooltip('minutes');
                $data['order']    = 0;
                $data['position'] = 3;
                $data['fieldset'] = '';
                $data['range']    = array('id'   => '',
                                          'name' => '');
                $data['required'] = false;
                $data['readOnly'] = false;
                $data['tab']      = 1;
                $data['integer']  = true;
                $data['length']   = 0;
                $data['default']  = null;

                $converted[] = $data;

                // projectId
                $data = array();

                $data['key']      = 'projectId';
                $data['label']    = Phprojekt::getInstance()->translate('Project');
                $data['type']     = 'time';
                $data['hint']     = Phprojekt::getInstance()->getTooltip('projectId');
                $data['order']    = 0;
                $data['position'] = 4;
                $data['fieldset'] = '';
                $data['range']    = array();
                $data['type']     = 'selectbox';

                $activeRecord = Phprojekt_Loader::getModel('Project', 'Project');
                $tree         = new Phprojekt_Tree_Node_Database($activeRecord, 1);
                $tree         = $tree->setup();
                foreach ($tree as $node) {
                    $data['range'][] = array('id'   => (int) $node->id,
                                             'name' => $node->getDepthDisplay('title'));
                }
                $data['required'] = true;
                $data['readOnly'] = false;
                $data['tab']      = 1;
                $data['integer']  = true;
                $data['length']   = 0;
                $data['default']  = null;

                $converted[] = $data;

                // notes
                $data = array();

                $data['key']      = 'notes';
                $data['label']    = Phprojekt::getInstance()->translate('Notes');
                $data['type']     = 'textarea';
                $data['hint']     = Phprojekt::getInstance()->getTooltip('notes');
                $data['order']    = 0;
                $data['position'] = 5;
                $data['fieldset'] = '';
                $data['range']    = array('id'   => '',
                                          'name' => '');
                $data['required'] = false;
                $data['readOnly'] = false;
                $data['tab']      = 1;
                $data['integer']  = false;
                $data['length']   = 0;
                $data['default']  = null;

                $converted[] = $data;
                break;
        }

        return $converted;
    }
}
