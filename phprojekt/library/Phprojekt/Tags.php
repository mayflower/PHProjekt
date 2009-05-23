<?php
/**
 * Tags class
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
 * The class provide the functions for manage tags per user
 * All the words are converted to crc32 for search it
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
class Phprojekt_Tags
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
    protected $_tagsModules = null;

    /**
     * Phprojekt_Tags_Users class
     *
     * @var Phprojekt_Tags_Users
     */
    protected $_tagsUsers = null;

    /**
     * Class for return the display data of the items
     *
     * @var Phprojekt_Search_Display
     */
    protected $_display = null;

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
        $this->_tags        = Phprojekt_Loader::getLibraryClass('Phprojekt_Tags_Tags');
        $this->_tagsModules = Phprojekt_Loader::getLibraryClass('Phprojekt_Tags_Modules');
        $this->_tagsUsers   = Phprojekt_Loader::getLibraryClass('Phprojekt_Tags_Users');
    }

    /**
     * Define the clone function for prevent the same point to same object.
     *
     * @return void
     */
    public function __clone()
    {
        $this->_tags        = Phprojekt_Loader::getLibraryClass('Phprojekt_Tags_Tags');
        $this->_tagsModules = Phprojekt_Loader::getLibraryClass('Phprojekt_Tags_Modules');
        $this->_tagsUsers   = Phprojekt_Loader::getLibraryClass('Phprojekt_Tags_Users');
    }

    /**
     * Index a string
     *
     * Index all the strings.
     *
     * @param integer $moduleId The module Id to store
     * @param integer $itemId   The item Id
     * @param string  $data     Strings to save separated by spaces/coma
     *
     * @return void
     */
    public function saveTags($moduleId, $itemId, $data)
    {
        $this->_index($moduleId, $itemId, $data);
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
            if (!isset($foundResults[$tagName])) {
                $foundResults[$tagName] = 0;
            }
            $foundResults[$tagName] = $foundResults[$tagName] + count($modules);

        }

        // Return the $limit tags
        if ($limit > 0) {
            $foundResults = array_slice($foundResults, 0, $limit);
        }

        // Return the formated array
        $tmp = $foundResults;
        $foundResults = array();
        foreach ($tmp as $tagName => $count) {
            if ($count > 0) {
                $foundResults[] = array('string' => $tagName,
                                        'count'  => $count);
            }
        }

        return $foundResults;
    }

    /**
     * Get all the modules for the current user that are tagged with $tag
     *
     * If the $limit is set,
     * the returned array is limited to the $limit tags
     *
     * @param string  $tag       The tag for search
     * @param integer $limit     The number of modules for return, 0 for all
     *
     * @return array
     */
    public function getModulesByTag($tag, $limit = 0)
    {
        $foundResults = array();
        $results      = array();
        $rights       = Phprojekt_Loader::getLibraryClass('Phprojekt_Item_Rights');
        $userId       = Phprojekt_Auth::getUserId();

        if (!empty($tag)) {
            // Find the tag
            $tagId = $this->_tags->getTagId($tag);

            if ($tagId > 0) {
                // Get The user-tags relations
                $tagUserIds = $this->_tagsUsers->getUserTagIds(0, $tagId);

                // Get The modules data
                foreach ($tagUserIds as $tagUserId => $tagId) {
                    $foundResults = array_merge($foundResults, $this->_tagsModules->getModulesByRelationId($tagUserId));
                }

                // Return the $limit tags
                if ($limit > 0) {
                    $foundResults = array_slice($foundResults, 0, $limit);
                }

                $display = Phprojekt_Loader::getLibraryClass('Phprojekt_Search_Display');
                foreach ($foundResults as $result) {
                    if ($rights->getItemRight($result['moduleId'], $result['itemId'], $userId) > 0) {
                        $results[] = $display->getDisplay($result['moduleId'], $result['itemId']);
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Get all tags that have a moduleId-itemId
     *
     * If the $limit is set,
     * the returned array is limited to the $limit tags
     *
     * @param integer $moduleId The module Id to store
     * @param integer $itemId   The item Id
     * @param integer $limit    The number of modules for return, 0 for all
     *
     * @return array
     */
    public function getTagsByModule($moduleId, $itemId, $limit = 0)
    {
        $foundResults = array();

        // Found all the relations moduleId-itemId <-> userId
        $tagUserRelations = $this->getRelationIdByModule($moduleId, $itemId);

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

        // Return the formated array
        $tmp = $foundResults;
        $foundResults = array();
        foreach ($tmp as $tagName) {
            $foundResults[] = array('string' => $tagName,
                                    'count'  => 1);
        }
        return $foundResults;
    }

    /**
     * Delete the entries for the moduleId-itemId
     *
     * @param integer $moduleId The module Id to delete
     * @param integer $itemId   The item Id
     */
    public function deleteTagsByItem($moduleId, $itemId)
    {
        $this->_tagsModules->deleteRelationsByItem($moduleId, $itemId);
    }

    /**
     * Delete the tags for the user
     *
     * @param integer $itemId     The item Id
     */
    public function deleteTagsByUser($userId)
    {
        // Get all the user-tags relations
        $tagUserRelations = $this->_tagsUsers->getUserTagIds($userId);

        $this->_tagsModules->deleteRelationsByUser($tagUserRelations);
        $this->_tagsUsers->deleteUserTags($userId);
    }

    /**
     * Index a string with the moduleId and the itemId
     * The function get a string and separate into many words
     * And store each of them.
     *
     * @param integer $moduleId The module Id to store
     * @param integer $itemId   The item Id
     * @param string  $data     String to save
     *
     * @return void
     */
    private function _index($moduleId, $itemId, $data)
    {
        $array = $this->_getWordsFromText($data);

        // Found all the relations moduleId-itemId <-> userId-tagId
        $oldTagUserRelations = $this->getRelationIdByModule($moduleId, $itemId);

        // Delete the entries for the moduleId-itemId <-> userId
        $this->_tagsModules->deleteRelations($moduleId, $itemId, $oldTagUserRelations);

        foreach ($array as $word) {
            $crc32 = crc32($word);
            // Save the tag
            $tagId = $this->_tags->saveTags($crc32, $word);

            // Save the tag-user relation
            $tagUserId = $this->_tagsUsers->saveTags($tagId);

            // Save the tag-user-moduleId relation
            $this->_tagsModules->saveTags($moduleId, $itemId, $tagUserId);
        }
    }

    /**
     * Get all the relations moduleId-itemId that are for the current user
     *
     * @param integer $moduleId The module Id to get
     * @param integer $itemId   The item Id
     *
     * @return array
     */
    public function getRelationIdByModule($moduleId, $itemId)
    {
        // Found all the relations moduleId-itemId <-> userId-tagId
        $moduleUserTagRelation = $this->_tagsModules->getRelationIdByModule($moduleId, $itemId);

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
    private function _stringToArray($string)
    {
        // Clean up the string
        $string = $this->_cleanupstring($string);
        // Split the string into an array
        $tempArray = preg_split("/[\s,_!:\.\-\/\+@\(\)\? ]+/", $string);
        // Strip off short or long words
        $tempArray = array_filter($tempArray, array($this, "_stripLengthWords"));

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
     * Remove the short or long words from the index
     *
     * @param array $var String to check
     *
     * @return boolean
     */
    private function _stripLengthWords($var)
    {
        return (strlen($var) > 2 && strlen($var) < 256);
    }

    /**
     * Return the field definiton for tags
     *
     * @return array
     */
    public function getFieldDefinition()
    {
        $fields   = array();
        $fields[] = array('key'   => 'string',
                          'label' => Phprojekt::getInstance()->translate('Tag'));
        $fields[] = array('key'   => 'count',
                          'label' => Phprojekt::getInstance()->translate('Count'));
        return $fields;
    }
}
