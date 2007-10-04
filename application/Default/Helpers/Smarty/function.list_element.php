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
 * Generate a list element and do all necessary converts, etc.
 * The function requires the parameter "field" and "value" to be passed.
 *
 * @param array $arguments Array with the field and value parameters
 *
 * @return array
 */
function smarty_function_list_element($arguments)
{
    if (array_key_exists('field', $arguments)
        && array_key_exists('value', $arguments)) {
        return Default_Helpers_ListView::generateListElement($arguments['field'], $arguments['value']);
    }

    return '';
}