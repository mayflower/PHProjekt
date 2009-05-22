<?php
/**
 * A model that receives information about Configuration models of other modules
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
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * A model that receives information about Configuration models of other modules
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Administration_Models_Configuration extends Phprojekt_ActiveRecord_Abstract
{
    /**
     * The name of a module
     *
     * @var string
     */
    protected $_module = '';

    /**
     * The module Id
     *
     * @var integer
     */
    protected $_moduleId = 0;

    /**
     * Class of the Configuration module
     *
     * @var Object class
     */
    protected $_object = null;

    /**
     * A list of directories that are not included in the search.
     * Usually Default and Administration
     *
     * @var array
     */
    protected static $_excludePaths = array('Default', 'Administration', 'Setting', 'Core', '.svn');

    /**
     * Returns a set of modules available and have Configuration sections
     *
     * @return array
     */
    public function getModules()
    {
        $results = array();

        // Module Configuration
        foreach (scandir(PHPR_CORE_PATH) as $dir) {
            $path = PHPR_CORE_PATH . DIRECTORY_SEPARATOR . $dir;
            if ($dir == '.' || $dir == '..' || in_array($dir, self::$_excludePaths)) {
                continue;
            }
            if (is_dir($path)) {
                $configClass = Phprojekt_Loader::getModelClassname($dir, sprintf('%sConfiguration', $dir));
                if (Phprojekt_Loader::tryToLoadClass($configClass)) {
                    $results[] = array('name'  => $dir,
                                       'label' => Phprojekt::getInstance()->translate($dir));
                }
            }
        }

        return $results;
    }

    /**
     * Define the current module to use in the Configuration
     *
     * @param string $module The module name
     *
     * @return void
     */
    public function setModule($module)
    {
        $this->_moduleId = Phprojekt_Module::getId($module);
        $this->_module   = $module;
    }

    /**
     * Get the object class to use for manage the Configuration
     *
     * @return Object class
     */
    public function getModel()
    {
        if (null === $this->_object) {
            $this->_object = Phprojekt_Loader::getModel($this->_module, sprintf('%sConfiguration', $this->_module));
        }

        return $this->_object;
    }

    /**
     * Return the value of one Configuration
     *
     * @param string  $configName The name of the Configuration
     *
     * @return mix
     */
    public function getAdministration($configName)
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
     * Collect all the values of the Configuration and return it in one row
     *
     * @param integer $moduleId The current moduleId
     * @param array   $metadata Array with all the fields
     *
     * @return array
     */
    public function getList($moduleId, $metadata)
    {
        $configurations  = array();

        $record    = $this->fetchAll('module_id = ' . (int) $moduleId);
        $functions = get_class_methods($this->_object);

        $data       = array();
        $data['id'] = 0;
        foreach ($metadata as $meta) {
            $data[$meta['key']] = '';
            foreach ($record as $config) {
                if ($config->keyValue == $meta['key']) {
                    $getter = 'get'.ucfirst($config->keyValue);
                    if (in_array($getter, $functions)) {
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
     * Validation functions for all the values
     *
     * @param array $params $_POST fields
     *
     * @return string
     */
    public function validateConfigurations($params)
    {
        $message = null;

        if (in_array('validateConfigurations', get_class_methods($this->getModel()))) {
            $message = call_user_func(array($this->getModel(), 'validateConfigurations'), $params);
        }

        return $message;
    }

    /**
     * Save the Configurations into the table
     *
     * @param array $params $_POST fields
     *
     * @return void
     */
    public function setConfigurations($params)
    {
        if (in_array('setConfigurations', get_class_methods($this->getModel()))) {
            call_user_func(array($this->getModel(), 'setConfigurations'), $params);
        } else {
            $fields = $this->getModel()->getFieldDefinition();
            foreach ($fields as $data) {
                foreach ($params as $key => $value) {
                    if ($key == $data['key']) {
                        $where = sprintf('key_value = %s AND module_id = d', $this->_db->quote($key),
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
