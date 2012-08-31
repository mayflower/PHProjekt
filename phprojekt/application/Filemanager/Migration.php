<?php
/**
 * Filemanager Migration
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
 * @subpackage Filemanager
 * @copyright  Copyright (c) 2012 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1.5
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 */

/**
 * Filemanager Migration
 *
 * This is used to install the Cal2 tables and Convert the Calendar1 data to the
 * new format.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Filemanager
 * @copyright  Copyright (c) 2012 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1.5
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 */
class Filemanager_Migration extends Phprojekt_Migration_Abstract
{
    private $_db;

    /**
     * Return the current module version.
     *
     * Implements Phprojekt_Migration_Abstract->getCurrentModuleVersion
     *
     * @return String Version
     */
    public function getCurrentModuleVersion()
    {
        return '6.1.5';
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
        $this->_db = $db;

        if (Phprojekt::compareVersion($currentVersion, '6.1.5') < 0) {
            $this->parseDbFile('Filemanager');
            Phprojekt::getInstance()->getCache()->clean(Zend_Cache::CLEANING_MODE_ALL);
            $this->_renameFilemanagersWithSameTitle();
            $this->_makeTitleProjectUniqueIndex();
        }
    }

    private function _renameFilemanagersWithSameTitle()
    {
        $this->_db->query(
<<<HERE
UPDATE filemanager AS f
JOIN (
    SELECT title, project_id
    FROM filemanager
    GROUP BY title, project_id
    HAVING COUNT(title) > 1
) AS c
  ON f.title = c.title AND f.project_id = c.project_id
SET f.title = CONCAT(f.title, ' (', f.id, ')')
HERE
);
    }

    private function _makeTitleProjectUniqueIndex()
    {
        $this->_db->query('ALTER TABLE filemanager ADD UNIQUE INDEX (title, project_id)');
    }
}
