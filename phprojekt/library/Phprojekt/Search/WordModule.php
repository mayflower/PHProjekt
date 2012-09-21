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
 * Class for manage the words-module relation.
 *
 * The class provide the functions for save/delete/search
 * the words - module relation in the SearchWordsModule table.
 */
class Phprojekt_Search_WordModule extends Zend_Db_Table_Abstract
{
    /**
     * Name of the table.
     *
     * @var string
     */
    protected $_name = 'search_word_module';

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $config = array('db' => Phprojekt::getInstance()->getDb());

        parent::__construct($config);
    }

    /**
     * Index a string with the moduleId and the item Id.
     * First check if exists, if not, insert it.
     * The function get a string and separate into many words and store each of them.
     *
     * @param integer $moduleId The module ID to store.
     * @param integer $itemId   The item ID to store.
     * @param array   $wordsId  Array with wordsId.
     *
     * @return void
     */
    public function indexWords($moduleId, $itemId, $wordsId)
    {
        foreach ($wordsId as $wordId) {
            $this->_save($moduleId, $itemId, $wordId);
        }
    }

    /**
     * Delete all the entries for one object.
     *
     * @param integer $moduleId The module ID to delete.
     * @param integer $itemId   The item ID to delete.
     *
     * @return array Array with word IDs.
     */
    public function deleteWords($moduleId, $itemId)
    {
        $ids   = array();
        $where = sprintf('module_id = %d AND item_id =  %d', (int) $moduleId, (int) $itemId);

        $result = $this->fetchAll($where);
        foreach ($result as $data) {
            $ids[] = $data->word_id;
        }
        $this->delete($where);

        return $ids;
    }

    /**
     * Get all the modules-item with the wordId.
     *
     * @param array   $words    Array with words IDs.
     * @param string  $operator Query operator.
     * @param integer $count    Limit query.
     *
     * @return array Array of results.
     */
    public function searchModuleByWordId($words, $operator = 'AND', $count = 0)
    {
        $ids    = array();
        $result = array();
        $rights = new Phprojekt_Item_Rights();
        $userId = Phprojekt_Auth::getUserId();
        $db     = Phprojekt::getInstance()->getDb();

        foreach ($words as $content) {
            $ids[] = (int) $content['id'];
        }

        if (!empty($ids)) {
            // Search by AND
            if ($operator == 'AND') {
                $sqlString = '';
                $selects = array();
                $first   = true;

                while (!empty($ids)) {
                    $id = array_pop($ids);
                    if ($first) {
                        $first     = false;
                        if (!empty($ids)) {
                            $selects[] = $db->select()
                                            ->from('search_word_module', array('item_id'))
                                            ->where('word_id = ' . (int) $id);
                        } else {
                            $selects[] = $db->select()
                                            ->from('search_word_module')
                                            ->where('word_id = ' . (int) $id);
                        }
                    } else {
                        if (!empty($ids)) {
                            $selects[] = $db->select()
                                            ->from('search_word_module', array('item_id'))
                                            ->where('word_id = ' . (int) $id . ' AND item_id IN (%s)');
                        } else {
                            $selects[] = $db->select()
                                            ->from('search_word_module')
                                            ->where('word_id = ' . (int) $id . ' AND item_id IN (%s)');
                        }
                    }
                }

                $first = true;
                while (!empty($selects)) {
                    $select = array_shift($selects)->__toString();
                    if ($first) {
                        $sqlString = $select;
                        $first     = false;
                    } else {
                        $sqlString = sprintf($select, $sqlString);
                    }
                }

                $stmt      = $db->query($sqlString);
                $tmpResult = $stmt->fetchAll(Zend_Db::FETCH_ASSOC);
            } else {
                // Search By OR
                $where = 'word_id IN (' . implode(', ', $ids) . ')';
                $order = array('module_id ASC', 'item_id DESC');
                $tmpResult = $this->fetchAll($where, $order)->toArray();
            }

            foreach ($tmpResult as $data) {
                // Limit to $count results
                if ((int) $count > 0 && count($result) >= $count) {
                    break;
                }

                $moduleName = Phprojekt_Module::getModuleName($data['module_id']);
                $model = Phprojekt_Loader::getModel($moduleName, $moduleName);

                if ($model) {
                    // Only fetch records with read access
                    $model = $model->find($data['item_id']);
                    if (!empty($model)) {
                        $result[$data['module_id'] . '-' . $data['item_id']] = $data;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Save the new word.
     *
     * This function use the Zend_DB insert.
     *
     * @param integer $moduleId The module ID to store.
     * @param integer $itemId   The item ID to store.
     * @param integer $wordId   The word ID to store.
     *
     * @return void
     */
    private function _save($moduleId, $itemId, $wordId)
    {
        $data['module_id'] = (int) $moduleId;
        $data['item_id']   = (int) $itemId;
        $data['word_id']   = (int) $wordId;
        $this->insert($data);
    }
}
