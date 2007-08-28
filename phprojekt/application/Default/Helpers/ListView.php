<?php
/**
 * List View helper class
 *
 * This class is for help on the draw of the list view
 *
 * LICENSE: Licensed under the terms of the GNU Publice License
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
 * The class return the values with some transformations
 * deppends on the type of the field
 *
 * Since this class is used by two other class,
 * must be inizialized only one time and by the class itself,
 * because that, the constructor are protected.
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Default_Helpers_ListView
{
    /**
     * Translator
     *
     * @var Phprojekt_LanguageAdapter
     */
    protected $_oTranslate = null;

    /**
     * Array with db cofig options
     *
     * @var array
     */
    protected $_db = null;

    /**
     * User configurations
     *
     * @var unknown_type
     */
    protected $_config = null;

    /**
     * Instance for create the class only one time
     *
     * @var Default_Helpers_ListView Object
     */
    protected static $_instance = null;

    /**
     * Constructor
     * Only can be created the class by the class it self
     *
     */
    protected function __construct()
    {
        $this->_oTranslate = Zend_Registry::get('translate');
        $this->_db         = Zend_Registry::get('db');
        $this->_config     = Zend_Registry::get('config');
    }

    /**
     * Return this class only one time
     *
     * @return Default_Helpers_ListView
     */
    static public function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Switch between the form types and call the function for each one
     *
     * @param array $field         Data of the field from the dbManager
     * @param mix   $originalValue The real value from the database
     *
     * @return string XHTML generated
     */
    public function generateListElement($field, $originalValue)
    {
        switch ($field['formType']) {
        default:
            return $this->listText($originalValue);
            break;
        case "textarea":
            return $this->listTextArea($originalValue);
            break;
        case "date":
            return $this->listDate($originalValue);
            break;
        case "datetime":
            return $this->listDateTime($originalValue);
            break;
        case "selectValues":
            return $this->listSelectValues($field, $originalValue);
            break;
        case "tree":
            return $this->listTree($field, $originalValue);
            break;
        case "userId":
            return $this->listUserId($originalValue);
            break;
        }
    }

    /**
     * Return a normal text calue
     *
     * @param mix $originalValue The real value from the database
     *
     * @return string XHTML generated
     */
    public function listText($originalValue)
    {
        return $originalValue;
    }

    /**
     * Return a textarea value
     *
     * @param mix $originalValue The real value from the database
     *
     * @return string XHTML generated
     */
    public function listTextArea($originalValue)
    {
        return $originalValue;
    }

    /**
     * Return a date value translated to the user locale
     * Using the value of the config->language
     *
     * @param mix $originalValue The real value from the database
     *
     * @return string XHTML generated
     */
    public function listDate($originalValue)
    {
        if (!empty($originalValue)) {
            $localeFormat = new Zend_Locale_Format();
            $locale       = $this->_config->language;
            $format       = $localeFormat->getDateFormat($locale);
            $date         = new Zend_Date($originalValue, $format, $locale);
            return $date->get($format);
        } else {
            return '';
        }
    }


    /**
     * Return a datetime value translated to the user locale
     * Using the value of the config->language
     *
     * @param mix $originalValue The real value from the database
     *
     * @return string XHTML generated
     */
    public function listDateTime($originalValue)
    {
        if (!empty($originalValue)) {
            $localeFormat = new Zend_Locale_Format();
            $locale       = $this->_config->language;
            list($dateData, $hourData) = split(" ", $originalValue);

            $formatDate = $localeFormat->getDateFormat($locale);
            $date       = new Zend_Date($dateData, $formatDate, $locale);
            $formatHour = $localeFormat->getTimeFormat($locale);
            $hour       = new Zend_Date($hourData, $formatHour, $locale);
            return $date->get($formatDate) . $hour->get($formatHour);
        } else {
            return '';
        }
    }

    /**
     * Return the selected value from a list of values
     * The data is parsed like key1#value1|key2#value2 in the formRange value
     * The value is translated before return
     *
     * @param array $field         Data of the field from the dbManager
     * @param mix   $originalValue The real value from the database
     *
     * @return string XHTML generated
     */
    public function listSelectValues($field, $originalValue)
    {
        $string = '';
        $data = explode('|', $field['formRange']);
        foreach ($data as $pairValues) {
            list($key, $value) = split("#", $pairValues);
            if ($key == $originalValue) {
                $string = $this->_oTranslate->translate($value);
            }
        }
        return $string;
    }

    /**
     * Return the title of the tree node
     * For make the data, the range value contain wich activerecord is used
     *
     * @param array $field         Data of the field from the dbManager
     * @param mix   $originalValue The real value from the database
     *
     * @return string XHTML generated
     */
    public function listTree($field, $originalValue)
    {
        $activeRecord = new $field['formRange']($this->_db);
        $tree = new Phprojekt_Tree_Node_Database($activeRecord, 1);
        $tree->setup();

        $node = $tree->getNodeById($originalValue);
        return $node->title;
    }

    /**
     * Return the name of an user
     *
     * @param mix $originalValue The real value from the database
     *
     * @return string XHTML generated
     */
    public function listUserId($originalValue)
    {
        $db   = Zend_Registry::get('db');
        $user = new Users_Models_User(array('db' => $db));
        $user->find($originalValue);
        return $user->username;
    }
}