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
 * @category   PHProjekt
 * @package    Application
 * @subpackage Project
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @author     Reno Reckling <reno.reckling@mayflower.de>
 */

/**
 * Project Migration
 *
 * This is used to add the user_proxy table
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Project
 * @copyright  Copyright (c) 2011 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1.0
 * @author     Reno Reckling <reno.reckling@mayflower.de>
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
        return '6.1.4';
    }

    public function onVersionStep($oldVersion, $newVersion) {
        if ($newVersion === "6.1.4") {
            $select = $this->_db->select();
            $select->from(
                array('m' => 'tags_modules'),
                array('m.module_id', 'm.item_id', 't.id')
            );
            $select->join(
                array('u' => 'tags_users'),
                'u.id = m.tag_user_id'
            );
            $select->join(
                array('t' => 'tags'),
                't.id = u.tag_id'
            );
            $select->group(array('m.module_id', 'm.item_id', 't.id'));

            $rows = $this->_db->fetchAll($select);
            if (count($rows) > 0) {
                $insertrows = array();

                foreach ($rows as $row) {
                    $insertrows[] = '(' .
                        implode(',', array(
                            $this->_db->quote($row['module_id']),
                            $this->_db->quote($row['item_id']),
                            $this->_db->quote($row['id'])
                        )) .
                        ')';
                }

                $query = 'INSERT INTO ' .
                    $this->_db->quoteIdentifier('tags_modules_items') .
                    '( module_id, item_id, tag_id ) VALUES ' .
                    implode(',', $insertrows);

                $this->_db->query($query);
            }
        }
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
            || Phprojekt::compareVersion($currentVersion, '6.1.4') < 0)
        {
            $obj =& $this;
            $this->_db = $db;
            $stepWrapper = function ($oldVersion, $newVersion) use ($obj) {
                $obj->onVersionStep($oldVersion, $newVersion);
            };

            $dbParser = new Phprojekt_DbParser(
                array('useExtraData' => false),
                Phprojekt::getInstance()->getDb()
            );
            $dbParser->parseSingleModuleData('Project', null,  $stepWrapper);

            Phprojekt::getInstance()->getCache()->clean(
                Zend_Cache::CLEANING_MODE_ALL
            );
        }
    }
}
