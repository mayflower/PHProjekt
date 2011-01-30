<?php
/**
 * Provides modules with the possibility to perform upgrade actions on minor
 * version changes.
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
 * Provides modules with the possibility to perform upgrade actions on minor
 * version changes.
 *
 * If a module wants to use this option, it has to provide a
 * Phprojekt_Extension_Abstract and implement the getMigration method.
 *
 * More info about Extensions can be found in the files
 * library/Phprojekt/Extensions.php and
 * library/Phprojekt/Extension/Abstract.php.
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
