<?php
/**
 * Our own class loader.
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
 * An own class loader that reads the class files from the
 * /application directory or from the Zend library directory depending
 * on the name of the class.
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class Phprojekt_Loader extends Zend_Loader
{
    /**
     * Identifier for controllers
     * It's normaly only needed by the internals
     *
     * @see _getClass
     */
    const CONTROLLER = 'Controllers';

    /**
     * Identifier for views
     * It's normaly only needed by the internals
     *
     * @see _getClass
     */
    const VIEW = 'Views';

    /**
     * Identifier for models
     * It's normaly only needed by the internals
     *
     * @see _getClass
     */
    const MODEL = 'Models';

    /**
     * Directories
     *
     * @var array
     */
    protected static $_directories = array(PHPR_CORE_PATH, PHPR_LIBRARY_PATH);

    /**
     * Load a class
     *
     * @param string       $class Name of the class
     * @param string|array $dirs  Directories to search
     *
     * @return void
     */
    public static function loadClass($class, $dirs = null)
    {
        if (preg_match("@Controller$@", $class)) {
            $names  = explode('_', $class);
            $front  = Zend_Controller_Front::getInstance();
            $module = (count($names) > 1) ? $names[0] : $front->getDefaultModule();

            $file   = PHPR_CORE_PATH . DIRECTORY_SEPARATOR
                    . $module . DIRECTORY_SEPARATOR
                    . $front->getModuleControllerDirectoryName()
                    . DIRECTORY_SEPARATOR
                    . array_pop($names) . '.php';

            if (self::isReadable($file)) {
                self::_includeFile($file, true);
            }
        }

        if (!class_exists($class)) {
            parent::loadClass($class, $dirs);
        }
    }

    /**
     * The autoload method used to load classes on demand
     * Returns either the name of the class or false, if
     * loading failed.
     *
     * @param string $class The name of the class
     *
     * @return mixed
     */
    public static function autoload($class)
    {
        try {
            self::loadClass($class, self::$_directories);
            return $class;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Instantiate a given class name. We asume that it's allready loaded.
     *
     * @param string $name Name of the class
     * @param array  $args Argument list
     *
     * @return object
     */
    protected static function _newInstance($name, $args)
    {
        /*
         * We have to use the reflection here, as expanding arguments
         * to an array is not possible without reflection.
         */
        $class = new ReflectionClass($name);
        return $class->newInstanceArgs($args);
    }

    /**
     * Finds a class. If a customized class is available in the Customized/
     * directory, it's loaded and the name is returned, instead of the
     * normal class.
     *
     * @param string $module Name of the module
     * @param string $item   Name of the class to be loaded
     * @param string $ident  Ident, might be 'Models', 'Controllers' or 'Views'
     *
     * @throws Zend_Exception If class not found
     *
     * @return string
     */
    protected static function _getClass($module, $item, $ident)
    {
        $logger = Zend_Registry::get('log');

        if ($ident != self::CONTROLLER) {
            $nIdentifier = sprintf("%s_%s_%s", $module, $ident, $item);
            $cIdentifier = sprintf("%s_%s_Customized_%s", $module, $ident, $item);
        } else {
            $nIdentifier = sprintf("%s_%s", $module, $item);
            $cIdentifier = sprintf("%s_Customized_%s", $module, $item);
            $logger->debug("$nIdentifier");
        }

        try {
            self::loadClass($cIdentifier, self::$_directories);
            $logger->debug("get customized class {$cIdentifier} "
                         . "instead of {$nIdentifier}");
            return $cIdentifier;
        } catch (Zend_Exception $ze) {

        }

        self::loadClass($nIdentifier, self::$_directories);
        return $nIdentifier;
    }


    /**
     * Load the class of a model and return the name of the class.
     * Always use the returned name to instantiate a class, a customized
     * class name might be loaded and returned by this method
     *
     * @param string $module Name of the module
     * @param string $model  Name of the class to be loaded
     *
     * @see _getClass
     *
     * @throws Zend_Exception If class not found
     *
     * @return string
     */
    public static function getModel($module, $model)
    {
        return self::_getClass($module, $model, self::MODEL);
    }

    /**
     * Load the class of the controllers and return the name of the class.
     * Always use the returned name to instantiate a class, a customized
     * class name might be loaded and returned by this method
     *
     * @param string $module     Name of the module
     * @param string $controller Name of the class to be loaded
     *
     * @see _getClass
     *
     * @throws Zend_Exception If class not found
     *
     * @return string
     */
    public static function getController($module, $controller)
    {
        return self::_getClass($module, $controller, self::CONTROLLER);
    }

    /**
     * Load the class of a model and return an instance of the class.
     * Always use the returned name to instantiate a class, a customized
     * class name might be loaded and returned by this method
     *
     * @param string $module Name of the module
     * @param string $view   Name of the class to be loaded
     *
     * @see _getClass
     *
     * @throws Zend_Exception If class not found
     *
     * @return string
     */
    public static function getView($module, $view)
    {
        return self::_getClass($module, $view, self::VIEW);
    }

    /**
     * Load the class of a model and return an new instance of the class.
     * Always use the returned name to instantiate a class, a customized
     * class name might be loaded and returned by this method.
     * This method can take more than the two arguments. Every other argument
     * is passed to the constructor.
     *
     * @param string $module Name of the module
     * @param string $model  Name of the model
     *
     * @return Object
     */
    public static function getModelFactory($module, $model)
    {
        $name = self::getModel($module, $model);
        $args = array_slice(func_get_args(), 2);

        return self::_newInstance($name, $args);
    }

   /**
    * Load the class of a view and return an new instance of the class.
    * Always use the returned name to instantiate a class, a customized
    * class name might be loaded and returned by this method
    *
    * @param string $module Name of the module
    * @param string $view   Name of the view
    *
    * @return Object
    */
    public static function getViewFactory($module, $view)
    {
        $name = self::getView($module, $view);
        $args = array_slice(func_get_args(), 2);

        return self::_newInstance($name, $args);
    }
}