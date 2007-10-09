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
    /* Define all the lang files */
    const LANG_AL = 'al.inc.php';
    const LANG_BG = 'bg.inc.php';
    const LANG_BR = 'br.inc.php';
    const LANG_CZ = 'cz.inc.php';
    const LANG_DA = 'da.inc.php';
    const LANG_DE = 'de.inc.php';
    const LANG_EE = 'ee.inc.php';
    const LANG_EN = 'en.inc.php';
    const LANG_ES = 'es.inc.php';
    const LANG_FI = 'fi.inc.php';
    const LANG_FR = 'fr.inc.php';
    const LANG_GE = 'ge.inc.php';
    const LANG_GR = 'gr.inc.php';
    const LANG_HE = 'he.inc.php';
    const LANG_HU = 'hu.inc.php';
    const LANG_IS = 'is.inc.php';
    const LANG_IT = 'it.inc.php';
    const LANG_JP = 'jp.inc.php';
    const LANG_KO = 'ko.inc.php';
    const LANG_LT = 'lt.inc.php';
    const LANG_LV = 'lv.inc.php';
    const LANG_NL = 'nl.inc.php';
    const LANG_NO = 'no.inc.php';
    const LANG_PL = 'pl.inc.php';
    const LANG_PT = 'pt.inc.php';
    const LANG_RO = 'ro.inc.php';
    const LANG_RU = 'ru.inc.php';
    const LANG_SE = 'se.inc.php';
    const LANG_SI = 'si.inc.php';
    const LANG_SK = 'sk.inc.php';
    const LANG_SV = 'sv.inc.php';
    const LANG_TH = 'th.inc.php';
    const LANG_TR = 'tr.inc.php';
    const LANG_TW = 'tw.inc.php';
    const LANG_UK = 'uk.inc.php';
    const LANG_ZH = 'zh.inc.php';

    /**
     * Contain all the already loaded locales
     *
     * @var array
     */
    protected $_langLoaded = array();

    /**
     * Generates the adapter
     *
     * Convert some PHProject lang shortname to the Zend locale names
     *
     * @param string $data   Path to the lang file
     * @param string $locale PHProjekt locale string
     *
     * @return void
     */
    public function __construct($data, $locale = null)
    {
        $locale = $this->_convertToZendLocale($locale);

        parent::__construct($data, $locale, array());
    }

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
        $lang = null;

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
            switch ($locale) {
                case 'sq_AL':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_AL;
                    break;
                case 'bg':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_BG;
                    break;
                case 'pt_BR':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_BR;
                    break;
                case 'cs_CZ':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_CZ;
                    break;
                case 'da':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_DA;
                    break;
                case 'de':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_DE;
                    break;
                case 'et_EE':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_EE;
                    break;
                case 'es':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_ES;
                    break;
                case 'fi':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_FI;
                    break;
                case 'fr':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_FR;
                    break;
                case 'ka_GE':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_GE;
                    break;
                case 'el_GR':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_GR;
                    break;
                case 'he':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_HE;
                    break;
                case 'hu':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_HU;
                    break;
                case 'is':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_IS;
                    break;
                case 'it':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_IT;
                    break;
                case 'ja_JP':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_JP;
                    break;
                case 'ko':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_KO;
                    break;
                case 'lt':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_LT;
                    break;
                case 'lv':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_LV;
                    break;
                case 'nl':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_NL;
                    break;
                case 'no':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_NO;
                    break;
                case 'pl':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_PL;
                    break;
                case 'pt':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_PT;
                    break;
                case 'ro':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_RO;
                    break;
                case 'ru':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_RU;
                    break;
                case 'sv_SE':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_SE;
                    break;
                case 'sl_SI':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_SI;
                    break;
                case 'sk':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_SK;
                    break;
                case 'sv':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_SV;
                    break;
                case 'th_TH':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_TH;
                    break;
                case 'tr':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_TR;
                    break;
                case 'zh_TW':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_TW;
                    break;
                case 'uk':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_UK;
                    break;
                case 'zh':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_ZH;
                    break;
                default:
                case 'en':
                    include_once PHPR_ROOT_PATH . '/languages/'. self::LANG_EN;
                    break;
            }

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

    /**
     * Transform the PHProjekt locale shortname to Zend locale shortname.
     *
     * @param string $locale PHProjekt locale
     *
     * @return string Zend locale
     */
    protected function _convertToZendLocale($locale)
    {
        switch ($locale) {
            case 'al':
                return 'sq_AL';
                break;
            case 'br':
                return 'pt_BR';
                break;
            case 'cz':
                return 'cs_CZ';
                break;
            case 'ee':
                return 'et_EE';
                break;
            case 'ge':
                return 'ka_GE';
                break;
            case 'gr':
                return 'el_GR';
                break;
            case 'jp':
                return 'ja_JP';
                break;
            case 'se':
                return 'sv_SE';
                break;
            case 'si':
                return 'sl_SI';
                break;
            case 'th':
                return 'th_TH';
                break;
            case 'tw':
                return 'zh_TW';
                break;
            default:
                return $locale;
        }
    }
}