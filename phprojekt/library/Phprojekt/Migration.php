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
 * Manages modules' migrations.
 */
class Phprojekt_Migration
{

    /** The migrations found. Format is moduleName => MigrationObject. */
    protected $_migrations = array();

    /**
     * Create a new Phprojekt_Migration object.
     *
     * @param Phprojekt_Extensions $extensions The extensions-object to get the
     *                                         migrations from.
     */
    public function __construct(Phprojekt_Extensions $extensions)
    {
        foreach ($extensions->getExtensions() as $module => $extension) {
            $migration = $extension->getMigration();

            if (!is_null($migration)) {
                $this->registerMigration($module, $migration);
            }
        }
    }

    /**
     * Add a new migration.
     *
     * @param                       string    $module The name of the module.
     * @param Phprojekt_Migration_Abstract $migration The migration object.
     *
     * @return void
     */
    public function registerMigration(
            $module,
            Phprojekt_Migration_Abstract $migration)
    {
        $this->_migrations[$module] = $migration;
    }

    private $_modulesNeedingUpgrade = null;

    /**
     * Retrieve the modules needing upgrades.
     *
     * The return value will be in following format:
     *  array(
     *      ModuleName => array(
     *          'from'      => string: The current version in the db.
     *          'to'        => string: The current code version of the module.
     *          'migration' => Phprojekt_Migration_Abstract: The module's
     *                                                       migration object.
     *      )
     *  )
     *
     * @return array See description
     */
    public function getModulesNeedingUpgrade()
    {
        if (!is_null($this->_modulesNeedingUpgrade)) {
            return $this->_modulesNeedingUpgrade;
        }

        $db             = Phprojekt::getInstance()->getDb();
        $moduleVersions = $db->fetchAssoc('SELECT LOWER(name), version FROM module');
        $return         = array();

        foreach ($this->_migrations as $module => $migration) {
            $module        = strtolower($module);
            $codeVersion   = $migration->getCurrentModuleVersion();

            if (array_key_exists($module, $moduleVersions)) {
                // The module is already installed and we are upgrading.
                $moduleVersion = $moduleVersions[$module]['version'];
                $compare       = Phprojekt::compareVersion(
                    $moduleVersion,
                    $codeVersion
                );

                if ($compare > 0) {
                    // The current db version is higher than the code version.
                    // TODO: Handle this.
                } else if ($compare < 0) {
                    $return[$module] = array(
                        'from'      => $moduleVersion,
                        'to'        => $codeVersion,
                        'migration' => $migration
                    );
                }
            } else {
                // The module is new. Add it to the list
                $return[$module] = array(
                    'from'      => null,
                    'to'        => $codeVersion,
                    'migration' => $migration
                );
            }
        }

        $this->_modulesNeedingUpgrade = $return;

        return $return;
    }

    /**
     * Check if an upgrade is needed.
     *
     * @param $module If specified, check only if this module needs upgrading.
     *
     * @return boolean Whether an upgrade is neccessary.
     */
    public function needsUpgrade($module = null)
    {
        $modules = $this->getModulesNeedingUpgrade();

        if (is_null($module)) {
            return (!empty($modules));
        } else {
            return array_key_exists($module, $modules);
        }
    }

    /**
     * Perform all the neccessary upgrades.
     *
     * @return void
     *
     * @throws Phprojekt_Migration_IKilledTheDatabaseException
     * @throws Exception If we could recover.
     */
    public function performAllUpgrades()
    {
        foreach (array_keys($this->getModulesNeedingUpgrade()) as $module) {
            $this->performUpgrade($module);
        }
    }

    /**
     * Perform the neccessary upgrades for the given module.
     *
     * @return void
     *
     * @throws Phprojekt_Migration_IKilledTheDatabaseException
     * @throws Exception If we could recover.
     */
    public function performUpgrade($module)
    {
        if (!array_key_exists($module, $this->_migrations)) {
            throw new Exception("No migration object found for $module.");
        }
        if (!$this->needsUpgrade($module)) {
            throw new Exception("Module $module does not need upgrading.");
        }

        $db = Phprojekt::getInstance()->getDb();
        $modules = $this->getModulesNeedingUpgrade();
        $data    = $modules[$module];
        $data['migration']->upgrade($data['from'], $db);

        $db->update(
            'module',
            array('version' => $data['to']),
            $db->quoteInto('name = ?', $module)
        );

        Phprojekt::getInstance()->getCache()->clean(
            Zend_Cache::CLEANING_MODE_ALL
        );
    }
}
