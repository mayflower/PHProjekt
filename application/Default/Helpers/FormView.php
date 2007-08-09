<?php
/**
 * Form View helper class
 *
 * This class is for help on the draw of the form
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
 * Form View helper class
 *
 * This class is for help on the draw of the form
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Default_Helpers_FormView
{
    /**
     * View Object for render
     *
     * @var Zend_View Object
     */
    protected $_view = null;

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
     * Instance for create the class only one time
     *
     * @var Default_Helpers_FormView Object
     */
    protected static $_instance = null;

    /**
     * Constructor
     * Only can be created the class by the class it self
     *
     * @param Zend_View Object
     */
    protected function __construct($view) {
        $this->_view       = $view;
        $this->_oTranslate = Zend_Registry::get('translate');
        $this->_db         = Zend_Registry::get('db');
    }

    /**
     * Return this class only one time
     *
     * @param Zend_View $view Zend_View Object
     * @return Default_Helpers_FormView
     */
    static public function getInstance($view)
    {
        if (null === self::$_instance) {
            self::$_instance = new self($view);
        }
        return self::$_instance;
    }

    /**
     * Make all the input fields and return and arrar for
     * use in smarty.
     *
     * @param array $fields      Array with the data of each field
     * @param int   $formColumns Number of columns to show
     *
     * @return array             The data for show in the template
     */
    public function makeColumns($fields, $formColumns)
    {
        $countFields = count($fields);
        $modFields   = $countFields % $formColumns;
        if ($modFields != 0) {
            for ($index = $modFields; $index < $formColumns; $index++) {
                $fields[] = array('formType'  => 'space',
                                  'formLabel' => '&nbsp',
                                  'value'     => '&nbsp;');
            }
        }

        return $fields;
    }

    /**
     * Switch between the form types and call the function for each one
     *
     * @param array $field Data of the field from the dbManager
     *
     * @return string XHTML generated
     */
    public function generateFormElement($field)
    {
        switch ($field['formType']) {
        default:
                return $this->formText($field);
                break;
        case "textarea":
                return $this->formTextArea($field);
                break;
        case "date":
                return $this->formDate($field);
                break;
        case "selectValues":
                return $this->formSelectValues($field);
                break;
        case "tree":
                return $this->formTree($field);
                break;
        }
    }

    /**
     * Generate a text input field
     *
     * @param array $field Data of the field from the dbManager
     *
     * @return string XHTML generated
     */
    public function formText($field)
    {
        return $this->_view->formText($field['formLabel'], $field['value']);
    }

    /**
     * Generate a textarea input field
     *
     * @param array $field Data of the field from the dbManager
     *
     * @return string XHTML generated
     */
    public function formTextArea($field)
    {
        $options = array('cols' => 30,
                         'rows' => 3);
        return $this->_view->formTextarea($field['tableField'], $field['value'], $options);
    }

    /**
     * Generate a text input field for dates
     *
     * @param array $field Data of the field from the dbManager
     *
     * @return string XHTML generated
     */
    public function formDate($field)
    {
        return $this->_view->formText($field['tableField'], $field['value']);
    }

    /**
     * Generate a select input field
     * The data is parsed like key1#value1|key2#value2
     *
     * @param array $field Data of the field from the dbManager
     *
     * @return string XHTML generated
     */
    public function formSelectValues($field)
    {
        $attribs = array();
        $options = array();

        $data = explode('|',$field['formRange']);
        foreach ($data as $pairValues) {
            list($key,$value) = split("#",$pairValues);
            $options[$key] = $this->_oTranslate->translate($value);
        }
        return $this->_view->formSelect($field['tableField'], $field['value'], $attribs, $options);
    }

    /**
     * Generate a select with tree values
     * For make the data, the range value contain wich activerecord is used
     *
     * @param array $field Data of the field from the dbManager
     *
     * @return string XHTML generated
     */
    public function formTree($field)
    {
        $attribs = array();
        $options = array();

        $activeRecord = new $field['formRange']($this->_db);
        $tree = new Phprojekt_Tree_Node_Database($activeRecord,1);
        $tree->setup();

        foreach ($tree as $node) {
            $key   = $node->id;
            $value = str_repeat('....', $node->getDepth()) . $node->title;
            $options[$key] = $this->_oTranslate->translate($value);
        }
        return $this->_view->formSelect($field['tableField'], $field['value'], $attribs, $options);
    }
}