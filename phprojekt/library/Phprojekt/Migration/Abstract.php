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
 * Provides modules with the possibility to perform upgrade actions on minor
 * version changes.
 *
 * If a module wants to use this option, it has to provide a
 * Phprojekt_Extension_Abstract and implement the getMigration method.
 *
 * More info about Extensions can be found in the files
 * library/Phprojekt/Extensions.php and
 * library/Phprojekt/Extension/Abstract.php.
 */
abstract class Phprojekt_Migration_Abstract
{
    /**
     * Returns the current module version. Should be hard-coded and changed when
     * the module's database layout changes.
     *
     * @return string A valid Phprojekt version string.
     */
    public abstract function getCurrentModuleVersion();

    /**
     * Perform the upgrade to the current version.
     *
     * When you implement this function, please consider a few things.
     * First: Use transactions!
     * In case anything goes wrong, you can just roll back and throw a new
     * Exception. (For debugging purposes, pass the old Exception as third
     * parameter to the new Exception).
     * IMPORTANT: Please don't throw Zend_Controller_Action_Exceptions with error codes 4xx unless you
     *            _really_ make sure they don't contain sensitive data. They
     *            might be shown to the user.
     *
     * If you can't use transactions (See for example
     * http://dev.mysql.com/doc/refman/5.0/en/implicit-commit.html) then try to
     * act in a way that makes it possible to recover the old state of the
     * database. (And to that if something goes wrong.) Then throw an Exception.
     *
     * If that fails, too, please use
     * Phprojekt::getInstance()->getLog()->debug() to save as much information
     * as possible and throw a Phprojekt_Migration_IKilledTheDatabaseException.
     *
     * @param                   string $currentVersion The current version of
     *                                                 the module or null if
     *                                                 not yet installed.
     * @param Zend_Db_Adapter_Abstract             $db The database.
     *
     * @return void
     *
     * @throws exception On any error.
     *
     * @TODO Provide a way to ask for additional tasks (conversion etc)
     * @TODO Provide a way to allow the callee to show messages and status.
     */
    public abstract function upgrade($currentVersion, Zend_Db_Adapter_Abstract $db);

    /**
     * Helper function that parses the SQL/Db.json file and updates the database
     * accordingly.
     *
     * @param string $module The name of this module.
     *
     * @return void
     */
    protected final function parseDbFile($module)
    {
        $dbParser = new Phprojekt_DbParser(
            array('useExtraData' => false),
            Phprojekt::getInstance()->getDb()
        );
        $dbParser->parseSingleModuleData($module);
    }
}
