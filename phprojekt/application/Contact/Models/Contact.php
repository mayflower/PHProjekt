<?php
/**
 * Contact model class
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
 * @package    Application
 * @subpackage Contact
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
 * Contact model class
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Contact
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Contact_Models_Contact extends Phprojekt_Item_Abstract
{
    /**
     * Field for display in the search results
     *
     * @var string
     */
    public $searchFirstDisplayField = 'name';

    /**
     * Field for display in the search results
     *
     * @var string
     */
    public $searchSecondDisplayField = 'company';

    /**
     * Rewrites parent fetchAll
     * only public records are shown
     *
     * @param string|array $where  Where clause
     * @param string|array $order  Order by
     * @param string|array $count  Limit query
     * @param string|array $offset Query offset
     * @param string       $select The comma-separated columns of the joined columns
     * @param string       $join   The join statements
     *
     * @return Zend_Db_Table_Rowset
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null, $select = null, $join = null)
    {
        // Set where
        if (null !== $where) {
            $where .= ' AND ';
        }

        $where .= sprintf('(owner_id = %d OR private = 0)', (int) Phprojekt_Auth::getUserId());

        return Phprojekt_ActiveRecord_Abstract::fetchAll($where, $order, $count, $offset, $select, $join);
    }

    /**
     * Validate the data of the current record
     *
     * @return boolean
     */
    public function recordValidate()
    {
        // One is the unique value available because is a global module
        if (Phprojekt_Module::getSaveType(Phprojekt_Module::getId($this->getModelName())) >= 1) {
            $this->projectId = 1;
        }

        return true;
    }

    /**
     * Save the rigths for the current item
     * The users are a POST array with userIds
     *
     * @param array $rights - Array of usersId with the bitmask access
     *
     * @return void
     */
    public function saveRights($rights)
    {
    }

    /**
     * Extension of the Abstract Record to don't save the search strings
     * Only allow save if the contact is public or the ownerId is the current user
     *
     * @return void
     */
    public function save()
    {
        $result = true;

        if (!$this->private || ($this->private && $this->ownerId == Phprojekt_Auth::getUserId())) {
            if ($this->id > 0) {
                $this->_history->saveFields($this, 'edit');
                $result = Phprojekt_ActiveRecord_Abstract::save();
            } else {
                $result = Phprojekt_ActiveRecord_Abstract::save();
                $this->_history->saveFields($this, 'add');
            }
        }

        return $result;
    }


    /**
     * Extension of the Abstract Record to delete an item
     * Only allow delete if the contact is public or the ownerId is the current user
     *
     * @return void
     */
    public function delete()
    {
        if (!$this->private || ($this->private && $this->ownerId == Phprojekt_Auth::getUserId())) {
            $this->deleteUploadFiles();
            $this->_history->saveFields($this, 'delete');
            parent::delete();
        }
    }

    /**
     * Return the data range for a select
     *
     * @param Phprojekt_ModelInformation_Interface $field the field description
     *
     * @return an array with key and value to be used as datarange
     */
    public function getRangeFromModel($field)
    {
        if (!$field->isRequired) {
            $options[] = array('id'   => 0,
                               'name' => '');
        }

        $result = $this->fetchAll("private = 0");
        foreach ($result as $item) {
            $options[] = array('id'   => $item->id,
                               'name' => $item->name);
        }
        return $options;
    }
}
