<?php
/**
 * Class for manage the words-module relation
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
 * @version    $Id$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * The class provide the functions for save/delete/search
 * the words - module relation in the SearchWordsModule table
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @subpackage Core
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_Search_WordModule extends Zend_Db_Table_Abstract
{
    /**
     * Name of the table
     *
     * @var string
     */
    protected $_name = 'search_word_module';

    /**
     * Chaneg the tablename for use with the Zend db class
     *
     * This function is only for PHProjekt6
     *
     * @param array $config The config array for the database
     */
    public function __construct()
    {
        $config = array('db' => Phprojekt::getInstance()->getDb());

        parent::__construct($config);
    }

    /**
     * Index a string with the moduleId and the item Id
     * First check if exists, if not, insert it.
     * The function get a string and separate into many words
     * And store each of them.
     *
     * @param integer $moduleId The moduleId to store
     * @param integer $itemId   The item Id to store
     * @param array   $wordsId  Array with wordsId
     *
     * @return void
     */
    public function indexWords($moduleId, $itemId, $wordsId)
    {
        foreach ($wordsId as $wordId) {
            if (!$this->_exists($moduleId, $itemId, $wordId)) {
                $this->_save($moduleId, $itemId, $wordId);
            }
        }
    }

    /**
     * Delete all the entries for one object
     *
     * @param integer $moduleId The moduleId to delete
     * @param integer $itemId   The item Id to delete
     *
     * @return array WordIds
     */
    public function deleteWords($moduleId, $itemId)
    {
        $where   = array();
        $ids     = array();
        $where[] = 'module_id = '. $this->getAdapter()->quote($moduleId);
        $where[] = 'item_id = '. $this->getAdapter()->quote($itemId);
        $result = $this->fetchAll($where);
        foreach ($result as $data) {
            $ids[] = $data->word_id;
        }
        $clone = clone($this);
        $clone->delete($where);
        return $ids;
    }

    /**
     * Get all the modules-item with the wordId
     *
     * @param integer $wordId  Word Id
     * @param integer $count   Limit query
     * @param integer $offset  Query offset
     *
     * @return array
     */
    public function searchModuleByWordId($wordId, $count = null, $offset = null)
    {
        $where   = array();
        $where[] = 'word_id = '. $this->getAdapter()->quote($wordId);
        return $this->fetchAll($where, 'item_id DESC', $count, $offset)->toArray();
    }

    /**
     * Check if the moduleId-itemId-wordId pair was already inserted
     *
     * @param integer $moduleId The moduleId to store
     * @param integer $itemId   The item Id to store
     * @param integer $wordId   The wordId
     *
     * @return boolean
     */
    private function _exists($moduleId, $itemId, $wordId)
    {
        return ($this->find($moduleId, $itemId, $wordId)->count() > 0);
    }

    /**
     * Save the new word
     *
     * This function use the Zend_DB insert
     *
     * @param integer $moduleId The moduleId to store
     * @param integer $itemId   The item Id to store
     * @param integer $wordId   The word Id to store
     *
     * @return void
     */
    private function _save($moduleId, $itemId, $wordId)
    {
        $data['module_id'] = $moduleId;
        $data['item_id']   = $itemId;
        $data['word_id']   = $wordId;
        $this->insert($data);
    }
}
