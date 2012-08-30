<?php
/**
 * Project Migration
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
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * Project Migration
 *
 * This is used to add the user_proxy table
 */
class Project_Migration extends Phprojekt_Migration_Abstract
{
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

        if (is_null($currentVersion)
                || Phprojekt::compareVersion($currentVersion, '6.1.0-dev') < 0) {
            $this->parseDbFile('Project');
            Phprojekt::getInstance()->getCache()->clean(Zend_Cache::CLEANING_MODE_ALL);
        }
        if (Phprojekt::compareVersion($currentVersion, '6.1.5') < 0) {
            $this->_renameProjectsWithSameTitle();
            $this->_makeTitleParentUniqueIndex();
        }
    }

    private function _renameProjectsWithSameTitle()
    {
        $this->_db->query(<<<HERE
UPDATE project AS p
JOIN (
    SELECT title, project_id
    FROM project
    GROUP BY title, project_id
    HAVING COUNT(title) > 1
) AS c
  ON p.title = c.title AND p.project_id = c.project_id
SET p.title = CONCAT(p.title, ' (', p.id, ')')
HERE
);
    }

    private function _makeTitleParentUniqueIndex()
    {
        $this->_db->query('ALTER TABLE project ADD UNIQUE INDEX (title, project_id)');
    }
}
