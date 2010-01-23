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
    private $_groupsUsers = array();
    private $_modules     = array();

    /**
     * Arrays with P6 info: the key of each array position is the P5 id, and the value the P6 id, they are used for
     * conversions.
     * E.g.: $this->_users[<oldId>] = <newId>
     *       $this->_users[50] = 40  (this could be because of id reordering)
     *
     * @var array
     */
    private $_users     = array();
    private $_groups    = array();
    private $_projects  = array();
    private $_calendars = array();
    private $_contacts  = array();
    private $_todos     = array();
    private $_helpdesk  = array();

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
     * Permissions values
     *
     * @var array
     */
    private $_dbItemRightValues = array();

    /**
     * Keep the project permissions values
     *
     * @var array
     */
    private $_dbProjectItemRightValues = array();

    /**
     * Root path, taken out from the config file path
     *
     * @var string
     */
    private $_p5RootPath = null;

    /**
     * Search vlaues
     *
     * @var array
     */
    private $_searchWord    = array();
    private $_searchDisplay = array();

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

    // Pagination
    const ROWS_PER_QUERY = 5000;

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
        $this->_accessRead  = Phprojekt_Acl::NONE | Phprojekt_Acl::READ | Phprojekt_Acl::ACCESS
            | Phprojekt_Acl::DOWNLOAD;
        $this->_accessWrite = $this->_accessRead | Phprojekt_Acl::WRITE | Phprojekt_Acl::CREATE | Phprojekt_Acl::COPY
            | Phprojekt_Acl::DELETE;
        $this->_accessAdmin = $this->_accessWrite | Phprojekt_Acl::ADMIN;

        // Set P5 root path
        $pos               = strpos($file, 'config.inc.php');
        $this->_p5RootPath = substr($file, 0, $pos);

        // Set modules
        $this->_setModules();
    }

    /**
     * Call all the migration functions
     *
     * @return void
     */
    public function migrateTables()
    {
        $this->_migrateUsers();
        $this->_migrateGroups();
        $this->_migrateGroupsUserRelations();
        $this->_migrateProjects();
        $this->_migrateTodos();
        $this->_migrateNotes();
        $this->_migrateCalendar();
        $this->_migrateFilemanager();
        $this->_migrateContacts();
        $this->_migrateHelpdesk();
        $this->_migrateTimeCard();

        $this->_executeItemRightsInsert();
        $this->_executeSearchDisplayInsert();
        $this->_executeSearchWordsInsert();
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
             $this->_dbOrig = Zend_Db::factory('pdo_' . PHPR_DB_TYPE, $dbParams);
        } catch (Exception $error) {
            throw new Exception('Can not connect to server at ' . PHPR_DB_HOST
                . ' using ' . PHPR_DB_USER . ' user ' . '(' . $error->getMessage() . ')');
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
        $query = "SELECT * FROM " . PHPR_DB_PREFIX . "users";
        $users = $this->_dbOrig->query($query)->fetchAll();

        // Just in case
        $this->_users[self::USER_ADMIN] = self::USER_ADMIN;

        // Multiple inserts
        $dbFields = array('id', 'user_id', 'module_id', 'key_value', 'value', 'identifier');
        $dbValues = array();

        $moduleId = $this->_getModuleId('Project');

        foreach ($users as $user) {
            $loginName = $user['loginname'];
            $firstName = $this->_fix($user['vorname'], 255);
            $lastName  = $this->_fix($user['nachname'], 255);

            if ($loginName != "root" && $loginName != "test") {
                switch (PHPR_LOGIN_SHORT) {
                    case '2':
                        $username = $user['loginname'];
                        break;
                    case '1':
                        $username = $user['kurz'];
                        break;
                    default:
                        $username = $user['nachname'];
                        break;
                }

                // Set random username for wrong values
                if (empty($username)) {
                    $username = md5(uniqid(rand(), 1));
                }

                if ($user['status'] == 0) {
                    $status = "A";
                } else {
                    $status = 'I';
                }

                $userId = $this->_tableManager->insertRow('user', array(
                    'username'  => $this->_fix($username),
                    'firstname' => $firstName,
                    'lastname'  => $lastName,
                    'status'    => $status,
                    'admin'     => 0
                ));

                // Add permission for this user to root project
                $userRightsAdd = array($userId => $this->_accessAdmin);
                $this->_addItemRights($moduleId, self::PROJECT_ROOT, $userRightsAdd);
            } else {
                // Don't migrate 'root' and 'test' users themselves, they are replaced by already inserted 'admin' and
                // 'test' although but its attributes will be migrated indeed.
                if ($loginName == 'root') {
                    $userId = self::USER_ADMIN;
                } elseif ($loginName == 'test') {
                    $userId = self::USER_TEST;
                }

                // Update name fields
                $data  = array('firstname' => $firstName,
                               'lastname'  => $lastName);
                $where = sprintf("id = %d", $userId);
                $this->_tableManager->updateRows('user', $data, $where);
            }

            // Migrate password for all users except for P5 'root'
            if ($loginName != 'root') {
                if (defined("PHPR_VERSION") && PHPR_VERSION >= '5.2.1') {
                    $password = $user['pw'];
                } else {
                    $password = md5('phprojektmd5' . $username);
                }

                if ($loginName == 'test') {
                    // Update setting
                    $data  = array('value' => $password);
                    $where = sprintf("user_id = %d AND module_id = 0 and key_value = 'password' AND "
                        . "identifier = 'Core'", $userId);
                    $this->_tableManager->updateRows('setting', $data, $where);
                } else {
                    // Prepare setting
                    $dbValues[] = array(null, $userId, 0, 'password', $password, 'Core');
                }
            }

            $oldUserId                      = $user['ID'];
            $this->_users[$oldUserId]       = $userId;
            $this->_userKurz[$user['kurz']] = $userId;
            $this->_timeZone[$userId]       = "000";
            $language                       = 'en';

            @$settings = unserialize($user['settings']);
            if (is_array($settings)) {
                if (isset($settings['timezone'])) {
                    $this->_timeZone[$userId] = $this->_getP6TimeZone($settings['timezone']);
                }
                if (isset($settings['langua'])) {
                    $language = $settings['langua'];
                }
            }

            // Migrate rest of settings
            if ($loginName == 'root' || $loginName == 'test') {
                // Update them
                // Email
                if (is_null($user['email'])) {
                    $user['email'] = '';
                }
                $data  = array('value' => $user['email']);
                $where = sprintf("user_id = %d AND module_id = 0 and key_value = 'email' AND identifier = 'Core'",
                    $userId);
                $this->_tableManager->updateRows('setting', $data, $where);

                // Language
                $data  = array('value' => $language);
                $where = sprintf("user_id = %d AND module_id = 0 and key_value = 'language' AND identifier = 'Core'",
                    $userId);
                $this->_tableManager->updateRows('setting', $data, $where);

                // Time Zone
                $data  = array('value' => $this->_timeZone[$userId]);
                $where = sprintf("user_id = %d AND module_id = 0 and key_value = 'timeZone' AND identifier = 'Core'",
                    $userId);
                $this->_tableManager->updateRows('setting', $data, $where);

            } else {
                // Insert them
                // Email
                if (is_null($user['email'])) {
                    $user['email'] = '';
                }
                $dbValues[] = array(null, $userId, 0, 'email', $user['email'], 'Core');

                // Language
                $dbValues[] = array(null, $userId, 0, 'language', $language, 'Core');

                // Time Zone
                $dbValues[] = array(null, $userId, 0, 'timeZone', $this->_timeZone[$userId], 'Core');
            }
        }

        // Run the multiples insert
        if (!empty($dbValues)) {
            $this->_tableManager->insertMultipleRows('setting', $dbFields, $dbValues);
        }
    }

    /**
     * Collect all the P5 groups and migrate them
     *
     * @return void
     */
    private function _migrateGroups()
    {
        $groups = $this->_dbOrig->query("SELECT * FROM " . PHPR_DB_PREFIX . "gruppen")->fetchAll();

        // Multiple inserts
        $dbFields = array('module_id', 'project_id');
        $dbValues = array();

        foreach ($groups as $project) {
            $projectId = $this->_tableManager->insertRow('project', array(
                'path'             => "/1/",
                'project_id'       => 1,
                'title'            => $this->_fix($project['name']),
                'notes'            => null,
                'owner_id'         => self::USER_ADMIN,
                'start_date'       => null,
                'end_date'         => null,
                'priority'         => 1,
                'current_status'   => 1,
                'complete_percent' => 0,
                'hourly_wage_rate' => 0,
                'budget'           => 0));

            $this->_groupsUsers[$project['ID']] = array();
            $this->_groups[$project['ID']]      = $projectId;

            // Add search values
            $words  = array($this->_fix($project['name'], 255));
            $itemId = $project['ID'];
            $this->_addSearchDisplay(1, $itemId, 1, $words[0], '');
            $this->_addSearchWords(implode(" ", $words), 1, $itemId);

            foreach ($this->_modules as $moduleId) {
                $dbValues[] = array($moduleId, $projectId);
            }
        }

        // Run the multiple inserts
        if (!empty($dbValues)) {
            $this->_tableManager->insertMultipleRows('project_module_permissions', $dbFields, $dbValues);
        }
    }

    /**
     * Migrates P5 group-users relations and creates a two-dimensional array with a list of groups of P5 as the first
     * dimension and users as the second one: _groupsUsers
     *
     * @return void
     */
    private function _migrateGroupsUserRelations()
    {
        // User group
        $userGroups = $this->_dbOrig->query("SELECT * FROM " . PHPR_DB_PREFIX . "grup_user "
            . "ORDER BY grup_ID")->fetchAll();

        $lastGroup = 0;
        $first     = true;
        foreach ($userGroups as $userGroup) {
            $oldUserId  = $userGroup['user_ID'];
            $oldGroupId = $userGroup['grup_ID'];

            if ($lastGroup != $oldGroupId) {
                if (!$first) {
                    // Migrate each permission
                    $moduleId = $this->_getModuleId('Project');
                    $itemId   = $this->_groups[$lastGroup];

                    // Add admin user
                    $userRightsAdd[self::USER_ADMIN] = $this->_accessAdmin;

                    $this->_addItemRights($moduleId, $itemId, $userRightsAdd);
                }

                // New group
                $userRightsAdd = array();

                // Just in case group wasn't in 'gruppen' table (it should be),
                if (!isset($this->_groupsUsers[$oldGroupId])) {
                    $this->_groupsUsers[$oldGroupId] = array();
                }

                $lastGroup = $oldGroupId;
            }

            $first = false;
            // User exists?
            if (isset($this->_users[$oldUserId])) {
                $userId = $this->_users[$oldUserId];

                // Avoid duplicate entries
                if (!in_array($userId, $this->_groupsUsers[$oldGroupId])) {
                    // Add user to internal groupsUsers variable
                    $this->_groupsUsers[$oldGroupId][] = $userId;
                }

                $userRightsAdd[$userId] = $this->_accessAdmin;
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
        // Project migration
        $projects = $this->_dbOrig->query("SELECT * FROM " . PHPR_DB_PREFIX . "projekte ORDER BY id")->fetchAll();

        $paths                     = array();
        $paths[self::PROJECT_ROOT] = "/1/";
        $projectsNotMigrated       = -1;

        // Multiple inserts
        $dbFields = array('module_id', 'project_id');
        $dbValues = array();

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
                $oldProjectId = $project['ID'];
                if (empty($project['parent'])) {
                    $parentId = $this->_groups[$project['gruppe']];
                    if (!isset($paths[$parentId])) {
                        $paths[$parentId] = $paths[self::PROJECT_ROOT] . $parentId . "/";
                    }
                } else {
                    // Has parent project been processed?
                    $oldParentId = $project['parent'];
                    if (isset($this->_projects[$oldParentId])) {
                        // Yes
                        $parentId = $this->_projects[$oldParentId];
                    } else {
                        // No - Continue to the next iteration of the foreach structure, current project has to be
                        // processed later.
                        continue;
                    }
                }

                // Check and repair corrupted dates the way P5 would show them to the user: 2005-06-31 -> 2005-07-01
                $startDate = Cleaner::sanitize('date', $project['anfang']);
                $endDate   = Cleaner::sanitize('date', $project['ende']);

                $project['von'] = $this->_processOwner($project['von']);

                $projectId = $this->_tableManager->insertRow('project', array(
                    'path'             => $paths[$parentId],
                    'project_id'       => $parentId,
                    'title'            => $this->_fix($project['name'], 255),
                    'notes'            => $this->_fix($project['note'], 65500),
                    'owner_id'         => $project['von'],
                    'start_date'       => $startDate,
                    'end_date'         => $endDate,
                    'priority'         => (int) $project['wichtung'],
                    'current_status'   => (int) $project['kategorie'],
                    'complete_percent' => $project['status'],
                    'hourly_wage_rate' => $this->_fix($project['stundensatz'], 10),
                    'budget'           => $this->_fix($project['budget'], 10)));

                $this->_projects[$oldProjectId] = $projectId;
                $path                           = $paths[$parentId] . $projectId . "/";
                $paths[$projectId]              = $path;

                // Migrate permissions
                $project['ID']          = $projectId;
                $project['p6ProjectId'] = $parentId;
                $this->_migratePermissions('Project', $project);

                // Add search values
                $words  = array($this->_fix($project['name'], 255), $this->_fix($project['note'], 65500));
                $itemId = $project['ID'];
                $this->_addSearchDisplay(1, $itemId, $project['p6ProjectId'], $words[0], $words[1]);
                $this->_addSearchWords(implode(" ", $words), 1, $itemId);

                foreach ($this->_modules as $moduleId) {
                    $dbValues[] = array($moduleId, $projectId);
                }

                // Take out this project from the array
                unset($projects[$index]);
            }
        }

        // Run the multiple inserts
        if (!empty($dbValues)) {
            $this->_tableManager->insertMultipleRows('project_module_permissions', $dbFields, $dbValues);
        }
    }

    /**
     * Migrate P5 todos
     *
     * @return void
     */
    private function _migrateTodos()
    {
        $run   = true;
        $start = 0;
        $end   = self::ROWS_PER_QUERY;

        $userIdRelation = array();

        while ($run) {
            $todos = $this->_dbOrig->query("SELECT * FROM " . PHPR_DB_PREFIX . "todo ORDER BY ID LIMIT "
                . $start . ", " . $end)->fetchAll();
            if (empty($todos)) {
                $run = false;
            } else {
                $start = $start + $end;
            }

            // Multiple inserts
            $dbFields = array('project_id', 'title', 'notes', 'owner_id', 'priority', 'current_status', 'user_id',
                'start_date', 'end_date');
            $dbValues = array();

            foreach ($todos as $todo) {
                $projectId   = $this->_processParentProjId($todo['project'], $todo['gruppe']);
                $todo['von'] = $this->_processOwner($todo['von']);

                $todo['status'] = (int) $todo['status'];
                if ($todo['status'] < 2) {
                    $todo['status'] = 1;
                } else if ($todo['status'] > 5) {
                    $todo['status'] = 5;
                }

                // Process assigned user
                $oldAssignedId = $todo['ext'];
                $todo['ext']   = null;
                if (!empty($oldAssignedId) && is_numeric($oldAssignedId)) {
                    // The assigned user exists in the DB?
                    if (isset($this->_users[$oldAssignedId])) {
                        // Yes
                        $todo['ext'] = $this->_users[$oldAssignedId];
                    }
                }
                $userIdRelation[$todo['ID']] = $todo['ext'];

                // If dates are empty strings, don't send the fields; if not,
                // clean them and fix wrong values as P5 would
                // show them to the users
                if (!empty($todo['anfang'])) {
                    $startDate = Cleaner::sanitize('date', $todo['anfang']);
                } else {
                    $startDate = null;
                }
                if (!empty($todo['deadline'])) {
                    $endDate = Cleaner::sanitize('date', $todo['deadline']);
                } else {
                    $endDate = null;
                }

                $dbValues[] = array($projectId, $this->_fix($todo['remark']),
                    $this->_fix($todo['note'], 65500), $todo['von'], $todo['priority'], $todo['status'],
                    $todo['ext'], $startDate, $endDate);
            }

            // Run the multiple inserts
            if (!empty($dbValues)) {
                $ids      = $this->_tableManager->insertMultipleRows('todo', $dbFields, $dbValues, true);
                $moduleId = $this->_getModuleId('Todo');
                foreach ($todos as $todo) {
                    // Migrate permissions
                    $oldTodoId           = $todo['ID'];
                    $todo['ID']          = array_shift($ids);
                    $todo['von']         = $this->_processOwner($todo['von']);
                    $todo['ext']         = $userIdRelation[$oldTodoId];
                    $todo['p6ProjectId'] = $this->_processParentProjId($todo['project'], $todo['gruppe']);

                    $this->_todos[$oldTodoId] = $todo['ID'];
                    $this->_migratePermissions('Todo', $todo);

                    // Add search values
                    $words  = array($this->_fix($todo['remark']), $this->_fix($todo['note'], 65500));
                    $itemId = $todo['ID'];
                    $this->_addSearchDisplay($moduleId, $itemId, $todo['p6ProjectId'], $words[0], $words[1]);
                    $this->_addSearchWords(implode(" ", $words), $moduleId, $itemId);
                }
            }
        }
    }

    /**
     * Migrate P5 notes
     *
     * @return void
     */
    private function _migrateNotes()
    {
        $run   = true;
        $start = 0;
        $end   = self::ROWS_PER_QUERY;

        while ($run) {
            $notes = $this->_dbOrig->query("SELECT * FROM " . PHPR_DB_PREFIX . "notes ORDER BY ID LIMIT "
                . $start . ", " . $end)->fetchAll();
            if (empty($notes)) {
                $run = false;
            } else {
                $start = $start + $end;
            }

            // Multiple inserts
            $dbFields = array('project_id', 'title', 'comments', 'owner_id');
            $dbValues = array();

            foreach ($notes as $note) {
                $projectId   = $this->_processParentProjId($note['projekt'], $note['gruppe']);
                $note['von'] = $this->_processOwner($note['von']);

                $dbValues[] = array($projectId, $this->_fix($note['name'], 255), $this->_fix($note['remark'], 65500),
                    $note['von']);
            }

            // Run the multiple inserts
            if (!empty($dbValues)) {
                $ids = $this->_tableManager->insertMultipleRows('note', $dbFields, $dbValues, true);

                foreach ($notes as $note) {
                    // Migrate permissions
                    $note['von']         = $this->_processOwner($note['von']);
                    $note['ID']          = array_shift($ids);
                    $note['p6ProjectId'] = $this->_processParentProjId($note['projekt'], $note['gruppe']);
                    $this->_migratePermissions('Note', $note);

                    // Add search values
                    $moduleId = $this->_getModuleId('Note');
                    $words    = array($this->_fix($note['name'], 255), $this->_fix($note['remark'], 65500));
                    $itemId   = $note['ID'];
                    $this->_addSearchDisplay($moduleId, $itemId, $note['p6ProjectId'], $words[0], $words[1]);
                    $this->_addSearchWords(implode(" ", $words), $moduleId, $itemId);
                }
            }
        }
    }

    /**
     * Migrate P5 timeproj
     *
     * @return void
     */
    private function _migrateTimeCard()
    {
        // Multiple inserts
        $dbFields = array('owner_id', 'date', 'start_time', 'end_time', 'minutes', 'project_id', 'notes', 'module_id',
            'item_id');
        $dbValues = array();

        $currentDatum = '';
        $currentUser  = -1;
        $lastHour     = 8;
        $lastMinutes  = 0;

        $run   = true;
        $start = 0;
        $end   = self::ROWS_PER_QUERY;

        while ($run) {
            // Timeproj
            $query = "SELECT * FROM " . PHPR_DB_PREFIX . "timeproj ORDER BY datum, users, projekt LIMIT "
                . $start . ", " . $end;
            $timeprojs = $this->_dbOrig->query($query)->fetchAll();
            if (empty($timeprojs)) {
                $run = false;
            } else {
                $start = $start + $end;
            }

            foreach ($timeprojs as $timeproj) {
                $userId = $timeproj['users'];
                if (isset($this->_users[$userId])) {
                    if ($currentDatum != $timeproj['datum']) {
                        $currentDatum = $timeproj['datum'];
                        $lastHour     = 8;
                        $lastMinutes  = 0;
                    }
                    if ($currentUser != $userId) {
                        $lastHour    = 8;
                        $lastMinutes = 0;
                        $currentUser = $userId;
                    }

                    $timeproj['projekt'] = $this->_processParentProjId($timeproj['projekt'], 0);

                    // Fix wrong values the way P5 would show it to the users
                    if (empty($timeproj['h']) || $timeproj['h'] < 0) {
                        $timeproj['h'] = 0;
                    } else if ($timeproj['h'] > 24) {
                         // I don't know how P5 shows more than 24 hours in a day, but I suppose this is the right way
                         $timeproj['h'] = 24;
                         $timeproj['m'] = 0;
                    }
                    if (empty($timeproj['m']) || $timeproj['m'] < 0) {
                        $timeproj['m'] = 0;
                    }

                    $minutes = ($timeproj['h'] * 60) + $timeproj['m'];

                    if ($minutes == 0) {
                        continue;
                    }

                    $dateTime = strtotime($timeproj['datum']);
                    if ($dateTime === false || $dateTime === -1) {
                        continue;
                    } else {
                        $year  = date("Y", $dateTime);
                        $month = date("m", $dateTime);
                        $day   = date("d", $dateTime);

                        $timeproj['datum'] = $year . "-" . $month . "-" . $day;

                        $starTimeValue = mktime($lastHour, $lastMinutes, 0, $month, $day, $year);
                        $endTimeValue  = mktime($lastHour, $lastMinutes + $minutes, 0, $month, $day, $year);

                        // Check endTime
                        if (date("d", $endTimeValue) != $day) {
                            // Split into 2 days
                            $starTime = date("H:i:s", $starTimeValue);
                            $endTime  = "00:00:00";

                            list($moduleId, $itemId) = $this->_getItemAndModule($timeproj);

                            $dbValues[] = array($this->_users[$userId], $timeproj['datum'], $starTime, $endTime,
                                $minutes, $timeproj['projekt'], $this->_fix($timeproj['note'], 65500),
                                $moduleId, $itemId);

                            $tmpMinutes = ((24 - $lastHour)* 60);
                            if ($lastMinutes > 0) {
                                $tmpMinutes += (60 - $lastMinutes);
                            }
                            $minutes = $minutes - $tmpMinutes;

                            $starTimeValue     = mktime(8, 0, 0, $month, $day + 1, $year);
                            $endTimeValue      = mktime(8, $minutes, 0, $month, $day + 1, $year);
                            $timeproj['datum'] = date("Y-m-d", $starTimeValue);
                            $currentDatum      = $timeproj['datum'];
                        }

                        $starTime    = date("H:i:s", $starTimeValue);
                        $lastHour    = date("H", $endTimeValue);
                        $lastMinutes = date("i", $endTimeValue);
                        $endTime     = $lastHour . ":" . $lastMinutes . ":00";
                        list($moduleId, $itemId) = $this->_getItemAndModule($timeproj);

                        $dbValues[] = array($this->_users[$userId], $timeproj['datum'], $starTime, $endTime, $minutes,
                            $timeproj['projekt'], $this->_fix($timeproj['note'], 65500), $moduleId, $itemId);
                    }
                }
            }
        }

        // Run the multiple inserts
        if (!empty($dbValues)) {
            $this->_tableManager->insertMultipleRows('timecard', $dbFields, $dbValues);
        }

        // Clean memory
        $this->_todos    = array();
        $this->_helpdesk = array();
    }

    /**
     * Migrate P5 events
     *
     * @return void
     */
    private function _migrateCalendar()
    {
        // Calendar
        $run      = true;
        $start    = 0;
        $end      = self::ROWS_PER_QUERY;
        $moduleId = $this->_getModuleId('Calendar');

        $sqlString = "SELECT MAX(id) as count FROM " . $this->_db->quoteIdentifier((string) 'calendar');
        $result    = $this->_db->query($sqlString)->fetchAll();
        $currentId = (int) $result[0]['count'];

        while ($run) {
            $events = $this->_dbOrig->query("SELECT * FROM " . PHPR_DB_PREFIX . "termine ORDER BY ID LIMIT "
                . $start . ", " . $end)->fetchAll();
            if (empty($events)) {
                $run = false;
            } else {
                $start = $start + $end;
            }

            // Multiple inserts
            $dbFields = array('parent_id', 'owner_id', 'project_id', 'title', 'place', 'notes', 'uid' ,
                'start_datetime', 'end_datetime', 'timezone', 'location', 'categories', 'priority', 'rrule',
                'properties', 'participant_id');
            $dbValues = array();

            foreach ($events as $index => $calendar) {
                // Start and End times
                if ($calendar['anfang'] == '----') {
                    // This is because start and end times are not required fields in P5, but they are required in P6.
                    $calendar['anfang'] = '09:00:00';
                } else {
                    $calendar['anfang'] = $this->_stringToTime($calendar['anfang']);
                }
                if ($calendar['ende'] == '----') {
                    // This is because start and end times are not required fields in P5, but they are required in P6.
                    $calendar['ende'] = '18:00:00';
                } else {
                    $calendar['ende'] = $this->_stringToTime($calendar['ende']);
                }

                $timezone        = (isset($this->_timeZone[$calendar['ID']])) ? $this->_timeZone[$calendar['ID']] : 2;
                $date            = Cleaner::sanitize('date', $calendar['datum']);
                $calendar['von'] = $this->_processOwner($calendar['von']);

                // Process participant
                $oldParticipId = $calendar['an'];
                if (isset($this->_users[$oldParticipId])) {
                    $participantId = $this->_users[$oldParticipId];
                } else {
                    // Don't migrate rows for non existing users
                    unset($events[$index]);
                    continue;
                }

                // Migrate row
                if (!empty($calendar['anfang']) && !empty($calendar['ende']) && !empty($calendar['datum'])) {
                    if (!empty($calendar['serie_typ']) && !empty($calendar['serie_bis'])) {
                        $rrule = $this->_serietypToRrule($calendar['serie_typ'], $calendar['serie_bis'],
                            $calendar['anfang']);
                    } else {
                        $rrule = "";
                    }

                    // Assign id before exists
                    $currentId++;
                    $oldCalendarId                    = $calendar['ID'];
                    $this->_calendars[$oldCalendarId] = $currentId;

                    // Process parent for this row
                    if (!empty($calendar['serie_id'])) {
                        $oldParentId = $calendar['serie_id'];
                        if (isset($this->_calendars[$oldParentId])) {
                            $parentId = $this->_calendars[$oldParentId];
                        } else {
                            // The P5 parent for this row is probably a deleted row,
                            // so it will be assigned current key id.
                            // The rest of the rows that point to the same deleted row,
                            // will point to the same 'new' row.
                            $parentId                       = $currentId;
                            $this->_calendars[$oldParentId] = $currentId;
                        }
                    } else {
                        $parentId = 0;
                    }

                    // @todo: 'ical_ID' field is not being migrated to 'uid' field,
                    // it will be done when implemented P6 ical
                    $dbValues[] = array($parentId, $calendar['von'], self::PROJECT_ROOT,
                        $this->_fix($calendar['event'], 255), $this->_fix($calendar['ort']),
                        $this->_fix($calendar['remark'], 65500), null, $date . " " . $calendar['anfang'],
                        $date . " " . $calendar['ende'], $timezone, $this->_fix($calendar['ort']), "",
                        $calendar['priority'], $rrule, "", $participantId);
                } else {
                    unset($events[$index]);
                }
            }

            // Run the multiple inserts
            if (!empty($dbValues)) {
                $ids = $this->_tableManager->insertMultipleRows('calendar', $dbFields, $dbValues, true);

                foreach ($events as $calendar) {
                    // Migrate permissions
                    $calendarId = array_shift($ids);

                    // Add owner permission to this item
                    $userRightsAdd           = array();
                    $userVon                 = $this->_processOwner($calendar['von']);
                    $userRightsAdd[$userVon] = $this->_accessAdmin;

                    // Add participant permission to this item, only if it wasn't added before
                    $oldParticipId = $calendar['an'];
                    if (isset($this->_users[$oldParticipId])) {
                        $participantId = $this->_users[$oldParticipId];
                        if (!isset($userRightsAdd[$participantId])) {
                            $userRightsAdd[$participantId] = $this->_accessWrite;
                        }
                    }

                    // Save permissions according to P6 criterion
                    $this->_addItemRights($moduleId, $calendarId, $userRightsAdd);

                    // Add search values
                    $words = array($this->_fix($calendar['event'], 255), $this->_fix($calendar['ort']),
                        $this->_fix($calendar['remark'], 65500));
                    $itemId = $calendarId;
                    $this->_addSearchDisplay($moduleId, $itemId, 1, $words[0], $words[2]);
                    $this->_addSearchWords(implode(" ", $words), $moduleId, $itemId);
                }
            }
        }

        // Clean Memory
        $this->_calendars = array();
    }

    /**
     * Migrate P5 filemanager
     *
     * @return void
     */
    private function _migrateFilemanager()
    {
        $run   = true;
        $start = 0;
        $end   = self::ROWS_PER_QUERY;

        while ($run) {
            // Filemanager
            $files = $this->_dbOrig->query("SELECT * FROM " . PHPR_DB_PREFIX . "dateien ORDER BY ID LIMIT "
                . $start . ", " . $end)->fetchAll();
            if (empty($files)) {
                $run = false;
            } else {
                $start = $start + $end;
            }

            // Multiple inserts
            $dbFields = array('owner_id', 'title', 'comments', 'project_id', 'files');
            $dbValues = array();

            foreach ($files as $index => $file) {
                // Is it a file? (not a folder)
                if ($file['typ'] == "f") {
                    $file['von']  = $this->_processOwner($file['von']);
                    $file['div2'] = $this->_processParentProjId($file['div2'], $file['gruppe']);
                    $newFilename  = md5(uniqid(rand(), 1));
                    $uploadDir    = str_replace('htdocs/setup.php', '', $_SERVER['SCRIPT_FILENAME']) . 'upload';

                    // All the required data is filled?
                    if (!empty($file['tempname']) && !empty($file['filename'])) {
                        $title = $this->_fix($file['filename'], 100);

                        // Copy file, if it is there
                        $originPath = PHPR_FILE_PATH . "\\" . $file['tempname'];
                        $targetPath = $uploadDir . "\\" . $newFilename;
                        if (file_exists($originPath)) {
                            copy($originPath, $targetPath);
                        }

                        $dbValues[] = array($file['von'], $title, $this->_fix($file['remark'], 65500),
                            $file['div2'], $this->_fix($newFilename . "|" . $file['filename']));
                    } else {
                        unset($files[$index]);
                    }
                } else {
                    unset($files[$index]);
                }
            }

            // Run the multiple inserts
            if (!empty($dbValues)) {
                $ids = $this->_tableManager->insertMultipleRows('filemanager', $dbFields, $dbValues, true);

                foreach ($files as $file) {
                    // Migrate permissions
                    $file['von']         = $this->_processOwner($file['von']);
                    $file['ID']          = array_shift($ids);
                    $file['p6ProjectId'] = $this->_processParentProjId($file['div2'], $file['gruppe']);
                    $this->_migratePermissions('Filemanager', $file);

                    // Add search values
                    $moduleId = $this->_getModuleId('Filemanager');
                    $words    = array($this->_fix($file['filename'], 100), $this->_fix($file['remark'], 65500));
                    $itemId   = $file['ID'];
                    $this->_addSearchDisplay($moduleId, $itemId, $file['p6ProjectId'], $words[0], $words[1]);
                    $this->_addSearchWords(implode(" ", $words), $moduleId, $itemId);
                }
            }
        }
    }

    /**
     * Migrate P5 contacts
     *
     * @return void
     */
    private function _migrateContacts()
    {
        $run   = true;
        $start = 0;
        $end   = self::ROWS_PER_QUERY;

        while ($run) {
            $contacts = $this->_dbOrig->query("SELECT * FROM " . PHPR_DB_PREFIX . "contacts ORDER BY ID LIMIT "
                . $start . ", " . $end)->fetchAll();
            if (empty($contacts)) {
                $run = false;
            } else {
                $start = $start + $end;
            }

            // Multiple inserts
            $dbFields = array('project_id', 'name', 'email', 'company', 'firstphone', 'secondphone', 'mobilephone',
                'street', 'city', 'zipcode', 'country', 'comment', 'owner_id', 'private');
            $dbValues = array();

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

                $dbValues[] = array(self::PROJECT_ROOT, $this->_fix($contact['vorname'] . ' ' . $contact['nachname']),
                    $this->_fix($contact['email']), $this->_fix($contact['firma']), $this->_fix($contact['tel1']),
                    $this->_fix($contact['tel2']), $this->_fix($contact['mobil']), $this->_fix($contact['strasse']),
                    $this->_fix($contact['stadt']), $this->_fix($contact['plz']), $this->_fix($contact['land']),
                    $this->_fix($comment), $contact['von'], 1);
            }

            // Run the multiple inserts
            if (!empty($dbValues)) {
                $ids = $this->_tableManager->insertMultipleRows('contact', $dbFields, $dbValues, true);

                foreach ($contacts as $contact) {
                    $contactId                      = array_shift($ids);
                    $oldContactId                   = $contact['ID'];
                    $this->_contacts[$oldContactId] = $contactId;
                }
            }
        }
    }

    /**
     * Migrate P5 Helpdesk
     *
     * @return void
     */
    private function _migrateHelpdesk()
    {
        $run   = true;
        $start = 0;
        $end   = self::ROWS_PER_QUERY;

        $ownerIdRelation  = array();
        $assignedRelation = array();

        while ($run) {
            $query = "SELECT * FROM " . PHPR_DB_PREFIX . "rts ORDER BY ID LIMIT "
                . $start . ", " . $end;
            $incidents = $this->_dbOrig->query($query)->fetchAll();
            if (empty($incidents)) {
                $run = false;
            } else {
                $start = $start + $end;
            }

            // Multiple inserts
            $dbFields = array('project_id', 'owner_id', 'title', 'assigned', 'date', 'priority', 'attachments',
                'description', 'status', 'due_date', 'author', 'solved_by', 'solved_date', 'contact_id');
            $dbValues = array();

            foreach ($incidents as $item) {
                $projectId = $this->_processParentProjId($item['proj'], $item['gruppe']);

                // Process owner - Id, email or wrong value
                $owner = $item['von'];
                // Default value:
                $ownerId = self::USER_ADMIN;
                if (is_numeric($owner) && !empty($owner)) {
                    // Id
                    $ownerId = (int) $owner;
                } else if (strpos($owner, '@')) {
                    // It is apparently an email - Search for the Id
                    $query   = sprintf("SELECT ID FROM " . PHPR_DB_PREFIX . "users WHERE email = '%s'", $owner);
                    $userIds = $this->_dbOrig->query($query)->fetchAll();
                    if (isset($userIds[0]['ID'])) {
                        $oldOwnerId = $userIds[0]['ID'];
                        if (isset($this->_users[$oldOwnerId])) {
                            $ownerId = $this->_users[$oldOwnerId];
                        }
                    }
                }

                // Process assigned user
                $oldAssignedId = (int) $item['assigned'];
                if (isset($this->_users[$oldAssignedId])) {
                    $assignedId = $this->_users[$oldAssignedId];
                } else {
                    $assignedId = null;
                }
                $assignedRelation[$item['ID']] = $assignedId;

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
                            // None
                            $priority = 10;
                            break;
                    }
                }

                // Process attachment
                $filenameField   = $item['filename'];
                $attachmentField = null;
                if (strpos($filenameField, "|")) {
                    $tmp = explode('\|', $filenameField);
                    if (isset($tmp[0]) && isset($tmp[1])) {
                        $currentFileName = $tmp[1];
                        $realName        = $tmp[0];
                        if ($currentFileName != '' && $realName != '') {
                            $md5Name         = md5(uniqid(rand(), 1));
                            $attachmentField = $md5Name . '|' . $realName;
                            $uploadDir       = str_replace('htdocs/setup.php', '', $_SERVER['SCRIPT_FILENAME'])
                                . 'upload';
                            // Copy file
                            $originPath = $this->_p5RootPath . '\\' . PHPR_DOC_PATH . '\\' . $currentFileName;
                            $targetPath = $uploadDir . '\\' . $md5Name;
                            if (file_exists($originPath)) {
                                copy($originPath, $targetPath);
                            }
                        }
                    }
                }

                // Process description
                $description  = $this->_fix($item['note'], 65500) . chr(10) . chr(10);
                $description .= $item['solution'];

                // Process status
                $status = $item['status'];
                if (is_numeric($status)) {
                    $statusNumber = (int) $status;
                    if ($statusNumber == 7) {
                        $statusNumber = self::HELPDESK_STATUS_OPEN;
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
                $dueDate = $item['due_date'];
                if (!empty($dueDate)) {
                    // Just in case
                    $dueDate = Cleaner::sanitize('date', $dueDate);
                } else {
                    $dueDate = null;
                }

                // Process author
                // Default value:
                $authorId = $ownerId;
                if (isset($item['autor']) && !empty($item['autor'])) {
                    // It is apparently an email - Search for the Id
                    $author  = $item['autor'];
                    $query   = sprintf("SELECT ID FROM " . PHPR_DB_PREFIX . "users WHERE email = '%s'", $author);
                    $userIds = $this->_dbOrig->query($query)->fetchAll();
                    foreach ($userIds as $userId) {
                        $oldAuthorId = $userId['ID'];
                        if (isset($this->_users[$oldAuthorId])) {
                            $authorId = $this->_users[$oldAuthorId];
                        }
                    }
                }
                $ownerIdRelation[$item['ID']] = $authorId;

                // Process P5 'solving' fields: 'solved' and 'solve_date'
                $solvedBy = $item['solved'];
                if (isset($this->_users[$solvedBy])) {
                    $solvedBy = $this->_users[$solvedBy];
                } else {
                    $solvedBy = null;
                }
                if (!empty($item['solve_time'])) {
                    $solvedDate = $this->_longDateToShortDate($item['solve_time']);
                } else {
                    $solvedDate = null;
                }

                // Process contact
                $contact = (int) $item['contact'];
                if (isset($this->_contacts[$contact])) {
                    $contact = $this->_contacts[$contact];
                } else {
                    $contact = null;
                }

                $dbValues[] = array($projectId, $ownerId, $this->_fix($item['name'], 255), $assignedId,
                    $date, $priority, $attachmentField, $description, $statusNumber, $dueDate, $authorId, $solvedBy,
                    $solvedDate, $contact);
            }

            // Run the multiple inserts
            if (!empty($dbValues)) {
                $ids = $this->_tableManager->insertMultipleRows('helpdesk', $dbFields, $dbValues, true);

                foreach ($incidents as $item) {
                    // Migrate permissions
                    $oldHelpdeskId       = $item['ID'];
                    $item['ID']          = array_shift($ids);
                    $item['von']         = $ownerIdRelation[$oldHelpdeskId];
                    $item['assigned']    = $assignedRelation[$oldHelpdeskId];
                    $item['p6ProjectId'] = $this->_processParentProjId($item['proj'], $item['gruppe']);
                    $this->_helpdesk[$oldHelpdeskId] = $item['ID'];
                    $this->_migratePermissions('Helpdesk', $item);

                    // Add search values
                    $moduleId = $this->_getModuleId('Helpdesk');
                    $words    = array($this->_fix($item['name'], 255), $this->_fix($item['note'], 65500));
                    $itemId   = $item['ID'];
                    $this->_addSearchDisplay($moduleId, $itemId, $item['p6ProjectId'], $words[0], $words[1]);
                    $this->_addSearchWords(implode(" ", $words), $moduleId, $itemId);
                }
            }
        }

        // Clean memory
        $this->_contacts = array();
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
                $minutes = (string) ((int) $minutes - ($addHours * 60));
                // Add zeros at the left to reach 2 positions in total for each string variable
                $hour    = str_repeat("0", 2 - strlen($hour)) . $hour;
                $minutes = str_repeat("0", 2 - strlen($minutes)) . $minutes;
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

    /**
     * Collect all the modules
     *
     * @return void
     */
    private function _setModules()
    {
        // Set the modules id
        $modules = $this->_db->query("SELECT * FROM module WHERE (save_type = 0 OR save_type = 2)")->fetchAll();
        foreach ($modules as $module) {
            $this->_modules[$module['name']] = $module['id'];
        }
    }

    /**
     * Return the module id
     *
     * @param string $module The name of the module
     *
     * @return integer
     */
    private function _getModuleId($module)
    {
        if (isset($this->_modules[$module])) {
            return $this->_modules[$module];
        } else {
            // Global modules
            $select = $this->_db->select()
                                ->from('module')
                                ->where('name = ?', $module);

            $stmt = $this->_db->query($select);
            $rows = $stmt->fetchAll();

            if (isset($rows[0])) {
                $this->_modules[$rows[0]['name']] = $rows[0]['id'];
                return $rows[0]['id'];
            } else {
                return 0;
            }
        }
    }

    /**
     * Migrates the permission from PHProjekt 5.x version to PHProjekt 6.0
     *
     * @param string $module Module to grant permissions to: Project / Note / Todo / Filemanager, Helpdesk
     * @param array  $item   Item data
     *
     * @return void
     */
    private function _migratePermissions($module, $item)
    {
        $userRightsAdd = array();

        // The given permissions accord with the content of 'acc_write' field of 'projekte' table
        if ($item['acc_write'] == 'w') {
            $access = $this->_accessWrite;
        } else {
            $access = $this->_accessRead;
        }
        $userVon = (int) $item['von'];

        // Process the 'acc' field
        $userIds = $this->_getUsersFromAccField($item);
        foreach ($userIds as $userId) {
            $userRightsAdd[$userId] = $access;
        }

        switch ($module) {
            case 'Todo':
                // Assigned user: 'ext' field - Give write access to assigned user, if any
                if (!empty($item['ext'])) {
                    $assignedId                 = $item['ext'];
                    $userRightsAdd[$assignedId] = $this->_accessWrite;
                }
                break;
            case 'Helpdesk':
                // Give write access to assigned user, if any. 'assigned' field
                if (!empty($item['assigned'])) {
                    $oldAssignedId = $item['assigned'];
                    if (isset($this->_users[$oldAssignedId])) {
                        $assignedId                 = $this->_users[$oldAssignedId];
                        $userRightsAdd[$assignedId] = $this->_accessWrite;
                    }
                }
                break;
            default:
                break;
        }

        // Add owner with Admin access. This may overwrite previous right assignment for owner, that's ok.
        if ($userVon > 0) {
            $userRightsAdd[$userVon] = $this->_accessAdmin;
        }

        // Filter only the user that have access to the parent project too
        $filterUserList = array();
        foreach ($userRightsAdd as $user => $data) {
            $index = "1-" . $item['p6ProjectId'] . "-" . $user;
            if (isset($this->_dbProjectItemRightValues[$index])) {
                $filterUserList[$user] = $data;
            }
        }

        // Migrate each permission
        $moduleId = $this->_getModuleId($module);
        $itemId   = $item['ID'];

        // Add admin user
        $filterUserList[self::USER_ADMIN] = $this->_accessAdmin;

        $this->_addItemRights($moduleId, $itemId, $filterUserList);
    }

    /**
     * Returns a list of users according to the value received that corresponds to 'acc' field of 'projekte' table.
     *
     * @param array $item  Array with data of the module item
     *
     * @return array List of users
    */
    private function _getUsersFromAccField($item)
    {
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
    private function _serietypToRrule($value, $endDate, $startTime)
    {
        $until = 'UNTIL=' . str_replace('-', '', $endDate) . 'T' . str_replace(':', '', $startTime) . 'Z;';
        if (substr($value, 0, 2) == 'a:' && strpos($value, 'weekday')) {
            // Serialized array
            $value       = unserialize($value);
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
            $weekDaysList = array(0 => 'MO', 1 => 'TU', 2 => 'WE', 3 => 'TH',
                                  4 => 'FR', 5 => 'SA', 6 => 'SU');
            if (isset($value['weekday']) && !empty($value['weekday'])) {
                $byDay = array_keys($value['weekday']);
                foreach ($byDay as $position => $day) {
                    if ($position > 0) {
                        $returnValue .= ",";
                    }
                    $returnValue .= $weekDaysList[$day];
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
     * Collect all the values for save  into 'item_rights' table according to received parameters
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
            if ($moduleId == 1) {
                $index = $moduleId . "-" . $itemId . "-" . $user;
                $this->_dbProjectItemRightValues[$index] = 1;
            }
            $this->_dbItemRightValues[] = array($moduleId, $itemId, $user, $rights);

            // Clean memory on each 200.000 rows
            if (count($this->_dbItemRightValues) > 200000) {
                $this->_executeItemRightsInsert();
            }
        }
    }

    /**
     * Insert all the permissions into item_rights table
     *
     * @return void
     */
    private function _executeItemRightsInsert()
    {
        $dbFields = array('module_id', 'item_id', 'user_id', 'access');
        if (!empty($this->_dbItemRightValues)) {
            $this->_tableManager->insertMultipleRows('item_rights', $dbFields, $this->_dbItemRightValues);
            $this->_dbItemRightValues = array();
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
    private function _processOwner($oldId)
    {
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
     * @param int $oldProjectId Id of the P5 parent project Id as it is in the original P5 item.
     * @param int $oldGroupId   Id of the P5 group Id as it is in the original P5 item.
     *
     * @return int  User id to insert in P6 field 'project_id'
     */
    private function _processParentProjId($oldProjectId, $oldGroupId)
    {
        if (isset($this->_projects[$oldProjectId])) {
            // Parent project
            $projectId = $this->_projects[$oldProjectId];
        } else if (isset($this->_groups[$oldGroupId])) {
            // Group Root project
            $projectId = $this->_groups[$oldGroupId];
        } else {
            // Non existing projects will be migrated as root one
            $projectId = self::PROJECT_ROOT;
        }

        return $projectId;
    }

    /**
     * Parse module and item id from timeproj
     *
     * @param array $data Timeproj record
     *
     * @return array (module_id, item_id)
     */
    private function _getItemAndModule($data)
    {
        $moduleId = $this->_getModuleId($data['module']);
        if ($moduleId < 1) {
            $moduleId = 1;
            $itemId = null;
        } else {
            switch ($data['module']) {
                case 'todo':
                case 'Todo':
                    if (isset($this->_todos[$data['module_id']])) {
                        $itemId = $this->_todos[$data['module_id']];
                    } else {
                        $moduleId = 1;
                        $itemId   = null;
                    }
                    break;
                case 'Helpdsek':
                case 'helpdesk':
                    if (isset($this->_helpdesk[$data['module_id']])) {
                        $itemId = $this->_helpdesk[$data['module_id']];
                    } else {
                        $moduleId = 1;
                        $itemId   = null;
                    }
                    break;
                default:
                    $itemId = null;
                    break;
            }
        }

        return array($moduleId, $itemId);
    }

    /**
     * Fix timeZone with the P6 values
     *
     * @param int $timeZone +/- TimeZone
     *
     * @return string
     */
    private function _getP6TimeZone($timeZone)
    {
        if ($timeZone == 0 || $timeZone > 12 || $timeZone < -12) {
            return "000";
        } else {
            return $timeZone;
        }
    }

    /**
     * Fix string witn utf8 encode and limit the characters
     *
     * @param string $string Normal string
     * @param int    $length Limit if characters
     *
     * @return string
     */
    private function _fix($string, $length = 0)
    {
        if ($length == 0) {
            return utf8_encode($string);
        } else {
            return substr(utf8_encode($string), 0, $length);
        }
    }

    /**
     * Keep the display data of each item
     *
     * @param int    $moduleId      Module id
     * @param int    $itemId        Item Id
     * @param int    $projectId     Parent project id
     * @param string $firstDisplay  First display string
     * @param string $secondDisplay Second display string
     *
     * @return void
     */
    private function _addSearchDisplay($moduleId, $itemId, $projectId, $firstDisplay, $secondDisplay)
    {
        if (strlen($secondDisplay) > 100) {
            $secondDisplay = substr($secondDisplay, 0, 100) . "...";
        }
        $this->_searchDisplay[] = array((int) $moduleId, (int) $itemId, (int) $projectId, $firstDisplay,
            $secondDisplay);

        // Clean memory on each 5.000 rows
        if (count($this->_searchDisplay) > 5000) {
            $this->_executeSearchDisplayInsert();
        }
    }

    /**
     * Process the data of the item into the search words
     * The return array have per word, the count of ocurrences,
     * and the pair moduleId-itemId where is it.
     *
     * @param string $string   All the string data of the item
     * @param int    $moduleId Module Id
     * @param int    $itemId   Item id
     *
     * @return array
     */
    private function _addSearchWords($string, $moduleId, $itemId)
    {
        // Clean up the string
        $string = Phprojekt_Converter_String::cleanupString($string);
        // Split the string into an array
        $tempArray = explode(" ", $string);
        // Strip off short or long words
        $tempArray = array_filter($tempArray, array("Phprojekt_Converter_String", "stripLengthWords"));
        // Strip off stop words
        if (!empty($this->_stopWords)) {
            $tempArray = array_filter($tempArray, array($this, "_stripStops"));
        }
        // Remove duplicate entries
        $tempArray = array_unique($tempArray);

        foreach ($tempArray as $word) {
            if (!isset($this->_searchWord[$word])) {
                $this->_searchWord[$word] = array('count' => 0,
                                                  'pair'  => array());
            }
            $this->_searchWord[$word]['count']++;
            $this->_searchWord[$word]['pair'][] = array($itemId, $moduleId);
        }

        return $tempArray;
    }

    /**
     * Insert all the search_display values
     *
     * @return void
     */
    private function _executeSearchDisplayInsert()
    {
        // Display
        $dbFields = array('module_id', 'item_id', 'project_id', 'first_display', 'second_display');
        if (!empty($this->_searchDisplay)) {
            $this->_tableManager->insertMultipleRows('search_display', $dbFields, $this->_searchDisplay);
            $this->_searchDisplay = array();
        }
    }

    /**
     * Insert all the searc_words and search_word_module values
     *
     * @return void
     */
    private function _executeSearchWordsInsert()
    {
        // Words
        $dbFields = array('word', 'count');
        $dbValues = array();
        foreach ($this->_searchWord as $word => $data) {
            $dbValues[] = array($word, $data['count']);
        }
        $ids = $this->_tableManager->insertMultipleRows('search_words', $dbFields, $dbValues, true);

        // Relations
        $dbFields = array('item_id', 'module_id', 'word_id');
        $dbValues = array();
        foreach ($this->_searchWord as $word => $data) {
            $id = array_shift($ids);
            foreach ($data['pair'] as $pair) {
                $dbValues[] = array($pair[0], $pair[1], $id);
            }

            if (count($dbValues) > 100000) {
                $this->_tableManager->insertMultipleRows('search_word_module', $dbFields, $dbValues);
                $dbValues = array();
            }
        }
        if (!empty($dbValues)) {
            $this->_tableManager->insertMultipleRows('search_word_module', $dbFields, $dbValues);
        }
    }
}
