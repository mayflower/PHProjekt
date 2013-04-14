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
 * @copyright  Copyright (c) 2012 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * Timecard Migration
 *
 * Migration routines for the timecard module.
 */
class Timecard_Migration extends Phprojekt_Migration_Abstract
{
    /**
     * The database on which to migrate
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db;

    /**
     * Return the current module version.
     *
     * Implements Phprojekt_Migration_Abstract->getCurrentModuleVersion
     *
     * @return String Version
     */
    public function getCurrentModuleVersion()
    {
        return '6.3.0';
    }

    /**
     * Upgrade to the latest version.
     *
     * @param String $currentVersion Phprojekt version string indicating our
     *                               current version
     * @param Zend_Db_Adapter_Abstract $db The database to use
     *
     * @return void
     * @throws Exception On Errors
     */
    public function upgrade($currentVersion, Zend_Db_Adapter_Abstract $db)
    {
        date_default_timezone_set('UTC');
        $this->_db = $db;
        $this->parseDbFile('Timecard');

        if (Phprojekt::compareVersion($currentVersion, '6.1.4') < 0) {
            $request  = new Zend_Controller_Request_Http();
            $uidSuffix = "@phprojekt6-" . $request->getHttpHost();
            Phprojekt::getInstance()->getDB()->query(
                "UPDATE timecard SET uri = id, uid = CONCAT(UUID(), \"{$uidSuffix}\");"
            );
            // This is mysql-only. Not sure if this is the ultimate way to go here.
            Phprojekt::getInstance()->getDB()->query('ALTER TABLE timecard ADD UNIQUE (uri)');
        }

        if (Phprojekt::compareVersion($currentVersion, '6.3.0') < 0) {
            Phprojekt::getInstance()->getDB()->query(
                "DELETE ir
                   FROM item_rights ir, module m
                  WHERE ir.module_id = m.id
                    AND m.name NOT IN ('Timecard', 'Project', 'Calendar2')");
            Phprojekt::getInstance()->getDB()->query(
                "DELETE rmp
                   FROM role_module_permissions rmp, module m
                  WHERE rmp.module_id = m.id
                    AND m.name NOT IN ('Timecard', 'Project', 'Calendar2')");
            Phprojekt::getInstance()->getDB()->query(
                "DELETE FROM module
                  WHERE name NOT IN ('Timecard', 'Project', 'Calendar2')");
            Phprojekt::getInstance()->getDB()->query(
                "DELETE FROM database_manager
                  WHERE table_name = 'Project'
                    AND table_field = 'contact_id'");
        }
    }

}
