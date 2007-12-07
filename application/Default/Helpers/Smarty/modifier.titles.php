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
 * Return an array of titles from an Phprojekt_Item_Abstract
 * using the information from the DatabaseManager provided by
 * the DatabaseManager object.
 * You can use this function to iterate over the titles of a list befor
 * displaying it.
 *
 * @param array $records All the record to draw
 *
 * @return array
 */
function smarty_modifier_titles($records)
{
    if (is_array($records)) {
        $record = current($records);
    } else {
        $record = $records;
    }

    if ($record instanceof Phprojekt_Item_Abstract) {
        /* @var Phprojekt_Item_Abstract $record */
        return $record->getInformation()->getTitles(MODELINFO_ORD_LIST);
    }

    return false;
}