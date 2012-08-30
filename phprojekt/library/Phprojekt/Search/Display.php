<?php
/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * Display Searchs class.
 *
 * The class provide the functions for display the item data of the results
 */
class Phprojekt_Search_Display extends Zend_Db_Table_Abstract
{
    /**
     * Name of the table.
     *
     * @var string
     */
    protected $_name = 'search_display';

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $config = array('db' => Phprojekt::getInstance()->getDb());

        parent::__construct($config);
    }

    /**
     * Return the display data for a moduleId-ItemId pair.
     *
     * @param array $data Array with the module data for show (moduleId => itemId).
     *
     * @return array Array with 'id', 'moduleId', 'moduleName', 'moduleLabel',
     *                          'firstDisplay', 'secondDisplay' and 'projectId'.
     */
    public function getDisplay($data)
    {
        $results = array();

        foreach ($data as $moduleId => $content) {
            $ids = array();
            foreach ($content as $id) {
                $ids[] = (int) $id;
            }

            if (!empty($ids)) {
                $where = sprintf('module_id = %d AND item_id IN (%s)', (int) $moduleId, implode(', ', $ids));

                $tmpResult   = $this->fetchAll($where)->toArray();
                $moduleName  = Phprojekt_Module::getModuleName($moduleId);
                $moduleLabel = Phprojekt::getInstance()->translate(Phprojekt_Module::getModuleLabel($moduleId), null,
                    $moduleName);

                foreach ($tmpResult as $result) {
                    $index           = $moduleId . '-' . $result['item_id'];
                    $results[$index] = array('id'            => (int) $result['item_id'],
                                             'moduleId'      => (int) $moduleId,
                                             'moduleName'    => $moduleName,
                                             'moduleLabel'   => $moduleLabel,
                                             'firstDisplay'  => $result['first_display'],
                                             'secondDisplay' => $result['second_display'],
                                             'projectId'     => (int) $result['project_id']);
                }

                foreach ($ids as $id) {
                    $index = $moduleId . '-' . $id;
                    if (!isset($results[$index])) {
                        $results[$index] = array('id'            => (int) $id,
                                                 'moduleId'      => (int) $moduleId,
                                                 'moduleName'    => Phprojekt_Module::getModuleName($moduleId),
                                                 'moduleLabel'   => $moduleLabel,
                                                 'firstDisplay'  => '',
                                                 'secondDisplay' => '',
                                                 'projectId'     => 1);
                    }
                }
            }
        }

        return array_values($results);
    }

    /**
     * Save the display data, insert or update.
     *
     * @param Phprojekt_Item_Abstract $object   The item object.
     * @param integer                 $moduleId The module ID to store.
     * @param integer                 $itemId   The item ID to store.
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

        if ($object->hasField($firstField)) {
            $firstDisplay = $object->$firstField;
        } else {
            $firstDisplay = "ID: " . $object->id;
        }

        if ($object->hasField($secondField)) {
            $secondDisplay = $object->$secondField;
            if (strlen($secondDisplay) > 100) {
                $secondDisplay = substr($secondDisplay, 0, 100) . "...";
            }
        }

        if (!$this->_exists($moduleId, $itemId)) {
            $this->_save($moduleId, $itemId, $projectId, $firstDisplay, $secondDisplay);
        } else {
            $this->_update($moduleId, $itemId, $projectId, $firstDisplay, $secondDisplay);
        }
    }

    /**
     * Delete the entry for one object.
     *
     * @param integer $moduleId The module ID to delete.
     * @param integer $itemId   The item ID.
     *
     * @return void
     */
    public function deleteDisplay($moduleId, $itemId)
    {
        $where = sprintf('module_id = %d AND item_id = %d', (int) $moduleId, (int) $itemId);
        $this->delete($where);
    }

    /**
     * Check if the moduleId-itemId pair was already inserted.
     *
     * @param integer $moduleId The module ID to store.
     * @param integer $itemId   The item ID.
     *
     * @return boolean True if exists.
     */
    private function _exists($moduleId, $itemId)
    {
        return ($this->find($moduleId, $itemId)->count() > 0);
    }

    /**
     * Save the new moduleId-item pair.
     *
     * This function use the Zend_DB insert.
     *
     * @param integer $moduleId      The module ID to store.
     * @param integer $itemId        The item ID.
     * @param integer $projectId     The parent project ID.
     * @param string  $firstDisplay  Text for the first display.
     * @param string  $secondDisplay Text for the second display.
     *
     * @return void
     */
    private function _save($moduleId, $itemId, $projectId, $firstDisplay, $secondDisplay)
    {
        $data['module_id']      = (int) $moduleId;
        $data['item_id']        = (int) $itemId;
        $data['first_display']  = $firstDisplay;
        $data['second_display'] = $secondDisplay;
        $data['project_id']     = (int) $projectId;
        $this->insert($data);
    }

    /**
     * Update a moduleId-item pair.
     *
     * This function use the Zend_DB update.
     *
     * @param integer $moduleId      The module ID to store.
     * @param integer $itemId        The item ID.
     * @param integer $projectId     The parent project ID.
     * @param string  $firstDisplay  Text for the first display.
     * @param string  $secondDisplay Text for the second display.
     *
     * @return void
     */
    private function _update($moduleId, $itemId, $projectId, $firstDisplay, $secondDisplay)
    {
        $data['first_display']  = $firstDisplay;
        $data['second_display'] = $secondDisplay;
        $data['project_id']     = (int) $projectId;

        $where = sprintf('module_id = %d AND item_id = %d', (int) $moduleId, (int) $itemId);

        $this->update($data, $where);
    }
}
