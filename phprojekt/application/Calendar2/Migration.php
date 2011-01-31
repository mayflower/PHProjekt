<?php
/**
 * Calendar2 Migration
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
 * @package    Application
 * @subpackage Calendar2
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: @package_version@
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 */

/**
 * Calendar2 Migration
 *
 * This is used to install the Cal2 tables and Convert the Calendar1 data to the
 * new format.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Calendar2
 * @copyright  Copyright (c) 2011 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1.0
 * @version    Release: @package_version@
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 */
class Calendar2_Migration extends Phprojekt_Migration_Abstract
{
    /**
     * Return the current module version.
     *
     * Implements Phprojekt_Migration_Abstract->getCurrentModuleVersion
     *
     * @return String Version
     */
    public function getCurrentModuleVersion()
    {
        return '6.1.0-dev';
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
        if (is_null($currentVersion)
                || Phprojekt::compareVersion($currentVersion, '6.1.0-dev') < 0) {
            $this->parseDbFile('Calendar2');
            // TODO: Convert the data.
        }
    }
}
