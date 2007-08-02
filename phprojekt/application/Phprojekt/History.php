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
 * History class for save all the fields changes
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
     * For add actions, the oldValue is empty
     * For delete actions, the newValue is empty
     *
     * @param Phprojekt_Item_Abstract $object The item object
     * @param string                  $action Action (edit/add/delete)
     *
     * @return void
     */
    public function saveFields($object,$action)
    {
        $authNamespace = new Zend_Session_Namespace('PHProjek_Auth');
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
    }

    /**
     * Get the differences between the actual data and the old data
     * of one item
     *
     * @param Phprojekt_Item_Abstract $object The item object
     * @param string                  $action Action (edit/add/delete)
     *
     * @return array The array with the differences
     */
    private function _getDifferences($object,$action)
    {
        $fields = $object->getFieldsForForm($object->getTableName());
        $clone = clone($object);
        $clone->find($object->id);
        $differences = array();

        if ($action == 'edit') {
            foreach ($fields as $fieldName => $fieldData) {
                if ($object->$fieldName != $clone->$fieldName) {
                    $differences[$fieldName] = array(
                            'oldValue' => $clone->$fieldName,
                            'newValue' => $object->$fieldName);
                }
            }
        } else if ($action == 'add') {
            foreach ($fields as $fieldName => $fieldData) {
                $differences[$fieldName] = array(
                    'oldValue' => '',
                    'newValue' => $object->$fieldName);
            }
        } else if ($action == 'delete') {
            foreach ($fields as $fieldName => $fieldData) {
                $differences[$fieldName] = array(
                    'oldValue' => $object->$fieldName,
                    'newValue' => '');
            }
        } else {
            ;
        }
        return $differences;
    }
}