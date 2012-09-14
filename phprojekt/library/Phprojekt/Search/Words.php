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
 * Class for manage the words on the Search.
 *
 * The class provide the functions for save/delete/search the words in the SearchWords table.
 */
class Phprojekt_Search_Words extends Zend_Db_Table_Abstract
{
    /**
     * Name of the table.
     *
     * @var string
     */
    protected $_name = 'search_words';

    /**
     * Stopwords that should not be indexed.
     *
     * @var array
     */
    protected $_stopWords = array();

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $config = array('db' => Phprojekt::getInstance()->getDb());

        $file = Phprojekt::getInstance()->getConfig()->searchStopwordList;

        if (file_exists($file)) {
            $tmp              = file_get_contents($file);
            $this->_stopWords = $this->_stringToArray($tmp);
        }

        parent::__construct($config);
    }

    /**
     * Index a string.
     * First check if exists, if not, insert it.
     * Keep and update the number of ocurrences of each word.
     * The function get a string and separate into many words and store each of them.
     *
     * @param string $data String to save.
     *
     * @return array Array with word IDs.
     */
    public function indexWords($data)
    {
        $words = $this->_stringToArray($data);
        $ids   = $this->_save($words);

        return $ids;
    }

    /**
     * Do the search looking for the words.
     * The operator work like: equal => look for the exact words.
     *                         like  => look for words that contain the word.
     *
     * @param string  $words    Some words separated by space.
     * @param string  $operator Query operator.
     * @param integer $count    Limit query.
     *
     * @return array Array with resutls.
     */
    public function searchWords($words, $operator = 'equal', $count = null)
    {
        $words = $this->_stringToArray($words);

        if (empty($words)) {
            return array();
        } else {
            $where = array();

            foreach ($words as $word) {
                if ($operator == 'like') {
                    $where[] = '(word LIKE ' . $this->getAdapter()->quote('%' . $word . '%') . ')';
                } else {
                    $where[] = '(word = ' . $this->getAdapter()->quote($word) . ')';
                }
            }
            $where = implode('OR', $where);

            return $this->fetchAll($where, 'count DESC', $count)->toArray();
        }
    }

    /**
     * Save or update the new word.
     *
     * This function use the Zend_DB insert/update.
     *
     * @param array $words Array with the words string.
     *
     * @return array Array with word IDs.
     */
    private function _save($words)
    {
        $ids         = array();
        $foundWords  = array();
        $quotedWords = array();

        foreach ($words as $word) {
            $quotedWords[] = $this->getAdapter()->quote($word);
        }

        if (!empty($quotedWords)) {
            $where  = 'word IN ('. implode(', ', $quotedWords) .')';
            $result = $this->fetchAll($where);
            foreach ($result as $row) {
                $foundWords[] = $row->word;
                $ids[]        = $row->id;
            }
            if (!empty($ids)) {
                $data = array('count' => new Zend_Db_Expr($this->_db->quoteIdentifier('count') . ' + 1'));
                $this->update($data, array($this->_db->quoteIdentifier('id') . ' IN (' . implode(',', $ids) . ')'));
            }
        }

        foreach ($words as $word) {
            if (!in_array($word, $foundWords)) {
                $data          = array();
                $data['word']  = $word;
                $data['count'] = 1;
                $ids[]         = $this->insert($data);
            }
        }

        return $ids;
    }

   /**
     * Decrease the ocurrences of the word.
     *
     * This function use the Zend_DB update.
     *
     * @param array $words Array with word IDs.
     *
     * @return void
     */
    public function decreaseWords($words)
    {
        $ids = array();
        foreach ($words as $id) {
            $ids[] = (int) $id;
        }

        if (!empty($ids)) {
            $where  = 'id IN ('. implode(', ', $ids) .')';
            $result = $this->fetchAll($where);
            $deleteIds = array();
            $updateIds = array();
            foreach ($result as $row) {
                if ($row->count == 1) {
                    $deleteIds[] = (int) $row->id;
                } else {
                    $updateIds[] = (int) $row->id;
                }
            }
            if (!empty($deleteIds)) {
                $this->delete(array($this->_db->quoteIdentifier('id') . ' IN (' . implode(',', $deleteIds) . ')'));
            }
            if (!empty($updateIds)) {
                $data = array('count' => new Zend_Db_Expr($this->_db->quoteIdentifier('count') . ' - 1'));
                $this->update($data, array($this->_db->quoteIdentifier('id') . ' IN (' . implode(',', $updateIds) . ')'));
            }
        }
    }

    /**
     * Return all the words accepted for index into an array.
     *
     * @param string $string The string to store.
     *
     * @return array Array with words.
     */
    private function _stringToArray($string)
    {
        // Clean up the string
        $string = Phprojekt_Converter_String::cleanupString($string);
        // Split the string into an array
        $tempArray = explode(" ", $string);
        // Strip off short or long words
        $tempArray = array_filter($tempArray, array("Phprojekt_Converter_String", "stripLengthWords"));
        // Strip off stop words
        if (!empty($this->_stopWords)) {
            $tempArray = array_filter($tempArray, array($this, "_stripStops"));
        }
        // Remove duplicate entries
        $tempArray = array_unique($tempArray);

        return $tempArray;
    }

    /**
     * Remove the StopWords from the index using the stopwords.txt file.
     *
     * @param array $string String to check.
     *
     * @return boolean True for valid string.
     */
    private function _stripStops($string)
    {
        return (!in_array($string, $this->_stopWords));
    }
}
