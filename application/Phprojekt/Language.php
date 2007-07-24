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

/* Zend_Locale */
require_once 'Zend/Translate.php';

/* Default_Helpers_PhprojectLanguage */
require_once PHPR_CORE_PATH . '/Phprojekt/LanguageAdapter.php';

/**
 * Extend Zend_Translate for add a new adapter
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_Language extends Zend_Translate
{
    /**
     * Create a new language object
     *
     * @param string $adapter Wich adaptor will you use
     *                Since the constructor must be equal to the
     *                parent constructor, this var are here but
     *                not used
     * @param string $options The filename of the lang gile
     * @param string $locale Wich locale want to load
     */
    public function __construct($adapter, $options, $locale = null)
    {
        $this->_adapter = new Phprojekt_LanguageAdapter($options, $locale);
    }

    /**
     * Translate the given string
     *
     * @param  string $messageId Original to translate
     * @param  string|Zend_Locale $locale OPTIONAL locale/language
     *                 to translate to
     *
     * @return string
     */
    public function _($messageId, $locale = null)
    {
        return $this->_adapter->translate($messageId, $locale);
    }


    /**
     * Translate the given string
     *
     * @param  string $messageId Original to translate
     * @param  string|Zend_Locale $locale OPTIONAL locale/language
     *                 to translate to
     *
     * @return string
     */
    public function translate($messageId, $locale = null)
    {
        return $this->_adapter->translate($messageId, $locale);
    }
}