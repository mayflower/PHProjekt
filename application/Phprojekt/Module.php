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
     * @param integer $projectId The current Project Id
     *
     * @return array
     */
    protected static function _getCachedIds($projectId)
    {
        if (isset(self::$_cache[$projectId]) && null !== self::$_cache[$projectId]) {
            return self::$_cache[$projectId];
        }

        $db     = Zend_Registry::get('db');
        $select = $db->select()
                     ->from(array('m' => 'Module'))
                     ->joinInner(array('rel' => 'ModuleProjectRelation'), sprintf("%s = %s", $db->quoteIdentifier("m.id"), $db->quoteIdentifier("rel.moduleId")))
                     ->where($db->quoteInto('rel.projectId = ?', $projectId))
                     ->where($db->quoteInto('rel.isActive  = ?', 1));
        $stmt = $db->query($select);
        $rows = $stmt->fetchAll();

        // Set the index 0, is not used but is needed for create other index
        self::$_cache[0] = array();

        foreach ($rows as $row) {
           self::$_cache[$projectId][$row['module']] = $row['id'];
        }

        if (isset(self::$_cache[$projectId])) {
            return self::$_cache[$projectId];
        } else {
            return array();
        }
    }

    /**
     * Returns the id for a given module
     *
     * @param string  $module    The Module name
     * @param integer $projectId The current Project Id
     *
     * @return integer
     */
    public static function getId($module, $projectId = null)
    {
        // Default project id for general request
        if (null === $projectId || $projectId < 1) {
            $projectId = 1;
        }
        $modules = self::_getCachedIds($projectId);

        if (array_key_exists($module, $modules)) {
            return $modules[$module];
        }

        return 0;
    }

    /**
     * Returns the name for a given module id
     *
     * @param integer $id        The module id
     * @param integer $projectId The current Project Id
     *
     * @return string
     */
    public static function getModuleName($id, $projectId = null)
    {
        // Default project id for general request
        if (null === $projectId || $projectId < 1) {
            $projectId = 1;
        }

        $modules = self::_getCachedIds($projectId);

        if ((in_array($id, $modules))) {
            return array_search($id, $modules);
        }

        return null;
    }
}