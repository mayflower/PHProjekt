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
class Phprojekt_Search_Default
{
    /**
     * Class for manage the words
     *
     * @var Phprojekt_Search_Words
     */
    protected $_words = null;

    /**
     * Class for manage the words in files
     *
     * @var Phprojekt_Search_Words
     */
    protected $_files = null;

    /**
     * Class for return the display data of the items
     *
     * @var Phprojekt_Search_Display
     */
    protected $_display = null;

    /**
     * Chaneg the tablename for use with the Zend db class
     *
     * This function is only for PHProjekt6
     *
     * @param array $config The config array for the database
     */
    public function __construct()
    {
        $this->_words   = new Phprojekt_Search_Words();
        $this->_files   = new Phprojekt_Search_Files();
        $this->_display = new Phprojekt_Search_Display();
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
        $moduleId = Phprojekt_Module::getId($object->getTableName(), $object->projectId);
        $itemId   = $object->id;

        $this->_words->deleteWords($moduleId, $itemId);

        $data = $this->_getObjectDataToIndex($object);

        $this->_display->saveDisplay($object, $moduleId, $itemId);

        foreach ($data as $key => $value) {
            $type = $object->getInformation()->find($key);
            if (isset($type->formType) && $type->formType == 'file') {
                $value = $this->_files->getWordsFromFile($value);
            }
            $this->_words->indexWords($moduleId, $itemId, $value);
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
        $moduleId = Phprojekt_Module::getId($object->getTableName(), $object->projectId);
        $itemId   = $object->id;

        $this->_words->deleteWords($moduleId, $itemId);
        $this->_display->deleteDisplay($moduleId, $itemId);
    }

    /**
     * Do the search itself
     * The operator work like: and => the item must contain all the words.
     *                         or  => the item can contain any word.
     * Only the items with readAccess are returned
     *
     * @param string $words    Some words separated by space
     * @param string $operator Operator AND/OR
     *
     * @uses:
     *      $db = Zend_Registry::get('db');
     *      $search = new Phprojekt_Search_Words(array('db' => $db));
     *      $search->search('text1 text2 text3','OR');
     *
     * @return array
     */
    public function search($words, $operator = 'AND')
    {
        $rights = new Phprojekt_Item_Rights();

        $result = $this->_words->searchWords($words, $operator);

        // Convert result to array and add the display data
        // only fetch records with read access
        $foundResults = array();

        $authNamespace = new Zend_Session_Namespace('PHProjekt_Auth');
        $userId        = $authNamespace->userId;

        foreach ($result as $tmp => $data) {
            if ($rights->hasRight($data['moduleId'], $data['itemId'], $userId, 'readAccess')) {
                $foundResults[] = $this->_display->getDisplay($data['moduleId'], $data['itemId']);
            }
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
}