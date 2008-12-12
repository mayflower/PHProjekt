<?php
/**
 * History class for save all the fields changes
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
 * @version    CVS: $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * The class save all the changes of the records.
 *
 * When you create a record, the new values are saved.
 * When you edit a record, the old and new values are saved.
 * When you delete a record, the old values are saved.
 *
 * In each change you can know:
 * - Who make the change (Wich user)
 * - When (timestamp)
 * - Where (Wich module)
 * - What (Wich action and wich field with old value and new value)
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_History extends Phprojekt_ActiveRecord_Abstract
{
    /**
     * Save each field that is with other value that before
     *
     * For add actions, the oldValue is empty and all the fields are saved
     * For delete actions, the newValue is empty and all the fields are saved.
     * For edit action, only the fields with other value that before are saved.
     *
     * @param Phprojekt_Item_Abstract $object The item object
     * @param string                  $action Action (edit/add/delete)
     *
     * @return void
     */
    public function saveFields($object, $action)
    {
        if (is_object($object) === true) {
            $differences = $this->_getDifferences($object, $action);

            foreach ($differences as $fieldName => $difference) {
                $history               = clone($this);
                $history->userId       = Phprojekt_Auth::getUserId();
                $history->moduleId     = Phprojekt_Module::getId($object->getTableName());
                $history->itemId       = $object->id;
                $history->field        = $fieldName;
                $history->oldValue     = $difference['oldValue'];
                $history->newValue     = $difference['newValue'];
                $history->action       = $action;
                $history->save();
            }
        } else {
            throw new Zend_Exception('The object do not exist');
        }
    }

    /**
     * Get the differences between the actual data and the old data of one item
     *
     * The function will inspect and collect
     * all the fields that have other value than before.
     *
     * For add action, return all the values.
     * For edit action, return only the fields that have changes.
     * For delete action, return all the values.
     *
     * @param Phprojekt_Item_Abstract $object The item object
     * @param string                  $action Action (edit/add/delete)
     *
     * @return array The array with the differences
     */
    private function _getDifferences(Phprojekt_Item_Abstract $object, $action)
    {
        $fields = $object->getInformation()->getFieldDefinition(Phprojekt_ModelInformation_Default::ORDERING_FORM);
        $clone  = clone($object);
        $clone->find($object->id);
        $differences = array();
        if ($action == 'edit') {
            foreach ($fields as $value) {
                $fieldName = $value['key'];
                if ($object->$fieldName != $clone->$fieldName) {
                    $differences[$fieldName] = array(
                            'oldValue' => $clone->$fieldName,
                            'newValue' => $object->$fieldName);
                }
            }
        } else if ($action == 'add') {
            foreach ($fields as $value) {
                $fieldName = $value['key'];
                $differences[$fieldName] = array(
                    'oldValue' => '',
                    'newValue' => $object->$fieldName);
            }
        } else if ($action == 'delete') {
            foreach ($fields as $value) {
                $fieldName = $value['key'];
                $differences[$fieldName] = array(
                    'oldValue' => $object->$fieldName,
                    'newValue' => '');
            }
        } else {
            ;
        }
        return $differences;
    }

    /**
     * Return the data array with all the changes for a item ID
     * The data is sorted by date and have all the values stored in the database
     * The data result is for use with a template
     * that correct the values for the user.
     *
     * @param Phprojekt_Item_Abstract $object    The item object
     * @param int                     $itemId    The item ID
     * @param int                     $moduleId  The id of the module (optional)
     * @param date                    $startDate Start date of the history list
     * @param date                    $endDate   End date of the history list
     * @param int                     $userId    User filter
     *
     * @return array
     */
    public function getHistoryData($object, $itemId, $moduleId = null,
                                   $startDate = null, $endDate = null, $userId = null)
    {
        if (!isset($moduleId)) {
            $moduleId = Phprojekt_Module::getId($object->getTableName());
        }
        $where  = $this->getAdapter()->quoteInto('moduleId = ?', (int)$moduleId);
        $where .= $this->getAdapter()->quoteInto(' AND itemId = ?', $itemId);

        if (!empty($startDate)) {
            $where .= $this->getAdapter()->quoteInto(' AND datetime >= ?', $startDate);
        }
        if (!empty($endDate)) {
            $where .= $this->getAdapter()->quoteInto(' AND datetime <= ?', $endDate);
        }
        if (!empty($userId)) {
            $where .= $this->getAdapter()->quoteInto(' AND userId = ?', $userId);
        }

        $result = array();

        foreach ($this->fetchAll($where, 'datetime DESC') as $row) {
            $result[] = array('userId'   => $row->userId,
                              'moduleId' => $row->moduleId,
                              'itemId'   => $row->itemId,
                              'field'    => $row->field,
                              'oldValue' => $row->oldValue,
                              'newValue' => $row->newValue,
                              'action'   => $row->action,
                              'datetime' => $row->datetime);
        }

        return $result;
    }

    /**
     * Returns the last changes, if there are any, for a specific module and item id.
     * The result data is used by Mail_Notification class, when telling the users related
     * to an item that it has been modified.
     *
     * @param Phprojekt_Item_Abstract $object    The item object
     *
     * @return array
     */
    public function getLastHistoryData($object)
    {
        $result   = array();
        $moduleId = Phprojekt_Module::getId($object->getTableName());
        $itemId   = $object->id;
        $where    = $this->getAdapter()->quoteInto('moduleId = ?', (int)$moduleId);
        $where   .= $this->getAdapter()->quoteInto(' AND itemId = ?', $itemId);

        $datetime = null;
        $action   = null;
        $results  = $this->fetchAll($where, 'id DESC');
        $stop     = false;
        foreach ($results as $row) {
            if (!$stop) {
                if (null == $datetime) {
                    $datetime = $row->datetime;
                    $action   = $row->action;
                }
                if ($action == $row->action) {
                    $diff = abs(strtotime($datetime) - strtotime($row->datetime));
                    if ($diff < 1) {
                        $result[] = array('userId'   => $row->userId,
                                          'moduleId' => $row->moduleId,
                                          'itemId'   => $row->itemId,
                                          'field'    => $row->field,
                                          'oldValue' => $row->oldValue,
                                          'newValue' => $row->newValue,
                                          'action'   => $row->action,
                                          'datetime' => $row->datetime);
                    } else {
                        $stop = true;
                        break;
                    }
                } else {
                    $stop = true;
                    break;
                }
            }
        }

        return $result;
    }
}
