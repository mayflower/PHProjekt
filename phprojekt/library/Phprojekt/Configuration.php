<?php
/**
 * Class for manage system configurations from different modules.
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
 * Class for manage system configurations from different modules.
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
class Phprojekt_Configuration extends Phprojekt_ActiveRecord_Abstract
{
    /**
     * The name of a module.
     *
     * @var string
     */
    protected $_module = '';

    /**
     * The module ID.
     *
     * @var integer
     */
    protected $_moduleId = 0;

    /**
     * Class of the Configuration module.
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
     * Returns a set of modules available and have Configuration sections.
     *
     * @return array Array with 'name' and 'label'.
     */
    public function getModules()
    {
        $results = array();

        // System settings
        $model = Phprojekt_Loader::getModel('Core', 'General_Configuration');
        if ($model) {
            $results[] = array('name'  => 'General',
                               'label' => Phprojekt::getInstance()->translate('General'));
        }

        // System modules Configurations
        foreach (scandir(PHPR_CORE_PATH) as $dir) {
            $path = PHPR_CORE_PATH . DIRECTORY_SEPARATOR . $dir;
            if ($dir == '.' || $dir == '..' || in_array($dir, self::$_excludePaths)) {
                continue;
            }
            if (is_dir($path)) {
                $configClass = Phprojekt_Loader::getModelClassname($dir, 'Configuration');
                if (Phprojekt_Loader::tryToLoadClass($configClass)) {
                    $results[] = array('name'  => $dir,
                                       'label' => Phprojekt::getInstance()->translate($dir, null, $dir));
                }
            }
        }

        // User modules Configurations
        foreach (scandir(PHPR_USER_CORE_PATH) as $dir) {
            $path = PHPR_USER_CORE_PATH . $dir;
            if ($dir == '.' || $dir == '..') {
                continue;
            }
            if (is_dir($path)) {
                $configClass = Phprojekt_Loader::getModelClassname($dir, 'Configuration');
                if (Phprojekt_Loader::tryToLoadClass($configClass, false, true)) {
                    $results[] = array('name'  => $dir,
                                       'label' => Phprojekt::getInstance()->translate($dir, null, $dir));
                }
            }
        }

        return $results;
    }

    /**
     * Define the current module to use in the Configuration.
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
     * Get the object class to use for manage the Configuration.
     *
     * @return mix Configuration class.
     */
    public function getModel()
    {
        if (null === $this->_object) {
            // System configuration
            if ($this->_module == 'General') {
                $this->_object = Phprojekt_Loader::getModel('Core', sprintf('%s_Configuration', $this->_module));
            } else {
                $this->_object = Phprojekt_Loader::getModel($this->_module, 'Configuration');
            }
        }

        return $this->_object;
    }

    /**
     * Return the value of one configuration.
     *
     * @param string $configName The name of the configuration.
     *
     * @return mix Value of the configuration.
     */
    public function getConfiguration($configName)
    {
        $toReturn = null;

        $where  = sprintf("key_value = %s AND module_id = %d", $this->_db->quote($configName), (int) $this->_moduleId);
        $record = $this->fetchAll($where);
        if (!empty($record)) {
            $toReturn = $record[0]->value;
        }

        return $toReturn;
    }

    /**
     * Collect all the values of the configuration and return it in one row.
     *
     * @param integer $moduleId The current module ID.
     * @param array   $metadata Array with all the fields.
     *
     * @return array Array with all the configurations.
     */
    public function getList($moduleId, $metadata)
    {
        $configurations  = array();
        $record          = $this->fetchAll('module_id = ' . (int) $moduleId);

        $data       = array();
        $data['id'] = 0;
        foreach ($metadata as $meta) {
            $data[$meta['key']] = '';
            foreach ($record as $config) {
                if ($config->keyValue == $meta['key']) {
                    $getter = 'get' . ucfirst($config->keyValue);
                    if (method_exists($this->getModel(), $getter)) {
                        $data[$meta['key']] = call_user_func(array($this->getModel(), $getter), $config->value);
                    } else {
                        $data[$meta['key']] = $config->value;
                    }
                    break;
                }
            }
        }
        $configurations[] = $data;

        return $configurations;
    }

    /**
     * Validation functions for all the values.
     *
     * @param array $params $_POST fields.
     *
     * @return string|null Error message.
     */
    public function validateConfigurations($params)
    {
        $message = null;

        if (method_exists($this->getModel(), 'validateConfigurations')) {
            $message = call_user_func(array($this->getModel(), 'validateConfigurations'), $params);
        }

        return $message;
    }

    /**
     * Save the Configurations into the table.
     *
     * @param array $params $_POST fields
     *
     * @return void
     */
    public function setConfigurations($params)
    {
        if (method_exists($this->getModel(), 'setConfigurations')) {
            call_user_func(array($this->getModel(), 'setConfigurations'), $params);
        } else {
            $fields = $this->getModel()->getFieldDefinition(Phprojekt_ModelInformation_Default::ORDERING_FORM);
            foreach ($fields as $data) {
                foreach ($params as $key => $value) {
                    if ($key == $data['key']) {
                        $where = sprintf('key_value = %s AND module_id = %d', $this->_db->quote($key),
                            (int) $this->_moduleId);
                        $record = $this->fetchAll($where);
                        if (isset($record[0])) {
                            $record[0]->keyValue = $key;
                            $record[0]->value    = $value;
                            $record[0]->save();
                        } else {
                            $clone           = clone $this;
                            $clone->moduleId = (int) $this->_moduleId;
                            $clone->keyValue = $key;
                            $clone->value    = $value;
                            $clone->save();
                        }
                        break;
                    }
                }
            }
        }
    }
}
