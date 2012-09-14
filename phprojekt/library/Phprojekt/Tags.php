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
 * Tags class.
 *
 * The class provide the functions for manage tags per user.
 */
class Phprojekt_Tags
{
    /**
     * Phprojekt_Tags_TagsTableMapper class.
     *
     * @var Phprojekt_Tags_Modules
     */
    protected $_tagsTableMapper = null;

    /**
     * Class for return the display data of the items.
     *
     * @var Phprojekt_Search_Display
     */
    protected $_display = null;

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->_tagsTableMapper = new Phprojekt_Tags_TagsTableMapper();
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
        $tags = $this->_tagsTableMapper
            ->getTagsForModuleItem($moduleId, $itemId, $limit);
        return $tags;
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
        $this->_tagsTableMapper->deleteTagsForModuleItem($moduleId, $itemId);
    }

    public function search($words, $limit = 0)
    {
        if ($words != "") {
            $rights = new Phprojekt_Item_Rights();
            $display = new Phprojekt_Search_Display();
            $results = $this->_tagsTableMapper->searchForProjectsWithTags(
                explode(" ", $words),
                $limit
            );

            $allowedModules = array();
            foreach ($results as $moduleId => $itemIds) {
                $allowedIds = array();
                $moduleName = Phprojekt_Module::getModuleName($moduleId);
                foreach ($itemIds as $itemId) {
                    $model = Phprojekt_Loader::getModel($moduleName, $moduleName);
                    if ($model) {
                        $model = $model->find($itemId);
                        if (!empty($model)) {
                            $allowedIds[] = $itemId;
                        }
                    }
                }

                if (count($allowedIds) > 0) {
                    $allowedModules[$moduleId] = $allowedIds;
                }
            }
            return $display->getDisplay($allowedModules);
        } else {
            return array();
        }
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
        $tags = $this->_stringToArray($data);
        $this->_tagsTableMapper->saveTagsForModuleItem(
            $moduleId, $itemId, $tags
        );
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

        return array_values($tempArray);
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
                          'label' => Phprojekt::getInstance()->translate('Tags'));
        $fields[] = array('key'   => 'count',
                          'label' => Phprojekt::getInstance()->translate('Count'));
        return $fields;
    }
}
