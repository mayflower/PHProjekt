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
     * Generates the adapter
     *
     * @param string|Zend_Locale $locale Locale/Language to set,
     *                                   identical with Locale identifiers
     *                                   see Zend_Locale for more information
     */
    public function __construct($locale)
    {
        $data = PHPR_ROOT_PATH . '/languages/' . $locale . '.inc.php';

        $this->_adapter = new Phprojekt_LanguageAdapter($data, $locale);
    }

    /**
     * Translate the given string
     *
     * @param string $messageId Original to translate
     * @param string $locale    Locale/language to translate to
     *
     * @return string
     */
    public function translate($messageId, $locale = null)
    {
        if (null !== $locale) {
            $this->_loadLangFile($locale);
        }
        return utf8_encode($this->_adapter->translate($messageId, $locale));
    }

    /**
     * Load another lang file if is needed
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
            $data = PHPR_ROOT_PATH . '/languages/' . $locale . '.inc.php';

            $this->_adapter = new Phprojekt_LanguageAdapter($data, $locale);
        }
    }
}