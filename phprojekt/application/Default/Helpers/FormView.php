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
final class Default_Helpers_FormView
{
    /**
     * Switch between the form types and call the function for each one
     *
     * @param Phprojekt_Item_Abstract $models Object model
     * @param array                   $params $_REQUEST array
     *
     * @return array Data with label, XHTML output and isRequired per field
     */
    public static function generateFormElement(Phprojekt_DatabaseManager_Field $field)
    {
        switch ($field->formType) {
            case "textarea":
                return self::formTextArea($field);
            case "date":
                return self::formDate($field);
            case "selectValues":
                return self::formSelectValues($field);
            case "tree":
                return self::formTree($field);
            case "space":
                return '';
            default:
                return self::formText($field);
        }
    }

    /**
     * Generate a text input field
     *
     * @param array $field Data of the field from the dbManager
     *
     * @return string XHTML generated
     */
    public static function formText(Phprojekt_DatabaseManager_Field $field)
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
    public static function formTextArea(Phprojekt_DatabaseManager_Field $field)
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
    public static function formDate(Phprojekt_DatabaseManager_Field $field)
    {
        return Zend_Registry::get('view')->formText($field->tableField, $field->value);
    }

    /**
     * Generate a select input field
     * The data is parsed like key1#value1|key2#value2
     * The value is translated before return
     *
     * @todo (Maybe) Move this into the field object itself
     * @param array $field Data of the field from the dbManager
     *
     * @return string XHTML generated
     */
    public static function formSelectValues(Phprojekt_DatabaseManager_Field $field)
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
    public static function formTree($field)
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