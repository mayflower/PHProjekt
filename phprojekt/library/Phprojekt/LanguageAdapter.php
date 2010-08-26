<?php
/**
 * Language Interface for use the PHProjekt lang files
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Language
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
 * Sinse the Zend use some type of Adapter that can not be used with the
 * PHProjekt lang files, we create an own Adapter for read these files.
 *
 * The class is an extension of the Zend_Translate_Adapter that is an abstract class.
 * So we only must redefine the functions defined on the Original Adapter.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Language
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_LanguageAdapter extends Zend_Translate_Adapter
{
    /**
     * Define all the lang files.
     */
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
     * Contain all the already loaded locales.
     *
     * @var array
     */
    protected $_langLoaded = array();

    /**
     * Generates the adapter.
     *
     * Convert some PHProject lang shortname to the Zend locale names.
     *
     * @param string $data   Path to the default lang file.
     * @param string $locale PHProjekt locale string.
     *
     * @return void
     */
    public function __construct($options)
    {
        $options['locale']         = self::_convertToZendLocale($options['locale']);
        $options['disableNotices'] = true;
        parent::__construct($options);
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
     * And also have an array with the already loaded languages for not load a same file two times.
     *
     * @param string             $data    Path to the default translation file.
     * @param string|Zend_Locale $locale  Locale/Language to set, identical with Locale identifiers
     *                                    see Zend_Locale for more information.
     * @param string|array       $options Options for the adaptor.
     *
     * @return void
     */
    protected function _loadTranslationData($data, $locale, array $options = array())
    {
        $options = array_merge($this->_options, $options);
        if (true === $options['clear'] || false === isset($this->_translate[$locale])) {
            $this->_translate[$locale] = array();
        }

        // Get the translated string from the cache if exists
        $cache = Phprojekt::getInstance()->getCache();
        if (!($this->_translate[$locale] = $cache->load('Phprojekt_LanguageAdapter_loadTranslationData_' . $locale))) {
            // Collect a new translation set
            if (true === empty($this->_translate[$locale])) {
                // Default
                $langFile    = $this->_getLangFile($locale);
                $languageDir = PHPR_CORE_PATH . '/Default/Languages/';
                $lang        = array();
                if (file_exists($languageDir . $langFile)) {
                    include_once($languageDir . $langFile);
                    if (isset($lang)) {
                        if (!isset($this->_translate[$locale]['Default'])) {
                            $this->_translate[$locale]['Default'] = array();
                        }
                        $this->_translate[$locale]['Default'] = array_merge($this->_translate[$locale]['Default'], $lang);
                        $this->_langLoaded[$locale] = 1;
                    }
                }

                // System modules
                $this->_processModuleFolder($locale, PHPR_CORE_PATH . DIRECTORY_SEPARATOR);

                // User modules
                $this->_processModuleFolder($locale, PHPR_USER_CORE_PATH);
            }

            if (true === empty($this->_translate[$locale])) {
                $temp                      = explode('_', (string) $locale);
                $this->_translate[$locale] = array($temp[0]);
            }

            $cache->save($this->_translate[$locale], 'Phprojekt_LanguageAdapter_loadTranslationData_' . $locale);
        }
    }

    /**
     * Collect the data and create the array translation set for modules folders.
     *
     * @param string|Zend_Locale $locale Locale/Language to set, identical with Locale identifiers
     *                                   see Zend_Locale for more information.
     *
     * @param string             $path   Path to the modules directory.
     *
     * @return void
     */
    private function _processModuleFolder($locale, $path)
    {
        $langFile = $this->_getLangFile($locale);
        $files    = scandir($path);
        foreach ($files as $module) {
            if ($module != '.' && $module != '..' && $module != 'Default') {
                // Get the translation file
                $lang        = array ();
                $languageDir = $path . $module . '/Languages/';
                if (file_exists($languageDir . $langFile)) {
                    include_once($languageDir . $langFile);
                    if (isset($lang)) {
                        if (!isset($this->_translate[$locale][$module])) {
                            $this->_translate[$locale][$module] = array();
                        }
                        $this->_langLoaded[$locale]         = 1;
                        $this->_translate[$locale][$module] = array_merge($this->_translate[$locale][$module], $lang);
                    }
                }
                // SubModules
                $this->_processSubModuleFolder($locale, $path, $langFile, $module);
            }
        }
    }

    /**
     * Collect the data and create the array translation set for SubModules folders.
     *
     * @param string|Zend_Locale $locale   Locale/Language to set, identical with Locale identifiers
     *                                     see Zend_Locale for more information.
     * @param string             $path     Path to the modules directory.
     * @param string             $langFile Current lang file for get.
     * @param string             $module   Folder name of the module.
     *
     * @return void
     */
    private function _processSubModuleFolder($locale, $path, $langFile, $module)
    {
        $languageDir = $path . $module . '/SubModules/';
        if (file_exists($languageDir)) {
            $subFiles = scandir($path . $module . '/SubModules/');
            foreach ($subFiles as $subModule) {
                if ($subModule != '.' && $subModule != '..') {
                    $languageDir = $path . $module . '/SubModules/' . $subModule . '/Languages/';
                    if (file_exists($languageDir . $langFile)) {
                        include_once($languageDir . $langFile);
                        if (isset($lang)) {
                            if (!isset($this->_translate[$locale][$subModule])) {
                                $this->_translate[$locale][$subModule] = array();
                            }
                            $this->_langLoaded[$locale] = 1;
                            $this->_translate[$locale][$subModule] = array_merge(
                                $this->_translate[$locale][$subModule], $lang);
                        }
                    } else if ($module == 'Core') {
                        $subCoreFiles = scandir(PHPR_CORE_PATH . '/' . $module . '/SubModules/' . $subModule);
                        foreach ($subCoreFiles as $subCoreModule) {
                            if ($subCoreModule != '.' && $subCoreModule != '..') {
                                $coreLanguageDir = PHPR_CORE_PATH . '/' . $module . '/SubModules/' . $subModule
                                    . '/' . $subCoreModule . '/Languages/';
                                if (file_exists($coreLanguageDir . $langFile)) {
                                    include_once($coreLanguageDir . $langFile);
                                    if (isset($lang)) {
                                        if (!isset($this->_translate[$locale][$subCoreModule])) {
                                            $this->_translate[$locale][$subCoreModule] = array();
                                        }
                                        $this->_langLoaded[$locale] = 1;
                                        $this->_translate[$locale][$subCoreModule] = array_merge(
                                            $this->_translate[$locale][$subCoreModule], $lang);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Return the correct file for the current locale.
     *
     * @param string|Zend_Locale $locale Locale/Language to set, identical with Locale identifiers
     *                                   see Zend_Locale for more information.
     *
     * @return string File to use.
     */
    private function _getLangFile($locale)
    {
        switch ($locale) {
            case 'sq_AL':
                $langFile = self::LANG_AL;
                break;
            case 'bg':
                $langFile = self::LANG_BG;
                break;
            case 'pt_BR':
                $langFile = self::LANG_BR;
                break;
            case 'cs_CZ':
                $langFile = self::LANG_CZ;
                break;
            case 'da':
                $langFile = self::LANG_DA;
                break;
            case 'de':
                $langFile = self::LANG_DE;
                break;
            case 'et_EE':
                $langFile = self::LANG_EE;
                break;
            case 'es':
                $langFile = self::LANG_ES;
                break;
            case 'fi':
                $langFile = self::LANG_FI;
                break;
            case 'fr':
                $langFile = self::LANG_FR;
                break;
            case 'ka_GE':
                $langFile = self::LANG_GE;
                break;
            case 'el_GR':
                $langFile = self::LANG_GR;
                break;
            case 'he':
                $langFile = self::LANG_HE;
                break;
            case 'hu':
                $langFile = self::LANG_HU;
                break;
            case 'is':
                $langFile = self::LANG_IS;
                break;
            case 'it':
                $langFile = self::LANG_IT;
                break;
            case 'ja_JP':
                $langFile = self::LANG_JP;
                break;
            case 'ko':
                $langFile = self::LANG_KO;
                break;
            case 'lt':
                $langFile = self::LANG_LT;
                break;
            case 'lv':
                $langFile = self::LANG_LV;
                break;
            case 'nl':
                $langFile = self::LANG_NL;
                break;
            case 'no':
            case 'nn_NO':
                $langFile = self::LANG_NO;
                break;
            case 'pl':
                $langFile = self::LANG_PL;
                break;
            case 'pt':
                $langFile = self::LANG_PT;
                break;
            case 'ro':
                $langFile = self::LANG_RO;
                break;
            case 'ru':
                $langFile = self::LANG_RU;
                break;
            case 'sv_SE':
                $langFile = self::LANG_SE;
                break;
            case 'sl_SI':
                $langFile = self::LANG_SI;
                break;
            case 'sk':
                $langFile = self::LANG_SK;
                break;
            case 'sv':
                $langFile = self::LANG_SV;
                break;
            case 'th_TH':
                $langFile = self::LANG_TH;
                break;
            case 'tr':
                $langFile = self::LANG_TR;
                break;
            case 'zh_TW':
                $langFile = self::LANG_TW;
                break;
            case 'uk':
                $langFile = self::LANG_UK;
                break;
            case 'zh':
                $langFile = self::LANG_ZH;
                break;
            default:
            case 'en':
                $langFile = self::LANG_EN;
                break;
        }

        return $langFile;
    }

    /**
     * Return all the trasnlated strings for the $locale.
     *
     * @param string|Zend_Locale $locale Locale/Language to set, identical with Locale identifiers
     *                                   see Zend_Locale for more information.
     *
     * @return array Array with all the translated strings.
     */
    public function getTranslatedStrings($locale)
    {
        $locale = self::_convertToZendLocale($locale);
        if (isset($this->_translate[$locale])) {
            $toReturn = $this->_translate[$locale];
        } else {
            $toReturn = array();
        }

        return $toReturn;
    }

    /**
     * Return the message translated.
     *
     * Search first in the Module translations.
     * Then in the Default translations.
     * Then return the untranslated message.
     *
     * @param string             $message    Message to translate.
     * @param string             $moduleName Module where search the translation.
     * @param string|Zend_Locale $locale     Locale/Language to set, identical with Locale identifiers
     *                                       see Zend_Locale for more information.
     * @return string Translated string.
     */
    public function get($message, $moduleName, $locale = null)
    {
        $locale = self::_convertToZendLocale($locale);
        if (isset($this->_translate[$locale][$moduleName]) &&
            isset($this->_translate[$locale][$moduleName][$message])) {
            $toReturn = $this->_translate[$locale][$moduleName][$message];
        } else if (isset($this->_translate[$locale]['Default']) &&
            isset($this->_translate[$locale]['Default'][$message])) {
            $toReturn = $this->_translate[$locale]['Default'][$message];
        } else {
            $toReturn = $message;
        }

        return $toReturn;
    }

    /**
     * Return the adapters name.
     *
     * Just a redefined function from the abstract Adapter.
     *
     * @return string "Phprojekt".
     */
    public function toString()
    {
        return "Phprojekt";
    }

    /**
     * Return if is loaded the lang file or not.
     * This is for do not read the same file two times.
     *
     * @param string|Zend_Locale $locale Locale/Language to set, identical with Locale identifiers
     *                                   see Zend_Locale for more information.
     *
     * @return boolean
     */
    public function isLoaded($locale)
    {
        $locale = self::_convertToZendLocale($locale);
        if (false === isset($this->_langLoaded[$locale])) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Transform the PHProjekt locale shortname to Zend locale shortname.
     *
     * @param string $locale PHProjekt locale.
     *
     * @return string Zend_Locale string.
     */
    protected static function _convertToZendLocale($locale)
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
            case 'no':
                return 'nn_NO';
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
                break;
        }
    }

    /**
     * Return an array with all the languages name in English.
     *
     * @return array Array with all the languages.
     */
    public static function getLanguageList()
    {
        // Keep the list in the session
        $sessionName           = 'Phprojekt_LanguageAdapter-getLanguageList';
        $languageListNamespace = new Zend_Session_Namespace($sessionName);
        if (!isset($languageListNamespace->list)) {
            $reflect   = new ReflectionClass('Phprojekt_LanguageAdapter');
            $constants = $reflect->getConstants();
            $languages = array();
            $locale    = new Zend_Locale();
            $available = array();

            // Get all the languages files in Defualt module
            $files = scandir(PHPR_CORE_PATH . '/Default/Languages');
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $available[str_replace('.inc.php', '', $file)] = 1;
                }
            }

            foreach ($constants as $value) {
                if (strstr($value, 'inc.php')) {
                    $value = str_replace('.inc.php', '', $value);

                    // Show only the availables languages
                    if (isset($available[$value])) {
                        $zendValue = substr(self::_convertToZendLocale($value), 0, 2);
                        $langName  = $locale->getTranslation($zendValue, 'language', 'en');

                        $languages[$value] = $langName . " (" . $value . ")";
                    }
                }
            }
            asort($languages);
            $languageListNamespace->list = $languages;
        }

        return $languageListNamespace->list;
    }
}
