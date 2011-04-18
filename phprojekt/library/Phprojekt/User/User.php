<?php
/**
 * User model class.
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
 * @subpackage User
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
 * User model class.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage User
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_User_User extends Phprojekt_Item_Abstract
{
    /**
     * Field for display in the search results.
     *
     * @var string
     */
    public $searchFirstDisplayField = 'username';

    /**
     * Field for display in the search results.
     *
     * @var string
     */
    public $searchSecondDisplayField = 'lastname';

    /**
     * Configuration to use or not the history class.
     *
     * @var boolean
     */
    public $useHistory = false;

    /**
     * Configuration to use or not the search class.
     *
     * @var boolean
     */
    public $useSearch = false;

    /**
     * Configuration to use or not the right class.
     *
     * This variable MUST be false here, since the user can't have rights.
     *
     * @var boolean
     */
    public $useRights = false;

    /**
     * Has many declrations.
     *
     * @var array
     */
    public $hasMany = array('settings' => array('classname' => 'Phprojekt_Setting',
                                                'module'    => 'Setting',
                                                'model'     => 'Setting'));

    /**
     * Has many and belongs to many declrations.
     *
     * @var array
     */
    public $hasManyAndBelongsToMany = array('groups' => array('classname' => 'Phprojekt_Groups_Groups',
                                                              'module'    => 'Groups',
                                                              'model'     => 'Groups'));

    /**
     * Returns the Model information manager.
     *
     * @return Phprojekt_ModelInformation_Interface An instance of a Phprojekt_ModelInformation_Interface.
     */
    public function getInformation()
    {
        if (null == $this->_informationManager) {
            $this->_informationManager = Phprojekt_Loader::getLibraryClass('Phprojekt_User_Information');
        }

        return $this->_informationManager;
    }

    /**
     * Checks if user is active.
     *
     * @return boolean ID user is active or not.
     */
    public function isActive()
    {
        if (strtoupper($this->status) != 'A') {
            return false;
        }

        return true;
    }

    /**
     * Searchs an user ID based on the username.
     *
     * @param string $username Username necessary to find the userId.
     *
     * @return integer|false User ID value or false.
     */
    public function findIdByUsername($username)
    {
        $db = Phprojekt::getInstance()->getDb();

        try {
            $users = $this->fetchAll($db->quoteInto("username = ?", $username), null, 1);

            if (!isset($users[0]) || !isset($users[0]->id)) {
                return false;
            }

            return $users[0]->id;
        } catch (Phprojekt_ActiveRecord_Exception $error) {
            Phprojekt::getInstance()->getLog()->warn($error->getMessage());
        }

        return false;
    }

    /**
     * Found an user using the id and return this class for the new user.
     * If the ID is wrong, return the actual user.
     *
     * @param integer $id The user ID.
     *
     * @return Phprojekt_User_User An instance of Phprojekt_User_User.
     */
    public function findUserById($id)
    {
        if ($id > 0) {
            $clone = clone($this);
            $clone->find($id);
            return $clone;
        } else {
            return $this;
        }
    }

    /**
     * Extencion of the ActiveRecord save adding default permissions.
     *
     * @return boolean True for a sucessful save.
     */
    public function save()
    {
        // Reset users by project cache
        $activeRecord = Phprojekt_Loader::getModel('Project', 'Project');
        $tree         = new Phprojekt_Tree_Node_Database($activeRecord, 1);
        $tree         = $tree->setup();
        foreach ($tree as $node) {
            $sessionName = 'Phprojekt_User_User-getAllowedUsers' . '-' . (int) $node->id;
            $namespace   = new Zend_Session_Namespace($sessionName);
            if (isset($namespace->users)) {
                $namespace->unsetAll();
            }
        }

        if ($this->id == 0) {
            if (parent::save()) {
                // Add default values
                $rights = Phprojekt_Loader::getLibraryClass('Phprojekt_Item_Rights');
                $rights->saveDefaultRights($this->id);
                return true;
            }
        } else {
            return parent::save();
        }
    }

    /**
     * Users can't be deleted, so if delete en exception is throw.
     *
     * @throws Phprojekt_User_Exception On try to delete an user.
     *
     * @return void
     */
    public function delete()
    {
        throw new Phprojekt_User_Exception("Users can't be deleted", 1);
    }

    /**
     * Returns an user setting.
     *
     * @param string $settingName  The key of the setting to get.
     * @param object $defaultValue Default value if setting is empty.
     *
     * @return mix Setting value.
     */
    static public function getSetting($settingName, $defaultValue = null)
    {
        $setting = Phprojekt_Loader::getLibraryClass('Phprojekt_Setting');
        $setting->setModule('User');

        $value = $setting->getSetting($settingName);
        if (empty($value)) {
            $value = $defaultValue;
        }

        return $value;
    }

    /**
     * Returns the display format form the config file.
     *
     * @return array Array of field names from user table to use.
     */
    static public function getDisplay()
    {
        $display = (int) Phprojekt::getInstance()->getConfig()->userDisplayFormat;

        switch ($display) {
            case 0:
            default:
                $value = array('lastname', 'firstname');
                break;
            case 1:
                $value = array('username', 'lastname', 'firstname');
                break;
            case 2:
                $value = array('username');
                break;
            case 3:
                $value = array('firstname', 'lastname');
                break;
        }

        return $value;
    }

    /**
     * Apply the display to the $model and return the result.
     *
     * @param array               $display The display format.
     * @param Phprojekt_User_User $model   The model to apply the display.
     *
     * @return string User display.
     */
    public static function applyDisplay(array $display, $model)
    {
        $showValue = array();
        foreach ($display as $value) {
            if (isset($model->$value)) {
                $showValue[] = $model->$value;
            }
        }

        return implode(', ', $showValue);
    }

    /**
     * Return all the users that have at least read access on the current project.
     *
     * This function needs that Phprojekt::setCurrentProjectId is called before.
     *
     * @return array Array of users with 'id' and 'name'.
     */
    public function getAllowedUsers()
    {
        // Cache the query
        $sessionName    = 'Phprojekt_User_User-getAllowedUsers' . '-' . (int) Phprojekt::getCurrentProjectId();
        $usersNamespace = new Zend_Session_Namespace($sessionName);

        if (!isset($usersNamespace->users)) {
            $displayName = $this->getDisplay();
            $rights      = Phprojekt_Loader::getLibraryClass('Phprojekt_Item_Rights');
            $ids         = $rights->getUsersWithRight(1, (int) Phprojekt::getCurrentProjectId());
            $where       = sprintf('status = %s', $this->getAdapter()->quote('A'));
            if (!empty($ids)) {
                $where .= sprintf(' AND id IN (%s) ', implode(',', $ids));
            }

            $result = $this->fetchAll($where, $displayName);
            $values = array();
            foreach ($result as $node) {
                $values[] = array('id'   => (int) $node->id,
                                  'name' => $node->applyDisplay($displayName, $node));
            }
            $usersNamespace->users = $values;
        }

        return $usersNamespace->users;
    }
}
