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
     * @var unknown_type
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
     * @param Zend_View Object
     */
    protected function __construct($view) {
        $this->_oTranslate = Zend_Registry::get('translate');
        $this->_db         = Zend_Registry::get('db');
        $this->_config     = Zend_Registry::get('config');
    }

    /**
     * Return this class only one time
     *
     * @param Zend_View $view Zend_View Object
     * @return Default_Helpers_ListView
     */
    static public function getInstance($view)
    {
        if (null === self::$_instance) {
            self::$_instance = new self($view);
        }
        return self::$_instance;
    }

    /**
     * Return only the first row that contain the titles
     *
     * @param array $data The array with data of each field
     *
     * @return array The first row of the fields data
     */
    public function getTitles(array $data)
    {
        if (true == empty($data)) {
            return $data[0] = array();
        }

        return $data[0];
    }

    /**
     * Switch between the form types and call the function for each one
     *
     * @param array $field Data of the field from the dbManager
     *
     * @return string XHTML generated
     */
    public function generateListElement($field)
    {
        switch ($field['formType']) {
        default:
                return $this->listText($field);
                break;
        case "textarea":
                return $this->listTextArea($field);
                break;
        case "date":
                return $this->listDate($field);
                break;
        case "selectValues":
                return $this->listSelectValues($field);
                break;
        case "tree":
                return $this->listTree($field);
                break;
        }
    }

    /**
     * Return a normal text calue
     *
     * @param array $field Data of the field from the dbManager
     *
     * @return string XHTML generated
     */
    public function listText($field)
    {
        return $field['value'];
    }

    /**
     * Return a textarea value
     *
     * @param array $field Data of the field from the dbManager
     *
     * @return string XHTML generated
     */
    public function listTextArea($field)
    {
        return $field['value'];
    }

    /**
     * Return a date value translated to the user locale
     * Using the value of the config->language
     *
     * @param array $field Data of the field from the dbManager
     *
     * @return string XHTML generated
     */
    public function listDate($field)
    {
        if (false === empty($field['value'])) {
            $localeFormat = new Zend_Locale_Format();
            $locale = $this->_config->language;
            $format = $localeFormat->getDateFormat($locale);
            $date    = new Zend_Date($field['value'], $format, $locale);
            return $date->get($format);
        } else {
            return '';
        }
    }

    /**
     * Return the selected value from a list of values
     * The data is parsed like key1#value1|key2#value2 in the formRange value
     * The value is translated before return
     *
     * @param array $field Data of the field from the dbManager
     *
     * @return string XHTML generated
     */
    public function listSelectValues($field)
    {
        $string = '';
        $data = explode('|',$field['formRange']);
        foreach ($data as $pairValues) {
            list($key,$value) = split("#",$pairValues);
            if (true === ($key == $field['value'])) {
                $string = $this->_oTranslate->translate($value);
            }
        }
        return $string;
    }

    /**
     * Return the title of the tree node
     * For make the data, the range value contain wich activerecord is used
     *
     * @param array $field Data of the field from the dbManager
     *
     * @return string XHTML generated
     */
    public function listTree($field)
    {
        $activeRecord = new $field['formRange']($this->_db);
        $tree = new Phprojekt_Tree_Node_Database($activeRecord,1);
        $tree->setup();

        $node = $tree->getNodeById($field['value']);
        return $node->title;
    }
}