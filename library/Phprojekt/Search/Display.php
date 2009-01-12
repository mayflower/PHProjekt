<?php
/**
 * Display Searchs class
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
 * The class provide the functions for display the item data of the results
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @subpackage Core
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_Search_Display extends Zend_Db_Table_Abstract
{
    /**
     * Name of the table
     *
     * @var string
     */
    protected $_name = 'SearchDisplay';

    /**
    /**
     * Chaneg the tablename for use with the Zend db class
     *
     * This function is only for PHProjekt6
     *
     * @param array $config The config array for the database
     */
    public function __construct()
    {
        $config = array('db' => Phprojekt::getInstance()->getDb());

        parent::__construct($config);
    }

    /**
     * Return the display data for a moduleId-ItemId pair
     *
     * @param integer $moduleId The module Id for search
     * @param integer $itemId   The item Id for search
     *
     * @return array
     */
    public function getDisplay($moduleId, $itemId)
    {
        $where     = array();
        $translate = Phprojekt::getInstance()->getTranslate();

        $where[] = 'moduleId = '. $this->getAdapter()->quote($moduleId);
        $where[] = 'itemId = '. $this->getAdapter()->quote($itemId);

        $tmpResult = $this->fetchAll($where)->toArray();

        if (isset($tmpResult[0])) {
            $result = array('id'            => $itemId,
                            'moduleId'      => $moduleId,
                            'moduleName'    => Phprojekt_Module::getModuleName($moduleId),
                            'moduleLabel'   => $translate->translate(Phprojekt_Module::getModuleLabel($moduleId)),
                            'firstDisplay'  => $tmpResult[0]['firstDisplay'],
                            'secondDisplay' => $tmpResult[0]['secondDisplay'],
                            'projectId'     => $tmpResult[0]['projectId']);
        } else {
            $result = array('id'            => $itemId,
                            'moduleId'      => $moduleId,
                            'moduleName'    => Phprojekt_Module::getModuleName($moduleId),
                            'moduleLabel'   => $translate->translate(Phprojekt_Module::getModuleLabel($moduleId)),
                            'firstDisplay'  => '',
                            'secondDisplay' => '',
                            'projectId'     => 1);
        }
        return $result;
    }

    /**
     * Save the display data, insert or update
     *
     * @param Phprojekt_Item_Abstract $object The item object
     * @param integer The module Id to store
     * @param integer The item Id to store
     *
     * @return void
     */
    public function saveDisplay($object, $moduleId, $itemId)
    {
        $firstDisplay  = '';
        $secondDisplay = '';
        $firstField    = $object->searchFirstDisplayField;
        $secondField   = $object->searchSecondDisplayField;
        $projectId     = $object->projectId;
        if (isset($object->$firstField)) {
            $firstDisplay  = $object->$firstField;
        }
        if (isset($object->$secondField)) {
            $secondDisplay = $object->$secondField;
        }
        if (!$this->_exists($moduleId, $itemId)) {
            $this->_save($moduleId, $itemId, $projectId, $firstDisplay, $secondDisplay);
        } else {
            $this->_update($moduleId, $itemId, $projectId, $firstDisplay, $secondDisplay);
        }
    }

    /**
     * Delete the entry for one object
     *
     * @param integer $moduleId The moduleId to delete
     * @param integer $itemId   The item Id
     *
     * @return void
     */
    public function deleteDisplay($moduleId, $itemId)
    {
        $where = array();
        $clone = clone($this);

        $where[] = 'moduleId = '. $clone->getAdapter()->quote($moduleId);
        $where[] = 'itemId = '. $clone->getAdapter()->quote($itemId);
        $clone->delete($where);
    }

    /**
     * Check if the moduleId-itemId pair was already inserted
     *
     * @param integer $moduleId The moduleId to store
     * @param integer $itemId   The item Id
     *
     * @return boolean
     */
    private function _exists($moduleId, $itemId)
    {
        return ($this->find($moduleId, $itemId)->count() > 0);
    }

    /**
     * Save the new moduleId-item pair
     *
     * This function use the Zend_DB insert
     *
     * @param integer $moduleId      The moduleId to store
     * @param integer $itemId        The item Id
     * @param integer $projectId     The parent project Id
     * @param string  $firstDisplay  Text for the first display
     * @param string  $secondDisplay Text for the second display
     *
     * @return void
     */
    private function _save($moduleId, $itemId, $projectId, $firstDisplay, $secondDisplay)
    {
        $data['moduleId']       = $moduleId;
        $data['itemId']         = $itemId;
        $data['firstDisplay']   = $firstDisplay;
        $data['secondDisplay']  = $secondDisplay;
        $data['projectId']      = $projectId;
        $this->insert($data);
    }

    /**
     * Update a moduleId-item pair
     *
     * This function use the Zend_DB update
     *
     * @param integer $moduleId      The moduleId to store
     * @param integer $itemId        The item Id
     * @param integer $projectId     The parent project Id
     * @param string  $firstDisplay  Text for the first display
     * @param string  $secondDisplay Text for the second display
     *
     * @return void
     */
    private function _update($moduleId, $itemId, $projectId, $firstDisplay, $secondDisplay)
    {
        $data['firstDisplay']   = $firstDisplay;
        $data['secondDisplay']  = $secondDisplay;
        $data['projectId']      = $projectId;

        $where   = array();
        $where[] = 'moduleId = '. $this->getAdapter()->quote($moduleId);
        $where[] = 'itemId = '. $this->getAdapter()->quote($itemId);

        $this->update($data, $where);
    }
}
