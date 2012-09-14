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
 * @copyright  Copyright (c) 2012 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * Tags Table Mapper class.
 *
 * The class provide the functions to access the tags database tables.
 */
class Phprojekt_Tags_TagsTableMapper
{
    protected $_db;

    const tagsTableName = 'tags';
    const tagsRelationTableName = 'tags_modules_items';

    /**
     * Constructor
     */
    public function __construct($db = null)
    {
        if (is_null($db)) {
            $this->_db = Phprojekt::getInstance()->getDb();
        }
    }

    public function getTagsForModuleItem($moduleId, $itemId, $limit = 0)
    {
        $select = $this->_db->select()->from(
            array('t' => self::tagsTableName),
            array('word')
        );

        $select->join(
            array('i' => self::tagsRelationTableName),
            't.id = i.tag_id'
        );

        $select->where('module_id = ?', (int) $moduleId)
            ->where('item_id = ?', (int) $itemId);

        if ($limit !== 0) {
            $select->limit($limit);
        }

        $rows = $this->_db->query($select)->fetchAll(Zend_Db::FETCH_COLUMN);
        $ret  = array();

        foreach ($rows as $row) {
            $ret[] = $row;
        }

        return $ret;
    }

    public function saveTagsForModuleItem($moduleId, $itemId, Array $tags = array())
    {
        $tags  = array_unique($tags);
        $idMap = $this->saveTagsAndReturnIdMap($tags);

        $this->deleteTagsForModuleItem($moduleId, $itemId);

        if (count($tags) > 0) {
            $stmt = 'INSERT INTO ' .
                $this->_db->quoteIdentifier(self::tagsRelationTableName) .
                ' (module_id, item_id, tag_id) VALUES ';

            $rows = array();
            foreach ($idMap as $tag => $id) {
                $rows[] = '(' .
                    implode(
                        ',',
                        array(
                            $this->_db->quote($moduleId),
                            $this->_db->quote($itemId),
                            $this->_db->quote($id)
                        )
                    ) .
                    ')';
            }

            $stmt .= implode(',', $rows);

            $stmt = $this->_db->query($stmt);
        }
    }

    protected function saveTagsAndReturnIdMap(Array $tags = array())
    {
        $ids     = array();
        $toAdd   = array();

        if (!empty($tags)) {
            $select = $this->_db->select()->from(
                self::tagsTableName,
                array('id', 'word'))
                ->where('word IN (?)', $tags);

            $rows = $this->_db->fetchAll($select);

            foreach ($rows as $row) {
                $ids[$row['word']] = $row['id'];
            }
        }

        foreach ($tags as $tag) {
            if (!array_key_exists($tag, $ids)) {
                $toAdd[] = $tag;
            }
        }

        $tagTable = new Zend_Db_Table(array(
            'db' => $this->_db,
            'name' => self::tagsTableName
        ));

        foreach ($toAdd as $newTag) {
            $ids[$newTag] = $tagTable->insert(
                array(
                    'word' => $newTag
                )
            );
        }

        return $ids;
    }

    public function deleteTagsForModuleItem($moduleId, $itemId)
    {
        $this->_db->delete(
            self::tagsRelationTableName,
            array(
                'module_id = ?' => (int) $moduleId,
                'item_id = ?' => (int) $itemId
            )
        );
    }

    public function searchForProjectsWithTags(Array $tags = array(), $limit = 0)
    {
        if (count($tags) === 0) {
            return array();
        }

        $tagGroupList = array();

        /* This loop fetches all matching tags per searched tag
         * These are aggragated my searchword because so we are later able to query for the matches for each searched
         * tag.
         */
        foreach ($tags as $tag) {
            $select = $this->_db->select()->from(self::tagsTableName, array('id'));
            $select->where('word LIKE ?', '%' . $tag . '%');
            $tagids = $this->_db->fetchCol($select);

            if (empty($tagids)) {
                //no matching tags, we can stop here
                return array();
            } else {
                $tagGroupList[] = $tagids;
            }
        }

        $moduleItemTagMap = array();

        /* We query the module-item pairs that have one of the tags that match the given searched tag.
         * We aggregate them by module->item->tagGroupListIdx so we are later able to determine which module-item
         * combination matched all the searched tags.
         */
        foreach ($tagGroupList as $index => $ids) {
            $select = $this->_db->select()->from(self::tagsRelationTableName, array('module_id', 'item_id'));
            $select->where('tag_id IN (?)', $ids);

            $rows = $this->_db->fetchAll($select);
            foreach ($rows as $row) {
                $moduleId = $row['module_id'];
                $itemId = $row['item_id'];
                if (!array_key_exists($moduleId, $moduleItemTagMap)) {
                    $moduleItemTagMap[$moduleId] = array();
                }
                $moduleItemTagMap[$moduleId][$itemId][] = $index;
            }
        }

        $ret  = array();
        $retCount = 0;

        foreach ($moduleItemTagMap as $moduleId => $itemList) {
            foreach ($itemList as $itemId => $tagList) {
                /* This check is necessary because one module-item pair could match a given searched tag multiple times.
                 * This would result in the duplicate tagGroupList indexes in the tagList which will be stripped by the
                 * array_intersect call.
                 */
                if (array_keys($tagGroupList) == array_intersect(array_keys($tagGroupList), $tagList)) {
                    $ret[(int) $moduleId][] = (int) $itemId;

                    if (++$retCount >= $limit && $limit > 0) {
                        return $ret;
                    }
                }
            }
        }

        return $ret;
    }

}
