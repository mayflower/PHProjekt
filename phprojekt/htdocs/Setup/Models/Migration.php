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
 * @author     Gustavo Solt <solt@mayflower.de>s
 */
class Setup_Models_Migration
{
    /**
     * Array with P5 info
     *
     * @var array
     */
    private $_groups   = array();
    private $_userKurz = array();
    private $_timeZone = array();

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
        $this->_migrateTimeCard();
        $this->_migrateCalendars();
        $this->_migrateFilemanagers();
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
            throw new Exception("Sorry, but it is not possible to migrate PHProjekt minor than 5.0");
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
        // Group migration
        $groups = $this->_dbOrig->query("SELECT * FROM " . PHPR_DB_PREFIX . "gruppen")->fetchAll();

        foreach ($groups as $group) {
            $this->_groups[$group["ID"]] = array();
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
        $users = $this->_dbOrig->query("SELECT * FROM " . PHPR_DB_PREFIX . "users")->fetchAll();

        foreach ($users as $user) {
            if ($user["loginname"] != "root" && $user["loginname"] != "test") {
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

                $this->_tableManager->insertRow('user', array(
                    'id'        => (int) $user["ID"],
                    'username'  => $username,
                    'firstname' => $user["vorname"],
                    'lastname'  => $user["nachname"],
                    'status'    => $status,
                    'admin'     => 0
                ));

                $this->_timeZone[$user["ID"]] = 2;
                $language                     = 'en';

                @$settings = unserialize($user["settings"]);
                if (is_array($settings)) {
                    if (isset($settings["timezone"])) {
                        $this->_timeZone[$user["ID"]] = $settings["timezone"];
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
                    'user_id'    => (int) $user["ID"],
                    'module_id'  => 0,
                    'key_value'  => 'password',
                    'value'      => $password,
                    'identifier' => 'Core'
                ));

                $this->_tableManager->insertRow('setting', array(
                    'id'         => null,
                    'user_id'    => (int) $user["ID"],
                    'module_id'  => 0,
                    'key_value'  => 'email',
                    'value'      => $user["email"],
                    'identifier' => 'Core'
                ));

                $this->_tableManager->insertRow('setting', array(
                    'id'         => null,
                    'user_id'    => (int) $user["ID"],
                    'module_id'  => 0,
                    'key_value'  => 'language',
                    'value'      => $language,
                    'identifier' => 'Core'
                ));

                $this->_tableManager->insertRow('setting', array(
                    'id'         => null,
                    'user_id'    => (int) $user["ID"],
                    'module_id'  => 0,
                    'key_value'  => 'timeZone',
                    'value'      => $this->_timeZone[$user["ID"]],
                    'identifier' => 'Core'
                ));

                $this->_tableManager->insertRow('item_rights', array(
                    'module_id' => $this->_getModuleId('Project'),
                    'item_id'   => 1,
                    'user_id'   => (int) $user["ID"],
                    'access'    => 255
                ));
            }

            $this->_userKurz[$user['kurz']] = (int) $user['ID'];
        }
    }

    /**
     * Migrate P5 group-users relations
     *
     * @return void
     */
    private function _migrateGroupsUserRelations()
    {
        // User group
        $userGroups = $this->_dbOrig->query("SELECT * FROM " . PHPR_DB_PREFIX . "grup_user")->fetchAll();

        foreach ($userGroups as $userGroup) {
            $this->_tableManager->insertRow('groups_user_relation', array(
                'groups_id' => (int) $userGroup["grup_ID"],
                'user_id'   => (int) $userGroup["user_ID"]
            ));

            $this->_groups[(int) $userGroup["grup_ID"]] = (int) $userGroup["user_ID"];
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
        $projects = $this->_dbOrig->query("SELECT * FROM " . PHPR_DB_PREFIX . "projekte ORDER BY ID")->fetchAll();

        $paths    = array();
        $paths[1] = "/1/";

        foreach ($projects as $project) {
            $project["ID"] = $this->_convertProjectId($project["ID"]);
            switch ($project["parent"]) {
                case 0:
                    $project["parent"] = 1;
                    $project["path"]   = $paths[1] . $project["ID"] . "/";
                    break;
                case 1:
                    $project["parent"] = 10001;
                    $project["path"]   = $paths[10001] . $project["ID"] . "/";
                    break;
                default:
                    $parent          = $project["parent"];
                    $project["path"] = $paths[$parent] . $project["ID"] . "/";
                    break;
            }
            $id         = $project["ID"];
            $paths[$id] = $project["path"];
            $tmpStatus  = $project["kategorie"];
            if (!empty($statusConversion[$tmpStatus])) {
                $tmpStatus = $statusConversion[$tmpStatus];
            } else {
                $tmpStatus = $statusConversion[3];
            }

            $this->_tableManager->insertRow('project', array(
                'id'               => (int) $project["ID"],
                'project_id'       => (int) $project["parent"],
                "path"             => $project["path"],
                "title"            => $project["name"],
                "notes"            => $project["note"],
                "owner_id"         => (int) $project["von"],
                "start_date"       => $project["anfang"],
                "end_date"         => $project["ende"],
                "priority"         => (int) $project["wichtung"],
                "current_status"   => (int) $tmpStatus,
                "complete_percent" => $project["status"],
                "hourly_wage_rate" => $project["stundensatz"],
                "budget"           => $project["budget"]));

            $this->_tableManager->insertRow('item_rights', array(
                'module_id' => $this->_getModuleId('Project'),
                'item_id'   => (int) $project["ID"],
                'user_id'   => 1,
                'access'    => 255
            ));

            $this->_migratePermissions('Project', $project["ID"], $project["acc"], 255, $project["gruppe"]);

            for ($i = 1; $i < 12; $i++) {
                $this->_tableManager->insertRow('project_module_permissions', array(
                    'module_id'  => $i,
                    'project_id' => (int) $project["ID"]
                ));
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
        // Todo
        $todos = $this->_dbOrig->query("SELECT * FROM " . PHPR_DB_PREFIX . "todo ORDER BY ID")->fetchAll();

        foreach ($todos as $todo) {
            $todo["project"] = $this->_convertProjectId($todo["project"]);
            $this->_tableManager->insertRow('todo', array(
                'id'             => (int) $todo["ID"],
                'project_id'     => $todo["project"],
                "title"          => $todo["remark"],
                "notes"          => $todo["note"],
                "owner_id"       => (int) $todo["von"],
                "start_date"     => $todo["anfang"],
                "end_date"       => $todo["deadline"],
                "priority"       => (int) $todo["priority"],
                "current_status" => (int) $todo["status"]
            ));

            $this->_tableManager->insertRow('item_rights', array(
                'module_id' => $this->_getModuleId('Todo'),
                'item_id'   => (int) $todo["ID"],
                'user_id'   => 1,
                'access'    => 255
            ));

            $this->_migratePermissions('Todo', $todo["ID"], $todo["acc"], 255, $todo["gruppe"]);
        }
    }

    /**
     * Migrate P5 notes
     *
     * @return void
     */
    private function _migrateNotes()
    {
        // Notes
        $notes = $this->_dbOrig->query("SELECT * FROM " . PHPR_DB_PREFIX . "notes ORDER BY ID")->fetchAll();

        foreach ($notes as $note) {
            $note["projekt"] = $this->_convertProjectId($note["projekt"]);
            $this->_tableManager->insertRow('note', array(
                'id'         => (int) $note["ID"],
                'project_id' => (int) $note["projekt"],
                "title"      => $note["name"],
                "comments"   => $note["remark"],
                "owner_id"   => (int) $note["von"],
                "category"   => $note["kategorie"]
            ));

            $this->_tableManager->insertRow('item_rights', array(
                'module_id' => $this->_getModuleId('Note'),
                'item_id'   => (int) $note["ID"],
                'user_id'   => 1,
                'access'    => 255
            ));

            $this->_migratePermissions('Note', $note["ID"], $note["acc"], 255, $note["gruppe"]);
        }
    }

    /**
     * Migrate P5 timecard and timeproj
     *
     * @return void
     */
    private function _migrateTimeCard()
    {
        // Timeproj
        $timeprojs = $this->_dbOrig->query("SELECT * FROM " . PHPR_DB_PREFIX . "timeproj ORDER BY ID")->fetchAll();

        foreach ($timeprojs as $timeproj) {
            $timeproj["projekt"] = $this->_convertProjectId($timeproj["projekt"]);
            $this->_tableManager->insertRow('timeproj', array(
                'id'         => (int) $timeproj["ID"],
                'notes'      => $timeproj["note"],
                "owner_id"   => (int) $timeproj["users"],
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
            $this->_tableManager->insertRow('timecard', array(
                'id'         => (int) $timecard["ID"],
                "owner_id"   => (int) $timecard["users"],
                "date"       => $timecard["datum"],
                "start_time" => $timecard["anfang"],
                "end_time"   => $timecard["ende"]
            ));
        }
    }

    /**
     * Migrate P5 events
     *
     * @return void
     */
    private function _migrateCalendars()
    {
        // Calendar
        $events = $this->_dbOrig->query("SELECT * FROM " . PHPR_DB_PREFIX . "termine ORDER BY ID")->fetchAll();

        foreach ($events as $calendar) {
            $calendar["projekt"] = $this->_convertProjectId($calendar["projekt"]);
            $calendar["anfang"]  = $this->_stringToTime($calendar["anfang"]);
            $calendar["ende"]    = $this->_stringToTime($calendar["ende"]);
            $timezone            = (isset($this->_timeZone[$calendar["ID"]])) ? $this->_timeZone[$calendar["ID"]] : 2;
            $uid                 = (!empty($calendar["serie_id"])) ? $calendar["serie_id"] : md5($calendar["datum"]
                . (int) $calendar["an"] . time() . rand());
            $this->_tableManager->insertRow('calendar', array(
                'id'             => (int) $calendar["ID"],
                "parent_id"      => (int) $calendar["parent"],
                "owner_id"       => (int) $calendar["von"],
                "project_id"     => (int) $calendar["projekt"],
                "title"          => $calendar["event"],
                "place"          => $calendar["ort"],
                "notes"          => $calendar["remark"],
                "uid"            => $uid,
                "start_date"     => $calendar["datum"],
                "start_time"     => $calendar["anfang"],
                "end_date"       => $calendar["datum"],
                "end_time"       => $calendar["ende"],
                "timezone"       => $timezone,
                "location"       => $calendar["ort"],
                "categories"     => "",
                "attendee"       => $calendar["an"],
                "priority"       => $calendar["priority"],
                "rrule"          => "",
                "properties"     => "",
                "participant_id" => (int) $calendar["an"]
            ));

            $this->_tableManager->insertRow('item_rights', array(
                'module_id' => $this->_getModuleId('Calendar'),
                'item_id'   => (int) $calendar["ID"],
                'user_id'   => 1,
                'access'    => 255
            ));

            if ($calendar["an"] != 1) {
                $this->_tableManager->insertRow('item_rights', array(
                    'module_id' => $this->_getModuleId('Calendar'),
                    'item_id'   => (int) $calendar["ID"],
                    'user_id'   => (int) $calendar["an"],
                    'access'    => 255
                ));
            }
        }
    }

    /**
     * Migrate P5 files
     *
     * @return void
     */
    private function _migrateFilemanagers()
    {
        // Filemanager
        $files = $this->_dbOrig->query("SELECT * FROM " . PHPR_DB_PREFIX . "dateien ORDER BY ID")->fetchAll();

        foreach ($files as $file) {
            $file["div2"] = $this->_convertProjectId($file["div2"]);
            $newFilename  = md5($file["tempname"]);

            $uploadDir = ereg_replace('htdocs/setup.php', '', $_SERVER['SCRIPT_FILENAME']) . 'upload';
            copy(PHPR_FILE_PATH . "\\" . $file["tempname"], $uploadDir . "\\" . $newFilename);

            $this->_tableManager->insertRow('filemanager', array(
                'id'         => (int) $file["ID"],
                'owner_id'   => (int) $file["von"],
                "title"      => $file["filename"],
                "comments"   => $file["remark"],
                "project_id" => (int) $file["div2"],
                "files"      => $newFilename . "|" . $file["filename"]
            ));

            $this->_tableManager->insertRow('item_rights', array(
                'module_id' => $this->_getModuleId('Filemanager'),
                'item_id'   => (int) $file["ID"],
                'user_id'   => 1,
                'access'    => 255
            ));

            $this->_migratePermissions('Filemanager', $file["ID"], $file["acc"], 255, $file["gruppe"]);
        }
    }

    /**
     * Migrates the permission from PHProjekt 5.x version to PHProjekt 6.0
     *
     * @param int   $moduleId Module to grant permissions
     * @param int   $itemId   Item to set the permission
     * @param mixed $users    Serialized array of users or group
     * @param int   $access   Access value
     * @param int   $group    Group of the project
     *
     * @return void
     */
    private function _migratePermissions($module, $itemId, $users, $access, $group)
    {
        $userList     = array();
        @$tmpUserList = unserialize($users);

        if (is_array($tmpUserList)) {
            foreach ($tmpUserList as $kurz) {
                $userList[] = $this->_userKurz[$kurz];
            }
        } elseif ($users == 'group') {
            if (isset($this->_userGroups[$group])) {
                $userList = $this->_userGroups[$group];
            }
        } elseif (is_int($users)) {
            $userList[] = $users;
        }

        foreach ($userList as $oneUser) {
            $this->_tableManager->insertRow('item_rights', array(
                'module_id' => (int) $this->_getModuleId($module),
                'item_id'   => (int) $itemId,
                'user_id'   => (int) $oneUser,
                'access'    => (int) $access
            ));
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
        $time = null;

        if (strlen($stringTime) == 3) {
            $time = substr($stringTime, 0, 1) . ":" . substr($stringTime, 1, 2) . ":00";
        } elseif ((strlen($stringTime)  == 4) && (is_numeric($stringTime))) {
            $time = substr($stringTime, 0, 2) . ":" . substr($stringTime, 2, 2) . ":00";
        }

        return $time;
    }

    /**
     * Converts the old project Id to a Phprojekt 6 ID
     *
     * @param integer $oldprojectId
     *
     * @return integer
     */
    private function _convertProjectId($oldprojectId)
    {
        if ($oldprojectId == 1) {
            return 10001;
        } elseif (empty($oldprojectId)) {
            return 1;
        }

        return $oldprojectId;
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
}
