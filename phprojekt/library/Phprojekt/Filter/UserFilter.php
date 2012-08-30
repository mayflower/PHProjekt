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
 * Filter by for columns that is defined by a user.
 *
 * Represents a where user where clause filter and provides
 * furthermore chaining abilities from the abstract.
 */
class Phprojekt_Filter_UserFilter extends Phprojekt_Filter_Abstract
{
    /**
     * Holds the actual identifier.
     *
     * @var string
     */
    protected $_identifier = null;

    /**
     * Holds the actual value.
     *
     * @var mixed
     */
    protected $_value = null;

    /**
     * Initialize a new user filter on an active record.
     * It uses the table name and the database adapter from the Active Record.
     *
     * @param Phprojekt_ActiveRecord_Abstract $record     An active record.
     * @param string                          $identifier The identifier usually the column to filter.
     * @param mixed                           $value      The value to filter.
     *
     * @return void
     */
    public function __construct(Phprojekt_ActiveRecord_Abstract $record, $identifier, $value)
    {
        $info = $record->info();
        $cols = $info['cols'];

        $identifier = Phprojekt_ActiveRecord_Abstract::convertVarToSql($identifier);
        if (!in_array($identifier, $cols)) {
            throw new InvalidArgumentException('Identifier not found');
        }

        $this->_identifier = $identifier;
        $this->_value      = $value;
        parent::__construct($record->getAdapter());
    }

    /**
     * Set the value for which we are filtering.
     *
     * @param mixed $value The value to filter for.
     *
     * @return void
     */
    public function setValue($value)
    {
        $this->_value = $value;
    }

    /**
     * Filters a select using a simple where clause.
     * This might get more complex in upcoming versions.
     * After running the filter() method you can easily run the database query with the modified query.
     *
     * @param Zend_Db_Select $select The select to update.
     *
     * @return void
     */
    public function filter(Zend_Db_Select $select, $tableName = null)
    {
        $db = $this->_adapter;
        if (null !== $tableName) {
            $query = sprintf("%s.%s = ?", $db->quoteIdentifier($tableName),
                $db->quoteIdentifier($this->_identifier));
        } else {
            $query = sprintf("%s = ?", $db->quoteIdentifier($this->_identifier));
        }

        $select->where($query, $this->_value);
    }

    /**
     * Backing store pair to safe to database.
     *
     * @return array Array with 'key' and 'value'.
     */
    protected function _getBackingStorePair()
    {
        return array('key'   => $this->_identifier,
                     'value' => $this->_value);
    }
}
