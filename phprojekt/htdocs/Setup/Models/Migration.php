<?php
/**
 * Migration
 *
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 2.1 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @package    Setup
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @link       http://www.phprojekt.com
 * @author     Gustavo Solt <solt@mayflower.de>
 * @since      File available since Release 6.0
 */

/**
 * Migration
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @package    Setup
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Setup_Models_Migration
{
    /**
     * Arrays with P6 info
     *
     * @var array
     */
    private $_userKurz    = array();
    private $_timeZone    = array();

    /**
     * Arrays with P6 info: the key of each array position is the P5 id, and the value the P6 id, they are used for
     * conversions.
     * E.g.: $this->_users[<oldId>] = <newId>
     *       $this->_users[50] = 40  (this could be because of id reordering)
     *
     * @var array
     */
    private $_users       = array();
    private $_groupsUsers = array();
    private $_projects    = array();
    private $_calendars   = array();
    private $_contacts    = array();

    /**
     * P5 Database
     *
     * @var Zend_db
     */
    private $_dbOrig = null;

    /**
     * P6 Database
     *
     * @var Zend_db
     */
    private $_db = null;

    /**
     * Table manager
     *
     * @var Phprojekt_Table
     */
    private $_tableManager = null;

    /**
     * Permissions for the users
     *
     * @var int
     */
    private $_accessRead  = null;
    private $_accessWrite = null;
    private $_accessAdmin = null;

    /**
     * Root path, taken out from the config file path
     *
     * @var string
     */
    private $_p5RootPath = null;

    // Permissions constants
    const ACCESS_READ     =   1;
    const ACCESS_WRITE    =   2;
    const ACCESS_ACCESS   =   4;
    const ACCESS_CREATE   =   8;
    const ACCESS_COPY     =  16;
    const ACCESS_DELETE   =  32;
    const ACCESS_DOWNLOAD =  64;
    const ACCESS_ADMIN    = 128;

    // Phproject 6 Ids
    const USER_ADMIN   = 1;
    const USER_TEST    = 2;
    const PROJECT_ROOT = 1;

    // Status constants
    const HELPDESK_STATUS_OPEN     = 1;
    const HELPDESK_STATUS_ASSIGNED = 2;
    const HELPDESK_STATUS_SOLVED   = 3;
    const HELPDESK_STATUS_VERIFIED = 4;
    const HELPDESK_STATUS_CLOSED   = 5;
    const TODO_STATUS_ACCEPTED = 1;

    // General constants
    const MODULES_AMOUNT       = 12;

    public function __construct($file, $db = null)
    {
        if (null === $db) {
            $this->_db = Phprojekt::getInstance()->getDb();
        } else {
            $this->_db = $db;
        }

        $this->_tableManager = new Phprojekt_Table($this->_db);

        include_once ($file);
        $this->_checkFile();

        // Set permission variables
        $this->_accessRead  = self::ACCESS_READ + self::ACCESS_ACCESS + self::ACCESS_DOWNLOAD;
        $this->_accessWrite = self::ACCESS_READ + self::ACCESS_WRITE + self::ACCESS_ACCESS + self::ACCESS_CREATE
            + self::ACCESS_COPY + self::ACCESS_DELETE + self::ACCESS_DOWNLOAD;
        $this->_accessAdmin = self::ACCESS_READ + self::ACCESS_WRITE + self::ACCESS_ACCESS + self::ACCESS_CREATE
            + self::ACCESS_COPY + self::ACCESS_DELETE + self::ACCESS_DOWNLOAD + self::ACCESS_ADMIN;

        // Set P5 root path
        $pos               = strpos($file, 'config.inc.php');
        $this->_p5RootPath = substr($file, 0, $pos);
    }

    /**
     * Call all the migration functions
     *
     * @return void
     */
    public function migrateTables()
    {
        $this->_getGroups();
        $this->_migrateUsers();
        $this->_migrateGroupsUserRelations();
        $this->_migrateProjects();
        $this->_migrateTodos();
        $this->_migrateNotes();
        // Awaiting definitions: $this->_migrateTimeCard();
        $this->_migrateCalendar();
        $this->_migrateFilemanager();
        $this->_migrateContacts();
        $this->_migrateHelpdesk();
    }

    /**
     * Checks for migration
     *
     * @throws Expection
     *
     * @param string $file The config file of P5
     *
     * @return void
     */
    private function _checkFile()
    {
        // Check version
        if (substr(PHPR_VERSION, 0, 1) != '5') {
            throw new Exception("Sorry, it is not possible to migrate from a version of PHProjekt previous to 5.0");
        }

        try {
            $dbParams = array(
                'host'     => PHPR_DB_HOST,
                'username' => PHPR_DB_USER,
                'password' => PHPR_DB_PASS,
                'dbname'   => PHPR_DB_NAME
             );
             $this->_dbOrig = Zend_Db::factory('pdo_'.PHPR_DB_TYPE, $dbParams);
        } catch (Exception $error) {
            throw new Exception('Can not connect to server at ' . PHPR_DB_HOST
                . ' using ' . PHPR_DB_USER . ' user ' . '(' . $error->getMessage() . ')');
        }
    }

    /**
     * Collect all the P5 groups
     *
     * @return void
     */
    private function _getGroups()
    {
        $groups = $this->_dbOrig->query("SELECT * FROM " . PHPR_DB_PREFIX . "gruppen")->fetchAll();

        foreach ($groups as $group) {
            $this->_groupsUsers[$group["ID"]] = array();
        }
    }

    /**
     * Migrate P5 users
     *
     * @return void
     */
    private function _migrateUsers()
    {
        // User migration
        $query = "SELECT * FROM " . PHPR_DB_PREFIX . "users WHERE is_deleted IS NULL";
        $users = $this->_dbOrig->query($query)->fetchAll();

        $this->_users[self::USER_ADMIN] = self::USER_ADMIN;

        foreach ($users as $user) {
            if ($user["loginname"] != "root") {
                // Don't migrate 'root' user, it is used 'admin' user instead that has already been inserted
                if ($user["loginname"] == "test") {
                    // First delete it from P6 DB because it was inserted previous to the migration
                    $this->_tableManager->deleteRows("user", "username = 'test'");
                    $where = sprintf("user_id = %s", self::USER_TEST);
                    $this->_tableManager->deleteRows("setting", $where);
                    $this->_tableManager->deleteRows("groups_user_relation", $where);
                    $this->_tableManager->deleteRows("project_role_user_permissions", $where);
                    $this->_tableManager->deleteRows("item_rights", $where);
                }

                switch (PHPR_LOGIN_SHORT) {
                    case '2':
                        $username = $user["loginname"];
                        break;
                    case '1':
                        $username = $user["kurz"];
                        break;
                    default:
                        $username = $user["nachname"];
                        break;
                }

                if ($user["status"] == 0) {
                    $status = "A";
                } else {
                    $status = 'I';
                }

                $oldUserId = $user["ID"];

                $userId = $this->_tableManager->insertRow('user', array(
                    'username'  => utf8_encode($username),
                    'firstname' => utf8_encode($user["vorname"]),
                    'lastname'  => utf8_encode($user["nachname"]),
                    'status'    => $status,
                    'admin'     => 0
                ));
                $this->_users[$oldUserId]       = $userId;
                $this->_userKurz[$user['kurz']] = $userId;
                $this->_timeZone[$userId]       = 2;
                $language                       = 'en';

                @$settings = unserialize($user["settings"]);
                if (is_array($settings)) {
                    if (isset($settings["timezone"])) {
                        $this->_timeZone[$userId] = $settings["timezone"];
                    }
                    if (isset($settings["langua"])) {
                        $language = $settings["langua"];
                    }
                }

                if (defined("PHPR_VERSION") && PHPR_VERSION >= '5.2.1') {
                    $password = $user['pw'];
                } else {
                    $password = md5('phprojektmd5' . $username);
                }

                $this->_tableManager->insertRow('setting', array(
                    'id'         => null,
                    'user_id'    => $userId,
                    'module_id'  => 0,
                    'key_value'  => 'password',
                    'value'      => $password,
                    'identifier' => 'Core'
                ));

                $this->_tableManager->insertRow('setting', array(
                    'id'         => null,
                    'user_id'    => $userId,
                    'module_id'  => 0,
                    'key_value'  => 'email',
                    'value'      => $user["email"],
                    'identifier' => 'Core'
                ));

                $this->_tableManager->insertRow('setting', array(
                    'id'         => null,
                    'user_id'    => $userId,
                    'module_id'  => 0,
                    'key_value'  => 'language',
                    'value'      => $language,
                    'identifier' => 'Core'
                ));

                $this->_tableManager->insertRow('setting', array(
                    'id'         => null,
                    'user_id'    => $userId,
                    'module_id'  => 0,
                    'key_value'  => 'timeZone',
                    'value'      => $this->_timeZone[$userId],
                    'identifier' => 'Core'
                ));

                // Add permission for this user to root project
                $moduleId      = $this->_getModuleId('Project');
                $userRightsAdd = array($userId => $this->_accessAdmin);

                $this->_addItemRights($moduleId, self::PROJECT_ROOT, $userRightsAdd);
            }
        }
    }

    /**
     * Migrates P5 group-users relations and creates a two-dimensional array with a list of groups of P5 as the first
     * dimension and users as the second one: _groups
     *
     * @return void
     */
    private function _migrateGroupsUserRelations()
    {
        // User group
        $userGroups = $this->_dbOrig->query("SELECT * FROM " . PHPR_DB_PREFIX . "grup_user")->fetchAll();

        foreach ($userGroups as $userGroup) {
            $oldUserId = $userGroup["user_ID"];

            // User exists?
            if (isset($this->_users[$oldUserId])) {
                $userId = $this->_users[$oldUserId];
                $group  = $userGroup["grup_ID"];

                $this->_tableManager->insertRow('groups_user_relation', array(
                    'groups_id' => $group,
                    'user_id'   => $userId
                ));

                // Just in case group wasn't in 'gruppen' table (it should be),
                if (!isset($this->_groupsUsers[$group])) {
                    $this->_groupsUsers[$group] = array();
                }

                // Avoid duplicate entries
                if (!in_array($userId, $this->_groupsUsers[$group])) {
                    // Add user to internal groupsUsers variable
                    $this->_groupsUsers[$group][] = $userId;
                }
            }
        }
    }

    /**
     * Migrate P5 projects
     *
     * @return void
     */
    private function _migrateProjects()
    {
        $statusConversion = array(
            1 => "offered",
            2 => "ordered",
            3 => "Working",
            4 => "ended",
            5 => "stopped",
            6 => "Re-Opened",
            7 => "waiting"
        );

        // Project migration
        $projects = $this->_dbOrig->query("SELECT * FROM " . PHPR_DB_PREFIX . "projekte ORDER BY id")->fetchAll();

        $paths                     = array();
        $paths[self::PROJECT_ROOT] = "/1/";
        $projectsNotMigrated       = -1;

        // As the Projects may have other projects that come later in the list as parents, it may be needed to postpone
        // their migration until next iteration, and so on, because of 'path' field.
        // Note: 'count($projects)' has to be outside the 'for' sentence because that amount varies after each iteration
        $totalProjects = count($projects);
        // Following looping structure in the 99% of the cases is iterated just a few times and then interrupted.
        for ($i = 0; $i < $totalProjects; $i++) {
            if ($projectsNotMigrated == count($projects)) {
                // Migration ended: last iteration hasn't migrated any Project so neither will do it this one.
                // This is supposed to happen when there are no projects left to migrate
                break;
            }
            $projectsNotMigrated = count($projects);

            foreach ($projects as $index => $project) {

                $oldProjectId = $project["ID"];
                if (empty($project["parent"])) {
                    $parentId = self::PROJECT_ROOT;
                } else {
                    // Has parent project been processed?
                    $oldParentId = $project["parent"];
                    if (isset($this->_projects[$oldParentId])) {
                        // Yes
                        $parentId = $this->_projects[$oldParentId];
                    } else {
                        // No - Continue to the next iteration of the foreach structure, current project has to be
                        // processed later.
                        continue;
                    }
                }

                $tmpStatus  = $project["kategorie"];
                if (!empty($statusConversion[$tmpStatus])) {
                    $tmpStatus = $statusConversion[$tmpStatus];
                } else {
                    $tmpStatus = $statusConversion[3];
                }

                // Check and repair corrupted dates the way P5 would show them to the user: 2005-06-31 -> 2005-07-01
                $startDate = Cleaner::sanitize('date', $project["anfang"]);
                $endDate   = Cleaner::sanitize('date', $project["ende"]);

                $project['von'] = $this->_processOwner($project['von']);

                $projectId = $this->_tableManager->insertRow('project', array(
                    'project_id'       => $parentId,
                    "title"            => utf8_encode($project["name"]),
                    "notes"            => utf8_encode($project["note"]),
                    "owner_id"         => $project['von'],
                    "start_date"       => $startDate,
                    "end_date"         => $endDate,
                    "priority"         => (int) $project["wichtung"],
                    "current_status"   => (int) $tmpStatus,
                    "complete_percent" => $project["status"],
                    "hourly_wage_rate" => utf8_encode($project["stundensatz"]),
                    "budget"           => utf8_encode($project["budget"])));

                $this->_projects[$oldProjectId] = $projectId;
                $path                           = $paths[$parentId] . $projectId . "/";
                $paths[$projectId]              = $path;

                // Update path
                $data  = array('path' => $path);
                $where = sprintf('id = %s', $projectId);
                $this->_tableManager->updateRows('project', $data, $where);

                // Migrate permissions
                $project["ID"] = $projectId;
                $this->_migratePermissions('Project', $project);

                for ($j = 1; $j < self::MODULES_AMOUNT; $j++) {
                    $this->_tableManager->insertRow('project_module_permissions', array(
                        'module_id'  => $j,
                        'project_id' => $projectId
                    ));
                }

                // Take out this project from the array
                unset($projects[$index]);
            }
        }
    }

    /**
     * Migrate P5 todos
     *
     * @return void
     */
    private function _migrateTodos()
    {
        $todos = $this->_dbOrig->query("SELECT * FROM " . PHPR_DB_PREFIX . "todo ORDER BY ID")->fetchAll();

        foreach ($todos as $todo) {
            $projectId   = $this->_processParentProjId($todo["project"]);
            $todo['von'] = $this->_processOwner($todo['von']);

            if (empty($todo['status'])) {
                $todo['status'] = self::TODO_STATUS_ACCEPTED;
            }

            // Process assigned user
            $oldAssignedId = $todo['ext'];
            $todo['ext']   = null;
            if (!empty($oldAssignedId) && is_numeric($oldAssignedId)) {
                // The assigned user exists in the DB?
                if (isset($this->_users[$oldAssignedId])) {
                    // Yes
                    $todo['ext']     = $this->_users[$oldAssignedId];
                    $data['user_id'] = $todo['ext'];
                }
            }

            $data = array('project_id'     => $projectId,
                          "title"          => utf8_encode($todo["remark"]),
                          "notes"          => utf8_encode($todo["note"]),
                          "owner_id"       => $todo['von'],
                          "priority"       => $todo["priority"],
                          "current_status" => $todo["status"],
                          "user_id"        => $todo["ext"]);

            // If dates are empty strings, don't send the fields; if not, clean them and fix wrong values as P5 would
            // show them to the users
            if (!empty($todo["anfang"])) {
                $data["start_date"] = Cleaner::sanitize('date', $todo["anfang"]);
            }
            if (!empty($todo["deadline"])) {
                $data["end_date"] = Cleaner::sanitize('date', $todo["deadline"]);
            }

            // Insert data
            $todoId = $this->_tableManager->insertRow('todo', $data);

            // Migrate permissions
            $todo["ID"] = $todoId;
            $this->_migratePermissions('Todo', $todo);
        }
    }

    /**
     * Migrate P5 notes
     *
     * @return void
     */
    private function _migrateNotes()
    {
        $notes = $this->_dbOrig->query("SELECT * FROM " . PHPR_DB_PREFIX . "notes ORDER BY ID")->fetchAll();

        foreach ($notes as $note) {
            $projectId   = $this->_processParentProjId($note["projekt"]);
            $note['von'] = $this->_processOwner($note['von']);

            $noteId = $this->_tableManager->insertRow('note', array(
                'project_id' => $projectId,
                "title"      => utf8_encode($note["name"]),
                "comments"   => utf8_encode($note["remark"]),
                "owner_id"   => $note['von']
            ));

            // Migrate permissions
            $note["ID"] = $noteId;
            $this->_migratePermissions('Note', $note);
        }
    }

    /**
     * @todo: this function doesn't work, its programming has to be completed. It has not been decided yet all its
     * functionality.
     *
     * Migrate P5 timecard and timeproj
     *
     * @return void
     */
    private function _migrateTimeCard()
    {
        // Timeproj
        $query     = "SELECT * FROM " . PHPR_DB_PREFIX . "timeproj ORDER BY datum, users, projekt";
        $timeprojs = $this->_dbOrig->query($query)->fetchAll();
        $currentDatum   = '';
        $currentUser    = -1;
        $currentProj    = -1;
        $currentHours   = 0;
        $currentMinutes = 0;

        foreach ($timeprojs as $timeproj) {
            $currentUser = $timeproj['users'];
            if (isset($this->_users[$currentUser])) {
                if ($currentDatum == $timeproj['datum'] && $currentUser == $timeproj['users']
                    && $currentProj == $timeproj['projekt']) {
                    // We are still working with the same day, user and project

                    // As it is wrong but possible, that the same project is found as booked more than once the same day
                    // for the same user (I saw it in a production DB), it is needed to obtain the total amount of time
                    // of the project
                    $currentHours   += $timeproj['h'];
                    $currentMinutes += $timeproj['m'];
                } else {
                    // New combination of date, user and project reached, so it is time to insert the previous project
                    // booking for specific date, user and project into P6 table 'timeproj'. This may occupy more than
                    // one row depending on working times charged in P5 'timecard' table.
                    $query = sprintf("SELECT * FROM %stimecard WHERE datum = %s AND users = %d and projekt = %d ORDER "
                        . "BY start_time", PHPR_DB_PREFIX, $this->_dbOrig->quote($timeproj['datum']),
                        $timeproj['users'], $timeproj['projekt']);
                    $timecards = $this->_dbOrig->query($query)->fetchAll();

                    foreach ($timecards as $timecard) {
                         // Todo this.
                         // Check that only migrates existing users. migrate text fields using utf8_enconde
                    }

                    // Prepare variables of current row for next iteration
                    $currentDatum   = $timeproj['datum'];
                    $currentUser    = $timeproj['users'];
                    $currentProj    = $timeproj['projekt'];
                    $currentHours   = $timeproj['h'];
                    $currentMinutes = $timeproj['m'];
                }

                // Old code:

                $timeproj["projekt"] = $this->_convertProjectId($timeproj["projekt"]);
                $userId              = $timeproj["users"];

                // Fix wrong values the way P5 would show it to the users
                if (empty($timeproj["h"])) {
                    $timeproj["h"] = 0;
                } else if ($timeproj["h"] > 24) {
                     // I don't know how P5 shows more than 24 hours in a day, but I suppose this is the right way
                     $timeproj["h"] == 24;
                     $timeproj["m"] == 0;
                }
                if (empty($timeproj["m"])) {
                    $timeproj["m"] = 0;
                } else if ($timeproj["m"] > 59) {
                    $addHours       = floor($timeproj["m"] / 60);
                    $timeproj["h"] += $addHours;
                    $timeproj["m"] -= $addHours * 60;
                }

                $this->_tableManager->insertRow('timeproj', array(
                    'id'         => (int) $timeproj["ID"],
                    'notes'      => $timeproj["note"],
                    "owner_id"   => $userId,
                    "project_id" => (int) $timeproj["projekt"],
                    "date"       => $timeproj["datum"],
                    "amount"     => $timeproj["h"] . ":" . $timeproj["m"] . ":00"
                ));
            }

            // Timecard
            $timecards = $this->_dbOrig->query("SELECT * FROM " . PHPR_DB_PREFIX . "timecard ORDER BY ID")->fetchAll();

            foreach ($timecards as $timecard) {
                $timecard["anfang"]  = $this->_stringToTime($timecard["anfang"]);
                $timecard["ende"]    = $this->_stringToTime($timecard["ende"]);
                $timecard["projekt"] = $this->_convertProjectId($timecard["projekt"]);
                $userId              = $timecard["users"];

                // Check / fix wrong values as P5 would show it to the user
                if ($timecard["anfang"] == $timecard["ende"] || empty($timecard["anfang"])) {
                    // Skip this item, because P5 will show it with 0 hours and 0 minutes in length in both cases
                    continue;
                }
                if (empty($timecard["anfang"])) {
                    // This is how P5 manages null start values
                    $timecard["anfang"] = "00:00:00";
                }

                $this->_tableManager->insertRow('timecard', array(
                    'id'         => (int) $timecard["ID"],
                    "owner_id"   => $userId,
                    "date"       => $timecard["datum"],
                    "start_time" => $timecard["anfang"],
                    "end_time"   => $timecard["ende"]
                ));
            }
        }
    }

    /**
     * Migrate P5 events
     *
     * @return void
     */
    private function _migrateCalendar()
    {
        // Calendar
        $events = $this->_dbOrig->query("SELECT * FROM " . PHPR_DB_PREFIX . "termine ORDER BY ID")->fetchAll();

        foreach ($events as $calendar) {
            // Start and End times
            if ($calendar['anfang'] == '----') {
                // This is because start and end times are not required fields in P5, but they are required in P6.
                $calendar['anfang'] = '09:00:00';
            } else {
                $calendar["anfang"] = $this->_stringToTime($calendar["anfang"]);
            }
            if ($calendar['ende'] == '----') {
                // This is because start and end times are not required fields in P5, but they are required in P6.
                $calendar['ende'] = '18:00:00';
            } else {
                $calendar["ende"] = $this->_stringToTime($calendar["ende"]);
            }

            $timezone            = (isset($this->_timeZone[$calendar["ID"]])) ? $this->_timeZone[$calendar["ID"]] : 2;
            $date                = Cleaner::sanitize('date', $calendar["datum"]);
            $calendar['von']     = $this->_processOwner($calendar['von']);
            $userVon             = $calendar['von'];

            // Process participant
            $oldParticipId = $calendar['an'];
            if (isset($this->_users[$oldParticipId])) {
                $participantId = $this->_users[$oldParticipId];
            } else {
                // Don't migrate rows for non existing users
                continue;
            }

            // Migrate row
            if (!empty($calendar["anfang"]) && !empty($calendar["ende"]) && !empty($calendar["datum"])) {

                if (!empty($calendar["serie_typ"]) && !empty($calendar["serie_bis"])) {
                    $rrule = $this->_serietypToRrule($calendar["serie_typ"], $calendar["serie_bis"],
                        $calendar["anfang"]);
                } else {
                    $rrule = "";
                }
                // @todo: 'ical_ID' field is not being migrated to 'uid' field, it will be done when implemented P6 ical

                $calendarId = $this->_tableManager->insertRow('calendar', array(
                    "owner_id"       => $calendar['von'],
                    "project_id"     => self::PROJECT_ROOT,
                    "title"          => utf8_encode($calendar["event"]),
                    "place"          => utf8_encode($calendar["ort"]),
                    "notes"          => utf8_encode($calendar["remark"]),
                    "uid"            => null,
                    "start_date"     => $date,
                    "start_time"     => $calendar["anfang"],
                    "end_date"       => $date,
                    "end_time"       => $calendar["ende"],
                    "timezone"       => $timezone,
                    "location"       => utf8_encode($calendar["ort"]),
                    "categories"     => "",
                    "priority"       => $calendar["priority"],
                    "rrule"          => $rrule,
                    "properties"     => "",
                    "participant_id" => $participantId
                ));

                $oldCalendarId                    = $calendar['ID'];
                $this->_calendars[$oldCalendarId] = $calendarId;

                // Process and update parent for this row
                if (empty($calendar['serie_id'])) {
                    $parentId = 0;
                } else {
                    $oldParentId = $calendar['serie_id'];
                    if (isset($this->_calendars[$oldParentId])) {
                        $parentId = $this->_calendars[$oldParentId];
                    } else {
                        // The P5 parent for this row is probably a deleted row, so it will be assigned current key id.
                        // The rest of the rows that point to the same deleted row, will point to the same 'new' row.
                        $parentId = $calendarId;
                        $this->_calendars[$oldParentId] = $calendarId;
                    }
                }
                $data  = array('parent_id' => $parentId);
                $where = sprintf('id = %s', $calendarId);
                $this->_tableManager->updateRows('calendar', $data, $where);

                // Give 'admin' user admin permission for this item
                $userRightsAdd                   = array();
                $userRightsAdd[self::USER_ADMIN] = $this->_accessAdmin;

                // Add owner permission to this item
                $userRightsAdd[$userVon] = $this->_accessAdmin;

                // Add participant permission to this item, only if it wasn't added before
                if (!isset($userRightsAdd[$participantId])) {
                    $userRightsAdd[$participantId] = $this->_accessWrite;
                }

                // Save permissions according to P6 criterion
                $moduleId = $this->_getModuleId('Calendar');
                $itemId   =  $calendarId;
                $this->_addItemRights($moduleId, $itemId, $userRightsAdd);
            }
        }
    }

    /**
     * Migrate P5 filemanager
     *
     * @return void
     */
    private function _migrateFilemanager()
    {
        // Filemanager
        $files = $this->_dbOrig->query("SELECT * FROM " . PHPR_DB_PREFIX . "dateien ORDER BY ID")->fetchAll();

        foreach ($files as $file) {
            $file['von']  = $this->_processOwner($file['von']);
            $file["div2"] = $this->_processParentProjId($file["div2"]);
            $newFilename  = md5($file["tempname"]);
            $uploadDir    = str_replace('htdocs/setup.php', '', $_SERVER['SCRIPT_FILENAME']) . 'upload';
            $title        = utf8_encode($file["filename"]);
            $title        = substr($title,0,100);

            copy(PHPR_FILE_PATH . "\\" . $file["tempname"], $uploadDir . "\\" . $newFilename);

            $fileId = $this->_tableManager->insertRow('filemanager', array(
                'owner_id'   => $file['von'],
                "title"      => $title,
                "comments"   => utf8_encode($file["remark"]),
                "project_id" => $file["div2"],
                "files"      => utf8_encode($newFilename . "|" . $file["filename"])
            ));

            // Migrate permissions
            $file["ID"] = $fileId;
            $this->_migratePermissions('Filemanager', $file);
        }
    }

    /**
     * Migrate P5 contacts
     *
     * @return void
     */
    private function _migrateContacts()
    {
        $contacts = $this->_dbOrig->query('SELECT * FROM ' . PHPR_DB_PREFIX . 'contacts ORDER BY ID')->fetchAll();

        foreach ($contacts as $contact) {
            $contact['von'] = $this->_processOwner($contact['von']);

            // 'Comment' field will have also all fields not supported by P6.
            $comment = $contact['bemerkung'];
            if (!empty($contact['email2'])) {
                $comment .= chr(10) . 'Email 2: ' . $contact['email2'];
            }
            if (!empty($contact['fax'])) {
                $comment .= chr(10) . 'Fax: ' . $contact['fax'];
            }
            if (!empty($contact['anrede'])) {
                $comment .= chr(10) . 'Salutation: ' . $contact['anrede'];
            }
            if (!empty($contact['url'])) {
                $comment .= chr(10) . 'URL: ' . $contact['url'];
            }
            if (!empty($contact['div1'])) {
                $comment .= chr(10) . 'User defined field 1: ' . $contact['div1'];
            }
            if (!empty($contact['div2'])) {
                $comment .= chr(10) . 'User defined field 2: ' . $contact['div2'];
            }

            $contactId = $this->_tableManager->insertRow('contact', array(
                'project_id'  => self::PROJECT_ROOT,
                'name'        => utf8_encode($contact['vorname'] . ' ' . $contact['nachname']),
                'email'       => utf8_encode($contact['email']),
                'company'     => utf8_encode($contact['firma']),
                'firstphone'  => utf8_encode($contact['tel1']),
                'secondphone' => utf8_encode($contact['tel2']),
                'mobilephone' => utf8_encode($contact['mobil']),
                'street'      => utf8_encode($contact['strasse']),
                'city'        => utf8_encode($contact['stadt']),
                'zipcode'     => utf8_encode($contact['plz']),
                'country'     => utf8_encode($contact['land']),
                'comment'     => utf8_encode($comment),
                'owner_id'    => $contact['von'],
                'private'     => 1
            ));
            $oldContactId                   = $contact["ID"];
            $this->_contacts[$oldContactId] = $contactId;
        }
    }

    /**
     * Migrate P5 Helpdesk
     *
     * @return void
     */
    private function _migrateHelpdesk()
    {
        $query     = "SELECT * FROM " . PHPR_DB_PREFIX . "rts WHERE is_deleted IS NULL ORDER BY ID";
        $incidents = $this->_dbOrig->query($query)->fetchAll();

        foreach ($incidents as $item) {
            $projectId = $this->_processParentProjId($item["proj"]);

            // Process owner - Id, email or wrong value
            $owner = $item["von"];
            // Default value:
            $ownerId = self::USER_ADMIN;
            if (is_numeric($owner) && !empty($owner)) {
                // Id
                $ownerId = (int) $owner;
            } else if (strpos($owner, '@')) {
                // It is apparently an email - Search for the Id
                $query   = sprintf("SELECT ID FROM " . PHPR_DB_PREFIX . "users WHERE email = '%s'", $owner);
                $userIds = $this->_dbOrig->query($query)->fetchAll();
                if (count($userIds > 0)) {
                    $oldOwnerId = $userIds[0]["ID"];
                    if (isset($this->_users[$oldOwnerId])) {
                        $ownerId     = $this->_users[$oldOwnerId];
                        $item["von"] = $ownerId;
                    }
                }
            }

            // Process assigned user
            $oldAssignedId = (int) $item["assigned"];
            if (isset($this->_users[$oldAssignedId])) {
                $assignedId       = $this->_users[$oldAssignedId];
                $item["assigned"] = $assignedId;
            } else {
                $assignedId       = null;
                $item["assigned"] = null;
            }

            // Process creation date
            $date = null;
            if (!empty($item['submit'])) {
                $date = $item['submit'];
            } else if (isset($item['created']) && !empty($item['created'])) {
                $date = $item['created'];
            }
            if (!empty($date)) {
                $date = $this->_longDateToShortDate($date);
            }

            // Process priority
            $priority = $item['priority'];
            if (is_numeric($priority)) {
                // It is an int
                $priority = (int) $priority;
                if ($priority < 1 || $priority > 10) {
                    $priority = 5;
                }
            } else {
                // Maybe it is a string representing the priority
                switch ($priority) {
                    case 'Hoch':
                        // High
                        $priority = 1;
                        break;
                    case 'Niedrig':
                    default:
                        // Low
                        $priority = 5;
                        break;
                    case 'Keine':
                        //None
                        $priority = 10;
                        break;
                }
            }

            // Process attachment
            $filenameField   = $item["filename"];
            $attachmentField = null;
            if (strpos($filenameField, "|")) {
                $tmp             = split("\|", $filenameField);
                $currentFileName = $tmp[1];
                $realName        = $tmp[0];
                if ($currentFileName != '' && $realName != '') {
                    $md5Name         = md5(uniqid(rand(), 1));
                    $attachmentField = $md5Name . '|' . $realName;
                    $uploadDir       = str_replace('htdocs/setup.php', '', $_SERVER['SCRIPT_FILENAME']) . 'upload';
                    // Copy file
                    $originPath = $this->_p5RootPath . '\\' . PHPR_DOC_PATH . '\\' . $currentFileName;
                    $targetPath = $uploadDir . '\\' . $md5Name;
                    copy($originPath, $targetPath);
                }
            }

            // Process description
            $description  = utf8_encode($item["note"]) . chr(10) . chr(10);
            $description .= $item["solution"];

            // Process status
            $status = $item["status"];
            if (is_numeric($status)) {
                $statusNumber = (int) $status;
                if ($statusNumber == 7) {
                    $statusNumber = self::HELPDESK_STATUS_OPEN;
                    // Informar al usuario, pasa 1 sola vez en el big script?
                }
            } else if (!empty($status)) {
                // It is apparently a descriptive string
                switch ($status) {
                    case 'erfolgreich geschlossen':
                        // Succesfully closed
                        $statusNumber = self::HELPDESK_STATUS_CLOSED;
                        break;
                    case 'erfolglos geschlossen':
                        // Unsuccessfully closed
                        $statusNumber = self::HELPDESK_STATUS_CLOSED;
                        break;
                    case 'in Bearbeitung':
                        // In treatment
                        $statusNumber = self::HELPDESK_STATUS_ASSIGNED;
                        break;
                    case 'neu':
                    default:
                        // New
                        $statusNumber = self::HELPDESK_STATUS_OPEN;
                        break;
                }
            } else {
                $statusNumber = self::HELPDESK_STATUS_OPEN;
            }

            // Process due date
            $dueDate = $item["due_date"];
            if (!empty($dueDate)) {
                // Just in case
                $dueDate = Cleaner::sanitize('date', $dueDate);
            } else {
                $dueDate = null;
            }

            // Process author
            // Default value:
            $authorId = $ownerId;
            if (isset($item["autor"]) && !empty($item["autor"])) {
                // It is apparently an email - Search for the Id
                $author  = $item["autor"];
                $query   = sprintf("SELECT ID FROM " . PHPR_DB_PREFIX . "users WHERE email = '%s'", $author);
                $userIds = $this->_dbOrig->query($query)->fetchAll();
                if (count($userIds > 0)) {
                    $oldAuthorId = $userIds[0]["ID"];
                    if (isset($this->_users[$oldAuthorId])) {
                        $authorId = $this->_users[$oldAuthorId];
                    }
                }
            }

            // Process P5 'solving' fields: 'solved' and 'solve_date'
            $solvedBy = $item["solved"];
            if (isset($this->_users[$solvedBy])) {
                $solvedBy = $this->_users[$solvedBy];
            } else {
                $solvedBy = null;
            }
            if (!empty($item["solve_time"])) {
                $solvedDate = $this->_longDateToShortDate($item["solve_time"]);
            } else {
                $solvedDate = null;
            }

            // Process contact
            $contact = (int) $item["contact"];
            if (isset($this->_contacts[$contact])) {
                $contact = $this->_contacts[$contact];
            } else {
                $contact = null;
            }

            $data = array("project_id"  => $projectId,
                          "owner_id"    => $ownerId,
                          "title"       => utf8_encode($item["name"]),
                          "assigned"    => $assignedId,
                          "date"        => $date,
                          "priority"    => $priority,
                          "attachments" => $attachmentField,
                          "description" => $description,
                          "status"      => $statusNumber,
                          "due_date"    => $dueDate,
                          "author"      => $authorId,
                          "solved_by"   => $solvedBy,
                          "solved_date" => $solvedDate,
                          "contact_id"  => $contact);

            // Insert data
            $itemId = $this->_tableManager->insertRow('helpdesk', $data);

            // Migrate permissions
            $item["ID"] = $itemId;
            $this->_migratePermissions('Helpdesk', $item);
        }
    }

    /**
     * Converts the old time format (hhmm) to a time format (hh:mm:ss)
     *
     * @param string $stringTime
     *
     * @return time
     */
    private function _stringToTime($stringTime)
    {
        $returnNull = false;

        if (strlen($stringTime) > 4) {
            // Fix wrong data as P5 shows it to the user
            $stringTime = substr($stringTime, 0, 4);
            if ((int) $stringTime > 2400) {
                // I haven't check this possibility in P5, this is my criterion
                $stringTime = "2400";
            }
        }

        // Fill the time with zeros at the left until it is reached 4 positions. E.g.: 400 -> 0400
        $stringTime = str_repeat("0", 4 - strlen($stringTime)) . $stringTime;
        if ((strlen($stringTime)  == 4) && (is_numeric($stringTime))) {
            $hour    = substr($stringTime, 0, 2);
            $minutes = substr($stringTime, 2, 2);
        } else {
            $returnNull = true;
        }

        if (!$returnNull) {
            // Fix wrong data in the way that P5 would show this wrong data to the user
            if ((int) $minutes > 59) {
                // Add to $hours as many hours as exceeded minutes (60 exceeded minutes = 1 hour)
                $addHours = floor((int) $minutes / 60);
                $hour     = (string) ((int) $hour + $addHours);
                // Take out equivalent extra minutes
                $minutes  = (string) ((int) $minutes - ($addHours * 60));
                // Add zeros at the left to reach 2 positions in total for each string variable
                $hour     = str_repeat("0", 2 - strlen($hour)) . $hour;
                $minutes  = str_repeat("0", 2 - strlen($minutes)) . $minutes;
            }
            $time = $hour . ":" . $minutes . ":00";
        } else {
            $time = null;
        }

        return $time;
    }

    /**
     * Converts the P5 datetime format YYYYMMDDHHMMSS to P6 date format YYYY-MM-DD
     *
     * @param string $date  Date & time in YYYYMMDDHHMMSS format
     *
     * @return string  Date in YYYY-MM-DD format
     */
    private function _longDateToShortDate($date)
    {
        if (strlen($date) == 14) {
            $year    = substr($date, 0, 4);
            $month   = substr($date, 4, 2);
            $day     = substr($date, 6, 2);
            $dateOut = $year . "-" . $month . "-" . $day;
            $dateOut = Cleaner::sanitize('date', $dateOut);
        } else {
            $dateOut = null;
        }
        return $dateOut;
    }

    private function _getModuleId($module)
    {
        $select = $this->_db->select()
                            ->from('module')
                            ->where('name = ?', $module);

        $stmt = $this->_db->query($select);
        $rows = $stmt->fetchAll();

        if (isset($rows[0])) {
            return $rows[0]['id'];
        } else {
            return 0;
        }
    }

    /**
     * Migrates the permission from PHProjekt 5.x version to PHProjekt 6.0 according to a criteria sent by email
     * and approved by the team
     *
     * @param string $module  Module to grant permissions to: Project / Note / Todo / Filemanager (P5 dateien)
     * @param array  $item    Item data
     *
     * @return void
     */
    private function _migratePermissions($module, $item)
    {
        // @todo: Permission migration for big DBs has just been tested for Project module.

        $projectPermFound = false;
        $userRightsAdd    = array();

        // The given permissions accord with the content of 'acc_write' field of 'projekte' table
        if ($item['acc_write'] == 'w') {
            $access = $this->_accessWrite;
        } else {
            $access = $this->_accessRead;
        }

        if ($module == 'Project') {
            // 1 - Fetch user permissions from 'project_users_rel'
            $query = sprintf("SELECT * FROM %sproject_users_rel WHERE project_ID = %d ORDER BY id", PHPR_DB_PREFIX,
                $item["ID"]);
            $projUserRels = $this->_dbOrig->query($query)->fetchAll();

            foreach ($projUserRels as $projUserRel) {
                // Discard rows with wrong or non-existing user_ID values
                $oldUserId = $projUserRel['user_ID'];
                if (empty($oldUserId) || !isset($this->_users[$oldUserId])) {
                    continue;
                }
                $userId = $this->_users[$oldUserId];

                // Discard repeated users
                if (!array_key_exists($userId, $userRightsAdd)) {
                    $projectPermFound       = true;
                    $userRightsAdd[$userId] = $access;
                }
            }
        }

        // If module is not Project, or if it is so and no permissions were found in table 'project_users_rel' then use
        // the contents of field 'acc' of the module
        if ($module != 'Project' || !$projectPermFound) {
            $userIds = $this->_getUsersFromAccField($item);
            foreach ($userIds as $userId) {
                // Avoid duplicate entries
                if (!array_key_exists($userId, $userRightsAdd)) {
                    $userRightsAdd[$userId] = $access;
                }
            }
        }

        $userVon = $item['von'];

        if ($module == 'Todo') {
            // Assigned user: 'ext' field -  Give write access to assigned user, if any
            if (!empty($item['ext'])) {
                $assignedId                 = $item['ext'];
                $userRightsAdd[$assignedId] = $this->_accessWrite;
            }
        } elseif ($module == 'Helpdesk') {
            // Give write access to assigned user, if any. 'assigned' field
            if (!empty($item['assigned'])) {
                $oldAssignedId = $item['assigned'];
                if (isset($this->_users[$oldAssignedId])) {
                    $assignedId                 = $this->_users[$oldAssignedId];
                    $userRightsAdd[$assignedId] = $this->_accessWrite;
                }
            }
        }

        // Add owner with Admin access. This may overwrite previous right assignment for owner, that's ok.
        $userRightsAdd[$userVon] = $this->_accessAdmin;

        // Add admin user with Admin access. This may overwrite previous right assignment for admin, that's ok.
        $userRightsAdd[self::USER_ADMIN] = $this->_accessAdmin;

        // Migrate each permission
        $moduleId = $this->_getModuleId($module);
        $itemId   = $item["ID"];
        $this->_addItemRights($moduleId, $itemId, $userRightsAdd);
    }

    /**
     * Returns a list of users according to the value received that corresponds to 'acc' field of 'projekte' table.
     *
     * @param array $item  Array with data of the module item
     *
     * @return array List of users
    */
    private function _getUsersFromAccField($item) {
        $userList = array();

        if (isset($item['acc'])) {
            $acc = $item['acc'];
        } elseif (isset($item['acc_read'])) {
            $acc = $item['acc_read'];
        }

        if (substr($acc, 0, 2) == 'a:') {
            // It is a serialized array of user ids
            $tmpAcc = unserialize($acc);
            foreach ($tmpAcc as $kurz) {
                // User exists?
                if (array_key_exists($kurz, $this->_userKurz)) {
                    $userId = $this->_userKurz[$kurz];
                    // Avoid duplicate entries
                    if (!in_array($userId, $userList)) {
                        $userList[] = $userId;
                    }
                }
            }
        } elseif ($acc == 'group') {
            // It is just the string 'group' so the users are the ones inside the group whose id is in 'gruppe' field.
            $group = $item['gruppe'];
            if (isset($this->_groupsUsers[$group])) {
                $userList = $this->_groupsUsers[$group];
            }
        } elseif (is_numeric($acc)) {
            // Just a user id
            $oldUserId = (int) $acc;
            // User exists?
            if (isset($this->_users[$oldUserId])) {
                $userList[] = $this->_users[$oldUserId];
            }
        } elseif ($acc == 'private') {
            // Just give permission for 'von' user, which will be given later by default
        } elseif ($acc == '(N;)') {
            // No permission - Just the User id #1 of this migration and the 'von' (owner) will have access to it
        } else {
            // All other content is supposed to be wrong -nothing to do
        }

        return $userList;
    }

    /**
     * Converts content of P5 recurrence field 'serie_typ' of 'termine' table to P6 format for 'rrule' field of
     * 'calendar' table.
     *
     * @param string $value  Recurrence parameters in P5 format
     *
     * @return string Recurrence parameters in P6 format
     */
    private function _serietypToRrule($value, $endDate, $startTime) {
        $until = 'UNTIL=' . str_replace('-', '', $endDate) . 'T' . str_replace(':', '', $startTime) . 'Z;';
        if (substr($value, 0, 2) == 'a:' && strpos($value, 'weekday')) {
            // Serialized array
            $value = unserialize($value);
            $returnValue ="ERROR on migration";

            // Frequency
            switch ($value['typ']) {
                // Daily
                case 'd':
                case 'd1':
                    $returnValue = 'FREQ=DAILY;' . $until . 'INTERVAL=1;';
                    break;
                // Weekly
                case 'w':
                case 'w1':
                    $returnValue = 'FREQ=WEEKLY;' . $until . 'INTERVAL=1;';
                    break;
                // Every 2 weeks
                case 'w2':
                    $returnValue = 'FREQ=WEEKLY;' . $until . 'INTERVAL=2;';
                    break;
                // Every 3 weeks
                case 'w3':
                    $returnValue = 'FREQ=WEEKLY;' . $until . 'INTERVAL=3;';
                    break;
                // Every 4 weeks
                case 'w4':
                    $returnValue = 'FREQ=WEEKLY;' . $until . 'INTERVAL=4;';
                    break;
                // Monthly
                case 'm':
                case 'm1':
                    $returnValue = 'FREQ=MONTHLY;' . $until . 'INTERVAL=1;';
                    break;
                // Annually
                case 'y':
                case 'y1':
                    $returnValue = 'FREQ=YEARLY;' . $until . 'INTERVAL=1;';
                    break;
            }


            // Weeks days
            $returnValue .= 'BYDAY=';
            $weeksDays = array( 0 => 'MO', 1 => 'TU', 2 => 'WE', 3 => 'TH',
                                4 => 'FR', 5 => 'SA', 6 => 'SU');
            if (isset($value['weekday']) && !empty($value['weekday'])) {
                $i = 0;
                foreach ($value['weekday'] as $day => $tmp) {
                    if ($i > 0) {
                        $returnValue .= ",";
                    }
                    $returnValue .= $weeksDays[$day];
                    $i++;
                }
            }
        } else if (strlen($value) <= 2) {
            // String mode, e.g.: 'd', 'w2'
            switch (substr($value, 0, 1)) {
                case 'd':
                default:
                    $returnValue = 'FREQ=DAILY;' . $until;
                    break;
                case 'w':
                    $returnValue = 'FREQ=WEEKLY;' . $until;
                    break;
                case 'm':
                    $returnValue = 'FREQ=MONTHLY;' . $until;
                    break;
                case 'y':
                    $returnValue = 'FREQ=YEARLY;' . $until;
                    break;
            }
            if (strlen($value > 1)) {
                $interval = substr($value, 1, 1);
            } else {
                $interval = '1';
            }
            $returnValue .= 'INTERVAL=' . $interval . ';';

            // Week day doesn't seem to be used in this case.
            $returnValue .= 'BYDAY=';

        } else {
            $returnValue = '';
        }

        return $returnValue;
    }

    /**
     * Inserts into 'item_rights' table the permissions according to received parameters
     *
     * @param int   $moduleId    Id of the module in 'module' table
     * @param int   $itemId      Id of the item in the table of the module
     * @param array $userRights  List of users and righes: the keys are the user ids and the value for each key is the
     *                           right for that user.
     *
     * @return void
     */
    private function _addItemRights($moduleId, $itemId, $userRights)
    {
        foreach ($userRights as $user => $rights) {
            $this->_tableManager->insertRow('item_rights', array(
                'module_id' => $moduleId,
                'item_id'   => $itemId,
                'user_id'   => $user,
                'access'    => $rights
                ));
        }
    }

    /**
     * Process the received owner for an item, if it is a non-existing user, returns the admin id, otherwise returns
     * the matching P6 user for this P5 one.
     *
     * @param int $oldId  Id of the owner user ('von' field) as it is in the original P5 item.
     *
     * @return int  User id to insert in P6 table owner field.
     */
    private function _processOwner($oldId) {
        if (!isset($this->_users[$oldId])) {
            // Non existing users will be migrated as 'admin' user
            $userId = self::USER_ADMIN;
        } else {
            $userId = $this->_users[$oldId];
        }
        return $userId;
    }

    /**
     * Process the received parent project Id for an item, if it is a non-existing one, returns the root project id,
     * otherwise returns the matching P6 project Id for this P5 one.
     *
     * @param int $oldId  Id of the P5 parent project Id as it is in the original P5 item.
     *
     * @return int  User id to insert in P6 field 'project_id'
     */
    private function _processParentProjId($oldId) {
        if (!isset($this->_projects[$oldId])) {
            // Non existing projects will be migrated as root one
            $projectId = self::PROJECT_ROOT;
        } else {
            $projectId = $this->_projects[$oldId];
        }
        return $projectId;
    }
}
