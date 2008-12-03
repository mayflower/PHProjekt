<?php
/**
 * Language Interface for use the PHProjekt lang files
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    CVS: $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Sinse the Zend use some type of Adapter that can not be used with the
 * PHProjekt lang files, we create an own Adapter for read these files.
 *
 * The class is an extension of the Zend_Translate and call the PHProjekt Adapter.
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_Language extends Zend_Translate
{
    private $_locale = null;
    
    /**
     * Constructor function
     *
     * The function is called with a locale string and then
     * create the PHProjekt adapter for load the translated strings.
     *
     * The lang files are in the folder languages with the name:
     * 'locale.inc.php' where locale is the locale string.
     *
     * For example the English translation is in the file: en.inc.php
     *
     * @param string|Zend_Locale $locale Locale/Language to set,
     *                                   identical with Locale identifiers
     *                                   see Zend_Locale for more information
     */
    public function __construct($locale)
    {
        $data = PHPR_CORE_PATH . '/Default/Languages/';
        $this->_loacale = $locale;
        $this->_adapter = new Phprojekt_LanguageAdapter($data, $locale);
    }

    /**
     * Translate the given string
     *
     * The function translate the message
     * usign the adapter created with the correct locale.
     * And return the strign using the utf8 encode
     * for make sence to the codification of the languages strings.
     *
     * If you want to translate the message in other languages
     * that is not the default that are you using,
     * you can call the function like trnaslate($message,$locale).
     * This will try to load the file first if is not already laoded
     * and then return the message in your $locale languauge
     *
     * @param string $message Original to translate
     * @param string $locale  Locale/language to translate to
     *
     * @return string
     */
    public function translate($message, $locale = null)
    {
        if (null !== $locale) {
            $this->_loadLangFile($locale);
            $this->_loacale = $locale;
        }
        return $this->_adapter->translate($message, $this->_loacale);
    }

    /**
     * Load another lang file if is needed
     *
     * If the lang file that you want is not loaded,
     * the function will load it.
     *
     * @param string|Zend_Locale $locale Locale/Language to set,
     *                                   identical with Locale identifiers
     *                                   see Zend_Locale for more information
     *
     * @return void
     */
    private function _loadLangFile($locale)
    {
        if (false === $this->_adapter->isLoaded($locale)) {
            $data = PHPR_CORE_PATH . '/Default/Languages/';

            $this->_adapter = new Phprojekt_LanguageAdapter($data, $locale);
        }
    }
    
    /**
     * Return all the trasnlated strings for the $locale
     *
     * @param string|Zend_Locale $locale Locale/Language to set,
     *                                   identical with Locale identifiers
     *                                   see Zend_Locale for more information
     * 
     * @return array
     */
    public function getTranslatedStrings($locale)
    {
        if (null !== $locale) {
            $this->_loadLangFile($locale);
        }
        return $this->_adapter->getTranslatedStrings($locale);
    }
}
