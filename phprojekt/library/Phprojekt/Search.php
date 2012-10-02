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
 * Search class.
 *
 * The class provide the functions for make a full text search.
 */
class Phprojekt_Search
{
    /**
     * Class for manage the words.
     *
     * @var Phprojekt_Search_Words
     */
    protected $_words = null;

    /**
     * Class for manage the words-module Relation.
     *
     * @var Phprojekt_Search_WordModule
     */
    protected $_wordModule = null;

    /**
     * Class for manage the words in files.
     *
     * @var Phprojekt_Search_Words
     */
    protected $_files = null;

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
        $this->_words      = new Phprojekt_Search_Words();
        $this->_wordModule = new Phprojekt_Search_WordModule();
        $this->_files      = new Phprojekt_Search_Files();
        $this->_display    = new Phprojekt_Search_Display();
    }

    /**
     * Define the clone function for prevent the same point to same object.
     *
     * @return void
     */
    public function __clone()
    {
        $this->_words      = new Phprojekt_Search_Words();
        $this->_wordModule = new Phprojekt_Search_WordModule();
        $this->_files      = new Phprojekt_Search_Files();
        $this->_display    = new Phprojekt_Search_Display();
    }

    /**
     * Index a object.
     *
     * First delete all the entries for this object for delete the unused strings.
     * Then get all the fields and values to index.
     * Then index each of one.
     *
     * @param Phprojekt_Item_Abstract $object The item object.
     *
     * @return void
     */
    public function indexObjectItem($object)
    {
        $words    = array();
        $moduleId = Phprojekt_Module::getId($object->getModelName());
        $itemId   = $object->id;

        $wordsId = $this->_wordModule->deleteWords($moduleId, $itemId);
        $this->_words->decreaseWords($wordsId);

        $data = $this->_getObjectDataToIndex($object);

        $this->_display->saveDisplay($object, $moduleId, $itemId);

        foreach ($data as $key => $value) {
            $type = $object->getInformation()->getType($key);
            if (null !== $type) {
                if ($type == 'upload') {
                    $field = array('type' => $type,
                                   'key'  => $key);
                    $files = Phprojekt_Converter_Text::convert($object, $field);
                    $value = $this->_files->getWordsFromFile($files);
                }

                if ($type != 'hidden') {
                    $words[] = $value;
                }
            }
        }

        $wordsId = $this->_words->indexWords(implode(" ", $words));
        $this->_wordModule->indexWords($moduleId, $itemId, $wordsId);
    }

    /**
     * Delete all the entries for one object.
     *
     * @param Phprojekt_Item_Abstract $object The item object.
     *
     * @return void
     */
    public function deleteObjectItem($object)
    {
        $moduleId = Phprojekt_Module::getId($object->getModelName());
        $itemId   = $object->id;

        $this->deleteObjectItemByIds($moduleId, $itemId);
    }

    public function deleteObjectItemByIds($moduleId, $itemId)
    {
        $wordsId = $this->_wordModule->deleteWords($moduleId, $itemId);
        $this->_words->decreaseWords($wordsId);
        $this->_display->deleteDisplay($moduleId, $itemId);
    }

    /**
     * Do the search itself.
     * Only the items with readAccess are returned.
     *
     * @param string  $words Some words separated by space
     * @param integer $count Limit query.
     *
     * @uses
     *      $search = new Phprojekt_Search();
     *      $search->search('text1 text2 text3', 10);
     *
     * @return array Array with results.
     */
    public function search($words, $count = null)
    {
        if (strstr($words, " ")) {
            $wordOperator     = 'equal';
            $wordCount        = 0;
            $relationOperator = 'AND';
        } else {
            $wordOperator     = 'like';
            $wordCount        = $count;
            $relationOperator = 'OR';
        }

        $result          = $this->_words->searchWords($words, $wordOperator, $wordCount);
        $tmpFoundResults = $this->_wordModule->searchModuleByWordId($result, $relationOperator, $count);
        $dataForDisplay  = array();

        // Limit the number of ocurrences per module to 3
        if ($count > 0) {
            $results = array();
            foreach ($tmpFoundResults as $moduleData) {
                if (!isset($results[$moduleData['module_id']])) {
                    $results[$moduleData['module_id']] = 0;
                }
                $results[$moduleData['module_id']]++;
                if ($results[$moduleData['module_id']] <= 3) {
                    $dataForDisplay[$moduleData['module_id']][] = $moduleData['item_id'];
                }
            }
        } else {
            // Convert result to array and add the display data
            foreach ($tmpFoundResults as $moduleData) {
                $dataForDisplay[$moduleData['module_id']][] = $moduleData['item_id'];
            }
        }

        return $this->_display->getDisplay($dataForDisplay);
    }

    /**
     * Get all the string values from the Object.
     *
     * Allow only text field (varchar, text, tinytext and longtext).
     *
     * @param Phprojekt_Item_Abstract $object The item object.
     *
     * @return array Array with allowed values.
     */
    private function _getObjectDataToIndex($object)
    {
        $allow    = array();
        $allow[]  = 'varchar';
        $allow[]  = 'text';
        $allow[]  = 'tinytext';
        $allow[]  = 'longtext';
        $data     = array();
        $metaData = $object->_metadata;

        foreach ($metaData as $field => $fieldInfo) {
            if (in_array($fieldInfo['DATA_TYPE'], $allow)) {
                $field        = Phprojekt_ActiveRecord_Abstract::convertVarFromSql($field);
                $data[$field] = $object->$field;
            }
        }

        return $data;
    }
}
