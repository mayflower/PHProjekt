<?php
/**
 * Language Interface for use the PHProject lang files
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *             GNU Public License 2.0
 * @version    CVS: $Id: 
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
*/

/** Zend_Locale */
require_once 'Zend/Locale.php';

/** Zend_Translate_Exception */
require_once 'Zend/Translate/Exception.php';

/** Zend_Translate_Adapter */
require_once 'Zend/Translate/Adapter.php';

/** Zend_Session_Namespace */
require_once 'Zend/Session/Namespace.php';

/**
 * Adapter class for use the PHProject lang files
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *             GNU Public License 2.0
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Default_Helpers_PhprojectLanguage extends Zend_Translate_Adapter
{
    /**
     * Generates the adapter
     *
     * @param  array               $data     Translation data
     * @param  string|Zend_Locale  $locale   OPTIONAL Locale/Language to set, identical with locale identifier,
     *                                       see Zend_Locale for more information
     */
    public function __construct($data, $locale = null)
    {
        parent::__construct($data, $locale);
    }

    /**
     * Load translation data
     *
     * @param  string|array  $filename  Filename and full path to the translation source
     * @param  string        $locale    Locale/Language to add data for, identical with locale identifier,
     *                                  see Zend_Locale for more information
     * @param  array         $option    OPTIONAL Options to use
     */
    protected function _loadTranslationData($filename, $locale, array $options = array())
    {
        $options = array_merge($this->_options, $options);
        if (($options['clear'] == true) ||  !isset($this->_translate[$locale])) {
            $this->_translate[$locale] = array();
        }

        /* Get the translated string from the session if exists */
        $session = new Zend_Session_Namespace();
        if (isset($session->translatedStrings)) {
            $this->_translate = $session->translatedStrings;
        } else {
            $session->translatedStrings = array();
        }
        
        /* Collect a new trasnaltion set */ 
        if (empty($this->_translate[$locale])) {
            $file = @fopen($filename, 'rb');
            if (!$file) {
                throw new Zend_Translate_Exception('Error opening translation file \'' . $filename . '\'.');
            }

            while(!feof($file)) {
                $content = fgets($file);
                $result = array();
                if (preg_match('#_lang\["([\w-:;,\./= \r\n]+)+"\]( )+=( )+?"([\w-:;,\./= \r\n]+)+"#', $content, $result)) {
                    $this->_translate[$locale][$result[1]] = $result[4];
                }
            }
            $session->translatedStrings = $this->_translate;
        }
    }

    /**
     * returns the adapters name
     *
     * @return string
     */
    public function toString()
    {
        return "PhprojectLanguage";
    }
}
