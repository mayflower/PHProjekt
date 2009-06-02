<?php
/**
 * User class for PHProjekt 6.0
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
 * @version    $Id$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Phprojekt_User for PHProjekt 6.0
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    PHProjekt
 * @subpackage Core
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */
class Phprojekt_User_User extends Phprojekt_ActiveRecord_Abstract implements Phprojekt_Model_Interface
{
    /**
     * Has many declrations
     *
     * @var array
     */
    public $hasMany = array('settings' => array('classname' => 'Setting_Models_Setting',
                                                'module'    => 'Setting',
                                                'model'     => 'Setting'));

    /**
     * Has many and belongs to many declrations
     *
     * @var array
     */
    public $hasManyAndBelongsToMany = array('groups' => array('classname' => 'Phprojekt_Groups_Groups',
                                                              'module'    => 'Groups',
                                                              'model'     => 'Groups'));

    /**
     * The standard information manager with hardcoded
     * field definitions
     *
     * @var Phprojekt_ModelInformation_Interface
     */
    protected $_informationManager;

    /**
     * Validate object
     *
     * @var Phprojekt_Model_Validate
     */
    protected $_validate = null;

    /**
     * Initialize new user
     * If is seted the user id in the session,
     * the class will get all the values of these user
     *
     * @param array $db Configuration for Zend_Db_Table
     *
     * @return void
     */
    public function __construct($db = null)
    {
        if (null === $db) {
            $db = Phprojekt::getInstance()->getDb();
        }
        parent::__construct($db);

        $this->_validate           = Phprojekt_Loader::getLibraryClass('Phprojekt_Model_Validate');
        $this->_informationManager = Phprojekt_Loader::getLibraryClass('Phprojekt_User_Information');
    }

    /**
     * Define the clone function for prevent the same point to same object.
     *
     * @return void
     */
    public function __clone()
    {
        parent::__clone();
        $this->_validate           = Phprojekt_Loader::getLibraryClass('Phprojekt_Model_Validate');
        $this->_informationManager = Phprojekt_Loader::getLibraryClass('Phprojekt_User_Information');
    }

    /**
     * Checks if user is active
     *
     * @return boolean id user is active or not
     */
    public function isActive()
    {
        if (strtoupper($this->status) != 'A') {
            return false;
        }
        return true;
    }

    /**
     * Searchs an user Id based on the username
     *
     * @param string $username username necessary to find the userId
     *
     * @return integer with the user id value. If the user is not found then
     * function will return false
     */
    public function findIdByUsername($username)
    {
        $db = Phprojekt::getInstance()->getDb();
        /* @var $db Zend_Db_Adapter_Abstract */

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
     * Found and user using the id and return this class for the new user
     * If the id is wrong, return the actual user
     *
     * @param int $id The user id
     *
     * @return Phprojekt_User_User
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
     * Extencion of the ActiveRecord save adding default permissions
     *
     * @return boolean true if saved correctly
     */
    public function save()
    {
        if ($this->id == 0) {
            if (parent::save()) {
                // adding default values
                $rights = Phprojekt_Loader::getLibraryClass('Phprojekt_Item_Rights');
                $rights->saveDefaultRights($this->id);
                return true;
            }
        } else {
            return parent::save();
        }
    }

    /**
     * Users can't be deleted, so if delete en exception is throw
     *
     * @return void
     */
    public function delete()
    {
        throw new Phprojekt_User_Exception("Users can't be deleted", 1);
    }

    /**
     * Get the information manager
     *
     * @see Phprojekt_Model_Interface::getInformation()
     *
     * @return Phprojekt_ModelInformation_Interface
     */
    public function getInformation()
    {
        return $this->_informationManager;
    }

    /**
     * Get the rigths for other users
     *
     * @return array
     */
    public function getRights()
    {
        return array();
    }

    /**
     * Save the rigths
     *
     * @return void
     */
    public function saveRights()
    {
    }

    /**
     * Validate the current record
     *
     * @return boolean
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
                    'field'   => Phprojekt::getInstance()->translate('Username'),
                    'label'   => Phprojekt::getInstance()->translate('Username'),
                    'message' => Phprojekt::getInstance()->translate('Already exists, choose another one please')));
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Return the error data
     *
     * @return array
     */
    public function getError()
    {
        return (array) $this->_validate->error->getError();
    }

    /**
     * Return the user setting
     *
     * @param string $settingName The key of the setting to get
     * @param object $defaultValue Default value if setting is empty
     *
     * @return int
     */
    static public function getSetting($settingName, $defaultValue = null)
    {
        $setting = Phprojekt_Loader::getModel('Setting', 'Setting');
        $setting->setModule('User');

        $value = $setting->getSetting($settingName);
        if (empty($value)) {
            $value = $defaultValue;
        }

        return $value;
    }

    /**
     * Return the display format form the config file
     *
     * @return string
     */
    static public function getDisplay()
    {
        $display = (int) Phprojekt::getInstance()->getConfig()->userDisplayFormat;

        switch ($display) {
            case 0:
            default:
                $value = 'lastname, firstname';
                break;
            case 1:
                $value = 'username, lastname, firstname';
                break;
            case 2:
                $value = 'username';
                break;
        }

        return $value;
    }

    /**
     * Apply the display to the $model and return the result
     *
     * @param string              $display The display format
     * @param Phprojekt_User_User $model   The model to apply the display
     *
     * @return string
     */
    public function applyDisplay($display, $model)
    {
        if (preg_match_all("/([a-zA-z_]+)/", $display, $values)) {
            $values = $values[1];
        } else {
            $values = $display;
        }

        $showValue = array();
        foreach ($values as $value) {
            if (isset($model->$value)) {
                $showValue[] = $model->$value;
            }
        }
        $showValue = implode(", ", $showValue);

        return $showValue;
    }
}
