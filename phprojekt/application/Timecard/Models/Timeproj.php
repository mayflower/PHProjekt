<?php
/**
 * Timeproj model class
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
 * Timeproj model class
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
class Timecard_Models_Timeproj extends Phprojekt_ActiveRecord_Abstract implements Phprojekt_Model_Interface
{
    /**
     * The standard information manager with hardcoded
     * field definitions
     *
     * @var Phprojekt_ModelInformation_Interface
     */
    protected $_informationManager;

    /**
     * Validate object
     *
     * @var Phprojekt_Model_Validate
     */
    protected $_validate = null;

    /**
     * Initialize new user
     * If is seted the user id in the session,
     * the class will get all the values of these user
     *
     * @param array $db Configuration for Zend_Db_Table
     *
     * @return void
     */
    public function __construct($db = null)
    {
        if (null === $db) {
            $db = Zend_Registry::get('db');
        }
        parent::__construct($db);

        $this->_validate           = new Phprojekt_Model_Validate();
        $this->_informationManager = new Timecard_Models_TimeprojInformation();
    }

    /**
     * Get the information manager
     *
     * @see Phprojekt_Model_Interface::getInformation()
     *
     * @return Phprojekt_ModelInformation_Interface
     */
    public function getInformation()
    {
        return $this->_informationManager;
    }

    /**
     * Get the rigths
     *
     * @return array
     */
    public function getRights()
    {
        return array();
    }

    /**
     * Save the rigths
     *
     * @return void
     */
    public function saveRights()
    {
    }

    /**
     * Validate the current record
     *
     * @return boolean
     */
    public function recordValidate()
    {
        $data      = $this->_data;
        $fields    = $this->_informationManager->getFieldDefinition(Phprojekt_ModelInformation_Default::ORDERING_FORM);
        $translate = Zend_Registry::get('translate');

        $amount = str_replace(":", "", $data['amount']);
        if (strlen($amount) == 6) {
            $amount = substr($amount, 0, 4);
        }
        $amount = intval($amount);
        if (($amount > 2359) || ($amount <= 0)) {
            $this->_validate->error->addError(array(
                'field'   => $translate->translate('Amount'),
                'message' => $translate->translate('The amount is invalid')));
                return false;
        }

        $this->_validate = new Phprojekt_Model_Validate();
        return $this->_validate->recordValidate($this, $data, $fields);
    }

    /**
     * Return the error data
     *
     * @return array
     */
    public function getError()
    {
        return (array) $this->_validate->error->getError();
    }

    /**
     * Return an array with information about the records and the fields
     *
     * @param string  $date Date for the request
     *
     * @return array
     */
    public function getRecords($date)
    {
        $where  = sprintf('(ownerId = %d AND date = "%s")', Phprojekt_Auth::getUserId(), $date);
        $order  = ' id ASC ';
        $models = $this->fetchAll($where, $order);

        $information     = $this->getInformation($order);
        $fieldDefinition = $information->getFieldDefinition($order);

        $data   = array();
        $datas  = array('timecard' => array(),
                        'timeproj' => array());
        $numRows = 0;

        foreach ($models as $cmodel) {
            $data['id'] = $cmodel->id;
            foreach ($fieldDefinition as $field) {
                $key   = $field['key'];
                $value = $cmodel->$key;
                if (is_scalar($value)) {
                    $data[$key] = $value;
                } else {
                    $data[$key] = (string) $value;
                }
                $data['rights'] = $cmodel->getRights();
            }
            $datas['timeproj'][] = $data;
        }

        $where  = sprintf('(ownerId = %d AND date = "%s")', Phprojekt_Auth::getUserId(), $date);
        $order  = 'startTime ASC';
        $timecard = new Timecard_Models_Timecard();
        $timecardRecords = $timecard->fetchall($where, $order);

        $information     = $timecard->getInformation($order);
        $timeCardfieldDefinition = $information->getFieldDefinition($order);

        foreach ($timecardRecords as $cmodel) {
            $data['id'] = $cmodel->id;
            foreach ($timeCardfieldDefinition as $field) {
                $key   = $field['key'];
                $value = $cmodel->$key;
                if (is_scalar($value)) {
                    $data[$key] = $value;
                } else {
                    $data[$key] = (string) $value;
                }
                $data['rights'] = $cmodel->getRights();
            }
            $datas['timecard'][] = $data;
        }

        $numRows = count($datas);
        $data = array('metadata' => $fieldDefinition,
                      'data'     => $datas,
                      'numRows'  => (int) $numRows);

        return $data;
    }
}
