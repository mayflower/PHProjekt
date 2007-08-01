<?php
/**
 * Language Interface for use the PHProject lang files
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
 * Adapter class for use the PHProject lang files
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_LanguageAdapter extends Zend_Translate_Adapter
{
    /**
     * Generates the adapter
     *
     * @param string             $data    Path to the translation file
     * @param string|Zend_Locale $locale  Locale/Language to set,
     *                                    identical with Locale identifiers
     *                                    see Zend_Locale for more information
     * @param string|array       $options Options for the adaptor
     */
    public function __construct($data, $locale = null, array $options = array())
    {
        parent::__construct($data, $locale, $options);
    }

    /**
     * Get the lang translations for the lang file
     *
     * @param string             $data    Path to the translation file
     * @param string|Zend_Locale $locale  Locale/Language to set,
     *                                    identical with Locale identifiers
     *                                    see Zend_Locale for more information
     * @param string|array       $options Options for the adaptor
     *
     * @return void
     */
    protected function _loadTranslationData($data, $locale, array $options = array())
    {
        $options = array_merge($this->_options, $options);
        if (true === $options['clear'] ||
            false === isset($this->_translate[$locale])) {
            $this->_translate[$locale] = array();
        }

        /* Get the translated string from the session if exists */
        $session = new Zend_Session_Namespace();
        if (true === isset($session->translatedStrings)) {
            $this->_translate = $session->translatedStrings;
        } else {
            $session->translatedStrings = array();
        }

        /* Collect a new trasnaltion set */
        if (false === empty($this->_translate[$locale])
         && true  === is_readable($data)) {
            /* Get the translation file */
            include_once $data;

            foreach ($_lang as $word => $translation) {
                $this->_translate[$locale][$word] = $translation;
            }

            $session->translatedStrings = $this->_translate;
        }
    }

    /**
     * Returns the adapters name
     *
     * @return string
     */
    public function toString()
    {
        return "Phprojekt";
    }
}