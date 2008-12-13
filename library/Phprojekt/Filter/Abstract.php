<?php
/**
 * Prefiltering
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
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @subpackage Core
 * @version    $Id$
 * @link       http://www.phprojekt.com
 * @author     David Soria Parra <david.soria_parra@mayflower.de>
 * @since      File available since Release 6.0
 */

/**
 * Filter provide a easy, chainable way to do pre filtering for databases.
 * It does the pre filtering by modifing a select statement.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     David Soria Parra <david.soria_parra@mayflower.de>
 */
abstract class Phprojekt_Filter_Abstract
{
    /**
     * The string to ues as an identifier
     * for the UserModuleSetting database
     *
     */
    const MODULESETTINGS_IDENTIFIER = 'Filter';

    /**
     * The next filter in chain
     *
     * @var Phprojekt_Filter_UserFilter
     */
    protected $_next = null;

    /**
     * Database adapter. This is needed to quote columns.
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_adapter = null;

    /**
     * Initialize a new filter using the db adapter to
     * quote values and identifiers.
     *
     * @param Zend_Db_Adapter_Abstract $adapter Db adapter for quoting
     */
    public function __construct(Zend_Db_Adapter_Abstract $adapter)
    {
         $this->_adapter = $adapter;
    }

    /**
     * Adds a filter to the chain. Every filter in the chain is applied
     * if the filter() method is called.
     *
     * @param Phprojekt_Filter_Interface $filter filter to be added
     *
     * @return void
     */
    public function addFilter($filter)
    {
        if (null == $this->_next) {
            $this->_next = $filter;
        }

        $this->_next->addFilter($filter);
    }

    /**
     * Saves the current filter chain to backing store, aka database
     * (so why is it called "backing store", and not "database" ;-) )
     *
     * @param Phprojekt_User_User $user     User for whom this filter is saved
     * @param integer             $moduleId Module id for this filter chain
     *
     * @return boolean
     */
    public function saveToBackingStore($user, $moduleId = 1)
    {
        $record = null;
        $pairs  = array();
        $entry  = $this;

        while (null !== $entry) {
            $pairs = $entry->_getBackingStorePair();

            if (false === array_key_exists('key', $pairs)
             || false === array_key_exists('value', $pairs)) {
                throw Exception('No valid backing store pair given');
            }

            $record             = $user->settings->create();
            $record->moduleId   = $moduleId;
            $record->keyValue   = $pairs['key'];
            $record->value      = $pairs['value'];
            $record->identifier = self::MODULESETTINGS_IDENTIFIER;
            $record->save();

            $entry = $entry->_next;
        }
    }

    /**
     * Filters a select
     *
     * @param Zend_Db_Select $select Db select statement to be filter
     *
     * @return void
     */
    abstract public function filter(Zend_Db_Select $select);

    /**
     * Backing store pair to safe to database
     *
     * @return array
     */
    abstract protected function _getBackingStorePair();
}
