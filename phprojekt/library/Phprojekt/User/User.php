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
 * User model class.
 */
class Phprojekt_User_User extends Phprojekt_ActiveRecord_Abstract implements Phprojekt_Model_Interface
{
    /**
     * Has many declrations.
     *
     * @var array
     */
    public $hasMany = array('settings' => array('classname' => 'Phprojekt_Setting',
                                                'module'    => 'Setting',
                                                'model'     => 'Setting'));

    /**
     * The standard information manager with hardcoded field definitions.
     *
     * @var Phprojekt_ModelInformation_Interface
     */
    protected $_informationManager;

    /**
     * Validate object.
     *
     * @var Phprojekt_Model_Validate
     */
    protected $_validate = null;

    /**
     * Setting object. Cached for Efficiency.
     *
     * @var Phprojekt_Setting
     */
    protected $_setting = null;

    /**
     * Initialize new user.
     *
     * If is seted the user id in the session,
     * the class will get all the values of these user.
     *
     * @param array $db Configuration for Zend_Db_Table.
     *
     * @return void
     */
    public function __construct($db = null)
    {
        if (null === $db) {
            $db = Phprojekt::getInstance()->getDb();
        }
        parent::__construct($db);

        $this->_validate           = new Phprojekt_Model_Validate();
        $this->_informationManager = new Phprojekt_User_Information();
    }

    /**
     * Define the clone function for prevent the same point to same object.
     *
     * @return void
     */
    public function __clone()
    {
        parent::__clone();
        $this->_validate           = new Phprojekt_Model_Validate();
        $this->_informationManager = new Phprojekt_User_Information();
    }

    /**
     * Overwrite fetchAll to provide a default sorting by the configured display name.
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null, $select = null, $join = null)
    {
        if (is_null($order)) {
            $order = self::getDisplay();
        }

        return parent::fetchAll($where, $order, $count, $offset, $select, $join);
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
     * Finds a user based on the username
     *
     * @param string $username Username of the user to find.
     *
     * @return Phprojekt_User_User|null User object or null.
     */
    public function findByUsername($username)
    {
        $db = Phprojekt::getInstance()->getDb();

        $users = $this->fetchAll($db->quoteInto('username = ?', $username), null, 1);

        if (isset($users[0])) {
            return $users[0];
        } else {
            return null;
        }
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
            if (!$clone->find($id)) {
                throw new RuntimeException("Could not find user with id $id");
            }
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
        if ($this->id == 0) {
            if (parent::save()) {
                // adding default values
                $rights = new Phprojekt_Item_Rights();
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
     * Get the information manager.
     *
     * @see Phprojekt_Model_Interface::getInformation()
     *
     * @return Phprojekt_ModelInformation_Interface An instance of Phprojekt_ModelInformation_Interface.
     */
    public function getInformation()
    {
        return $this->_informationManager;
    }

    /**
     * Save the rigths.
     *
     * @return void
     */
    public function saveRights()
    {
    }

    /**
     * Validate the current record.
     *
     * @return boolean True on valid.
     */
    public function recordValidate()
    {
        $data   = $this->_data;
        $fields = $this->_informationManager->getFieldDefinition(Phprojekt_ModelInformation_Default::ORDERING_FORM);
        $result = $this->_validate->recordValidate($this, $data, $fields);

        if ($result) {
            // Username repeated?
            $db      = Phprojekt::getInstance()->getDb();
            $where   = sprintf("username = %s AND id != %d", $db->quote($this->username), (int) $this->id);
            $records = $this->fetchAll($where);
            if (count($records) > 0) {
                $this->_validate->error->addError(array(
                    'field'   => 'username',
                    'label'   => Phprojekt::getInstance()->translate('Username'),
                    'message' => Phprojekt::getInstance()->translate('Already exists, choose another one please')));
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Returns the error data.
     *
     * @return array Array with errors.
     */
    public function getError()
    {
        return (array) $this->_validate->error->getError();
    }

    /**
     * Returns an user setting.
     *
     * @param string $settingName  The key of the setting to get.
     * @param object $defaultValue Default value if setting is empty.
     *
     * @return mix Setting value.
     */
    public function getSetting($settingName, $defaultValue = null)
    {
        if (is_null($this->_setting)) {
            $this->_setting = new Phprojekt_Setting();
            $this->_setting->setModule('User');
        }

        $value = $this->_setting->getSetting($settingName, $this->id);
        if (empty($value)) {
            $value = $defaultValue;
        }

        return $value;
    }

    /**
     * This function wraps around the phprojekt setting for the user timezone
     * to return a DateTimeZone object.
     *
     * @return DateTimeZone The timezone of the user.
     */
    public static function getUserDateTimeZone()
    {
        $tz = Phprojekt_Auth_Proxy::getEffectiveUser()->getSetting('timezone', '0');
        $tz = explode('_', $tz);
        $hours = (int) $tz[0];
        if ($hours >= 0) {
            $hours = '+' . $hours;
        }
        $minutes = '00';
        if (array_key_exists(1, $tz)) {
            // We don't need the minus sign
            $minutes = abs($tz[1]);
        }
        $datetime = new Datetime($hours . ':' . $minutes);
        return $datetime->getTimezone();
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
     * @param array  $display  The display format.
     * @param object $object   The model to apply the display.
     *
     * @return string User display.
     */
    public static function applyDisplay(array $display, $object)
    {
        $showValue = array();
        foreach ($display as $value) {
            if (isset($object->$value)) {
                $showValue[] = $object->$value;
            }
        }

        return implode(', ', $showValue);
    }

    /**
     * Returns the name of the user formatted for display.
     *
     * @return string
     */
    public function getDisplayName()
    {
        static $format = null;
        if (is_null($format)) {
            $format = self::getDisplay();
        }
        return self::applyDisplay($format, $this);
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
        $where  = sprintf('status = %s', $this->getAdapter()->quote('A'));
        $result = $this->fetchAll($where);
        $values = array();

        foreach ($result as $node) {
            $values[] = array('id'   => (int) $node->id, 'name' => $node->displayName);
        }
        return $values;
    }
}
