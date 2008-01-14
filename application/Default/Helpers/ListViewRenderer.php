<?php
/**
 * List View helper class
 *
 * This class is for help on the draw of the list view
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
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
class Default_Helpers_ListViewRenderer implements Phprojekt_RenderHelper
{
    /**
     * The model to render
     *
     * @var Phprojekt_Abstract_Item
     */
    protected $_model;

    /**
     * Instance for create the class only one time
     *
     * @var Default_Helpers_ListView Object
     */
    protected static $_instance = null;

    /**
     * Return this class only one time
     *
     * @return Default_Helpers_ListView
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Set the model, which is rendered
     *
     * @param Phprojekt_Item_Abstract $model Model to render
     *
     * @return void
     */
    public function setModel($model)
    {
        if (is_array($model)) {
            $this->_model = $model;
        } else if ($model instanceof Phprojekt_IModel) {
            $this->_model = array($model);
        }
    }

    /**
     * Return the model that is rendered
     *
     * @return array
     */
    public function &getModel()
    {
        return $this->_model;
    }

    /**
     * Render the content of the list view and return it
     *
     * @return string
     */
    public function render($name = 'list.tpl')
    {
        if (null === $this->getModel() || count($this->getModel()) == 0) {
            return '';
        }

        $view = Zend_Registry::get('view');

        $view->records = $this->getModel();

        
        return $view->render($name);
    }

    /**
     * Switch between the form types and call the function for each one
     *
     * @param array $field Data of the field from the dbManager
     *
     * @return string XHTML generated
     */
    public static function generateListElement($field)
    {
        $originalValue = $field['value'];

        switch ($field['type']) {
            default:
                return self::text($originalValue);
                break;
            case "textarea":
                return self::textArea($originalValue);
                break;
            case "date":
                return self::date($originalValue);
                break;
            case "datetime":
                return self::dateTime($originalValue);
                break;
            case "selectValues":
                return self::selectValues($field, $originalValue);
                break;
            case "tree":
                return self::tree($field, $originalValue);
                break;
            case "userId":
                return self::userId($originalValue);
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
    public static function text($originalValue)
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
    public static function textArea($originalValue)
    {
        return $originalValue;
    }

    /**
     * Return a date value translated to the user locale
     * Using the value of the config->language
     *
     * @param mixed $originalValue The real value from the database
     *
     * @return string XHTML generated
     */
    public static function date($originalValue)
    {
        if (!empty($originalValue)) {
            $locale       = Zend_Registry::get('config')->language;
            $localeFormat = new Zend_Locale_Format();
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
     * @param mixed $originalValue The real value from the database
     *
     * @return string XHTML generated
     */
    public static function dateTime($originalValue)
    {
        if (!empty($originalValue)) {
            $localeFormat = new Zend_Locale_Format();
            $locale       = Zend_Registry::get('config')->language;
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
     * @param mixed $originalValue The real value from the database
     *
     * @return string XHTML generated
     */
    public static function selectValues($field, $originalValue)
    {
        $string = '';
        $data   = explode('|', $field['range']);
        foreach ($data as $pairValues) {
            list($key, $value) = split("#", $pairValues);
            if ($key == $originalValue) {
                $string = Zend_Registry::get('translate')->translate($value);
            }
        }
        return $string;
    }

    /**
     * Return the title of the tree node
     * For make the data, the range value contain wich activerecord is used
     *
     * @param array $field         Data of the field from the dbManager
     * @param mixed $originalValue The real value from the database
     *
     * @todo Don't put class names into formRange
     *
     * @return string XHTML generated
     */
    public static function tree($field, $originalValue)
    {
        $activeRecord = Phprojekt_Loader::getModel($field['range'], $field['range']);
        $tree         = new Phprojekt_Tree_Node_Database($activeRecord, 1);
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
    public static function userId($originalValue)
    {
        $user = Phprojekt_Loader::getModel('User', 'User');
        $user->find($originalValue);
        return $user->username;
    }
}