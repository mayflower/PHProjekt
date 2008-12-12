<?php
/**
 * Filter by for columns that is defined by a user
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
 * @version    CVS: $Id:
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     David Soria Parra <david.soria_parra@mayflower.de>
 */

/**
 * Represents a where user where clause filter and provides
 * furthermore chaining abilities from the abstract.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     David Soria Parra <david.soria_parra@mayflower.de>
 */
class Phprojekt_Filter_UserFilter extends Phprojekt_Filter_Abstract
{
    /**
     * Holds the actual identifier
     *
     * @var string
     */
    protected $_identifier = null;

    /**
     * Holds the actual value
     *
     * @var mixed
     */
    protected $_value = null;

    /**
     * Initialize a new user filter on an active record. It uses the
     * table name and the database adapter from the Active Record.
     *
     * @param Phprojekt_ActiveRecord_Abstract $record     An active record
     * @param string                          $identifier The identifier usually the column to filter
     * @param mixed                           $value      The value to filter
     */
    public function __construct(Phprojekt_ActiveRecord_Abstract $record, $identifier, $value)
    {
        $info = $record->info();
        $cols = $info['cols'];

        if (array_key_exists($identifier, $cols)) {
            throw new Exception('Identifier not found');
        }

        $this->_identifier = $identifier;
        $this->_value      = $value;
        parent::__construct($record->getAdapter());
    }

    /**
     * Set the value for which we are filtering
     *
     * @param mixed $value The value to filter for
     *
     * @return void
     */
    public function setValue($value)
    {
        $this->_value = $value;
    }

    /**
     * Filters a select using a simple where clause. This might
     * get more complex in upcoming versions. After running the filter()
     * method you can easily run the database query with the modified query.
     *
     * @param Zend_Db_Select $select The select to update
     *
     * @return void
     */
    public function filter(Zend_Db_Select $select)
    {
        $select->where(sprintf('%s = %s',
                    $this->_adapter->quote($this->_identifier),
                    $this->_adapter->quote($this->_value)));

        if ($this->_next) {
            $this->_next->filter($select);
        }
    }

    /**
     * Backing store pair to safe to database
     *
     * @return array
     */
    protected function _getBackingStorePair()
    {
        return array('key'   => $this->_identifier,
                     'value' => $this->_value);
    }
}
