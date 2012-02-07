<?php
/**
 * Tags class.
 *
 * The class provide the functions for manage tags per user.
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Tags
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
 * Tags class.
 *
 * The class provide the functions for manage tags per user.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Tags
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_Tags
{
    /**
     * Singleton instance.
     *
     * @var Phprojekt_Tags_Users
     */
    protected static $_instance = null;

    /**
     * Phprojekt_Tags_Tags class.
     *
     * @var Phprojekt_Tags_Tags
     */
    protected $_tags = null;

    /**
     * Phprojekt_Tags_UserModule class.
     *
     * @var Phprojekt_Tags_Modules
     */
    protected $_tagsModules = null;

    /**
     * Phprojekt_Tags_Users class.
     *
     * @var Phprojekt_Tags_Users
     */
    protected $_tagsUsers = null;

    /**
     * Class for return the display data of the items.
     *
     * @var Phprojekt_Search_Display
     */
    protected $_display = null;

    /**
     * Return this class only one time.
     *
     * @return Phprojekt_Tags_Users An instance of Phprojekt_Tags_Users.
     */
    public static function getInstance()
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor.
     *
     * @return void
     */
    private function __construct()
    {
        $this->_tags        = new Phprojekt_Tags_Tags();
        $this->_tagsModules = new Phprojekt_Tags_Modules();
        $this->_tagsUsers   = new Phprojekt_Tags_Users();
    }

    /**
     * Define the clone function for prevent the same point to same object.
     *
     * @return void
     */
    public function __clone()
    {
        $this->_tags        = new Phprojekt_Tags_Tags();
        $this->_tagsModules = new Phprojekt_Tags_Modules();
        $this->_tagsUsers   = new Phprojekt_Tags_Users();
    }

    /**
     * Index a string.
     *
     * Index all the strings.
     *
     * @param integer $moduleId The module ID to store.
     * @param integer $itemId   The item ID.
     * @param string  $data     Strings to save separated by spaces/coma.
     *
     * @return void
     */
    public function saveTags($moduleId, $itemId, $data)
    {
        $this->_index($moduleId, $itemId, $data);
    }

    /**
     * Get all the tags for the current user
     * and return and array sorted by the number of ocurrences first and tag name second.
     *
     * If the $limit is set, the returned array is limited to the $limit tags.
     *
     * @param integer $limit The number of tags for return, 0 for all.
     *
     * @return array Array with results.
     */
    public function getTags($limit = 0)
    {
        $foundResults = array();

        $select = Phprojekt::getInstance()->getDb()->select();
        $select->from(array('t' => 'tags'), array('string' => 'word', 'count' => 'COUNT(word)'))
            ->join(array('tu' => 'tags_users'), 'tu.tag_id = t.id', array())
            ->join(array('tm' => 'tags_modules'), 'tm.tag_user_id = tu.id', array())
            ->where('tu.user_id = ?', Phprojekt_Auth::getUserId())
            ->group('t.word')
            ->order('count DESC')
            ->order('string ASC');
        if ($limit !== 0) {
            if (!is_int($limit)) {
                throw new Exception('$limit must be an integer!');
            }
            $select->limit($limit);
        }
        return $select->query()->fetchAll(Zend_Db::FETCH_ASSOC);
    }

    /**
     * Get all the modules for the current user that are tagged with $tag.
     *
     * If the $limit is set, the returned array is limited to the $limit tags.
     *
     * @param string  $tag   The tag for search.
     * @param integer $limit The number of modules for return, 0 for all.
     *
     * @return array Array with results.
     */
    public function getModulesByTag($tag, $limit = 0)
    {
        $foundResults = array();
        $results      = array();
        $rights       = new Phprojekt_Item_Rights();
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

                // Convert result to array and add the display data
                // only fetch records with read access
                $display        = new Phprojekt_Search_Display();
                $dataForDisplay = array();
                foreach ($foundResults as $result) {
                    if ($rights->getItemRight($result['moduleId'], $result['itemId'], $userId) > 0) {
                        $dataForDisplay[$result['moduleId']][] = $result['itemId'];
                    }
                }

                $results = $display->getDisplay($dataForDisplay);
            }
        }

        return $results;
    }

    /**
     * Get all tags that have a moduleId-itemId.
     *
     * If the $limit is set, the returned array is limited to the $limit tags.
     *
     * @param integer $moduleId The module ID to store.
     * @param integer $itemId   The item ID.
     * @param integer $limit    The number of modules for return, 0 for all.
     *
     * @return array Array with results.
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
     * Delete the entries for the moduleId-itemId.
     *
     * @param integer $moduleId The module ID to delete.
     * @param integer $itemId   The item ID.
     *
     * @return void
     */
    public function deleteTagsByItem($moduleId, $itemId)
    {
        $this->_tagsModules->deleteRelationsByItem($moduleId, $itemId);
    }

    /**
     * Delete the tags for the user.
     *
     * @param integer $itemId The item ID.
     *
     * @return void
     */
    public function deleteTagsByUser($userId)
    {
        // Get all the user-tags relations
        $tagUserRelations = $this->_tagsUsers->getUserTagIds($userId);

        $this->_tagsModules->deleteRelationsByUser($tagUserRelations);
        $this->_tagsUsers->deleteUserTags($userId);
    }

    /**
     * Index a string with the moduleId and the itemId.
     *
     * The function get a string and separate into many words and store each of them.
     *
     * @param integer $moduleId The module ID to store.
     * @param integer $itemId   The item ID.
     * @param string  $data     String to save.
     *
     * @return void
     */
    private function _index($moduleId, $itemId, $data)
    {
        $array = $this->_stringToArray($data);

        // Found all the relations moduleId-itemId <-> userId-tagId
        $oldTagUserRelations = $this->getRelationIdByModule($moduleId, $itemId);

        // Delete the entries for the moduleId-itemId <-> userId
        $this->_tagsModules->deleteRelations($moduleId, $itemId, $oldTagUserRelations);

        foreach ($array as $word) {
            // Save the tag
            $tagId = $this->_tags->saveTags($word);

            // Save the tag-user relation
            $tagUserId = $this->_tagsUsers->saveTags($tagId);

            // Save the tag-user-moduleId relation
            $this->_tagsModules->saveTags($moduleId, $itemId, $tagUserId);
        }
    }

    /**
     * Get all the relations moduleId-itemId that are for the current user.
     *
     * @param integer $moduleId The module ID to get.
     * @param integer $itemId   The item ID.
     *
     * @return array Array with tag-user Ids.
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
     * Return all the words accepted for index into an array.
     *
     * @param string $string The string to store.
     *
     * @return array Array of words.
     */
    private function _stringToArray($string)
    {
        // Clean up the string
        $string = Phprojekt_Converter_String::cleanupString($string);
        // Split the string into an array
        $tempArray = explode(" ", $string);
        // Strip off short or long words
        $tempArray = array_filter($tempArray, array("Phprojekt_Converter_String", "stripLengthWords"));

        return $tempArray;
    }

    /**
     * Return the field definiton for tags.
     *
     * @return array Array with the tags fields.
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
