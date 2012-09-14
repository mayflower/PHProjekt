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
 * Contact model class.
 */
class Contact_Models_Contact extends Phprojekt_Item_Abstract
{
    /**
     * Field for display in the search results.
     *
     * @var string
     */
    public $searchFirstDisplayField = 'name';

    /**
     * Field for display in the search results.
     *
     * @var string
     */
    public $searchSecondDisplayField = 'company';

    /**
     * Rewrites parent fetchAll, only public records are shown.
     *
     * @param string|array $where  Where clause.
     * @param string|array $order  Order by.
     * @param string|array $count  Limit query.
     * @param string|array $offset Query offset.
     * @param string       $select The comma-separated columns of the joined columns.
     * @param string       $join   The join statements.
     *
     * @return Zend_Db_Table_Rowset The rowset with the results.
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
     * Validate the data of the current record.
     *
     * @return boolean True for valid.
     */
    public function recordValidate()
    {
        return true;
    }

    /**
     * Extension of saveRights() for don't save the rights.
     *
     * @param array $rights Array of user IDs with the bitmask access.
     *
     * @return void
     */
    public function saveRights($rights)
    {
    }

    /**
     * Extension of save() for don't save the search strings.
     * Only allow save if the contact is public or the ownerId is the current user.
     *
     * @return void
     */
    public function save()
    {
        $result = true;

        if ($this->ownerId == Phprojekt_Auth_Proxy::getEffectiveUserId()) {
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
     * Extension of delete() for don't save the search strings.
     * Only allow delete if the contact is public or the ownerId is the current user.
     *
     * @return void
     */
    public function delete()
    {
        if ($this->ownerId == Phprojekt_Auth_Proxy::getEffectiveUserId()) {
            $this->deleteUploadFiles();
            $this->_history->saveFields($this, 'delete');
            parent::delete();
        }
    }

    /**
     * Return the data range for a select.
     *
     * @param Phprojekt_ModelInformation_Interface $field The field description.
     *
     * @return array Array with 'id' and 'name'.
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

    /**
     * Overwrite hasRight to fit contact's own rights system
     */
    public function hasRight($userId, $right, $projectId = null)
    {
        if (Phprojekt_Auth::isAdminUser() || $this->ownerId == Phprojekt_Auth_Proxy::getEffectiveUserId()) {
            return true;
        } else {
            return ($right === Phprojekt_Acl::READ);
        }
    }
}
