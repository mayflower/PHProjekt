<?php
/**
 * Filter View helper class
 *
 * This class is for help on the draw of the filters in the list view
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
class Default_Helpers_FilterViewRenderer implements Phprojekt_RenderHelper
{
    /**
     * The filters in the session
     *
     * @var array
     */
    protected $_filters;

    /**
     * The fields of the model to render
     *
     * @var array
     */
    protected $_fields;

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
     * Set the filters
     *
     * @param array $filters All the filters to render
     *
     * @return void
     */
    public function setModel($filters)
    {
        $this->_filters = $filters;
    }

    /**
     * Return the filters for rendered
     *
     * @return array
     */
    public function &getModel()
    {
        return $this->_filters;
    }

    /**
     * Set the fields for show in the filter form
     *
     * @param array $fields The fields with listUseFilter set to 1
     *
     * @return void
     */
    public function setFields($fields)
    {
        if (is_array($fields)) {
            $this->_fields = $fields;
        } else {
            $this->_fields = array();
        }
    }

    /**
     * Return thr fields for show in the filter form
     *
     * @return array
     */
    public function getFields()
    {
        return $this->_fields;
    }

    /**
     * Render the content of the filters view and return it
     *
     * @param string $name Name of the template to render
     *
     * @return string
     */
    public function render($name = 'filter.tpl')
    {
        $fields = $this->getFields();
        if (!empty($fields)) {
            $view = Zend_Registry::get('view');

            $view->fields  = $fields;
            $view->filters = $this->getModel();
            return $view->render($name);
        } else {
            return '';
        }
    }

    /**
     * Convert the filter in a user view mode
     *
     * @param string $field Field in the database
     * @param string $rule  Rule of the filter
     * @param string $text  Text to serch
     *
     * @return string XHTML generated
     */
    public static function generateFilterElement($field, $rule, $text)
    {
        static $count = 0;

        $translator = Zend_Registry::get('translate');

        $field = $translator->translate($field);
        $rule  = $translator->translate($rule);

        if ($count > 0) {
            $prefix = $translator->translate('AND').'&nbsp;';
        } else {
            $prefix = '';
        }
        $count++;

        return $prefix.$field.'&nbsp;'.$rule.'&nbsp;'.$text.'&nbsp;';
    }
}