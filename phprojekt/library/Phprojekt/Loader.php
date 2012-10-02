<?php
/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * An own class loader that reads the class files from the
 * /application directory or from the Zend library directory depending
 * on the name of the class.
 */
class Phprojekt_Loader extends Zend_Loader
{
    /**
     * Directories.
     *
     * @var array
     */
    protected static $_directories = array(PHPR_CORE_PATH, PHPR_LIBRARY_PATH, PHPR_USER_CORE_PATH);

    /**
     * Define the set of allowed characters for classes..
     */
    const CLASS_PATTERN = '[A-Za-z0-9_]+';

    /**
     * Adds a directory to search when autoloading.
     *
     * @param string $directory The directory
     *
     * @return void
     */
    public static function addIncludeDirectory($directory)
    {
        self::$_directories[] = $directory;
    }

    /**
     * Load a class
     *
     * @param string       $class Name of the class.
     * @param string|array $dirs  Directories to search.
     *
     * @return void
     */
    public static function loadClass($class, $dirs = null)
    {
        if (preg_match("@Controller$@", $class)) {
            $names  = explode('_', $class);
            $front  = Zend_Controller_Front::getInstance();
            $module = (count($names) > 1) ? $names[0] : $front->getDefaultModule();

            $path = $module . DIRECTORY_SEPARATOR
                  . $front->getModuleControllerDirectoryName()
                  . DIRECTORY_SEPARATOR
                  . array_pop($names) . '.php';

            if (self::isReadable(PHPR_CORE_PATH . DIRECTORY_SEPARATOR . $path)) {
                self::_includeFile(PHPR_CORE_PATH . DIRECTORY_SEPARATOR . $path, true);
            } else if (self::isReadable(PHPR_USER_CORE_PATH . $path)) {
                self::_includeFile(PHPR_USER_CORE_PATH . $path, true);
            }
        }

        if (!class_exists($class, false)) {
            if (null === $dirs) {
                $dirs = self::$_directories;
            }
            parent::loadClass($class, $dirs);
        }
    }

    /**
     * The autoload method used to load classes on demand.
     * Returns either the name of the class or throws the Zend_Excepton, if loading failed.
     * The Zend_Exception can be catched for something like: new MissingClass().
     *
     * @param string $class The name of the class.
     *
     * @return string|Zend_Exception Class name on success; false on failure.
     */
    public static function autoload($class)
    {
        try {
            @self::loadClass($class, self::$_directories);
            return $class;
        } catch (Zend_Exception $error) {
            return false;
        }
    }

    /**
     * Load the class of a model and return the name of the class.
     *
     * Always use the returned name to instantiate a class, a customized
     * class name might be loaded and returned by this method.
     *
     * @param string $module Name of the module.
     * @param string $model  Name of the class to be loaded.
     *
     * @throws Zend_Exception If class not found.
     *
     * @return string Identifier class name.
     */
    public static function getModelClassname($module, $model)
    {
        return "{$module}_Models_{$model}";
    }

    /**
     * Load the class of a model and return an instance of the class.
     *
     * Always use the returned name to instantiate a class, a customized
     * class name might be loaded and returned by this method
     *
     * @param string $module Name of the module.
     * @param string $view   Name of the class to be loaded.
     *
     * @throws Zend_Exception If class not found.
     *
     * @return string Identifier class name.
     */
    public static function getViewClassname($module, $view)
    {
        return "{$module}_Views_{$view}";
    }

    /**
     * Load the class of a model and return an new instance of the class.
     *
     * Always use the returned name to instantiate a class, a customized
     * class name might be loaded and returned by this method.
     *
     * This method can take more than the two arguments.
     * Every other argument is passed to the constructor.
     *
     * The class is temporally cached in the Registry for the next calls.
     * Only is cached if don't have any arguments
     *
     * Be sure that the class have the correct "__clone" function defined
     * if it have some internal variables with other classes for prevent
     * the same point to the object.
     *
     * @param string $module Name of the module.
     * @param string $model  Name of the model.
     *
     * @return Object
     */
    public static function getModel($module, $model)
    {
        if (func_num_args() > 2) {
            throw new Exception('Arguments are not supported anymore');
        }

        $name = self::getModelClassname($module, $model);

        return class_exists($name) ? new $name() : false;
    }

    /**
     * Returns the name of the model for a given object.
     *
     * @param Phprojekt_Model_Interface $object An active record.
     *
     * @return string|boolean
     */
    public static function getModelFromObject(Phprojekt_Model_Interface $object)
    {
        $match = null;
        $pattern = str_replace('_', '', self::CLASS_PATTERN);
        if (preg_match("@_(" . $pattern . ")$@", get_class($object), $match)) {
            return $match[1];
        }

        return false;
    }

    /**
     * Returns the name of the modul for a given object.
     *
     * @param Phprojekt_ActiveRecord_Abstract $object An active record.
     *
     * @return string|boolean
     */
    public static function getModuleFromObject(Phprojekt_Model_Interface $object)
    {
        $match = null;
        $pattern = str_replace('_', '', self::CLASS_PATTERN);
        if (preg_match("@^(" . $pattern . ")_@", get_class($object), $match)) {
            if ($match[1] == 'Phprojekt') {
                return 'Core';
            } else {
                return $match[1];
            }
        }

        return false;
    }

    /**
     * Try to include a file by the class name.
     *
     * @param string  $class          The name of the class.
     * @param boolean $isLibraryClass True if the class is in the library dir.
     * @param boolean $isUserModule   True if the class is in the user core dir.
     *
     * @return boolean
     */
    public static function tryToLoadClass($class, $isLibraryClass = false, $isUserModule = false)
    {
        $names  = explode('_', $class);

        if (!$isLibraryClass) {
            if ($isUserModule) {
                $file = substr(PHPR_USER_CORE_PATH, 0, strlen(PHPR_USER_CORE_PATH) -1);
            } else {
                $file = PHPR_CORE_PATH;
            }
        } else {
            $file = PHPR_LIBRARY_PATH;
        }
        foreach ($names as $name) {
            $file .= DIRECTORY_SEPARATOR . $name;
        }
        $file .= '.php';

        $assert = false;
        if (file_exists($file)) {
            self::_includeFile($file, true);
            $assert = true;
        }

        return $assert;
    }

    /**
     * Try to include a library file by the class name.
     *
     * @param string $class The name of the class.
     *
     * @return boolean
     */
    public static function tryToLoadLibClass($class)
    {
        return self::tryToLoadClass($class, true);
    }

    /**
     * Add the module path for load customs templates.
     *
     * @param Zend_View|null $view View class.
     *
     * @return void;
     */
    public static function loadViewScript($view = null)
    {
        $module = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
        if (null === $view) {
            $view = Phprojekt::getInstance()->getView();
        }

        // Try the system module
        $path = PHPR_CORE_PATH . DIRECTORY_SEPARATOR . $module . DIRECTORY_SEPARATOR
            . 'Views' . DIRECTORY_SEPARATOR . 'dojo' . DIRECTORY_SEPARATOR;
        if (!is_dir($path)) {
            // Try the user module
            $path = PHPR_USER_CORE_PATH . $module . DIRECTORY_SEPARATOR
            . 'Views' . DIRECTORY_SEPARATOR . 'dojo' . DIRECTORY_SEPARATOR;
        }

        $view->addScriptPath($path);
    }
}
