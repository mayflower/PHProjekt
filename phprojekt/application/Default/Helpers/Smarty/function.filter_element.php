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
 * @author     Gustavo Solt <solt@mayflower.de>
 * @since      File available since Release 1.0
 */

/**
 * Generate a filter list element and do all necessary converts, etc.
 * The function requires the parameter "field", "rule" and "text" to be passed.
 *
 * @param array $arguments Array with the field, rule and text parameters
 *
 * @return array
 */
function smarty_function_filter_element($arguments)
{
    if (array_key_exists('field', $arguments)
        && array_key_exists('rule', $arguments)
        && array_key_exists('text', $arguments) ) {
        return Default_Helpers_FilterViewRenderer::generateFilterElement($arguments['field'], $arguments['rule'], $arguments['text']);
    }

    return '';
}
?>