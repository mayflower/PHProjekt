<?php
/**
 * Smarty plugin
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @link       http://www.phprojekt.com
 * @author     David Soria Parra <soria_parra@mayflower.de>
 * @since      File available since Release 1.0
 */

/**
 * Set the form mode
 *
 * @param array $records The row for make the form
 *
 * @return array
 */
function smarty_modifier_form_ordering($records)
{
    $allowedRecords = array();
    if (!is_array($records) && $records instanceof Phprojekt_Model_Interface && $records->getRights() != '') {
        $fields = $records->getInformation()->getFieldDefinition(MODELINFO_ORD_FORM);
        $result = array();
        foreach ($fields as $field) {
            $field['value'] = $records->$field['key'];
            $result[] = $field;
        }
        $allowedRecords = $result;
    } else if (is_array($records)) {
        foreach ($records as &$record) {
            /* @var Phprojekt_Item_Abstract $record */
            if ($record instanceof Phprojekt_Model_Interface) {
                if ($record->getRights() != '') { /* @todo fix rights */
                    $fields = $record->getInformation()->getFieldDefinition(MODELINFO_ORD_FORM);
                    $result = array();
                    foreach ($fields as $field) {
                        $field['value'] = $record->$field['key'];
                        $result[] = $field;
                    }
                    $allowedRecords[] = $result;
                }
            }
        }
    }

    return $allowedRecords;
}