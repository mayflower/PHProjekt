<?php
/**
 * Represents a module in PHProjekt and coordinates it's mapping
 * to a database
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id$
 * @author     David Soria Parra <soria_parra@mayflower.de>
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * Represents a module in PHProjekt and coordinates it's mapping
 * to a database
 *
 * @copyright  2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class Phprojekt_Module
{
    /**
     * Saves the cache for our module entries, to minimize
     * database lookups
     *
     * @var array
     */
    protected static $_cache = null;

    /**
     * Receives all module <-> id combinations from the database.
     *
     * This is somewhat a pretty stupid caching mechanism, but
     * as the module id itself is used often, we try not to do it
     * using active record.
     *
     * The method returns an array of the following format
     *  array( MODULENAME => MODULEID,
     *         MODULENAME => MODULEID );
     *
     * @todo: Provide a ActiveRecord like interface, but actually don't do
     *        ActiveRecord
     *
     * @return array
     */
    protected static function _getCachedIds()
    {
        if (null !== self::$_cache) {
            return self::$_cache;
        }

        $db   = Zend_Registry::get('db');
        $rows = $db->fetchAll('SELECT id, module
                                 FROM ' . $db->quoteIdentifier('Module'));
        foreach ($rows as $row) {
           self::$_cache[$row['module']] = $row['id'];
        }

        return self::$_cache;
    }

    /**
     * Returns the id for a given module
     *
     * @param string $module
     *
     * @return integer
     */
    public static function getId($module)
    {
        $modules = self::_getCachedIds();

        if (array_key_exists($module, $modules)) {
            return $modules[$module];
        }

        return 0;
    }

    /**
     * Returns the name for a given module id
     *
     * @param integer The module id
     *
     * @return string
     */
    public static function getModuleName($id)
    {
        $modules = self::_getCachedIds();

        if ((in_array($id, $modules))) {
            return array_search($id, $modules);
        }

        return null;
    }
}