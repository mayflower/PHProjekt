<?php
/**
 * Represents a module in PHProjekt and coordinates it's mapping
 * to a database
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    CVS: $Id$
 * @author     David Soria Parra <soria_parra@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Represents a module in PHProjekt and coordinates it's mapping
 * to a database
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
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
        if (isset(self::$_cache) && null !== self::$_cache) {
            return self::$_cache;
        }

        $moduleNamespace = new Zend_Session_Namespace('getCachedIds');
        if (!isset($moduleNamespace->modules)) {
            $db     = Phprojekt::getInstance()->getDb();
            $select = $db->select()
                         ->from('Module');
            $stmt = $db->query($select);
            $rows = $stmt->fetchAll();

            foreach ($rows as $row) {
                self::$_cache[$row['name']] = array('id'       => $row['id'],
                                                    'label'    => $row['label'],
                                                    'saveType' => $row['saveType']);
            }
            $moduleNamespace->modules = self::$_cache;
        } else {
            self::$_cache = $moduleNamespace->modules;
        }

        if (isset(self::$_cache)) {
            return self::$_cache;
        } else {
            return array();
        }
    }

    /**
     * Returns the id for a given module
     *
     * @param string $name The Module name
     *
     * @return integer
     */
    public static function getId($name)
    {
        $modules = self::_getCachedIds();

        if (array_key_exists($name, $modules)) {
            return $modules[$name]['id'];
        }

        return 0;
    }

    /**
     * Returns the name for a given module id
     *
     * @param integer $id The module id
     *
     * @return string
     */
    public static function getModuleName($id)
    {
        $modules = self::_getCachedIds();

        foreach ($modules as $name => $data) {
            if ($data['id'] == $id) {
                return $name;
            }
        }

        return null;
    }

    /**
     * Returns the label for a given module id
     *
     * @param integer $id The module id
     *
     * @return string
     */
    public static function getModuleLabel($id)
    {
        $modules = self::_getCachedIds();

        foreach ($modules as $data) {
            if ($data['id'] == $id) {
                return $data['label'];
            }
        }

        return null;
    }

    /**
     * Returns the saveType for a given module
     *
     * @param string $name The Module name
     *
     * @return integer
     */
    public static function getSaveType($id)
    {
        $modules = self::_getCachedIds();

        foreach ($modules as $data) {
            if ($data['id'] == $id) {
                return $data['saveType'];
            }
        }

        return 0;
    }
}
