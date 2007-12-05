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
 * Set the list mode
 *
 * @param array $records Array with the records
 *
 * @return array
 */
function smarty_modifier_list_ordering($records)
{
    if (!is_array($records) && $records instanceof Phprojekt_Item_Abstract && $records->getRights() <> '') {
        return $records;
    } else if (is_array($records)) {
        $allowedRecords=array();
        foreach ($records as &$record) {
            /* @var Phprojekt_Item_Abstract $record */
            if ($record instanceof Phprojekt_Item_Abstract) {
                if ($record->getRights() <> '') {
                    $allowedRecords[]=$record;
                }
            }
        }
        return $allowedRecords;
    }

    return '';
}

