<?php
/**
 * Class for manage the words on the Search
 *
 * LICENSE: Licensed under the terms of the PHProjekt 6 License
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    CVS: $Id:
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 */

/**
 * The class provide the functions for save/delete/search the words in the
 * SearchWords table
 *
 * @copyright  2007 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @subpackage Core
 * @license    http://phprojekt.com/license PHProjekt 6 License
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_Search_Words extends Zend_Db_Table_Abstract
{
    /**
     * Name of the table
     *
     * @var string
     */
    protected $_name = 'SearchWords';

    /**
     * Chaneg the tablename for use with the Zend db class
     *
     * This function is only for PHProjekt6
     *
     * @param array $config The config array for the database
     */
    public function __construct()
    {
        $config = array('db' => Zend_Registry::get('db'));

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
     * @param string  $data     String to save
     *
     * @return void
     */
    public function indexWords($moduleId, $itemId, $data)
    {
        $array = $this->_getWordsFromText($data);
        foreach ($array as $word) {
            if (!$this->_exists($moduleId, $itemId, $word)) {
                $this->_save($moduleId, $itemId, $word);
            }
        }
    }

    /**
     * Delete all the entries for one object
     *
     * @param integer $moduleId The moduleId to delete
     * @param integer $itemId   The item Id to delete
     *
     * @return void
     */
    public function deleteWords($moduleId, $itemId)
    {
        $where = array();
        $clone = clone($this);

        $where[] = 'moduleId = '. $clone->getAdapter()->quote($moduleId);
        $where[] = 'itemId = '. $clone->getAdapter()->quote($itemId);
        $clone->delete($where);
    }

    /**
     * Do the search looking for the words
     * The operator work like: and => the item must contain all the words.
     *                         or  => the item can contain any word.
     *
     * @param string $words    Some words separated by space
     * @param string $operator Operator AND/OR
     *
     * @return array
     */
    public function searchWords($words, $operator)
    {
        $result = array();
        $words = $this->_getWordsFromText($words);
        foreach ($words as $word) {
            $where     = array();
            $where[]   = 'word LIKE '. $this->getAdapter()->quote('%'.$word.'%');
            $tmpResult = $this->fetchAll($where)->toArray();

            foreach ($tmpResult as $tmp => $values) {
                unset($tmpResult[$tmp]['word']);
            }

            if (empty($result)) {
                $result = $tmpResult;
            } else {
                switch ($operator) {
                    default:
                    case 'AND':
                        foreach ($result as $tmp => $values) {
                            $found = false;
                            foreach ($tmpResult as $data) {
                                if (($data['moduleId'] == $values['moduleId']) &&
                                    ($data['itemId']   == $values['itemId'])) {
                                    $found = true;
                                    break;
                                }
                            }
                            if (!$found) {
                                unset($result[$tmp]);
                            }
                        }
                        break;
                    case 'OR':
                        foreach ($tmpResult as $tmp => $data) {
                            $found = false;
                            foreach ($result as $values) {
                                if (($data['moduleId'] == $values['moduleId']) &&
                                    ($data['itemId']   == $values['itemId'])) {
                                    $found = true;
                                    break;
                                }
                            }
                            if (!$found) {
                                $result[] = $data;
                            }
                        }
                        break;
                }
            }
        }

        return $result;
    }

    /**
     * Check if the moduleId-item-word pair was already inserted
     *
     * @param integer $moduleId The moduleId to store
     * @param integer $itemId   The item Id to store
     * @param integer $word     The word
     *
     * @return boolean
     */
    private function _exists($moduleId, $itemId, $word)
    {
        return ($this->find($moduleId, $itemId, $word)->count() > 0);
    }

    /**
     * Save the new word
     *
     * This function use the Zend_DB insert
     *
     * @param integer $moduleId The moduleId to store
     * @param integer $itemId   The item Id to store
     * @param string  $word     The word itself
     *
     * @return void
     */
    private function _save($moduleId, $itemId, $word)
    {
        $data['moduleId'] = $moduleId;
        $data['itemId']   = $itemId;
        $data['word']     = $word;
        $this->insert($data);
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
    private function _stringToArray($string) {
        // Clean up the string
        $string = $this->_cleanupstring($string);
        // Split the string into an array
        $tempArray = preg_split("/[\s,_!:\.\-\/\+@\(\)\? ]+/", $string);
        // strip off short words
        $tempArray = array_filter($tempArray, array($this, "_stripShortsWords"));
        // strip off stop words
        $tempArray = array_filter($tempArray, array($this, "_stripStops"));
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
        $string = preg_replace('#\W+#msiU', ' ', strtoupper(strtr(strip_tags($string), array_flip(get_html_translation_table(HTML_ENTITIES)))));
        // Translate bad
        $search = array ("'&(quot|#34);'i", "'&(amp|#38);'i", "'&(lt|#60);'i", "'&(gt|#62);'i", "'&(nbsp|#160);'i",
                         "'&(iexcl|#161);'i", "'&(cent|#162);'i", "'&(pound|#163);'i", "'&(copy|#169);'i", "'&(ldquo|bdquo);'i",
                         "'&auml;'", "'&ouml;'", "'&uuml;'", "'&Auml;'", "'&Ouml;'",
                         "'&Uuml;'", "'&szlig;'", "'\''", "'\"'", "'\('", "'\)'");
        $replace = array (" ", " ", " ", " ", " ",
                          " ", " ", " ", " ", " ",
                          "�", "�", "�", "�", "�",
                          "�", "�",  " ", " ", " ", " ");
        $string = preg_replace($search, $replace, strip_tags($string));
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
        return(strlen($string) > 2);
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
            $_temp = file($file);
            $searchStopWords = array();
            if (!empty($_temp[0])) {
                $searchStopWords = explode(" ", $_temp[0]);
            }
        }
        return(!in_array(strtoupper($string), $searchStopWords));
    }
}