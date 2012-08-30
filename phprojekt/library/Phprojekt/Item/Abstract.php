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
 * An item, with database manager support.
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
        $this->_validate  = new Phprojekt_Model_Validate();
        $this->_history   = new Phprojekt_History();
        $this->_search    = new Phprojekt_Search();
        $this->_rights    = new Phprojekt_Item_Rights();
    }

    /**
     * Define the clone function for prevent the same point to same object.
     *
     * @return void
     */
    public function __clone()
    {
        parent::__clone();
        $this->_validate = new Phprojekt_Model_Validate();
        $this->_history  = new Phprojekt_History();
        $this->_search   = new Phprojekt_Search();
        $this->_rights   = new Phprojekt_Item_Rights();
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
        $notification = new Phprojekt_Notification();
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
        $fields = $this->getInformation()->getFieldDefinition(Phprojekt_ModelInformation_Default::ORDERING_FORM);

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
        $this->trackUploadedfiles();
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
     * Updated the database table for the unused files.
     * Delete files that are no longe used by the item.
     *
     * @return void
     */
    public function trackUploadedfiles()
    {
        $fields = $this->getInformation()->getInfo(
            Phprojekt_ModelInformation_Default::ORDERING_FORM,
            Phprojekt_DatabaseManager::COLUMN_NAME
        );

        foreach ($fields as $field) {
            $field = Phprojekt_ActiveRecord_Abstract::convertVarFromSql($field);
            if ($this->getInformation()->getType($field) == 'upload' && !empty($this->_storedId)) {
                $files = Default_Helpers_Upload::parseModelValues($this->_data[$field]);
                $originalFiles = Default_Helpers_Upload::parseModelValues($this->_originalData[$field]);

                // remove files that are in the old filelist, but not in the new one
                $toBeDeleted = array();
                foreach ($originalFiles as $ofile) {
                    $toBeDeleted[$ofile['md5']] = $ofile;
                }

                foreach ($files as $file) {
                    if (array_key_exists($file['md5'], $toBeDeleted)) {
                        unset($toBeDeleted[$file['md5']]);
                    }
                }

                $deleteList = array();
                foreach ($toBeDeleted as $hash => $file) {
                    $deleteList[] = $file;
                }

                Default_Helpers_Upload::deleteFiles($deleteList);

                Default_Helpers_Upload::removeFilesFromUnusedFileList($files);
            }
        }
    }

    /**
     * Delete all the files uploaded in the upload fields.
     *
     * @return void
     */
    public function deleteUploadFiles()
    {
        // If there is any upload file -> delete the files from the server
        $fields = $this->getInformation()->getInfo(
            Phprojekt_ModelInformation_Default::ORDERING_FORM,
            Phprojekt_DatabaseManager::COLUMN_NAME
        );

        foreach ($fields as $field) {
            $field = Phprojekt_ActiveRecord_Abstract::convertVarFromSql($field);
            if ($this->getInformation()->getType($field) == 'upload') {
                $filesField = $this->$field;
                $files = Default_Helpers_Upload::parseModelValues($filesField);
                Default_Helpers_Upload::deleteFiles($files);
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
        return $this->getInformation()->getInfo(
            Phprojekt_ModelInformation_Default::ORDERING_LIST,
            Phprojekt_DatabaseManager::COLUMN_NAME
        );
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
        if (!Phprojekt_Auth::isAdminUser()) {
            $join .= sprintf(' INNER JOIN item_rights ON (item_rights.item_id = %s
                AND item_rights.module_id = %d AND item_rights.user_id = %d) ',
                $this->getAdapter()->quoteIdentifier($this->getTableName() . '.id'),
                Phprojekt_Module::getId($this->getModelName()), Phprojekt_Auth_Proxy::getEffectiveUserId());

            // Set where
            if (null !== $where) {
                $where .= ' AND ';
            }
            $where .= ' (' . sprintf('(%s.owner_id = %d OR %s.owner_id IS NULL)', $this->getTableName(),
                Phprojekt_Auth_Proxy::getEffectiveUserId(), $this->getTableName());
            $where .= sprintf(' OR ((item_rights.access & %d) = %d)) ', Phprojekt_Acl::READ, Phprojekt_Acl::READ);
        }

        return parent::fetchAll($where, $order, $count, $offset, $select, $join);
    }

    /**
     * Override count function to account for rights.
     *
     * @param string $where A where clause to count a subset of the results.
     *
     * @return integer Count of results.
     */
    public function count($where = null)
    {
        if (Phprojekt_Auth::isAdminUser()) {
            return parent::count($where);
        }

        $db       = Phprojekt::getInstance()->getDb();
        $rawTable = $this->getTableName();
        $table    = $db->quoteIdentifier($rawTable);
        $select   = $db->select()->from($rawTable, array('COUNT(*)'));

        if (!is_null($where)) {
            $select->where($where);
        }

        $select->join(array('ir' => 'item_rights'), "ir.item_id = {$table}.id", array())
            ->where('ir.module_id = :thisModule')
            ->where('ir.user_id = :effectiveUser')
            ->where("{$table}.owner_id = :effectiveUser OR (ir.access & :right) = :right")
            ->bind(
                array(
                    ':thisModule' => Phprojekt_Module::getId($this->getModelName()),
                    ':effectiveUser' => Phprojekt_Auth_Proxy::getEffectiveUserId(),
                    ':right' => Phprojekt_Acl::READ
                )
            );

        return $select->query()->fetchColumn();
    }

    /**
     * Returns the rights merged with the role for the current user.
     *
     * @return array Array of rights per user.
     */
    public function getRights($userId = null)
    {
        // backward compatbility
        if (null === $userId) {
            $userId = Phprojekt_Auth_Proxy::getEffectiveUserId();
        }

        $moduleId = Phprojekt_Module::getId($this->getModelName());
        return Phprojekt_Right::getRightsForItems($moduleId, $this->projectId, $userId, array($this->id));
    }

    public function hasRight($userId, $right, $projectId = null)
    {
        if (Phprojekt_Auth::isAdminUser() || $this->isNew()) {
            return true;
        }

        $projectId = is_null($projectId) ? $this->projectId : $projectId;
        $moduleId = Phprojekt_Module::getId($this->getModelName());
        $rights   = Phprojekt_Right::getRightsForItems($moduleId, $projectId, $userId, array($this->id));
        if (!isset($rights[$this->id])) {
            return Phprojekt_Acl::NONE;
        }

        return ($rights[$this->id] & $right) == $right;
    }

    /**
     * Returns the rights merged with the role for each user has on a Phprojekt item.
     *
     * @return array Array of rights per user.
     */
    public function getUsersRights()
    {
        $moduleId = Phprojekt_Module::getId($this->getModelName());
        return $this->_rights->getUsersRights($moduleId, $this->id);
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

    /**
     * Returns all users with the given right.
     *
     * @param int  $rights The bitmask with rights. (ORed constants from Phprojekt_Acl.) Any rights if omitted or null.
     * @param bool $exact  Only return users with these exact rights. Defaults to false if omitted.
     *
     * @return array of User The users with the given right.
     */
    public function getUsersWithRights($rights = null, $exact = false) {
        return $this->_rights->getUsersWithRight(
            Phprojekt_Module::getId($this->getModelName()),
            $this->id,
            $rights,
            $exact
        );
    }

}
