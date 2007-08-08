<?php
/**
 * Adapter for Smarty
 *
 * This class implements the Zend_View_Abstract interface
 * for interaction with smarty
 *
 * LICENSE: Licensed under the terms of the GNU Publice License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @link       http://www.phprojekt.com
 * @author     David Soria Parra <soria_parra@mayflower.de>
 * @since      File available since Release 1.0
 */

/**
 * Adapter for Smarty
 *
 * This class implements the Zend_View_Abstract interface
 * for interaction with smarty. The template directory of smarty
 * is not setable using the Zend_View_Abstract::setScriptPath operations.
 * They are set using the translated filename from the helper.
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class Default_Helpers_Smarty extends Zend_View_Abstract
{
    /**
     * Smarty object
     * @var Smarty
     */
    protected $_smarty;

    /**
     * Name of the directory where the compiled templates are stored
     *
     * @var string
     */
    public $templateCompiledDir = 'templates_c';

    /**
     * Constructor
     *
     * @param string $compilePath Path for the compiled smarty templates
     *
     * @return void
     */
    public function __construct($compilePath = null)
    {
        Zend_Loader::loadFile('Smarty.class.php',
        PHPR_LIBRARY_PATH.DIRECTORY_SEPARATOR.'Smarty');

        $this->_smarty = new Smarty();
        $this->caching = false;

        if (null !== $compilePath) {
            $this->templateCompiledDir = $compilePath;
        }

        /**
         * Register various helper functions
         */
        $this->_smarty->register_function('url', array($this, 'urlHelper'));
        $this->_smarty->register_function('link_to', array($this, 'urlHelper'));
        $this->_smarty->register_modifier('translate',
                                         array($this, 'translateModifier'));
    }

    /**
     * Assign variables to the template
     *
     * Allows setting a specific key to the specified value, OR passing an array
     * of key => value pairs to set en masse.
     *
     * @param string|array $key   the assigments to use (key or array of key
     * => value pairs)
     * @param mixed        $value (Optional) If assigning a named variable,
     * use this as the value.
     *
     * @see __set
     * @return void
     */
    public function assign($key, $value = null)
    {
        if (is_array($key)) {
            $this->_smarty->assign($key);
        } else {
            $this->_smarty->assign($key, $value);
        }
    }

    /**
     * Assign a variable to the template
     *
     * @param string $key   The variable name.
     * @param mixed  $value The variable value.
     *
     * @return void
     */
    public function __set($key, $value)
    {
        $this->assign($key, $value);
    }

    /**
     * Retrieve an assigned variable
     *
     * @param string $key The variable name.
     *
     * @return mixed The variable value.
     */
    public function __get($key)
    {
        return $this->_smarty->get_template_vars($key);
    }

    /**
     * Run - helper function to fit with zend view
     *
     * @return string The output.
     */
    protected function _run()
    {
        /*
        * smarty needs a template_dir, and can only use templates,
        * found in that directory, so we have to strip it from the filename
        * We use the given filename to set the template dir,
        * so we we have a nice ":moduleDir/:action" configuration available
        * in the front setup */
        $file                        = func_get_arg(0);
        $this->_smarty->template_dir = dirname($file);

        $cpath = realpath($this->_smarty->template_dir
        . DIRECTORY_SEPARATOR
        . '..'
        . DIRECTORY_SEPARATOR
        . $this->templateCompiledDir);

        if (is_dir($this->templateCompiledDir)) {
            $this->_smarty->compile_dir = $this->templateCompiledDir;
        } elseif (is_dir($cpath)) {
            $this->_smarty->compile_dir = $cpath;
        } else {
            throw new Zend_View_Exception('Cannot set directory '.
            'for compiled templates');
        }

        $this->setHelperPath(PHPR_LIBRARY_PATH, 'Zend_View_Helper_');

        /* why 'this'?
        * to emulate standard zend view and form functionality
        * doesn't mess up smarty in any way */
        $this->_smarty->assign_by_ref('view', $this);
        $form = Default_Helpers_FormView::getInstance($this);
        $this->_smarty->assign_by_ref('form', $form);

        // process the template (and filter the output)
        echo $this->_smarty->fetch(basename($file));
    }

    /**
     * This helper make the links in the template
     *
     * @param array $array Helper array for building urls
     *
     * @return array
     */
    public function urlHelper($array)
    {
        $defaults = array (
        'module'     => $this->module,
        'controller' => $this->controller,
        'action'     => $this->action);

        if (!array_key_exists('defaults', $array)
        || $array['defaults'] == "true") {
            $array = array_merge($defaults, $array);
        }

        if (array_key_exists('defaults', $array)) {
            unset ($array['defaults']);
        }

        return $this->url($array, 'default', true);
    }

    /**
     * Translate all the string with " |translate"
     *
     * @param string $string Input text to be translated
     *
     * @return array
     */
    public function translateModifier($string)
    {
        $translator = Zend_Registry::get('translate');
        /* @var $translator Zend_Translate_Adapter */
        return $translator->translate($string);
    }
}