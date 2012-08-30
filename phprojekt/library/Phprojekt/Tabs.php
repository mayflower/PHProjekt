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
 * Manage tabs-module relations.
 *
 * The class return the tab on each module ID.
 */
class Phprojekt_Tabs
{
    /**
     * Saves the cache for our tab-module entries, to minimize database lookups.
     *
     * @var array
     */
    protected static $_cache = null;

    /**
     * Receives all tabs <-> moduleId combinations from the database.
     *
     * The method returns an array of the following format:
     *  array( MODULEID => array(TABID => TABLABEL),
     *         MODULEID => array(TABID => TABLABEL));
     *
     * @param integer $moduleId The Module ID.
     *
     * @return array Array with 'id' and 'label'.
     */
    protected static function _getCachedIds($moduleId)
    {
        if (isset(self::$_cache[$moduleId]) && null !== self::$_cache[$moduleId]) {
            return self::$_cache[$moduleId];
        }

        $db     = Phprojekt::getInstance()->getDb();
        $select = $db->select()
                     ->from(array('t' => 'tab'))
                     ->joinInner(array('rel' => 'module_tab_relation'),
                                 sprintf("%s = %s", $db->quoteIdentifier("t.id"),
                                 $db->quoteIdentifier("rel.tab_id")))
                     ->where(sprintf('rel.module_id = %d', (int) $moduleId));
        $stmt = $db->query($select);
        $rows = $stmt->fetchAll();

        // Set the index 0, is not used but is needed for create other index
        self::$_cache[0] = array();

        self::$_cache[$moduleId] = array();
        foreach ($rows as $row) {
           self::$_cache[$moduleId][] = array('id'    => $row['id'],
                                              'label' => $row['label']);
        }

        return self::$_cache[$moduleId];
    }

    /**
     * Returns the tabs for a given module.
     *
     * @param string $moduleId The Module ID.
     *
     * @return array Array with 'id' and 'label'.
     */
    public static function getTabsByModule($moduleId)
    {
        return self::_getCachedIds($moduleId);
    }

    /**
     * Returns all the tabs.
     *
     * @return array Rowset of results.
     */
    public static function getTabs()
    {
        $db     = Phprojekt::getInstance()->getDb();
        $select = $db->select()
                     ->from('tab');
        $stmt = $db->query($select);

        return $stmt->fetchAll();
    }

    /**
     * Save/update the tab only.
     *
     * @param string  $label The tab label.
     * @param integer $id    The tab ID if exists.
     *
     * @return integer Tab ID.
     */
    public function saveTab($label, $id = 0)
    {
        $db = Phprojekt::getInstance()->getDb();
        if ($id > 0) {
            $data['label'] = $label;
            $db->update('tab', $data, 'id = ' . (int) $id);
            return $id;
        } else {
            $data['label'] = $label;
            $db->insert('tab', $data);
            return $db->lastInsertId();
        }
    }

    /**
     * Save the tab-module relation.
     *
     * @param array   $tabIds   Wrray with tab ID.
     * @param integer $moduleId The module ID.
     *
     * @return void
     */
    public function saveModuleTabRelation($tabIds, $moduleId)
    {
        $db = Phprojekt::getInstance()->getDb();
        $db->delete('module_tab_relation', sprintf('module_id = %d', (int) $moduleId));
        if (!is_array($tabIds)) {
            $tabIds = array($tabIds);
        }
        foreach ($tabIds as $tabId) {
            $data['tab_id']    = (int) $tabId;
            $data['module_id'] = (int) $moduleId;
            $db->insert('module_tab_relation', $data);
        }
    }
}
