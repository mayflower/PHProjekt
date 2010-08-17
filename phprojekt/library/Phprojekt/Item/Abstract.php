<?php
/**
 * An item, with database manager support.
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
 * @subpackage Item
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavao Solt <solt@mayflower.de>
 */

/**
 * An item, with database manager support.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Item
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavao Solt <solt@mayflower.de>
 */
abstract class Phprojekt_Item_Abstract extends Phprojekt_ActiveRecord_Abstract implements Phprojekt_Model_Interface
{
    /**
     * Represents the database_manager class.
     *
     * @var Phprojekt_ActiveRecord_Abstract
     */
    protected $_dbManager = null;

    /**
     * Validate object.
     *
     * @var Phprojekt_Model_Validate
     */
    protected $_validate = null;

    /**
     * History object.
     *
     * @var Phprojekt_History
     */
    protected $_history = null;

    /**
     * Full text Search object.
     *
     * @var Phprojekt_Search_Words
     */
    protected $_search = null;

    /**
     * Rights class object.
     *
     * @var array
     */
    protected $_rights = null;

    /**
     * Field for display in the search results.
     *
     * @var string
     */
    public $searchFirstDisplayField = 'title';

    /**
     * Field for display in the search results.
     *
     * @var string
     */
    public $searchSecondDisplayField = 'notes';

    /**
     * Initialize new object.
     *
     * @param array $db Configuration for Zend_Db_Table.
     *
     * @return void
     */
    public function __construct($db = null)
    {
        parent::__construct($db);

        $this->_dbManager = new Phprojekt_DatabaseManager($this, $db);
        $this->_validate  = Phprojekt_Loader::getLibraryClass('Phprojekt_Model_Validate');
        $this->_history   = Phprojekt_Loader::getLibraryClass('Phprojekt_History');
        $this->_search    = Phprojekt_Loader::getLibraryClass('Phprojekt_Search');
        $this->_rights    = Phprojekt_Loader::getLibraryClass('Phprojekt_Item_Rights');
    }

    /**
     * Define the clone function for prevent the same point to same object.
     *
     * @return void
     */
    public function __clone()
    {
        parent::__clone();
        $this->_validate = Phprojekt_Loader::getLibraryClass('Phprojekt_Model_Validate');
        $this->_history  = Phprojekt_Loader::getLibraryClass('Phprojekt_History');
        $this->_search   = Phprojekt_Loader::getLibraryClass('Phprojekt_Search');
        $this->_rights   = Phprojekt_Loader::getLibraryClass('Phprojekt_Item_Rights');
    }

    /**
     * Returns the database manager instance used by this phprojekt item.
     *
     * @return Phprojekt_DatabaseManager An instance of Phprojekt_DatabaseManager.
     */
    public function getInformation()
    {
        return $this->_dbManager;
    }

    /**
     * Returns the notification instance used by this phprojekt item.
     *
     * @return Phprojekt_Notification An intance of Phprojekt_Notification.
     */
    public function getNotification()
    {
        $notification = Phprojekt_Loader::getLibraryClass('Phprojekt_Notification');
        $notification->setModel($this);

        return $notification;
    }

    /**
     * Returns the field information of the item.
     *
     * @return Phprojekt_DatabaseManager_Field An intance of Phprojekt_DatabaseManager_Field.
     */
    public function current()
    {
        $key = $this->convertVarFromSql($this->key());

        return new Phprojekt_DatabaseManager_Field($this->getInformation(), $key, parent::current());
    }

    /**
     * Assign a value to a var using some validations from the table data.
     *
     * @param string $varname Name of the var to assign.
     * @param mixed  $value   Value for assign to the var.
     *
     * @return void
     */
    public function __set($varname, $value)
    {
        $varForInfo = Phprojekt_ActiveRecord_Abstract::convertVarToSql($varname);
        $info       = $this->info();

        if (true == isset($info['metadata'][$varForInfo])) {
            $type  = $info['metadata'][$varForInfo]['DATA_TYPE'];
            $value = Phprojekt_Converter_Value::set($type, $value);
        } else {
            $value = Cleaner::sanitize('string', $value);
        }

        parent::__set($varname, $value);
    }

    /**
     * Return if the values are valid or not.
     *
     * @return boolean True for valid.
     */
    public function recordValidate()
    {
        $data   = $this->_data;
        $fields = $this->_dbManager->getFieldDefinition(Phprojekt_ModelInformation_Default::ORDERING_FORM);

        return $this->_validate->recordValidate($this, $data, $fields);
    }

    /**
     * Get a value of a var.
     * Is the var is a float, return the locale float.
     *
     * @param string $varname Name of the var to assign.
     *
     * @return mixed Value of the var.
     */
    public function __get($varname)
    {
        $info       = $this->info();
        $value      = parent::__get($varname);
        $varForInfo = Phprojekt_ActiveRecord_Abstract::convertVarToSql($varname);

        if (true == isset($info['metadata'][$varForInfo])) {
            $type  = $info['metadata'][$varForInfo]['DATA_TYPE'];
            $value = Phprojekt_Converter_Value::get($type, $value);
        }

        return $value;
    }

    /**
     * Return the error data.
     *
     * @return array Array with errors.
     */
    public function getError()
    {
        return (array) $this->_validate->error->getError();
    }

    /**
     * Extension of the Abstract Record to save the history.
     *
     * @return void
     */
    public function save()
    {
        $result = true;
        if ($this->id > 0) {
            $this->_history->saveFields($this, 'edit');
            $result = parent::save();
        } else {
            $result = parent::save();
            $this->_history->saveFields($this, 'add');
        }

        $this->_search->indexObjectItem($this);

        return $result;
    }

    /**
     * Use directly the the Abstract Record to don't save the history or search words.
     *
     * @return void
     */
    public function parentSave()
    {
        return parent::save();
    }

    /**
     * Extension of the Abstract Record to delete an item.
     *
     * @return void
     */
    public function delete()
    {
        $moduleId = Phprojekt_Module::getId($this->getModelName());

        $this->deleteUploadFiles();
        $this->_history->saveFields($this, 'delete');
        $this->_search->deleteObjectItem($this);
        $this->_rights->saveRights($moduleId, $this->id, array());

        parent::delete();
    }

    /**
     * Delete all the files uploaded in the upload fields.
     *
     * @return void
     */
    public function deleteUploadFiles()
    {
        // Is there is any upload file, -> delete the files from the server
        $fields = $this->getInformation()->getInfo(Phprojekt_ModelInformation_Default::ORDERING_FORM,
            Phprojekt_DatabaseManager::COLUMN_NAME);
        foreach ($fields as $field) {
            $field = Phprojekt_ActiveRecord_Abstract::convertVarFromSql($field);
            if ($this->getInformation()->getType($field) == 'upload') {
                $filesField = $this->$field;
                $files      = explode('||', $filesField);
                foreach ($files as $file) {
                    $md5Name          = substr($file, 0, strpos($file, '|'));
                    $fileAbsolutePath = Phprojekt::getInstance()->getConfig()->uploadPath . $md5Name;
                    if (!empty($md5Name) && file_exists($fileAbsolutePath)) {
                        unlink($fileAbsolutePath);
                    }
                }
            }
        }
    }

    /**
     * Return the fields that can be filtered.
     *
     * This function must be here for be overwrited by the default module.
     *
     * @return array Array with field names.
     */
    public function getFieldsForFilter()
    {
        return $this->getInformation()->getInfo(Phprojekt_ModelInformation_Default::ORDERING_LIST,
            Phprojekt_DatabaseManager::COLUMN_NAME);
    }

    /**
     * Rewrites parent fetchAll, so that only records with read access are shown.
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
        // Only fetch records with read access
        $join .= sprintf(' INNER JOIN item_rights ON (item_rights.item_id = %s
            AND item_rights.module_id = %d AND item_rights.user_id = %d) ',
            $this->getAdapter()->quoteIdentifier($this->getTableName() . '.id'),
            Phprojekt_Module::getId($this->getModelName()), Phprojekt_Auth::getUserId());

        // Set where
        if (null !== $where) {
            $where .= ' AND ';
        }
        $where .= ' (' . sprintf('(%s.owner_id = %d OR %s.owner_id IS NULL)', $this->getTableName(),
            Phprojekt_Auth::getUserId(), $this->getTableName());
        $where .= ' OR (item_rights.access > 0)) ';

        return parent::fetchAll($where, $order, $count, $offset, $select, $join);
    }

    /**
     * Returns the rights merged with the role for the current user.
     *
     * @return array Array of rights per user.
     */
    public function getRights()
    {
        $rights = $this->_rights->getRights(Phprojekt_Module::getId($this->getModelName()), $this->id);

        return $this->_mergeRightsAndRole($rights);
    }

    /**
     * Returns the rights merged with the role for the current user for each item.
     *
     * @param array $ids An array with all the IDs for check.
     *
     * @return array Array of rights per user.
     */
    public function getMultipleRights($ids)
    {
        $allRights = $this->_rights->getMultipleRights(Phprojekt_Module::getId($this->getModelName()), $ids);

        $return = array();
        foreach ($allRights as $user => $rights) {
            $return[$user] = $this->_mergeRightsAndRole($rights);
        }

        return $return;
    }

    /**
     * Returns the rights merged with the role for each user has on a Phprojekt item.
     *
     * @return array Array of rights per user.
     */
    public function getUsersRights()
    {
        $rights = $this->_rights->getUsersRights(Phprojekt_Module::getId($this->getModelName()), $this->id);

        return $this->_mergeRightsAndRole($rights);
    }

    /**
     * Returns the right merged with the role for each user has on a Phprojekt item.
     *
     * @param array $rights Array of rights per user.
     *
     * @return array Array of rights per user.
     */
    public function _mergeRightsAndRole($rights)
    {
        $moduleId = Phprojekt_Module::getId($this->getModelName());
        $saveType = Phprojekt_Module::getSaveType($moduleId);
        switch ($saveType) {
            case Phprojekt_Module::TYPE_NORMAL:
                $roleRights      = new Phprojekt_RoleRights($this->projectId, $moduleId, $this->id);
                $roleRightRead   = $roleRights->hasRight('read');
                $roleRightWrite  = $roleRights->hasRight('write');
                $roleRightCreate = $roleRights->hasRight('create');
                $roleRightAdmin  = $roleRights->hasRight('admin');

                // Map roles with item rights and make one array
                foreach ($rights as $userId => $access) {
                    foreach ($access as $name => $value) {
                        switch ($name) {
                            case 'admin':
                                $rights[$userId]['admin'] = $roleRightAdmin && $value;
                                break;
                            case 'download':
                                $rights[$userId]['download'] = ($roleRightRead || $roleRightWrite || $roleRightAdmin)
                                    && $value;
                                break;
                            case 'delete':
                                $rights[$userId]['delete'] = ($roleRightWrite || $roleRightAdmin) && $value;
                                break;
                            case 'copy':
                                $rights[$userId]['copy'] = ($roleRightWrite || $roleRightCreate || $roleRightAdmin)
                                    && $value;
                                break;
                            case 'create':
                                $rights[$userId]['create'] = ($roleRightWrite || $roleRightCreate || $roleRightAdmin)
                                    && $value;
                                break;
                            case 'access':
                                $rights[$userId]['access'] = ($roleRightRead || $roleRightWrite || $roleRightCreate
                                || $roleRightAdmin) && $value;
                                break;
                            case 'write':
                                $rights[$userId]['write'] = ($roleRightWrite || $roleRightCreate || $roleRightAdmin)
                                    && $value;
                                break;
                            case 'read':
                                $rights[$userId]['read'] = ($roleRightRead || $roleRightWrite || $roleRightAdmin)
                                    && $value;
                                break;
                            case 'none':
                                $rights[$userId]['none'] = $value;
                                break;
                        }
                    }
                }
                break;
            case Phprojekt_Module::TYPE_GLOBAL:
                break;
            case Phprojekt_Module::TYPE_MIX:
                // Implement saveType 2
                break;
        }

        return $rights;
    }

    /**
     * Save the rights for the current item.
     *
     * The users are a POST array with user IDs.
     *
     * @param array $rights Array of user IDs with the bitmask access.
     *
     * @return void
     */
    public function saveRights($rights)
    {
        $this->_rights->saveRights(Phprojekt_Module::getId($this->getModelName()), $this->id, $rights);
    }
}
