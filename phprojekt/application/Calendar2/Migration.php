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
        return '6.1.0-beta1';
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
        date_default_timezone_set('utc');
        $this->_db = $db;

        if (is_null($currentVersion)
                || Phprojekt::compareVersion($currentVersion, '6.1.0-beta1') < 0) {
            $this->parseDbFile('Calendar2');
            Phprojekt::getInstance()->getCache()->clean(Zend_Cache::CLEANING_MODE_ALL);
            $this->_migrateFromOldCalendar();
            $this->_removeOldCalendar();
        }
    }

    /**
     * Change this to true to enable (quite noisy) debug output for the
     * migration routines.
     */
    private $_debug = false;

    //private $_tagsObj  = null;
    private $_oldCalId = null;
    private $_newCalId = null;
    private $_search   = null;

    private function _migrateFromOldCalendar()
    {
        $db = $this->_db;

        if (!in_array('calendar', $db->listTables())) {
            throw new Exception('Old Calendar is gone, cannot migrate');
        }

        $this->_db->delete('calendar2');

        $this->_copyEvents();
        $this->_updateUsers();
        $this->_updateRights();
        $this->_updateLastEnd();

        //TODO no tags, no search, no history, no changes single occurrences yet;
    }

    private function _copyEvents()
    {
        $db = $this->_db;

        $db->query('INSERT INTO calendar2 SELECT id, project_id, title AS summary, notes AS description, place AS
            location, "" AS comments, start_datetime AS start, NULL AS last_end, end_datetime AS end,
            owner_id, rrule, NULL AS recurrence_id, visibility, NULL AS uid, NOW() AS last_modified, NULL AS
            uri FROM calendar WHERE parent_id = 0 OR parent_id = id OR rrule != ""');

        // This actually happens...
        $db->query('UPDATE calendar2 SET end = start WHERE end < start');
    }

    private function _updateUsers()
    {
        $db = $this->_db;

        $db->query('insert ignore into calendar2_user_relation (calendar2_id, user_id, confirmation_status) select
            p.id as calendar2_id, c.participant_id as user_id, c.status + 1 as confirmation_status from calendar as
            c join calendar as p on c.parent_id = p.id where c.parent_id != 0 and c.parent_id != c.id');

        $db->query('insert ignore into calendar2_user_relation (calendar2_id, user_id, confirmation_status) select
            id as calendar2_id, participant_id as user_id, status + 1 as confirmation_status from calendar where
            parent_id = 0 or parent_id = id');
    }

    private function _updateRights()
    {
        $db = $this->_db;

        $db->query('insert ignore into item_rights (module_id, item_id, user_id, access) select
            ' . Phprojekt_Module::getId('Calendar2') . ' as module_id, calendar2_id as item_id, user_id, 1 as access
            from calendar2_user_relation');

        $db->query('insert into item_rights (module_id, item_id, user_id, access) select
            ' . Phprojekt_Module::getId('Calendar2') . ' as module_id, id as item_id, owner_id as user_id, 255 as
            access from calendar2 on duplicate key update access = 255');
    }

    /**
     * Updates the lastEnd values of all entries
     *
     * @return void
     */
    private function _updateLastEnd()
    {
        if ($this->_debug) Phprojekt::getInstance()->getLog()->debug('_updateLastEnd');
        $this->_db->query('UPDATE calendar2 SET last_end = end WHERE rrule = ""');

        $entries = $this->_db->select()->from('calendar2', array('id', 'start', 'end', 'rrule'))
            ->where('rrule != ""')
            ->query()->fetchAll();

        $update = $this->_db->prepare('UPDATE calendar2 SET last_end = :last_end WHERE id = :id');

        $x = 0;
        foreach ($entries as $key => $e) {
            if ($this->_debug && ++$x % 100 == 0) Phprojekt::getInstance()->getLog()->debug($x);
            $start    = new Datetime($e['start']);
            $end      = new Datetime($e['end']);
            $duration = $start->diff($end);

            $helper = new Calendar2_Helper_Rrule($start, $duration, $e['rrule']);
            $update->execute(
                array(
                    ':last_end' => $helper->getUpperTimeBoundary()->format('Y-m-d H:i:s'),
                    ':id' => $e['id']
                )
            );
        }

    }

    /**
     * Removes the old calendar.
     */
    private function _removeOldCalendar()
    {
        $db = $this->_db;

        $db->query('DROP TABLE calendar');
        $db->delete('module', 'name = "Calendar"');
        $db->delete('item_rights', $db->quoteInto('module_id = ?', $this->_oldCalId));
        $db->delete('database_manager', 'table_name = "Calendar"');
        $db->delete('history', $db->quoteInto('module_id = ?', $this->_oldCalId));
    }
}
