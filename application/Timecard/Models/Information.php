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
 * @version    CVS: $Id: Interface.php 635 2008-04-02 19:32:05Z david $
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
    public function getFieldDefinition($ordering = 'month')
    {
        $converted = array();
        $translate = Zend_Registry::get('translate');

        switch ($ordering) {
            case 'today':
            default:
                // Sum of hours
                $data = array();
                $data['key']      = 'startTime';
                $data['label']    = $translate->translate('startTime');
                $data['type']     = 'time';
                $data['hint']     = $translate->translate('startTime');
                $data['order']    = 0;
                $data['position'] = 2;
                $data['fieldset'] = '';
                $data['range']    = array('id'   => '',
                                          'name' => '');
                $data['required'] = true;
                $data['readOnly'] = false;
                $data['tab']      = 1;

                $converted[] = $data;

                // Sum of hours
                $data = array();
                $data['key']      = 'endTime';
                $data['label']    = $translate->translate('endTime');
                $data['type']     = 'time';
                $data['hint']     = $translate->translate('endTime');
                $data['order']    = 0;
                $data['position'] = 3;
                $data['fieldset'] = '';
                $data['range']    = array('id'   => '',
                                          'name' => '');
                $data['required'] = false;
                $data['readOnly'] = false;
                $data['tab']      = 1;

                $converted[] = $data;
                break;
            case 'month':
                // date
                $data = array();
                $data['key']      = 'date';
                $data['label']    = $translate->translate('date');
                $data['type']     = 'date';
                $data['hint']     = $translate->translate('date');
                $data['order']    = 0;
                $data['position'] = 1;
                $data['fieldset'] = '';
                $data['range']    = array('id'   => '',
                                  'name' => '');
                $data['required'] = true;
                $data['readOnly'] = true;
                $data['tab']      = 1;

                $converted[] = $data;

                // Sum of hours
                $data = array();
                $data['key']      = 'sum';
                $data['label']    = $translate->translate('Working Times');
                $data['type']     = 'time';
                $data['hint']     = $translate->translate('Working Times');
                $data['order']    = 0;
                $data['position'] = 2;
                $data['fieldset'] = '';
                $data['range']    = array('id'   => '',
                                          'name' => '');
                $data['required'] = true;
                $data['readOnly'] = true;
                $data['tab']      = 1;

                $converted[] = $data;

                // Sum of Bookinks
                $data = array();
                $data['key']      = 'bookings';
                $data['label']    = $translate->translate('Project Bookings');
                $data['type']     = 'time';
                $data['hint']     = $translate->translate('Project Bookings');
                $data['order']    = 0;
                $data['position'] = 3;
                $data['fieldset'] = '';
                $data['range']    = array('id'   => '',
                                          'name' => '');
                $data['required'] = true;
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
        switch ($ordering) {
            default:
                $result = array();
                break;
        }

        return $result;
    }
}
