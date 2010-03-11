<?php
/**
 * Default model class.
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
 * @subpackage Default
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
 * Default model class.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Default
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Default_Models_Default implements Phprojekt_Model_Interface
{
    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Information about the fields.
     *
     * @see Phprojekt_Item_Abstract
     *
     * @return void
     */
    public function getInformation()
    {
    }

    /**
     * Empty iterator implementation as a model must be iteratable.
     *
     * @see Iterator::next()
     *
     * @return void
     */
    public function next()
    {
    }

    /**
     * Empty iterator implementation as a model must be iteratable.
     *
     * @see Iterator::rewind()
     *
     * @return void
     */
    public function rewind()
    {
    }

    /**
     * Empty iterator implementation as a model must be iteratable.
     *
     * @see Iterator::current()
     *
     * @return void
     */
    public function current()
    {
    }

    /**
     * Empty iterator implementation as a model must be iteratable.
     *
     * @see Iterator::valid()
     *
     * @return void
     */
    public function valid()
    {
        return false;
    }

    /**
     * Empty iterator implementation as a model must be iteratable.
     *
     * @see Iterator::key()
     *
     * @return void
     */
    public function key ()
    {
    }

    /**
     * Default fetchall - needs to be implemented.
     *
     * @return void
     */
    public function fetchAll()
    {
    }

    /**
     * Default find - needs to be implemented.
     *
     * @return void
     */
    public function find()
    {
    }

    /**
     * Some magic. The index controller code always has an instance of
     * an model. If no other model is specified, the index controller uses
     * this default model. As the index controller expect the model to be an
     * active record and this default model cannot be used as an active record,
     * as no database table exists for this model, all the calls to the
     * active record provided methods will fail.
     * To avoid this, we just suck all the calls and don't spit warnings.
     *
     * @return boolean False.
     */
    public function save()
    {
        return false;
    }

    /**
     * Gets the rights of the item for the current user.
     *
     * @return array Empty array,
     */
    public function getRights()
    {
        return array();
    }

    /**
     * Gets the rights of various items for the current user.
     *
     * @param array $ids Array with various item IDs.
     *
     * @return array Empty array per ID.
     */
    public function getMultipleRights($ids)
    {
        $return = array();
        foreach ($ids as $ids) {
            $return[$id] = array();
        }

        return $return;
    }

    /**
     * Gets the rights of the item for other users.
     *
     * @return array Empty array.
     */
    public function getUsersRights()
    {
        return array();
    }

    /**
     * Validate the data of the current record.
     *
     * @return boolean Always true.
     */
    public function recordValidate()
    {
        return true;
    }

    /**
     * Get an array with all the fields for make the filter select.
     *
     * @return array Empty array.
     */
    public function getFieldsForFilter()
    {
        return array();
    }
}
