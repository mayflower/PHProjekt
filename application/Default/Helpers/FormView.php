<?php
/**
 * Form View helper class
 *
 * This class is for help on the draw of the form
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
 * Form View helper class
 *
 * The class return a form element deppend on the formType of the field
 * with the data from the dbmanager.
 *
 * For draw the form element, the class uses the Zend_View_Helper
 *
 * Since this class is used by two other classes,
 * it must be inizialized only one time and by the class itself,
 * and because of that, the constructor is protected.
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
    protected $_translate = null;

    /**
     * Array with db config options
     *
     * @var array
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
     * @param Zend_View $view View object for form
     */
    protected function __construct($view)
    {
        $this->_view       = $view;
        $this->_translate  = Zend_Registry::get('translate');
        $this->_db         = Zend_Registry::get('db');
    }

    /**
     * Singleton pattern
     *
     * @param Zend_View $view Zend_View Object
     *
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
     * Count the fields and
     * add the needed empty fields for complete the number of columns
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
     * @param Phprojekt_Item_Abstract $models Object model
     * @param array                   $params $_REQUEST array
     *
     * @return array Data with label, XHTML output and isRequired per field
     */
    public function generateFormElement($models, $params)
    {
        $output = array();

        /* Get the Item ID */
        if (true === isset($params['id'])) {
            $itemid = (int) $params['id'];
        } else {
            $itemid = 0;
        }

        $fields = (array) $models->getFieldsForForm($models->getTableName());

        if ($itemid > 0) {
            $models->find($itemid);
        }

        /* Get the parent field, by default id projectId */
        $info = $models->info();
        $parentField = 'projectId';
        if (is_array($info) && in_array('parent', $info['cols'])) {
            $parentField = 'parent';
        }

        foreach ($fields as  $field) {
            /* Label */
            /* $fieldName = $field->tableField;
            $tmpOutput['label'] = $this->_translate->translate($field->formLabel);


            if (true === isset($params[$fieldName])) {
                $field['value'] = $params[$fieldName];
            } else {
                $field->value = $models->$fieldName;
            }

            if ($fieldName == $parentField) {
                $session = new Zend_Session_Namespace();
                if (isset($session->lastProjectId)) {
                    $field['value'] = $session->lastProjectId;
                }
            }

            switch ($field['formType']) {
            default:
                $tmpOutput['output'] = $this->formText($field);
                break;
            case "textarea":
                $tmpOutput['output'] = $this->formTextArea($field);
                break;
            case "date":
                $tmpOutput['output'] = $this->formDate($field);
                break;
            case "selectValues":
                $tmpOutput['output'] = $this->formSelectValues($field);
                break;
            case "tree":
                $tmpOutput['output'] = $this->formTree($field);
                break;
            case "space":
                $tmpOutput['output'] = null;
                break;
            }

            $tmpOutput['isRequired'] = $field['isRequired'];

            $output[] = $tmpOutput;
            unset($tmpOutput);
            */
        }

        return $output;
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
     * The value is translated before return
     *
     * @param array $field Data of the field from the dbManager
     *
     * @return string XHTML generated
     */
    public function formSelectValues($field)
    {
        $attribs = array();
        $options = array();

        $data = explode('|', $field['formRange']);
        foreach ($data as $pairValues) {
            list($key, $value) = split("#", $pairValues);
            $options[$key]     = $this->_translate->translate($value);
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
        $tree         = new Phprojekt_Tree_Node_Database($activeRecord, 1);
        $tree->setup();

        foreach ($tree as $node) {
            $key   = $node->id;
            $value = str_repeat('....', $node->getDepth()) . $node->title;

            $options[$key] = $this->_translate->translate($value);
        }
        return $this->_view->formSelect($field['tableField'], $field['value'], $attribs, $options);
    }
}