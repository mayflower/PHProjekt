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
 * Minutes model class.
 */
class Minutes_Models_Minutes extends Phprojekt_Item_Abstract
{
    /**
     * Relation to Items.
     *
     * @var array hasMany
     */
    public $hasMany = array('items'=> array('module'=>'Minutes_SubModules_MinutesItem',
                                            'model' =>'MinutesItem'));


    /**
     * Constructor initializes additional Infomanager.
     *
     * @param array $db Configuration for Zend_Db_Table.
     *
     * @return void
     */
    public function __construct($db = null)
    {
        parent::__construct($db);

        $this->_dbManager = new Minutes_Models_MinutesInformation($this, $db);
    }

    /**
     * Customized version to calculate the status of a minutes item regardless of its saved database entry.
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
        $result = parent::fetchAll($where, $order, $count, $offset, $select, $join);

        return array_map(array($this, '_calcStatus'), $result);
    }

    /**
     * Function to calculate status based on other item properties.
     *
     * @param Phproject_Item_Abstract Item to do status calculations with.
     *
     * @return Phproject_Item_Abstract Current Item.
     */
    protected function _calcStatus(Phprojekt_Item_Abstract &$minutes)
    {
        $meetingDatetime = strtotime($minutes->meetingDatetime);
        $now             = strtotime("now");

        $status = 0;
        if ($meetingDatetime > $now) {
            $status = 1;
        } else {
            $status = 2;
            $count  = count($minutes->items->fetchAll());

            if ($count > 0) {
                $status = 3;
            }
        }

        if (4 != $minutes->itemStatus) {
            $minutes->itemStatus = $status;
        } else {
            $minutes->itemStatus = 4;
        }

        return $minutes;
    }
}
