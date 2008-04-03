<?php
/**
 * Tags class
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
 * The class provide the functions for manage tags per user
 * All the words are converted to crc32 for search it
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
class Phprojekt_Tags_Default
{
    /**
     * Singleton instance
     *
     * @var Phprojekt_Tags_Users
     */
    protected static $_instance = null;

    /**
     * Phprojekt_Tags_Tags class
     *
     * @var Phprojekt_Tags_Tags
     */
    protected $_tags = null;

    /**
     * Phprojekt_Tags_UserModule class
     *
     * @var Phprojekt_Tags_Modules
     */
    protected $_tagsModule = null;

    /**
     * Phprojekt_Tags_Users class
     *
     * @var Phprojekt_Tags_Users
     */
    protected $_tagsUsers = null;

    /**
     * Return this class only one time
     *
     * @return Phprojekt_Tags_Users
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Chaneg the tablename for use with the Zend db class
     *
     * This function is only for PHProjekt6
     *
     * @param array $config The config array for the database
     */
    private function __construct()
    {
        $this->_tags        = new Phprojekt_Tags_Tags();
        $this->_tagsModules = new Phprojekt_Tags_Modules();
        $this->_tagsUsers   = new Phprojekt_Tags_Users();
    }

    /**
     * Index a string
     *
     * Index all the strings.
     *
     * @param string  $module The module to store
     * @param integer $itemId The item ID
     * @param string  $data   Strings to save separated by spaces/coma
     *
     * @return void
     */
    public function saveTags($module, $itemId, $data)
    {
        $this->_index($module, $itemId, $data);
    }

    /**
     * Get all the tags for the current user
     * and return and array sorted by the number of ocurrences
     *
     * If the $limit is set,
     * the returned array is limited to the $limit tags
     *
     * @param integer $limit The number of tags for return, 0 for all
     *
     * @return array
     */
    public function getTags($limit = 0)
    {
        $foundResults = array();

        // Get all the user-tags relations
        $tmpResults = $this->_tagsUsers->getUserTagIds();

        // Convert result to array per tags
        // And count the number of occurrences
        foreach ($tmpResults as $tagUserId => $tagId) {
            $tagName = $this->_tags->getTagName($tagId);

            $modules = $this->_tagsModules->getModulesByRelationId($tagUserId);
            foreach ($modules as $moduleData) {
                if (!isset($foundResults[$tagName])) {
                    $foundResults[$tagName] = 0;
                }
                $foundResults[$tagName]++;
            }
        }

        // Sort by the number of occureences
        arsort($foundResults);

        // Return the $limit tags
        if ($limit > 0) {
            $foundResults = array_slice($foundResults, 0, $limit);
        }

        return $foundResults;
    }

    /**
     * Get all the modules for the current user that are tagged with $tag
     *
     * If the $limit is set,
     * the returned array is limited to the $limit tags
     *
     * @param string  $tag   The tag for search
     * @param integer $limit The number of modules for return, 0 for all
     *
     * @return array
     */
    public function getModulesByTag($tag, $limit = 0)
    {
        $foundResults = array();

        if (!empty($tag)) {
            // Find the tag
            $tagId = $this->_tags->getTagId($tag);

            if ($tagId > 0) {
                // Get The user-tags relations
                $tagUserId = $this->_tagsUsers->getUserTagIds($tagId);

                // Get The modules data
                $foundResults = $this->_tagsModules->getModulesByRelationId($tagUserId);

                // Return the $limit tags
                if ($limit > 0) {
                    $foundResults = array_slice($foundResults, 0, $limit);
                }
            }
        }

        return $foundResults;
    }

    /**
     * Get all tags that have a module-itemId
     *
     * If the $limit is set,
     * the returned array is limited to the $limit tags
     *
     * @param string  $module The module to store
     * @param integer $itemId The item ID
     * @param integer $limit  The number of modules for return, 0 for all
     *
     * @return array
     */
    public function getTagsByModule($module, $itemId, $limit = 0)
    {
        $foundResults = array();

        // Found all the relations module-itemId <-> userId
        $tagUserRelations = $this->getRelationIdByModule($module, $itemId);

        foreach ($tagUserRelations as $tagUserId) {
            // Find the tagid
            $tagId = $this->_tagsUsers->getTagId($tagUserId);

            // Find the tagName
            $tagName = $this->_tags->getTagName($tagId);

            $foundResults[] = $tagName;
        }

        // Return the $limit tags
        if ($limit > 0) {
            $foundResults = array_slice($foundResults, 0, $limit);
        }

        return $foundResults;
    }

    /**
     * Index a string with the module and the item ID
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

        // Found all the relations module-itemId <-> userId-tagId
        $oldTagUserRelations = $this->getRelationIdByModule($module, $itemId);

        // Delete the entries for the module-itemId <-> userId
        $this->_tagsModules->_delete($module, $itemId, $oldTagUserRelations);

        foreach ($array as $word) {
            $crc32 = crc32($word);
            // Save the tag
            $tagId = $this->_tags->saveTags($crc32, $word);

            // Save the tag-user relation
            $tagUserId = $this->_tagsUsers->saveTags($tagId);

            // Save the tag-user-module relation
            $this->_tagsModules->saveTags($module, $itemId, $tagUserId);
        }
    }

    /**
     * Get all the relations module-item that are for the current user
     *
     * @param string  $module The module to get
     * @param integer $itemId The item ID
     *
     * @return array
     */
    public function getRelationIdByModule($module, $itemId)
    {
        // Found all the relations module-itemId <-> userId-tagId
        $moduleUserTagRelation = $this->_tagsModules->getRelationIdByModule($module, $itemId);

        // Select only the relation with the current user
        $tagUserRelations = array();
        foreach ($moduleUserTagRelation as $tagUserId) {
            if ($this->_tagsUsers->isFromUser($tagUserId)) {
                $tagUserRelations[] = $tagUserId;
            }
        }

        return $tagUserRelations;
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
        $string = preg_replace('#\W+#msiU', ' ', strtolower(strtr(strip_tags($string), array_flip(get_html_translation_table(HTML_ENTITIES)))));
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
}