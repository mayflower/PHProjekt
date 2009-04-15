<?php
/**
 * Minutes Item model class
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
class Minutes_Models_MinutesItem extends Phprojekt_ActiveRecord_Abstract implements Phprojekt_Model_Interface
{
    /**
     * The Minutes object this item is related to
     * 
     * @var Phprojekt_Item_Abstract 
     */
    protected $_minutes;
    
    /**
     * The Id of the minutes this Item belongs to
     * 
     * @var integer
     */
    protected $_minutesId; 
    
    /**
     * The standard information manager with hardcoded
     * field definitions
     *
     * @var Phprojekt_ModelInformation_Interface
     */
    protected $_informationManager;

    /**
     * Validate object
     *
     * @var Phprojekt_Model_Validate
     */
    protected $_validate = null;
        
    /**
     * Initialize new minutes item
     *
     * @param array $db Configuration for Zend_Db_Table
     *
     * @return void
     */
    public function __construct($db = null)
    {
        if (null === $db) {
            $db = Phprojekt::getInstance()->getDb();
        }
        parent::__construct($db);

        $this->_validate           = Phprojekt_Loader::getLibraryClass('Phprojekt_Model_Validate');
        $this->_informationManager = Phprojekt_Loader::getModel('Minutes', 'MinutesItemInformation');
    }

    /**
     * Define the clone function for prevent the same point to same object.
     *
     * @return void
     */
    public function __clone()
    {
        parent::__clone();
        $this->_validate           = Phprojekt_Loader::getLibraryClass('Phprojekt_Model_Validate');
        $this->_informationManager = Phprojekt_Loader::getModel('Minutes', 'MinutesItemInformation');
    }
    
    /**
     * Return the information manager
     * 
     * @return Phprojekt_ModelInformation_Interface
     */
    public function getInformation()
    {
        return $this->_informationManager;
    }
    
    /**
     * Validate the current record
     *
     * @return boolean
     */
    public function recordValidate()
    {
        $data   = $this->_data;
        $fields = $this->_informationManager->getFieldDefinition(Phprojekt_ModelInformation_Default::ORDERING_FORM);
        
        return $this->_validate->recordValidate($this, $data, $fields);
    }

    /**
     * Get the rights.
     *
     * @return array
     */
    public function getRights()
    {
        // The rights of the related minutes also apply to its items.
        if (is_null($this->_minutesId)) {
            Phprojekt::getInstance()->getLog()->debug('Minutes not initialized when checking rights');
        } else {
            $rights = $this->_minutes->_data;//getRights();
            Phprojekt::getInstance()->getLog()->debug('Minutes rights: '.print_r($rights, true));
        }
        return $this->_minutes->getRights();
    }
    
    /**
     * Initialize the related minutes object
     * 
     * @param integer $minutesId
     * @return void
     */
    public function init($minutesId = null)
    {
        $this->_minutes   = Phprojekt_Loader::getModel('Minutes', 'Minutes');
        $this->_minutesId = $minutesId;
        return $this;
    }

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
     * 
     * @todo mock function 
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null, $select = null, $join = null)
    {
        $minutes = $this->_minutes->find($this->_minutesId);
        //$result = $foobar->fetchAll($where, $order, $count, $offset, $select, $join);
        if (null !== $where) {
            $where .= ' AND ';
        }
        $where .= sprintf('(%s.minutesId = %d )', $this->getTableName(), $this->_minutesId);
        $result = parent::fetchAll($where, $order, $count, $offset, $select, $join);
        
        return $result;
    }
    

    /**
     * @todo mock function
     *
     * @return void
     */
    public function save()
    {
        //Phprojekt::getInstance()->getLog()->debug(print_r($this, TRUE));
        return parent::save();
    }
}
