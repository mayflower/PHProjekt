<?php
/**
 * Migration model class.
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
 * @package    Htdocs
 * @subpackage Setup
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: 6.1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
 * Migration model class.
 *
 * @category   PHProjekt
 * @package    Htdocs
 * @subpackage Setup
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: 6.1.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Setup_Models_Migration
{
    /**
     * Relation: User Kurz in p5 to user ID in p6.
     *
     * @var array
     */
    private $_userKurz = array();

    /**
     * Relation: User timezone in p5 to user timezone in p6.
     *
     * @var array
     */
    private $_timeZone = array();

    /**
     * Relation: Group ID in p5 => Array of user IDs of P6.
     *
     * @var array
     */
    private $_groupsUsers = array();

    /**
     * Array with all the modules in P6
     *
     * @var array
     */
    private $_modules = array();

    /**
     * Relation: User ID in p5 to User ID in p6.
     *
     * @var array
     */
    private $_users = array();

    /**
     * Relation: Group ID in p5 to project ID in p6.
     *
     * @var array
     */
    private $_groups = array();

    /**
     * Relation: Project ID in p5 to project ID in p6.
     *
     * @var array
     */
    private $_projects = array();

    /**
     * P5 Database.
     *
     * @var Zend_Db_Adapter_Abstract
     */
    private $_dbOrig = null;

    /**
     * P6 Database.
     *
     * @var Zend_Db_Adapter_Abstract
     */
    private $_db = null;

    /**
     * Table manager.
     *
     * @var Phprojekt_Table
     */
    private $_tableManager = null;

    /**
     * Permissions for the users.
     *
     * @var int
     */
    private $_accessRead  = null;
    private $_accessWrite = null;
    private $_accessAdmin = null;

    /**
     * Permissions values.
     *
     * @var array
     */
    private $_dbItemRightValues = array();

    /**
     * Keep the project permissions values.
     *
     * @var array
     */
    private $_dbProjectItemRightValues = array();

    /**
     * Root path, taken out from the config file path.
     *
     * @var string
     */
    private $_p5RootPath = null;

    /**
     * Set the diff between the server and GMT
     *
     * @var integer
     */
    private $_diffToUtc = 0;

    /**
     * Search values.
     *
     * @var array
     */
    private $_searchWord    = array();
    private $_searchDisplay = array();

    // Phprojekt 6 Ids
    const USER_ADMIN   = 1;
    const USER_TEST    = 2;
    const PROJECT_ROOT = 1;

    // Pagination
    const ROWS_PER_QUERY = 5000;

    /**
     * Return a list of all modules available for migrate.
     *
     * @return Array
     */
    public static function getModulesToMigrate()
    {
        return array('System', 'Timecard');
    }

    /**
     * Constructor.
     *
     * @param $file string p5 configuration
     * @param $diffToUtc int difference to utc
     * @param $db null|Zend_Db_Adapter_Abstract p6 database
     */
    public function __construct($file, $diffToUtc, $db = null)
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

        // Modules
        $this->_modules = $this->_getModules();

        // Set the diff between the server and GMT
        $this->_diffToUtc = $diffToUtc;

        // Users
        $this->_users = $this->_getSession('migratedUsers');

        // User Kurz
        $this->_userKurz = $this->_getSession('migratedUserKurz');

        // TimeZone
        $this->_timeZone = $this->_getSession('migratedTimeZone');

        // Groups-Users
        $this->_groupsUsers = $this->_getSession('migratedGroupsUsers');

        // Groups
        $this->_groups = $this->_getSession('migratedGroups');

        // Projects
        $this->_projects = $this->_getSession('migratedProjects');

        // Project ItemRight
        $this->_dbProjectItemRightValues = $this->_getSession('migratedProjectRights');

        // Search Word
        $this->_searchWord = $this->_getSession('migratedSearchWord');
    }

    /**
     * Migrate Users, groups, projects and the user access relations.
     *
     * @return void
     */
    public function migrateSystem()
    {
        $this->_migrateUsers();
        $this->_migrateGroups();
        $this->_migrateGroupsUserRelations();
        $this->_migrateProjects();

        $this->_executeItemRightsInsert();
        $this->_executeSearchDisplayInsert();

        // Save Project ItemRight
        $this->_saveSession('migratedProjectRights', $this->_dbProjectItemRightValues);

        // Save words
        $this->_saveSession('migratedSearchWord', $this->_searchWord);

    }

    /**
     * Migrate the Timecard module.
     *
     * @return void
     */
    public function migrateTimecard()
    {
        $this->_migrateTimecard();
    }

    /**
     * Migrate the words found in all the modules.
     *
     * Also since is the last function, clean the session.
     *
     * @return void
     */
    public function migrateWords()
    {
        $this->_modules = $this->_getModules();

        // Remove all the session data
        $namespaces = array('migratedUsers', 'migratedUserKurz', 'migratedTimeZone', 'migratedGroupsUsers',
            'migratedGroups', 'migratedProjects', 'migratedProjectRights', 'migratedModules');
        foreach ($namespaces as $name) {
            $this->_cleanSession($name);
        }

        // Search Word
        $this->_executeSearchWordsInsert();
        $this->_cleanSession('migratedSearchWord');
    }

    /**
     * Checks for migration.
     *
     * @throws Exception If there is an error in the DB connection.
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
     * Migrate P5 users.
     *
     * @return void
     */
    private function _migrateUsers()
    {
        // User migration
        $query = "SELECT * FROM " . PHPR_DB_PREFIX . "users WHERE is_deleted IS NULL";
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

                $username = $this->_fix($username);
                if ($username === 'Admin') {
                    continue;
                }
                $userId = $this->_tableManager->insertRow('user', array(
                    'username'  => $username,
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

            // Migrate password for all users except for P5 'root' and 'test'
            if ($loginName != 'root' && $loginName != 'test') {
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

        // Save data into the session
        // Users
        $this->_saveSession('migratedUsers', $this->_users);

        // UserKurz
        $this->_saveSession('migratedUserKurz', $this->_userKurz);

        // TimeZone
        $this->_saveSession('migratedTimeZone', $this->_timeZone);
    }

    /**
     * Collect all the P5 groups and migrate them.
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
            $itemId = $projectId;
            $this->_addSearchDisplay(1, $itemId, 1, $words[0], '');
            $this->_addSearchWords(implode(" ", $words), 1, $itemId);

            // Migrate permission for admin
            $moduleId                        = $this->_getModuleId('Project');
            $userRightsAdd                   = array();
            $userRightsAdd[self::USER_ADMIN] = $this->_accessAdmin;
            $this->_addItemRights($moduleId, $projectId, $userRightsAdd);

            foreach ($this->_modules as $moduleId) {
                $dbValues[] = array($moduleId, $projectId);
            }
        }

        // Run the multiple inserts
        if (!empty($dbValues)) {
            $this->_tableManager->insertMultipleRows('project_module_permissions', $dbFields, $dbValues);
        }

        // Save data into the session
        // Groups-Users
        $this->_saveSession('migratedGroupsUsers', $this->_groupsUsers);

        // Groups
        $this->_saveSession('migratedGroups', $this->_groups);
    }

    /**
     * Migrates P5 group-users relations and creates a two-dimensional array
     * with a list of groups of P5 as the first dimension and users as the second one: _groupsUsers.
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
                    if (isset($this->_groups[$lastGroup])) {
                        $moduleId = $this->_getModuleId('Project');
                        $itemId   = $this->_groups[$lastGroup];

                        $this->_addItemRights($moduleId, $itemId, $userRightsAdd);
                    }
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

                $userRightsAdd[$userId] = $this->_accessRead;
            }
        }

        // Finish with the last one
        // Migrate each permission
        if (isset($this->_groups[$lastGroup])) {
            $moduleId = $this->_getModuleId('Project');
            $itemId   = $this->_groups[$lastGroup];

            $this->_addItemRights($moduleId, $itemId, $userRightsAdd);
        }

        // Save data into the session
        // Groups-Users
        $this->_saveSession('migratedGroupsUsers', $this->_groupsUsers);
    }

    /**
     * Migrate P5 projects.
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

        // Store the names of the projects under each parent so we can rename duplicates.
        $projectNamesByParentId = array();

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
                    $parentId = (isset($this->_groups[$project['gruppe']])) ? (int) $this->_groups[$project['gruppe']]
                        : self::PROJECT_ROOT;
                    if (!isset($paths[$parentId])) {
                        $paths[$parentId] = $paths[self::PROJECT_ROOT] . $parentId . "/";
                    }
                } else {
                    // Has parent project been processed?
                    $oldParentId = $project['parent'];
                    if (isset($this->_projects[$oldParentId])) {
                        // Yes
                        $parentId = (int) $this->_projects[$oldParentId];
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

                $project['name'] = $this->_fix($project['name'], 255);

                // Rename projects with the same name and parent ID
                if (!array_key_exists($parentId, $projectNamesByParentId)) {
                    $projectNamesByParentId[$parentId] = array();
                }
                if (in_array(strtolower($project['name']), $projectNamesByParentId[$parentId])) {
                    $project['name'] = $project['name'] . '-' . $oldProjectId;
                }

                $projectNamesByParentId[$parentId][] = strtolower($project['name']);

                $projectId = $this->_tableManager->insertRow('project', array(
                    'path'             => $paths[$parentId],
                    'project_id'       => $parentId,
                    'title'            => $project['name'],
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
                $words  = array($project['name'], $this->_fix($project['note'], 65500));
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

        // Save data into the session
        // Projects
        $this->_saveSession('migratedProjects', $this->_projects);
    }

    /**
     * Migrate P5 timeproj and timecard.
     *
     * @return void
     */
    private function _migrateTimecard()
    {
        // Multiple inserts
        $dbFields = array('owner_id', 'start_datetime', 'end_time', 'minutes', 'project_id', 'notes', 'module_id',
            'item_id');
        $dbValues = array();

        // get all user Ids with bookings
        $userIds = $this->_dbOrig->select()
            ->distinct()
            ->from(PHPR_DB_PREFIX . 'timecard', array('users'))
            ->query()->fetchAll(Zend_Db::FETCH_COLUMN);

        foreach($userIds as $userId) {
            if(!array_key_exists($userId, $this->_users)) {
                continue;
            }
            $datumRegexp = '[[:digit:]]{4}-[[:digit:]]{2}-[[:digit:]]{2}';
            $timecardRows = $this->_dbOrig->select()->from(
                PHPR_DB_PREFIX . 'timecard',
                array('datum', 'anfang', 'ende', 'users')
            )
                ->where('users = ?', $userId)
                ->where('anfang IS NOT NULL AND ende IS NOT NULL')
                ->where('anfang < ende')
                ->where('datum REGEXP "'. $datumRegexp . '"')
                ->query()->fetchAll();
            $projectRows = $this->_dbOrig->select()->from(
                PHPR_DB_PREFIX . 'timeproj',
                array('datum', 'users', 'projekt', 'h', 'm', 'note', 'module', 'module_id')
            )
                ->where('users = ?', $userId)
                ->where('datum REGEXP "' . $datumRegexp . '"')
                ->where('(h > 0 AND m >= 0) OR (h = 0 AND m > 0)')
                ->query()->fetchAll();

            $days = array();
            foreach($timecardRows as $row) {
                // don't migrate running bookings
                if (is_null($row['ende'])) {
                    continue;
                }

                if (!array_key_exists($row['datum'], $days)) {
                    $days[$row['datum']] = array(
                        'bookings' => array(),
                        'projectBookings' => array(),
                        'bookingMinutesLeft' => 24 * 60
                    );
                }

                $minutes = $this->_timecardGetDifferenceInMinutes($row['anfang'], $row['ende']);
                if ($days[$row['datum']]['bookingMinutesLeft'] - $minutes < 0) {
                    continue;
                }

                $days[$row['datum']]['bookings'][] = array(
                    'row' => $row,
                    'minutes' => min($minutes, $days[$row['datum']]['bookingMinutesLeft'])
                );
                $days[$row['datum']]['bookingMinutesLeft'] -= $minutes;
            }

            // aggregate worked hours
            foreach($projectRows as $row) {
                // ignore project bookings which are not backed by timecard bookings
                if (!array_key_exists($row['datum'], $days)) {
                    continue;
                }

                $days[$row['datum']]['projectBookings'][] = array(
                    'row' => $row,
                    'minutes' => (int) $row['h'] * 60 + (int) $row['m']
                );
            }

            // add the root project as fallback
            foreach(array_keys($days) as $datum) {
                $days[$datum]['projectBookings'][] = array(
                    'row' => array(
                        'projekt' => '-1',
                        'note' => '',
                        'module' => '',
                        'module_id' => 0
                    ),
                    'minutes' => 24*60
                );
            }

            foreach($days as $daykey => $day) {
                $projectBooking = array_shift($day['projectBookings']);
                foreach($day['bookings'] as $booking) {
                    //skip zero length bookings
                    if ($booking['minutes'] == 0) {
                        continue;
                    }

                    $datum = $booking['row']['datum'];

                    // assign each booking a project
                    $starttime = $this->_timecardTimeToDatetime($datum, $booking['row']['anfang']);
                    $endtime = clone $starttime;
                    while ($booking['minutes'] > 0) {
                        while ($projectBooking['minutes'] == 0) {
                            // Never empty because we added a 24-hour fallback
                            $projectBooking = array_shift($day['projectBookings']);
                        }
                        list($moduleId, $itemId) = $this->_getItemAndModule($projectBooking['row']);

                        $bookedMinutes = null;
                        if ($booking['minutes'] > $projectBooking['minutes']) {
                            // fill booking with projectTime
                            $endtime->add(new DateInterval('PT' . $projectBooking['minutes'] . 'M'));
                            $bookedMinutes = $projectBooking['minutes'];
                            $booking['minutes'] -= $projectBooking['minutes'];
                            $projectBooking['minutes'] = 0;
                        } else {
                            // fill booking with projectTime
                            $endtime->add(new DateInterval('PT' . $booking['minutes'] . 'M'));
                            $bookedMinutes = $booking['minutes'];
                            $projectBooking['minutes'] -= $booking['minutes'];
                            $booking['minutes'] = 0;
                        }


                        // use the invisible root if no project time was booked
                        if ($projectBooking['row']['projekt'] == '-1') {
                            $projectId = 1;
                        } else {
                            $projectId = $this->_processParentProjId($projectBooking['row']['projekt'], 0);
                        }

                        $dbValues[] = array(
                            $this->_users[$userId],
                            $starttime->format('Y-m-d H:i:s'),
                            $endtime->format('H:i:s'),
                            $bookedMinutes,
                            $projectId,
                            $this->_fix($projectBooking['row']['note'], 65500),
                            $moduleId,
                            $itemId
                        );

                        $starttime = clone $endtime;
                    }
                }
            }
        }

        // Run the multiple inserts
        if (!empty($dbValues)) {
            $this->_tableManager->insertMultipleRows('timecard', $dbFields, $dbValues);
        }
    }

    private function _timecardTimeToDatetime($date, $time) {
        //fix time values
        if (strlen($time) == 3) {
            $time = "0" . $time;
        }

        $d = new DateTime($date);
        $d->setTime(substr($time, 0, 2), substr($time, 2, 2));

        return $d;
    }

    private function _timecardGetDifferenceInMinutes($start, $end) {
        //fix time values
        $start = sprintf('%04d', $start);
        $end   = sprintf('%04d', $end);

        $startHour = (int) substr($start, 0, 2);
        $endHour = (int) substr($end, 0, 2);
        $startMinute = (int) substr($start, 2, 2);
        $endMinute = (int) substr($end, 2, 2);

        return ($endHour * 60 + $endMinute) - ($startHour * 60 + $startMinute);
    }

    /**
     * Converts the old time format (hhmm) to a time format (hh:mm:ss).
     *
     * @param string $stringTime P5 Time string.
     *
     * @return string Time string.
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
     * Converts the P5 datetime format YYYYMMDDHHMMSS to P6 date format YYYY-MM-DD.
     *
     * @param string $date Date & time in YYYYMMDDHHMMSS format.
     *
     * @return string Date in YYYY-MM-DD format.
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
     * Collect all the modules.
     *
     * @return Array modules in database
     */
    private function _getModules()
    {
        $modulesNamespace = new Zend_Session_Namespace('migratedModules');
        if (!isset($modulesNamespace->modules)) {
            $modulesNamespace->modules = array();

            $modules = $this->_db->query("SELECT * FROM module WHERE (save_type = 0 OR save_type = 2)")->fetchAll();
            foreach ($modules as $module) {
                $modulesNamespace->modules[$module['name']] = $module['id'];
            }
        }

        return $modulesNamespace->modules;
    }

    /**
     * Return the module ID.
     *
     * @param string $module The name of the module.
     *
     * @return integer The module ID in P6.
     */
    private function _getModuleId($module)
    {
        if (isset($this->_modules[$module])) {
            return $this->_modules[$module];
        } else {
            // Global modules
            $select = $this->_db->select()
                                ->from('module')
                                ->where('name = ?', (string) $module);

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
     * Migrates the permission from PHProjekt 5.x version to PHProjekt 6.0.
     *
     * @param string $module Module to grant permissions to: Project / Note / Todo / Filemanager
     * @param array  $item   Item data.
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
     * @param array $item Array with data of the module item.
     *
     * @return array List of user IDs.
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
     * Collect all the values for save  into 'item_rights' table according to received parameters.
     *
     * @param integer $moduleId   ID of the module in 'module' table.
     * @param integer $itemId     ID of the item in the table of the module.
     * @param array   $userRights List of users and righes: the keys are the user ids and the value for each key is the
     *                            right for that user.
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
     * Insert all the permissions into item_rights table.
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
     * @param integer $oldId ID of the owner user ('von' field) as it is in the original P5 item.
     *
     * @return integer User ID to insert in P6 table owner field.
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
     * Process the received parent project ID for an item, if it is a non-existing one, returns the root project id,
     * otherwise returns the matching P6 project ID for this P5 one.
     *
     * @param integer $oldProjectId ID of the P5 parent project Id as it is in the original P5 item.
     * @param integer $oldGroupId   ID of the P5 group Id as it is in the original P5 item.
     *
     * @return integer User ID to insert in P6 field 'project_id'.
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
     * Parse module and item ID from timeproj.
     *
     * @param array $data Timeproj record.
     *
     * @return array Array (module_id, item_id).
     */
    private function _getItemAndModule($data)
    {
        $moduleId = $this->_getModuleId($data['module']);
        if ($moduleId < 1) {
            $moduleId = 1;
            $itemId   = null;
        } else {
            $itemId = null;
        }

        return array($moduleId, $itemId);
    }

    /**
     * Fix timeZone with the P6 values.
     *
     * @param integer $timeZone +/- TimeZone.
     *
     * @return string P6 timeZone.
     */
    private function _getP6TimeZone($timeZone)
    {
        $timeZone = $timeZone + $this->_diffToUtc;
        if ($timeZone == 0 || $timeZone > 12 || $timeZone < -12) {
            return "000";
        } else {
            return $timeZone;
        }
    }

    /**
     * Fix string within utf8 encode and limit the length of the string.
     *
     * @param string  $string Normal string.
     * @param integer $length Maximal length of the string in bytes.
     *
     * @return string Fixed string.
     */
    private function _fix($string, $length = 0)
    {
        $encodings = mb_detect_order();
        $encodings[] = 'ISO-8859-1';

        $string = mb_convert_encoding(
            $string,
            'UTF-8',
            mb_detect_encoding($string, $encodings, true)
        );

        if ($length !== 0) {
            $string = mb_strcut($string, 0, $length);
        }
        return $string;
    }

    /**
     * Keep the display data of each item.
     *
     * @param integer $moduleId      Module ID.
     * @param integer $itemId        Item ID.
     * @param integer $projectId     Parent project ID.
     * @param string  $firstDisplay  First display string.
     * @param string  $secondDisplay Second display string.
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
     * Process the data of the item into the search words.
     * The return array have per word, the count of occurrences,
     * and the pair moduleId-itemId where is it.
     *
     * @param string  $string   All the string data of the item.
     * @param integer $moduleId Module ID
     * @param integer $itemId   Item ID
     *
     * @return array Array with words.
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
     * Insert all the search_display values.
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
     * Insert all the search_words and search_word_module values.
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

    /**
     * Save a value into the session
     *
     * @param string $name  Namespace for the session.
     * @param array  $value Array with data to save.
     *
     * @return void
     */
    private function _saveSession($name, $value)
    {
        $namespace       = new Zend_Session_Namespace($name);
        $namespace->data = $value;
    }

    /**
     * Get the value of the session.
     *
     * Return an empty array if the session don't exists.
     *
     * @param string $name Namespace for the session.
     *
     * @return array Array with stored data.
     */
    private function _getSession($name)
    {
        $namespace = new Zend_Session_Namespace($name);

        return (isset($namespace->data)) ? $namespace->data : array();
    }

    /**
     * Delete a session.
     *
     * @param string $name Namespace for the session.
     *
     * @return void
     */
    private function _cleanSession($name)
    {
        $namespace = new Zend_Session_Namespace($name);
        $namespace->unsetAll();
    }
}
