<?php
/**
 * Class for manage the words on the Search
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
 * The class provide the functions for save/delete/search the words in the
 * SearchWords table
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
class Phprojekt_Search_Words extends Zend_Db_Table_Abstract
{
    /**
     * Name of the table
     *
     * @var string
     */
    protected $_name = 'search_words';

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
     * Index a string
     * First check if exists, if not, insert it.
     * Keep and update the number of ocurrences of each word
     * The function get a string and separate into many words
     * And store each of them.
     *
     * @param string $data String to save
     *
     * @return array() Array with wordIds
     */
    public function indexWords($data)
    {
        $words = $this->_getWordsFromText($data);
        $ids   = $this->_save($words);

        return $ids;
    }

    /**
     * Do the search looking for the words
     * The operator work like: and => the item must contain all the words.
     *                         or  => the item can contain any word.
     *
     * @param string  $words    Some words separated by space
     * @param integer $count    Limit query
     * @param integer $offset   Query offset
     *
     * @return array
     */
    public function searchWords($words, $count = null, $offset = null)
    {
        $words = $this->_getWordsFromText($words);
        $where = array();

        foreach ($words as $word) {
            $where[] = '(word LIKE '. $this->getAdapter()->quote('%'.$word.'%').')';
        }
        $where = implode('OR', $where);

        return $this->fetchAll($where, 'count DESC', $count, $offset)->toArray();
    }

    /**
     * Save or update the new word
     *
     * This function use the Zend_DB insert/update
     *
     * @param array $words Array with the words string
     *
     * @return array
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
            $where  = array('word IN ('. implode(', ', $quotedWords) .')');
            $result = $this->fetchAll($where);
            foreach ($result as $row) {
                $data  = array('count' => $row->count + 1);
                $where = $this->getAdapter()->quoteInto('id = ?', (int) $row->id);
                $this->update($data, $where);
                $foundWords[] = $row->word;
                $ids[]        = $row->id;
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
     * Decrease the ocurrences of the word
     *
     * This function use the Zend_DB update
     *
     * @param array $words Array with wordId
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
            $where  = array('id IN ('. implode(', ', $ids) .')');
            $result = $this->fetchAll($where);
            foreach ($result as $row) {
                $where = array('id = '. (int) $row->id);
                if ($row->count == 1) {
                    $this->delete($where);
                } else {
                    $data = array('count' => $row->count - 1);
                    $this->update($data, $where);
                }
            }
        }
    }

    /**
     * Get all the words into an array
     *
     * @param string $string The string to store
     *
     * @return array
     */
    private function _getWordsFromText($string)
    {
        return $this->_stringToArray($string);
    }

    /**
     * Return all the words accepted for index into an array
     *
     * @param string $string The string to store
     *
     * @return array
     */
    private function _stringToArray($string)
    {
        // Clean up the string
        $string = $this->_cleanupstring($string);
        // Split the string into an array
        $tempArray = preg_split("/[\s,_!:\.\-\/\+@\(\)\? ]+/", $string);
        // Strip off short words
        $tempArray = array_filter($tempArray, array($this, "_stripShortsWords"));
        // Strip off stop words
        $tempArray = array_filter($tempArray, array($this, "_stripStops"));
        // Remove duplicate entries
        $tempArray = array_unique($tempArray);

        return $tempArray;
    }

    /**
     * Clean Up a string for search or index
     *
     * @param string $string The string for cleanup
     *
     * @return string
     */
    private function _cleanupString($string)
    {
        // Clean up HTML
        $string = utf8_decode($string);
        $string = preg_replace('#\W+#msiU', ' ', strtolower(strtr(strip_tags($string),
                               array_flip(get_html_translation_table(HTML_ENTITIES)))));
        // Translate bad
        $search = array ("'&(quot|#34);'i", "'&(amp|#38);'i", "'&(lt|#60);'i",
                         "'&(gt|#62);'i", "'&(nbsp|#160);'i",
                         "'&(iexcl|#161);'i", "'&(cent|#162);'i", "'&(pound|#163);'i",
                         "'&(copy|#169);'i", "'&(ldquo|bdquo);'i",
                         "'&auml;'", "'&Auml;'",
                         "'&euml;'", "'&Euml;'",
                         "'&iuml;'", "'&Iuml;'",
                         "'&ouml;'", "'&Ouml;'",
                         "'&uuml;'", "'&Uuml;'",
                         "'&szlig;'", "'\''", "'\"'", "'\('", "'\)'");
        $replace = array (" ", " ", " ", " ", " ",
                          " ", " ", " ", " ", " ",
                          chr(228), chr(196),
                          chr(235), chr(203),
                          chr(239), chr(207),
                          chr(246), chr(214),
                          chr(252), chr(220),
                          chr(223), " ", " ", " ", " ");
        $string = preg_replace($search, $replace, strip_tags($string));
        $string = utf8_encode($string);

        return $string;
    }

    /**
     * Remove the short words from the index
     *
     * @param array $string String to check
     *
     * @return boolean
     */
    private function _stripShortsWords($string)
    {
        return (strlen($string) > 2);
    }

    /**
     * Remove the StopWords from the index
     * using the stopwords.txt file
     *
     * @param array $string String to check
     *
     * @return boolean
     */
    private function _stripStops($string)
    {
        $searchStopWords = array();

        $file = PHPR_CORE_PATH . DIRECTORY_SEPARATOR
                . 'Phprojekt' . DIRECTORY_SEPARATOR
                . 'stopwords.txt';

        if (file_exists($file)) {
            $tmp             = file($file);
            $searchStopWords = array();
            if (!empty($tmp[0])) {
                $searchStopWords = explode(" ", $tmp[0]);
            }
        }

        return (!in_array(strtoupper($string), $searchStopWords));
    }
}
