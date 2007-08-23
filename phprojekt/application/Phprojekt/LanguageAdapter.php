<?php
/**
 * Language Interface for use the PHProjekt lang files
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
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
 * The class is an extension of the Zend_Translate_Adapter that is an abstract class.
 * So we only must redefine the functions defined on the Original Adapter.
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_LanguageAdapter extends Zend_Translate_Adapter
{
    /**
     * Contain all the already loaded locales
     *
     * @var array
     */
    protected $_langLoaded = array();

    /**
     * This protected function is for collect the data and create the array translation set.
     *
     * In this case the data is readed from the PHProjekt lang files.
     *
     * The file contain an array like $_lang[$key] = $value,
     * where $key is the string to be translated and $value is the translated string.
     *
     * Since is not nessesary load the file in each request,
     * we use sessions for save the langs translations.
     * And also have an array with the already loaded languages
     * for not load a same file two times.
     *
     * @param string             $data    Path to the translation file
     * @param string|Zend_Locale $locale  Locale/Language to set,
     *                                    identical with Locale identifiers
     *                                    see Zend_Locale for more information
     * @param string|array       $options Options for the adaptor
     *
     * @return void
     *
     * @todo "include_once $data" is not a good code.
     *       Maybe must have some checks before include the file
     *
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

        /* Collect a new translation set */
        if (true === empty($this->_translate[$locale])
         && true  === is_readable($data)) {
            /* Get the translation file */
            define('LANG_FILE',$data);
            include_once LANG_FILE;

            $this->_translate[$locale] = $lang;

            $session->translatedStrings = $this->_translate;
            $this->_langLoaded[$locale] = 1;
        }
    }

    /**
     * Returns the adapters name
     *
     * Just a redefined fucntion from the abstract Adapter
     *
     * @return string
     */
    public function toString()
    {
        return "Phprojekt";
    }

    /**
     * Return if is loaded the lang file or not.
     * This is for do not read the same file two times.
     *
     * @param string|Zend_Locale $locale Locale/Language to set,
     *                                   identical with Locale identifiers
     *                                   see Zend_Locale for more information
     *
     * @return boolean
     */
    public function isLoaded($locale)
    {
        if (false === isset($this->_langLoaded[$locale])) {
            return false;
        } else {
            return true;
        }
    }
}