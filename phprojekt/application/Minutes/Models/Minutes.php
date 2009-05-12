<?php
/**
 * Minutes model class
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
 * @package    PHProjekt
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @link       http://www.phprojekt.com
 * @author     Sven Rautenberg <sven.rautenberg@mayflower.de>
 * @since      File available since Release 6.0
 */

/**
 * Minutes model class
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @package    PHProjekt
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Sven Rautenberg <sven.rautenberg@mayflower.de>
 */
class Minutes_Models_Minutes extends Phprojekt_Item_Abstract
{
    /**
     * Relation to Items
     * 
     * @var array hasMany
     */
    public $hasMany = array('items' => array('classname' => 'Minutes_Models_MinutesItem'));
    
    /**
     * Customized version to calculate the status of a minutes item regardless of its saved database entry.
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
        $result = parent::fetchAll($where, $order, $count, $offset, $select, $join);
        return array_map(array($this, '_calcStatus'), $result);
    }

    /**
     * Function to calculate status based on other item properties
     *
     * @todo enter calculations based on spec
     *
     * @param Phproject_Item_Abstract Item to do status calculations with
     *
     * @return Phproject_Item_Abstract
     */
    protected function _calcStatus(Phprojekt_Item_Abstract &$minutes)
    {
        $meetingDate = new Zend_Date($minutes->meetingDate);
        $now = new Zend_Date();
        
        $status = 0;
        if ($meetingDate->isLater($now)) {
            $status = 1;
        } else {
            $status = 2;
            
            $count = count($minutes->items->fetchAll());
            
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

    /**
     * Customized save, forces status field to be zero - is calculated on loading
     *
     * @return void
     */
    public function save()
    {
        // Force status=0 if not status==4 (FINAL)
        if (4 != $this->itemStatus) {
            $this->itemStatus = 0;
        }
        return parent::save();
    }
}
