<?php
/**
 * Render Helper
 *
 * Render Helper to provide easy access to the translation object
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
 * Translate all the string with " |translate"
 *
 * @param string $string Input text to be translated
 *
 * @return string $string
 */
class Default_Helpers_Translate
{
    public function translate($string)
    {
        if (!empty($string)) {
            $translator = Zend_Registry::get('translate');
            /* @var $translator Zend_Translate_Adapter */
            return $translator->translate($string);
        }

        return '';
    }
}