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
 * The class save all the changes of the records.
 *
 * When you create a record, the new values are saved.
 * When you edit a record, the old and new values are saved.
 * When you delete a record, the old values are saved.
 *
 * In each change you can know:
 *
 * - Who make the change (Wich user).
 * - When (timestamp).
 * - Where (Wich module).
 * - What (Wich action and wich field with old value and new value).
 */
class Phprojekt_History extends Phprojekt_ActiveRecord_Abstract
{
    /**
     * Save each field that is with other value that before.
     *
     * For add actions, the oldValue is empty and all the fields are saved.
     * For delete actions, the newValue is empty and all the fields are saved.
     * For edit action, only the fields with other value that before are saved.
     *
     * @param Phprojekt_Item_Abstract $object The item object.
     * @param string                  $action Action (edit/add/delete).
     *
     * @throws Zend_Exception If the object do not exist.
     *
     * @return void
     */
    public function saveFields(Phprojekt_Item_Abstract $object, $action)
    {
        $differences = $this->_getDifferences($object, $action);

        foreach ($differences as $fieldName => $difference) {
            $history           = clone($this);
            $history->userId   = Phprojekt_Auth::getUserId();
            $history->moduleId = Phprojekt_Module::getId($object->getModelName());
            $history->itemId   = $object->id;
            $history->field    = $fieldName;
            $history->oldValue = $difference['oldValue'];
            $history->newValue = $difference['newValue'];
            $history->action   = $action;
            $history->datetime = gmdate("Y-m-d H:i:s");
            $history->save();
        }
    }

    /**
     * Get the differences between the actual data and the old data of one item.
     *
     * The function will inspect and collect all the fields that have other value than before.
     *
     * For add action, return all the values.
     * For edit action, return only the fields that have changes.
     * For delete action, return all the values.
     *
     * @param Phprojekt_Item_Abstract $object The item object.
     * @param string                  $action Action (edit/add/delete).
     *
     * @return array The array with 'oldValue' and 'newValue'.
     */
    private function _getDifferences(Phprojekt_Item_Abstract $object, $action)
    {
        $getter = 'getShortFieldDefinition';
        $order  = Phprojekt_ModelInformation_Default::ORDERING_FORM;
        if (method_exists($object->getInformation(), $getter)) {
            $fields = call_user_func(array($object->getInformation(), $getter), $order);
        } else {
            $fields = $object->getInformation()->getFieldDefinition($order);
        }

        $clone  = clone($object);
        $clone->find($object->id);
        $differences = array();
        if ($action == 'edit') {
            foreach ($fields as $value) {
                $fieldName = $value['key'];
                if ($value['type'] == 'textarea') {
                    $objectFieldName = str_replace("\n", "", strip_tags($object->$fieldName));
                    $cloneFieldName  = str_replace("\n", "", strip_tags($clone->$fieldName));
                } else {
                    $objectFieldName = $object->$fieldName;
                    $cloneFieldName  = $clone->$fieldName;
                }
                if ($objectFieldName != $cloneFieldName) {
                    $oldValue = $this->_convertDateTimes($clone->$fieldName, $value['type'], 'userToUtc');
                    $newValue = $this->_convertDateTimes($object->$fieldName, $value['type'], 'userToUtc');

                    $differences[$fieldName] = array('oldValue' => $oldValue,
                                                     'newValue' => $newValue);
                }
            }
        } else if ($action == 'add') {
            foreach ($fields as $value) {
                $fieldName = $value['key'];
                if ($value['type'] == 'textarea') {
                    $objectFieldName = str_replace("\n", "", strip_tags($object->$fieldName));
                } else {
                    $objectFieldName = $object->$fieldName;
                }
                if (!empty($objectFieldName)) {
                    $newValue = $this->_convertDateTimes($object->$fieldName, $value['type'], 'userToUtc');

                    $differences[$fieldName] = array('oldValue' => '',
                                                     'newValue' => $object->$fieldName);
                }
            }
        } else if ($action == 'delete') {
            foreach ($fields as $value) {
                $fieldName = $value['key'];
                $oldValue  = $this->_convertDateTimes($clone->$fieldName, $value['type'], 'userToUtc');

                $differences[$fieldName] = array('oldValue' => $clone->$fieldName,
                                                 'newValue' => '');
            }
        }

        return $differences;
    }

    /**
     * Return the data array with all the changes for a item ID.
     *
     * The data is sorted by date and have all the values stored in the database.
     * The data result is for use with a template that correct the values for the user.
     *
     * @param Phprojekt_Item_Abstract $object    The item object.
     * @param integer                 $itemId    The item ID.
     * @param integer                 $moduleId  The ID of the module (optional).
     * @param date                    $startDate Start date of the history list (optional).
     * @param date                    $endDate   End date of the history list (optional).
     * @param integer                 $userId    User filter (optional).
     *
     * @return array Array with 'userId', 'moduleId', 'itemId', 'field', 'label',
     *                          'oldValue', 'newValue', 'action' and 'datetime'.
     */
    public function getHistoryData($object, $itemId, $moduleId = null,
                                   $startDate = null, $endDate = null, $userId = null)
    {
        if (!isset($moduleId)) {
            $moduleId = Phprojekt_Module::getId($object->getModelName());
        }

        if (null === $object) {
            $moduleName = Phprojekt_Module::getModuleName($moduleId);
            $object     = Phprojekt_Loader::getModel($moduleName, $moduleName);
        }

        $order           = Phprojekt_ModelInformation_Default::ORDERING_FORM;
        $fieldDefinition = $object->getInformation()->getFieldDefinition($order);

        $where = sprintf('module_id = %d AND item_id = %d', (int) $moduleId, (int) $itemId);

        if (!empty($startDate)) {
            $where .= $this->getAdapter()->quoteInto(' AND datetime >= ?', $startDate);
        }
        if (!empty($endDate)) {
            $where .= $this->getAdapter()->quoteInto(' AND datetime <= ?', $endDate);
        }
        if (!empty($userId)) {
            $where .= sprintf(' AND user_id = %d', (int) $userId);
        }

        $result = array();
        $fields = array();
        foreach ($fieldDefinition as $field) {
            $fields[$field['key']] = $field;
        }
        foreach ($this->fetchAll($where, 'datetime DESC') as $row) {
            if (isset($fields[$row->field])) {
                $oldField        = $fields[$row->field];
                $oldField['key'] = 'oldValue';
                $oldValue        = Phprojekt_Converter_Text::convert($row, $oldField);
                $newField        = $fields[$row->field];
                $newField['key'] = 'newValue';
                $newValue        = Phprojekt_Converter_Text::convert($row, $newField);
                $label           = $fields[$row->field]['label'];
            } else {
                $oldValue = $row->oldValue;
                $newValue = $row->newValue;
                $label    = $row->field;
            }

            if (DateTime::createFromFormat('Y-m-d H:i:s', $oldValue)
                    && DateTime::createFromFormat('Y-m-d H:i:s', $newValue)) {
                $oldValue = $this->_convertDateTimes($oldValue, 'datetime', 'utcToUser');
                $newValue = $this->_convertDateTimes($newValue, 'datetime', 'utcToUser');
            } else if ((DateTime::createFromFormat('H:i:s', $oldValue)
                    && DateTime::createFromFormat('H:i:s', $newValue))) {
                $oldValue = $this->_convertDateTimes($oldValue, 'time', 'utcToUser');
                $newValue = $this->_convertDateTimes($newValue, 'time', 'utcToUser');
            }

            if ($oldValue != $newValue) {
                $result[] = array('userId'   => (int) $row->userId,
                                  'moduleId' => (int) $row->moduleId,
                                  'itemId'   => (int) $row->itemId,
                                  'field'    => $row->field,
                                  'label'    => $label,
                                  'oldValue' => $oldValue,
                                  'newValue' => $newValue,
                                  'action'   => $row->action,
                                  'datetime' => $this->_convertDateTimes($row->datetime, 'datetime', 'utcToUser'));
            }
        }

        return $result;
    }

    /**
     * Returns the last changes, if there are any, for a specific module and item id.
     *
     * The result data is used by Mail_Notification class, when telling the users related
     * to an item that it has been modified.
     *
     * @param Phprojekt_Item_Abstract $object The item object
     *
     * @return array Array with 'userId', 'moduleId', 'itemId', 'field', 'label',
     *                          'oldValue', 'newValue', 'action' and 'datetime'.
     */
    public function getLastHistoryData($object)
    {
        $result   = array();
        $moduleId = Phprojekt_Module::getId($object->getModelName());
        $itemId   = $object->id;
        $where    = sprintf('module_id = %d AND item_id = %d', (int) $moduleId, (int) $itemId);

        $fields = array();
        $fieldDefinition = $object->getInformation()->getFieldDefinition();
        foreach($fieldDefinition as $field) {
            $fields[$field['key']] = $field;
        }

        $datetime = null;
        $action   = null;
        $history  = $this->fetchAll($where, 'id DESC');
        $stop     = false;
        foreach ($history as $row) {
            if (!$stop) {
                if (null === $datetime) {
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
                                          'label'    => isset($fields[$row->field]) ?
                                                            $fields[$row->field]['label'] : $row->field,
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

        return array_reverse($result);
    }

    /**
     * Return the last value for an assigned user.
     *
     * @param Phprojekt_Item_Abstract $model     The item object.
     * @param string                  $fieldName Field name used for assign users.
     *
     * @return integer Last user ID.
     */
    public function getLastAssignedUser($model, $fieldName)
    {
        $changes = $this->getLastHistoryData($model);
        if ($changes[0]['action'] == 'edit') {
            foreach ($changes as $change) {
                if ($change['field'] == $fieldName) {
                    // The user has changed
                    if ($change['oldValue'] != $model->ownerId && $change['oldValue'] != '0'
                        && $change['oldValue'] !== null) {
                        return $change['oldValue'];
                    }
                }
            }
        }

        return 0;
    }

    /**
     * Convert time and datetimes values into uset or utc values.
     *
     * @param mix    $value Current value.
     * @param string $type  Type of the field.
     * @param string $side  utcToUser or userToUtc, used for convert times.

     * @return string Date or Time format.
     */
    private function _convertDateTimes($value, $type, $side)
    {
        if ($type == 'datetime') {
            $value = date("Y-m-d H:i:s", Phprojekt_Converter_Time::$side($value));
        } else if ($type == 'time') {
            $value = date("H:i:s", Phprojekt_Converter_Time::$side($value));
        }

        return $value;
    }
}
