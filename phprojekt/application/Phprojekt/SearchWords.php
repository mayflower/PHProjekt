<?php
/**
 * Search class
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
 * The class provide the functions for make a full text search
 * Index all the items and then search it by the crc32 converted word
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
class Phprojekt_SearchWords extends Zend_Db_Table_Abstract
{
	protected $_name = 'SearchWords';
	
    /**
     * Chaneg the tablename for use with the Zend db class
     *
     * This function is only for PHProjekt6
     *
     * @param array $config The config array for the database
     */
    public function __construct($config = array())
    {
        if (null === $config) {
            $config = array('db' => Zend_Registry::get('db'));
        }

        if (!is_array($config)) {
            $config = array('db' => $config);
        }

        if (!array_key_exists('db', $config) ||
            !is_a($config['db'], 'Zend_Db_Adapter_Abstract')) {
            throw new Phprojekt_ActiveRecord_Exception("SearchWords class must "
                                                     . "be initialized using a valid "
                                                     . "Zend_Db_Adapter_Abstract");

        }
        parent::__construct($config);
    }

    /**
     * Index a object
     *
     * First delete all the entries for this object
     * for delete the unused strings
     *
     * Then get all the fields and values to index.
     * Then index each of one.
     *
     * @param Phprojekt_Item_Abstract $object The item object
     *
     * @return void
     */
    public function indexObjectItem($object)
    {
        $module = $object->getTableName();
        $itemId = $object->id;

        $this->_delete($module, $itemId);

        $data = $this->_getObjectDataToIndex($object);
        foreach ($data as $key => $value) {
            $type = $object->getInformation()->find($key);
            if (isset($type->formType) && $type->formType == 'file') {
                $this->_indexFile($module, $itemId, $value);
            } else {
                $this->_index($module, $itemId, $value);
            }
        }
    }

    /**
     * Delete all the entries for one object
     *
     * @param Phprojekt_Item_Abstract $object The item object
     *
     * @return void
     */
    public function deleteObjectItem($object)
    {
        $module = $object->getTableName();
        $itemId = $object->id;

        $this->_delete($module, $itemId);
    }

    /**
     * Do the search itself
     * The operator work like: and => the item must contain all the words.
     *                         or  => the item can contain any word.
     *
     * @param string $words    Some words separated by space
     * @param string $operator Operator AND/OR
     *
     * @uses:
     *      $db = Zend_Registry::get('db');
     *      $search = new Phprojekt_SearchWords(array('db' => $db));
     *      $search->search('text1 text2 text3','OR');
     *
     * @return array
     */
    public function search($words, $operator = 'AND')
    {
        $result = array();
        $words = $this->_getWordsFromText($words);
        foreach ($words as $word) {
            $crc32     = crc32($word);
            $where     = array();
            $where[]   = 'crc32 = '. $this->getAdapter()->quote($crc32);
            $tmpResult = $this->fetchAll($where)->toArray();

            foreach ($tmpResult as $tmp => $values) {
                unset($tmpResult[$tmp]['word']);
                unset($tmpResult[$tmp]['crc32']);
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
                                if (($data['module'] == $values['module']) &&
                                    ($data['itemId'] == $values['itemId'])) {
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
                                if (($data['module'] == $values['module']) &&
                                    ($data['itemId'] == $values['itemId'])) {
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

        // Convert result to array per module
        $foundResults = array();
        foreach ($result as $tmp => $data) {
            $foundResults[$data['module']][] = $data['itemId'];
        }
        return $foundResults;
    }

    /**
     * Get all the string values from the Object
     *
     * Allow only text field (varchar, text, tinytext and longtext)
     *
     * @param Phprojekt_Item_Abstract $object The item object
     *
     * @return array
     */
    private function _getObjectDataToIndex($object)
    {
        $allow = array();
        $allow[] = 'varchar';
        $allow[] = 'text';
        $allow[] = 'tinytext';
        $allow[] = 'longtext';
        $data  = array();

        $metaData = $object->_metadata;
        foreach ($metaData as $field => $fieldInfo) {
            if (in_array($fieldInfo['DATA_TYPE'], $allow)) {
                $data[$field] = $object->$field;
            }
        }
        return $data;
    }

    /**
     * Index a string with the module and the item ID
     * First check if exists, if not, insert it.
     * The function get a string and separate into many words
     * And store each of them.
     *
     * @param string  $module The module to store
     * @param integer $itemId The item ID
     * @param string  $data   String to save
     *
     * @return void
     */
    private function _index($module, $itemId, $data)
    {
        $array = $this->_getWordsFromText($data);
        foreach ($array as $word) {
            $crc32 = crc32($word);
            if (!$this->_exists($module, $itemId, $crc32)) {
                $this->_save($module, $itemId, $crc32, $word);
            }
        }
    }

    /**
     * Index a file
     * First check if exists, if not, insert it.
     * The function get a string and separate into many words
     * And store each of them.
     *
     * @param string  $module The module to store
     * @param integer $itemId The item ID
     * @param string  $file   The name of the file
     *
     * @return void
     */
    private function _indexFile($module, $itemId, $file)
    {
        $array = $this->_getWordsFromFile($file, $this->_getFileType($file));
        foreach ($array as $word) {
            $crc32 = crc32($word);
            if (!$this->_exists($module, $itemId, $crc32)) {
                $this->_save($module, $itemId, $crc32, $word);
            }
        }
    }

    /**
     * Check if the module-item-crc32 pair was already inserted
     *
     * @param string  $module The module to store
     * @param integer $itemId The item ID
     * @param integer $crc32  The crc32 number of the word
     *
     * @return boolean
     */
    private function _exists($module, $itemId, $crc32)
    {
        // $clone = clone($this);
        return ($this->find($module, $itemId, $crc32)->count() > 0);
    }

    /**
     * Get the FileType by its extension
     *
     * @param string $filename The name of the file
     *
     * @return string
     */
    private function _getFileType($filename) {
        return(strtoupper(array_pop(explode(".", $filename))));
    }

    /**
     * Get all the words from a file into an array
     *
     * @param string $file     The name of the file
     * @param string $fileType The filetype
     *
     * @return array
     */
    private function _getWordsFromFile($file, $fileType)
    {
        $string = '';
        $file = PHPR_CORE_PATH . DIRECTORY_SEPARATOR
                . 'Phprojekt' . DIRECTORY_SEPARATOR
                . $file;

        if (is_readable($file)) {
            switch ($fileType) {
                default:
                    $string = implode(' ', file($file));
                    break;
            }
        }

        return $this->_stringToArray($string);
    }

    /**
     * Delete all the entries for one object
     *
     * @param string  $module The module to store
     * @param integer $itemId The item ID
     *
     * @return void
     */
    private function _delete($module, $itemId)
    {
        $where = array();
        $clone = clone($this);

        $where[] = 'module = '. $clone->getAdapter()->quote($module);
        $where[] = 'itemId = '. $clone->getAdapter()->quote($itemId);
        $clone->delete($where);
    }

    /**
     * Save the new word
     *
     * This function use the Zend_DB insert
     *
     * @param string  $module The module to store
     * @param integer $itemId The item ID
     * @param integer $crc32  The crc32 number of the word
     * @param string  $word   The word itself
     *
     * @return void
     */
    private function _save($module, $itemId, $crc32, $word)
    {
        $data['crc32']  = $crc32;
        $data['module'] = $module;
        $data['itemId'] = $itemId;
        $data['word']   = $word;
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
     * @param array $var String to check
     *
     * @return boolean
     */
    private function _stripShortsWords($var)
    {
        return(strlen($var) > 2);
    }

    /**
     * Remove the StopWords from the index
     * using the stopwords.txt file
     *
     * @param array $var String to check
     *
     * @return boolean
     */
    private function _stripStops($var)
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
        return(!in_array(strtoupper($var), $searchStopWords));
    }
}