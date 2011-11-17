<?php
/**
 * User-Tag <-> Modules relation class.
 *
 * The class provide the functions for manage the relation between
 * the user-tag relation and modules.
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
 * @subpackage Tags
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
 * User-Tag <-> Modules relation class.
 *
 * The class provide the functions for manage the relation between
 * the user-tag relation and modules.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Tags
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_Tags_Modules extends Zend_Db_Table_Abstract
{
    /**
     * Table name.
     *
     * @var string
     */
    protected $_name = 'tags_modules';

    /**
     * Construct.
     *
     * @return void
     */
    public function __construct()
    {
        $config = array('db' => Phprojekt::getInstance()->getDb());
        parent::__construct($config);
    }

    /**
     * Save a new relation User-Tag <-> ModuleId-ItemId.
     *
     * Is nessesary check if exists,
     * since the relations are delete before insert it
     * but can be the same word in the string separated by spaces.
     *
     * This function use the Zend_DB insert.
     *
     * @param integer $moduleId  The module ID to store.
     * @param integer $itemId    The item ID.
     * @param integer $tagUserId The User-Tag relation ID.
     *
     * @return void
     */
    public function saveTags($moduleId, $itemId, $tagUserId)
    {
        if ($this->find($moduleId, $itemId, $tagUserId)->count() == 0) {
            $data['module_id']   = $moduleId;
            $data['item_id']     = $itemId;
            $data['tag_user_id'] = $tagUserId;
            $this->insert($data);
        }
    }

    /**
     * Return all the modules with the relation User-Tag.
     *
     * @param integer $tagUserId Relation User-Tag ID.
     *
     * @return array Array with 'itemId' and 'moduleId'.
     */
    public function getModulesByRelationId($tagUserId)
    {
        $foundResults = array();
        $rights       = new Phprojekt_Item_Rights();
        $userId       = Phprojekt_Auth::getUserId();

        $where   = sprintf('tag_user_id = %d', (int) $tagUserId);
        $modules = $this->fetchAll($where, 'item_id DESC');

        foreach ($modules as $moduleData) {
            if ($rights->getItemRight($moduleData->module_id, $moduleData->item_id, $userId) > 0) {
                $foundResults[] = array('itemId'   => $moduleData->item_id,
                                        'moduleId' => $moduleData->module_id);
            }
        }

        return $foundResults;
    }

    /**
     * Return all the relations with the pair moduleId-itemId.
     *
     * @param integer $moduleId The module ID to store.
     * @param integer $itemId   The item ID.
     *
     * @return array Array of tag_user IDs.
     */
    public function getRelationIdByModule($moduleId, $itemId)
    {
        $foundResults = array();

        $where   = sprintf('module_id  = %d AND item_id  = %d', (int) $moduleId, (int) $itemId);
        $modules = $this->fetchAll($where);

        if (!empty($modules)) {
            foreach ($modules as $moduleData) {
                $foundResults[] = $moduleData->tag_user_id;
            }
        }

        return $foundResults;
    }

    /**
     * Delete all the entries for one userId-moduleId-itemId pair.
     *
     * @param integer $moduleId   The module ID to store.
     * @param integer $itemId     The item ID.
     * @param array   $tagUserIds All the relationsId for delete.
     *
     * @return void
     */
    public function deleteRelations($moduleId, $itemId, $tagUserIds)
    {
        $ids = array();
        foreach ($tagUserIds as $id) {
            $ids[] = (int) $id;
        }

        if (!empty($ids)) {
            $where   = array();
            $where[] = sprintf('module_id = %d', (int) $moduleId);
            $where[] = sprintf('item_id = %d', (int) $itemId);
            $where[] = 'tag_user_id IN ('. implode(', ', $ids) .')';
            $this->delete($where);
        }
    }

    /**
     * Delete all the entries for the moduleId-itemId pair.
     *
     * @param integer $moduleId The module ID to store.
     * @param integer $itemId   The item ID.
     *
     * @return void
     */
    public function deleteRelationsByItem($moduleId, $itemId)
    {
        $where = sprintf('module_id = %d AND item_id = %d', (int) $moduleId, (int) $itemId);
        $this->delete($where);
    }

    /**
     * Delete all the entries for one userId.
     *
     * @param array $tagUserIds All the relationsId for delete.
     *
     * @return void
     */
    public function deleteRelationsByUser($tagUserIds)
    {
        $ids = array();
        foreach ($tagUserIds as $id) {
            $ids[] = (int) $id;
        }

        if (!empty($ids)) {
            $where   = array();
            $where[] = 'tag_user_id IN ('. implode(', ', $ids) .')';
            $this->delete($where);
        }
    }
}
