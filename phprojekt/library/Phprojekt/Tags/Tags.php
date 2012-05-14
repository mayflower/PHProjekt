<?php
/**
 * Tags class.
 *
 * The class provide the functions for manage all the tags
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
 * The class provide the functions for manage all the tags
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
class Phprojekt_Tags_Tags extends Zend_Db_Table_Abstract
{
    /**
     * Table name.
     *
     * @var string
     */
    protected $_name = 'tags';

    /**
     * Construct.
     *
     * @return void
     */
    public function __construct()
    {
        $config = array('db' => Phprojekt::getInstance()->getDb());
        parent::__construct($config);
    }

    /**
     * Save the new word.
     *
     * This function use the Zend_DB insert.
     * First check if the pair don´t exist.
     *
     * @param string  $word  The word itself.
     *
     * @return integer ID of the tag.
     */
    public function saveTags($word)
    {
        $id    = 0;
        $where = $this->getAdapter()->quoteInto('word  = ?', $word);

        $record = $this->fetchAll($where);
        if ($record->count() == 0) {
            $data['word']  = $word;
            $id = $this->insert($data);
        } else {
            $records = current((array) $record);
            $record  = array_shift($records);
            $id      = $record['id'];
        }

        return $id;
    }

    /**
     * Find the ID of one tag.
     *
     * @param string $word The word for search.
     *
     * @return integer ID of the tag.
     */
    public function getTagId($word)
    {
        $where = $this->getAdapter()->quoteInto('word = ?', $word);

        $record = $this->fetchAll($where);
        if ($record->count() > 0) {
            $records = current((array) $record);
            $record  = array_shift($records);
            return $record['id'];
        }

        return 0;
    }

    /**
     * Find the name of one tagId.
     *
     * @param integer $tagId The tag ID for search.
     *
     * @return string Word.
     */
    public function getTagName($tagId)
    {
        $records = current($this->find($tagId));
        $record  = array_shift($records);

        return $record['word'];
    }
}
