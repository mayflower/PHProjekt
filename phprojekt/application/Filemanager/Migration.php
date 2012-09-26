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
 * Filemanager Migration
 *
 * This is used to install the Cal2 tables and Convert the Calendar1 data to the
 * new format.
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
            $this->_renameFilemanagersWithSameTitle();
            $this->parseDbFile('Filemanager');
            Phprojekt::getInstance()->getCache()->clean(Zend_Cache::CLEANING_MODE_ALL);
            $this->_renameFilesWithSameName();
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

    private function _renameFilesWithSameName()
    {
        $rows = $this->_db->select()->from('filemanager', array('id', 'files'))
            ->query()->fetchAll();

        foreach ($rows as $row) {
            $this->_makeRowFilenamesUnique($row);
        }
    }

    private function _makeRowFilenamesUnique($row)
    {
        $namesToHashes    = $this->_filesStringToNameHashesMap($row['files']);
        $hashesToNewNames = $this->_hashesToUniqueNames($namesToHashes);

        $newFilesString = $this->_hashNameMapToFilesString($hashesToNewNames);
        $this->_saveFilesString($row['id'], $newFilesString);
    }

    private function _filesStringToNameHashesMap($filesString)
    {
        $ret = array();
        foreach (explode('||', $filesString) as $hashNamePair) {
            list($hash, $name) = explode('|', $hashNamePair, 2);
            $ret[$name][] = $hash;
        }

        return $ret;
    }

    private function _hashesToUniqueNames($namesToHashes)
    {
        $ret = array();
        foreach ($namesToHashes as $name => $hashes) {
            if (count($hashes) == 1) {
                $hash = $hashes[0];
                $ret[$hash] = $name;
            } else {
                for ($i = 0; $i < count($hashes); $i++) {
                    $hash = $hashes[$i];
                    $ret[$hash] = $name . ' (' . ($i + 1) . ')';
                }
            }
        }

        return $ret;
    }

    private function _hashNameMapToFilesString($hashesToNames)
    {
        $entries = array();
        foreach ($hashesToNames as $hash => $name) {
            $entries[] = $hash . '|' . $name;
        }
        return implode('||', $entries);
    }

    private function _saveFilesString($id, $filesString)
    {
        $this->_db->update(
            'filemanager',
            array('files' => $filesString),
            'id = ' . (int) $id
        );
    }
}
