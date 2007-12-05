<?php
/**
 * History class for save all the fields changes
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
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
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
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
        if (true === is_object($object)) {
            $authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');
            $differences   = $this->_getDifferences($object, $action);

            foreach ($differences as $fieldName => $difference) {
                $history               = clone($this);
                $history->userId       = $authNamespace->userId;
                $history->module       = $object->getTableName();
                $history->dataobjectId = $object->id;
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
        $fields = $object->getInformation()->getFieldDefinition(MODELINFO_ORD_FORM);
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
     * @param Phprojekt_Item_Abstract $object The item object
     * @param int                     $itemId The item ID
     *
     * @return array
     */
    public function getHistoryData($object, $itemId)
    {
        $table  = $object->getTableName();
        $where  = $this->getAdapter()->quoteInto('module = ?', $table);
        $where .= $this->getAdapter()->quoteInto('AND dataobjectId = ?', $itemId);
        $order  = 'datetime DESC';
        $result = array();

        foreach ($this->fetchAll($where, $order) as $row) {
            $result[] = array('userId'       => $row->userId,
                              'module'       => $row->module,
                              'dataobjectId' => $row->dataobjectId,
                              'field'        => $row->field,
                              'oldValue'     => $row->oldValue,
                              'newValue'     => $row->newValue,
                              'action'       => $row->action,
                              'datetime'     => $row->datetime);
        }
        return $result;
    }
}