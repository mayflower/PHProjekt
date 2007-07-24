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
    protected static $_directories = array(PHPR_CORE_PATH, PHPR_LIBRARY_PATH);

    /**
     * Load a class
     *
     * @param string       $class Name of the class
     * @param string|array $dirs  Directories to search
     */
    public static function loadClass($class, $dirs = null)
    {
        if (preg_match("@Controller$@", $class)) {
            if (strpos('_', $class) === false) {
                $class = 'Default_'.$class;
            }

            $class = preg_replace("@([A-Za-z0-9]*_)?([A-Za-z]+)Controller$@",
                                  "\\1Controllers_\\2Controller", $class);

            $path     = str_replace('_', DIRECTORY_SEPARATOR, $class);
            $classDir = dirname($path);
            if (!is_array($dirs)) {
                $dirs = array($dirs, $classDir);
            } else {
                foreach ($dirs as $dir) {
                    $dirs[] = $dir . DIRECTORY_SEPARATOR .$classDir;
                }
            }

        }

        parent::loadClass($class, $dirs);
    }

    /**
     * The autoload method used to load classes on demand
     * Returns either the name of the class or false, if
     * loading failed.
     *
     * @param  string $class The name of the class
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
}