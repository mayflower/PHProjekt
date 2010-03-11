<?php
/**
 * A generic interface to interact with models.
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
 * @package    Phprojekt
 * @subpackage Model
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Eduardo Polidor <polidor@mayflower.de>
 */

/**
 * The model interface describes the smallest set of methods that must be provided by a model.
 * All core components that donnot deal with a specific interface should use this interface
 * to interact with an object.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Model
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Eduardo Polidor <polidor@mayflower.de>
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
     * Validate the data of the current record.
     *
     * @return boolean True for valid.
     */
    public function recordValidate();

    /**
     * Gets the rights of the item for the current user.
     *
     * @return array Array of rights per user.
     */
    public function getRights();

    /**
     * Gets the rights of various items for the current user
     *
     * @param array $ids Array with various item IDs.
     *
     * @return array Array of rights per user.
     */
    public function getMultipleRights($ids);

    /**
     * Gets the rights of the item for other users.
     *
     * @return array Array of rights per user.
     */
    public function getUsersRights();
}
