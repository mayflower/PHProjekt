<?php
/**
 * Class for manage user setting from different modules.
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
 * @subpackage Core
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
 * Class for manage user setting from different modules.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Core
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Phprojekt_Setting extends Phprojekt_ActiveRecord_Abstract
{
    /**
     * Name for use with the session.
     */
    const IDENTIFIER = 'Phprojekt_Setting-getSetting-';

    /**
     * The name of a module.
     *
     * @var string
     */
    protected $_module = 'User';

    /**
     * The module ID.
     *
     * @var integer
     */
    protected $_moduleId = 0;

    /**
     * Class of the setting module.
     *
     * @var Object class
     */
    protected $_object = null;

    /**
     * A list of directories that are not included in the search.
     * Usually Default and Core.
     *
     * @var array
     */
    protected static $_excludePaths = array('Default', 'Core');

    /**
     * Returns a set of modules available that have Setting.php files.
     *
     * @return array Array with 'name' and 'label'.
     */
    public function getModules()
    {
        $results = array();
        // System settings
        $model = Phprojekt_Loader::getModel('Core', 'User_Setting');
        if ($model) {
            $results[] = array('name'  => 'User',
                               'label' => Phprojekt::getInstance()->translate('User'));
        }

        $modelNotification = Phprojekt_Loader::getModel('Core', 'Notification_Setting');
        if ($modelNotification) {
            $results[] = array('name'  => 'Notification',
                               'label' => Phprojekt::getInstance()->translate('Notification'));
        }

        // System modules settings
        foreach (scandir(PHPR_CORE_PATH) as $dir) {
            $path = PHPR_CORE_PATH . DIRECTORY_SEPARATOR . $dir;
            if ($dir == '.' || $dir == '..' || in_array($dir, self::$_excludePaths)) {
                continue;
            }
            if (is_dir($path)) {
                $settingClass = Phprojekt_Loader::getModelClassname($dir, 'Setting');
                if (Phprojekt_Loader::tryToLoadClass($settingClass)) {
                    $results[] = array('name'  => $dir,
                                       'label' => Phprojekt::getInstance()->translate($dir, null, $dir));
                }
            }
        }

        // User modules settings
        foreach (scandir(PHPR_USER_CORE_PATH) as $dir) {
            $path = PHPR_USER_CORE_PATH . $dir;
            if ($dir == '.' || $dir == '..') {
                continue;
            }
            if (is_dir($path)) {
                $settingClass = Phprojekt_Loader::getModelClassname($dir, 'Setting');
                if (Phprojekt_Loader::tryToLoadClass($settingClass, false, true)) {
                    $results[] = array('name'  => $dir,
                                       'label' => Phprojekt::getInstance()->translate($dir, null, $dir));
                }
            }
        }

        return $results;
    }

    /**
     * Define the current module to use in the settings.
     *
     * @param string $module The module name.
     *
     * @return void
     */
    public function setModule($module)
    {
        $this->_moduleId = Phprojekt_Module::getId($module);
        $this->_module   = $module;
    }

    /**
     * Get the object class to use for manage the settings.
     *
     * @return Object Class.
     */
    public function getModel()
    {
        if (null === $this->_object) {
            // System settings

            if ($this->_module == 'User' || $this->_module == 'Notification') {
                $this->_object = Phprojekt_Loader::getModel('Core', sprintf('%s_Setting', $this->_module));
            } else {
                $this->_object = Phprojekt_Loader::getModel($this->_module, 'Setting');
            }
        }

        return $this->_object;
    }

    /**
     * Return the value of one setting.
     *
     * @param string  $settingName The name of the setting.
     * @param integer $userId      The user ID, if is not setted, the current user is used.
     *
     * @return mix Value of the setting.
     */
    public function getSetting($settingName, $userId = 0)
    {
        $toReturn = null;
        if (!$userId) {
            $userId = Phprojekt_Auth::getUserId();
        }

        $namespace = new Zend_Session_Namespace(self::IDENTIFIER . $userId);
        if (!isset($namespace->$settingName)) {
            $where = sprintf('user_id = %d AND key_value = %s AND module_id = %d', (int) $userId,
                $this->_db->quote($settingName), (int) $this->_moduleId);
            $record = $this->fetchAll($where);
            if (!empty($record)) {
                $toReturn = $record[0]->value;
            }
            $namespace->$settingName = $toReturn;
        } else {
            $toReturn = $namespace->$settingName;
        }

        return $toReturn;
    }

    /**
     * Collect all the values of the settings and return it in one row.
     *
     * @param integer $moduleId The current module ID.
     * @param array   $metadata Array with all the fields.
     * @param integer $userId   The user ID, if is not setted, the current user is used.
     *
     * @return array Array with all the settings.
     */
    public function getList($moduleId, $metadata, $userId = null)
    {
        if (method_exists($this->getModel(), 'getList')) {
            return $this->getModel()->getList($moduleId, $metadata, $userId = null);
        }

        $settings = array();

        if ($userId === null) {
            $userId = (int) Phprojekt_Auth::getUserId();
        }

        $where  = sprintf('module_id = %d AND user_id = %d', (int) $moduleId, (int) $userId);
        $record = $this->fetchAll($where);

        $data       = array();
        $data['id'] = 0;
        foreach ($metadata as $meta) {
            $data[$meta['key']] = '';
            foreach ($record as $oneSetting) {
                if ($oneSetting->keyValue == $meta['key']) {
                    $getter = 'get' . ucfirst($oneSetting->keyValue);
                    if (method_exists($this->getModel(), $getter)) {
                        $data[$meta['key']] = call_user_func(array($this->getModel(), $getter), $oneSetting->value);
                    } else {
                        $data[$meta['key']] = $oneSetting->value;
                    }
                    break;
                }
            }
        }
        $settings[] = $data;

        return $settings;
    }

    /**
     * Validation functions for all the values.
     *
     * @param array $params $_POST fields.
     *
     * @return string Error message.
     */
    public function validateSettings($params)
    {
        $message = null;

        if (method_exists($this->getModel(), 'validateSettings')) {
            $message = call_user_func(array($this->getModel(), 'validateSettings'), $params);
        }

        return $message;
    }

    /**
     * Save the settings into the table.
     *
     * @param array   $params $_POST fields.
     * @param integer $userId The user ID, if is not setted, the current user is used.
     *
     * @return void
     */
    public function setSettings($params, $userId = 0)
    {
        if (!$userId) {
            $userId = Phprojekt_Auth::getUserId();
        }

        if (method_exists($this->getModel(), 'setSettings')) {
            call_user_func(array($this->getModel(), 'setSettings'), $params, $userId);
        } else {
            $namespace = new Zend_Session_Namespace(self::IDENTIFIER . $userId);
            $fields    = $this->getModel()->getFieldDefinition(Phprojekt_ModelInformation_Default::ORDERING_FORM);
            foreach ($fields as $data) {
                foreach ($params as $key => $value) {
                    if ($key == $data['key']) {
                        $where  = sprintf('user_id = %d AND key_value = %s AND module_id = %d', (int) $userId,
                            $this->_db->quote($key), (int) $this->_moduleId);
                        $record = $this->fetchAll($where);
                        if (isset($record[0])) {
                            $record[0]->keyValue = $key;
                            $record[0]->value    = $value;
                            $record[0]->save();
                        } else {
                            $clone             = clone $this;
                            $clone->userId     = $userId;
                            $clone->moduleId   = (int) $this->_moduleId;
                            $clone->keyValue   = $key;
                            $clone->value      = $value;
                            $clone->identifier = $this->_module;
                            $clone->save();
                        }
                        $namespace->$key = $value;
                        break;
                    }
                }
            }
        }
    }
}
