<?php
/**
 * Adapter for Smarty
 *
 * This class implements the Zend_View_Abstract interface
 * for interaction with smarty
 *
 * LICENSE: Licensed under the terms of the GNU Publice License
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *             GNU Public License 2.0
 * @version    CVS: $Id$
 * @link       http://www.phprojekt.com
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
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *             GNU Public License 2.0
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
     * @param string $tmplPath
     * @param array $extraParams
     * @return void
     */
    public function __construct($compilePath = null)
    {
        Zend_Loader::loadFile('Smarty.class.php',
                        PHPR_LIBRARY_PATH.DIRECTORY_SEPARATOR.'Smarty');

        $this->_smarty = new Smarty();

        if (null !== $compilePath) {
            $this->templateCompiledDir = $compilePath;
        }
    }

    /**
     * Assign variables to the template
     *
     * Allows setting a specific key to the specified value, OR passing an array
     * of key => value pairs to set en masse.
     *
     * @see __set()
     * @param string|array $spec The assignment strategy to use (key or array of key
     * => value pairs)
     * @param mixed $value (Optional) If assigning a named variable, use this
     * as the value.
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
     * @param string $key The variable name.
     * @param mixed $val The variable value.
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
     * @return mixed The variable value.
     */
    public function __get($key)
    {
        return $this->_smarty->get_template_vars($key);
    }

    /**
     * Run
     *
     * @param string $name The template to process.
     * @return string The output.
     */
    protected function _run()
    {
        /* why 'this'?
         * to emulate standard zend view functionality
         * doesn't mess up smarty in any way */
        $this->_smarty->assign_by_ref('this', $this);

       /*
        * smarty needs a template_dir, and can only use templates,
        * found in that directory, so we have to strip it from the filename
        * We use the given filename to set the template dir,
        * so we we have a nice ":moduleDir/:action" configuration available
        * in the front setup */
        $file = func_get_arg(0);
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
            throw new Zend_View_Exception('Cannot set directory for compiled templates');
        }

        // process the template (and filter the output)
        echo $this->_smarty->fetch(basename($file));
    }
}