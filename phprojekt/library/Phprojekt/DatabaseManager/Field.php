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
 * Represent a field in an active record and hold additional information from the DatabaseManager.
 */
class Phprojekt_DatabaseManager_Field
{
    /**
     * Class contain the db information.
     *
     * @var Phprojekt_DatabaseManager
     */
    protected $_metadata;

    /**
     * Field value
     *
     * @var mix
     */
    public $value;

    /**
     * Initialise a new object.
     *
     * @param Phprojekt_DatabaseManager $dbm   DatabaseManager Object.
     * @param string                    $name  Name of the field.
     * @param mixed                     $value Value of the field.
     *
     * @return void
     */
    public function __construct(Phprojekt_DatabaseManager $dbm, $name, $value = null)
    {
        $this->value     = (string) $value;
        $this->_metadata = $dbm->find($name);
    }

    /**
     * Get a value.
     *
     * @param string $name Name of the field.
     *
     * @return mix Value of the var.
     */
    public function __get($name)
    {
        $name = Phprojekt_ActiveRecord_Abstract::convertVarToSql($name);
        if (!is_null($this->_metadata) && isset($this->_metadata->$name)) {
            return $this->_metadata->$name;
        }

        return null;
    }

    /**
     * Function to print this class.
     *
     * @return string Class in a print format.
     */
    public function __toString()
    {
        return $this->value;
    }
}
