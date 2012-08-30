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
 * The model interface describes the smallest set of methods that must be provided by a model.
 * All core components that donnot deal with a specific interface should use this interface
 * to interact with an object.
 */
interface Phprojekt_Model_Interface extends Iterator
{
    /**
     * Returns an object that implements the model information interface
     * and that provides detailed information about the fields and their types.
     * For database objects implementing Phprojekt_Item this
     * ModelInformation implementation is usually the DatabaseManager.
     *
     * @return Phprojekt_ModelInformation_Interface An instance of Phprojekt_ModelInformation_Interface.
     */
    public function getInformation();

    /**
     * Find a dataset, usually by an id. If the record is found
     * the current object is filled with the data and returns itself.
     *
     * @return Phprojekt_ModelInformation_Interface An instance of Phprojekt_ModelInformation_Interface.
     */
    public function find();

    /**
     * Fetch a set of records. Depending on the implementation it might be possible to limit the fetch by
     * e.g. providing a where clause.
     * A model _neednot_ to implement a limiting mechanism.
     *
     * @return array
     */
    public function fetchAll();

    /**
     * Save the current object to the backend.
     *
     * @return boolean True on sucessful save.
     */
    public function save();

    /**
     * Checks if the model has a specified field.
     *
     * @param string $field Name of the field.
     *
     * @return boolean Whether is exists.
     */
    public function hasField($field);
}
