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
 * @param array $records
 *
 * @return array
 */
function smarty_modifier_list_ordering($records)
{
    return $records;
   /* if (!is_array($records) && $records instanceof Phprojekt_Item_Abstract ) {
        $records->_colInfo = $records->getDatabaseManager()->setColumnOrdering(Phprojekt_DatabaseManager::LIST_ORDER);
        return $records;
    } else if (is_array($records)) {
        foreach($records as &$record) {
            if ($record instanceof Phprojekt_Item_Abstract) {
                $record->_colInfo = $record->getDatabaseManager()->setColumnOrdering(Phprojekt_DatabaseManager::LIST_ORDER);
            }
        }
        return $records;
    }*/

    return '';
}