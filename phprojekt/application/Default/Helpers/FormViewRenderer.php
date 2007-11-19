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
class Default_Helpers_FormViewRenderer implements Phprojekt_RenderHelper
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
    public function setModel( $model)
    {
        if ($model instanceof Phprojekt_IModel) {
            $this->_model = $model;
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
    public function render()
    {
        $view = Zend_Registry::get('view');
        if (null === $this->getModel()) {
            $view->message = '&nbsp;';
        } else {
            $view->record = $this->getModel();
        }
        return $view->render('form.tpl');
    }
    /**
     * Switch between the form types and call the function for each one
     *
     * @param Phprojekt_DatabaseManager_Field $field Object model
     *
     * @return array Data with label, XHTML output and isRequired per field
     */
    public static function generateFormElement(Phprojekt_DatabaseManager_Field $field)
    {
        switch ($field->formType) {
            case "textarea":
                return self::textArea($field);
            case "date":
                return self::date($field);
            case "selectValues":
                return self::selectValues($field);
            case "tree":
                return self::tree($field);
            case "space":
                return '';
            default:
                return self::text($field);
        }
    }

    /**
     * Generate a text input field
     *
     * @param array $field Data of the field from the dbManager
     *
     * @return string XHTML generated
     */
    public static function text(Phprojekt_DatabaseManager_Field $field)
    {
        return Zend_Registry::get('view')->formText($field->tableField, $field->value);
    }

    /**
     * Generate a textarea input field
     *
     * @param array $field Data of the field from the dbManager
     *
     * @return string XHTML generated
     */
    public static function textArea(Phprojekt_DatabaseManager_Field $field)
    {
        return Zend_Registry::get('view')->formTextarea($field->tableField, $field->value,
                                                        array('cols' => 30, 'rows' => 3));
    }

    /**
     * Generate a text input field for dates
     *
     * @param array $field Data of the field from the dbManager
     *
     * @return string XHTML generated
     */
    public static function date(Phprojekt_DatabaseManager_Field $field)
    {
        return Zend_Registry::get('view')->formText($field->tableField, $field->value);
    }

    /**
     * Generate a select input field
     * The data is parsed like key1#value1|key2#value2
     * The value is translated before return
     *
     * @todo (Maybe) Move this into the field object itself
     *
     * @param array $field Data of the field from the dbManager
     *
     * @return string XHTML generated
     */
    public static function selectValues(Phprojekt_DatabaseManager_Field $field)
    {
        $attribs = array();
        $options = array();

        $data = explode('|', $field->formRange);
        foreach ($data as $pairValues) {
            list($key, $value) = split("#", $pairValues);
            $options[$key]     = Zend_Registry::get('translate')->translate($value);
        }

        return Zend_Registry::get('view')->formSelect($field->tableField, $field->value, $attribs, $options);
    }

    /**
     * Generate a select with tree values
     * For make the data, the range value contain wich activerecord is used
     *
     * @param array $field Data of the field from the dbManager
     *
     * @return string XHTML generated
     */
    public static function tree($field)
    {
        $attribs = array();
        $options = array();

        $activeRecord = Phprojekt_Loader::getModel($field->formRange, $field->formRange);
        $tree         = new Phprojekt_Tree_Node_Database($activeRecord, 1);
        $tree->setup();

        foreach ($tree as $node) {
            $key   = $node->id;
            $value = str_repeat('....', $node->getDepth()) . $node->title;

            $options[$key] = Zend_Registry::get('translate')->translate($value);
        }
        return Zend_Registry::get('view')->formSelect($field->tableField, $field->value, $attribs, $options);
    }
}