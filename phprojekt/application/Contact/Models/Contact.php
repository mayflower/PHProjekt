<?php
/**
 * Contact model class.
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
 * Contact model class.
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
     * Configuration to use or not the history class.
     *
     * @var boolean
     */
    public $useHistory = true;

    /**
     * Configuration to use or not the search class.
     *
     * @var boolean
     */
    public $useSearch = false;

    /**
     * Configuration to use or not the right class.
     *
     * @var boolean
     */
    public $useRights = false;

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
     * Only allow save if the contact is public or the ownerId is the current user.
     *
     * @return boolean True for a sucessful save.
     */
    public function save()
    {
        $result = true;
        if (!$this->private || ($this->private && $this->ownerId == Phprojekt_Auth::getUserId())) {
            $result = parent::save();
        }

        return $result;
    }


    /**
     * Only allow delete if the contact is public or the ownerId is the current user.
     *
     * @return void
     */
    public function delete()
    {
        if (!$this->private || ($this->private && $this->ownerId == Phprojekt_Auth::getUserId())) {
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
}
