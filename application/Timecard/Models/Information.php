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
 * @version    $Id:$
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
        $translate = Phprojekt::getInstance()->getTranslate();

        // date
        $dateData = array();
        $dateData['key']      = 'date';
        $dateData['label']    = $translate->translate('date');
        $dateData['type']     = 'date';
        $dateData['hint']     = $translate->translate('date');
        $dateData['order']    = 0;
        $dateData['position'] = 1;
        $dateData['fieldset'] = '';
        $dateData['range']    = array('id'   => '',
                                      'name' => '');
        $dateData['required'] = true;
        $dateData['readOnly'] = true;
        $dateData['tab']      = 1;

        // startDate
        $startDateData = array();
        $startDateData['key']      = 'startTime';
        $startDateData['label']    = $translate->translate('startTime');
        $startDateData['type']     = 'time';
        $startDateData['hint']     = $translate->translate('startTime');
        $startDateData['order']    = 0;
        $startDateData['position'] = 2;
        $startDateData['fieldset'] = '';
        $startDateData['range']    = array('id'   => '',
                                           'name' => '');
        $startDateData['required'] = true;
        $startDateData['readOnly'] = false;
        $startDateData['tab']      = 1;

        // endDate
        $endDateData = array();
        $endDateData['key']      = 'endTime';
        $endDateData['label']    = $translate->translate('endTime');
        $endDateData['type']     = 'time';
        $endDateData['hint']     = $translate->translate('endTime');
        $endDateData['order']    = 0;
        $endDateDatadata['position'] = 3;
        $endDateData['fieldset'] = '';
        $endDateData['range']    = array('id'   => '',
                                         'name' => '');
        $endDateData['required'] = false;
        $endDateData['readOnly'] = false;
        $endDateData['tab']      = 1;

        switch ($ordering) {
            case 'today':
            default:
                $converted[] = $startDateData;
                $converted[] = $endDateData;
                break;
            case 'export':
                $converted[] = $dateData;
                $converted[] = $startDateData;
                $converted[] = $endDateData;
                break;
            case 'month':
                // date
                $converted[] = $dateData;

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
