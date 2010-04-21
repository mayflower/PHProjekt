<?php
/**
 * Represents a module in PHProjekt and coordinates it's mapping to a database.
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Module
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */

/**
 * Represents a module in PHProjekt and coordinates it's mapping to a database.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Module
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     David Soria Parra <soria_parra@mayflower.de>
 */
class Phprojekt_Module
{
    /**
     * The module saveType.
     *
     * const TYPE_NORMAL = Under a project.
     * const TYPE_GLOBAL = Global (Under root project).
     * const TYPE_MIX    = Mix Under a project AND global.
     */
    const TYPE_NORMAL = 0; // Under a project.
    const TYPE_GLOBAL = 1; // Global (Under root project).
    const TYPE_MIX    = 2; // Mix Under a project AND global.

    /**
     * Saves the cache for our module entries, to minimize database lookups.
     *
     * @var array
     */
    protected static $_cache = null;

    /**
     * Receives all module <-> id combinations from the database.
     *
     * This is somewhat a pretty stupid caching mechanism,
     * but as the module id itself is used often,
     * we try not to do it using active record.
     *
     * The method returns an array of the following format:
     *  array( MODULENAME => MODULEID,
     *         MODULENAME => MODULEID );
     *
     * @return array Array with 'id', 'label' and 'saveType'.
     */
    protected static function _getCachedIds()
    {
        if (isset(self::$_cache) && null !== self::$_cache) {
            return self::$_cache;
        }

        $moduleNamespace = new Zend_Session_Namespace('Phprojekt_Module_Module-_getCachedIds');
        if (!isset($moduleNamespace->modules)) {
            $db     = Phprojekt::getInstance()->getDb();
            $select = $db->select()
                         ->from('module');
            $stmt = $db->query($select);
            $rows = $stmt->fetchAll();

            foreach ($rows as $row) {
                self::$_cache[$row['name']] = array('id'       => $row['id'],
                                                    'label'    => $row['label'],
                                                    'saveType' => $row['save_type']);
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
     * Returns the ID for a given module.
     *
     * @param string $name The Module name.
     *
     * @return integer Module ID.
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
     * Returns the name for a given module ID
     *
     * @param integer $id The module ID.
     *
     * @return string The module name.
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
     * Returns the label for a given module ID
     *
     * @param integer $id The module ID.
     *
     * @return string The module label.
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
     * Returns the saveType for a given module.
     *
     * @param string $name The Module name.
     *
     * @return integer The module saveType.
     */
    public static function getSaveType($id)
    {
        $modules = self::_getCachedIds();

        foreach ($modules as $data) {
            if ($data['id'] == $id) {
                return intval($data['saveType']);
            }
        }

        return 0;
    }

    /**
     * Returns if the module is saved under a project.
     *
     * @param string $name The Module name.
     *
     * @return boolean True if the saveType is 0.
     */
    public static function saveTypeIsNormal($id)
    {
        return (self::getSaveType($id) == self::TYPE_NORMAL);
    }

    /**
     * Returns if the module is saved as global.
     *
     * @param string $name The Module name.
     *
     * @return boolean True if the saveType is 1.
     */
    public static function saveTypeIsGlobal($id)
    {
        return (self::getSaveType($id) == self::TYPE_GLOBAL);
    }
}
