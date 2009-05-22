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
            $this->_save($moduleId, $itemId, $wordId);
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
        return $this->fetchAll('word_id = '. (int) $wordId, 'item_id DESC', $count, $offset)->toArray();
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
        $data['module_id'] = (int) $moduleId;
        $data['item_id']   = (int) $itemId;
        $data['word_id']   = (int) $wordId;
        $this->insert($data);
    }
}
