<?php
/**
 * Manages modules' migrations.
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
 * @subpackage Migration
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1.0
 * @version    Release: @package_version@
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 */

/**
 * Manages modules' migrations.
 *
 * @category   PHProjekt
 * @package    Phprojekt
 * @subpackage Migration
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1.0
 * @version    Release: @package_version@
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
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
     * @return boolean Whether an upgrade is neccessary.
     */
    public function needsUpgrade()
    {
        $modules = $this->getModulesNeedingUpgrade();
        return (!empty($modules));
    }

    /**
     * Perform the neccessary upgrades.
     *
     * @return void
     */
    public function performUpgrade() {
        $db = Phprojekt::getInstance()->getDb();

        foreach (self::getModulesNeedingUpgrade() as $module => $data) {
            $data['migration']->upgrade($data['from'], $db);
            $where = $db->quoteInto('name = ?', $module);
            $db->update(
                'module',
                array('version' => $data['to']),
                $where
            );
        }
    }
}
