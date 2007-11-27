<?php
/**
 * Adapter for Smarty
 *
 * This class implements the Zend_View_Abstract interface
 * for interaction with smarty
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
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
        PHPR_LIBRARY_PATH . DIRECTORY_SEPARATOR . 'Smarty');

        $this->_smarty = new Smarty();
        $this->_smarty->force_compile = true;

        if (null !== $compilePath) {
            $this->templateCompiledDir = $compilePath;
        }

        /**
         * Register various helper functions
         */
        $this->_smarty->register_function('url',
                                          array($this, 'urlHelper'));
        $this->_smarty->register_function('link_to',
                                          array($this, 'urlHelper'));
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
     * Assign an external value by ref. This makes possible to bind
     * arrays and objects to a variable
     *
     * @param string $key   Name of the parameter
     * @param mixed  $value Object to assign
     *
     * @return void
     */
    public function assignByRef($key, &$value)
    {
        $this->_smarty->assign_by_ref($key, $value);
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
     * Return the smarty template engine object
     *
     * @return Smarty
     */
    public function getEngine()
    {
        return $this->_smarty;
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
        * to emulate standard zend view, list and form functionality
        * doesn't mess up smarty in any way */
        $this->_smarty->assign_by_ref('view', $this);

        // process the template (and filter the output)
        echo $this->_smarty->fetch(basename($file));
    }

    /**
     * This helper make the links in the template
     *
     * @param array $array Helper array for building urls
     *
     * @return string
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
     * Finds a view script from the available directories.
     * If there are not a script, the default script is used
     *
     * @param $name string The base name of the script.
     * @return string The path to the script to render
     */
    protected function _script($name)
    {
        $this->_smarty->clear_cache($name);
        $paths = $this->getAllPaths();
        if (0 == count($paths['script'])) {
            require_once 'Zend/View/Exception.php';
            throw new Zend_View_Exception('no view script directory set; unable to determine location for view script',
                $this);
        }

        foreach ($paths['script']as $dir) {
            if (is_readable($dir . $name)) {
                return $dir . $name;
            }
        }

        $defaultDir = PHPR_CORE_PATH
                    . DIRECTORY_SEPARATOR
                    . 'Default'
                    . DIRECTORY_SEPARATOR
                    . 'Views'
                    . DIRECTORY_SEPARATOR
                    . 'scripts'
                    . DIRECTORY_SEPARATOR;
        return $defaultDir . $name;
    }
}