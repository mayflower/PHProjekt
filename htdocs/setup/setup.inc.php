<?php
/**
 * Setup routine
 *
 * LICENSE: Licensed under the terms of the GNU Publice License
 *
 * @copyright  Copyright (c) 2007 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *             GNU Public License 2.0
 * @version    CVS: $Id: setup.inc.php 828 2008-07-07 02:05:54Z gustavo $
 * @link       http://www.phprojekt.com
 * @since      File available since Release 1.0
*/

function checkServer() {

    // check the PHP version
    if (substr(phpversion(),0,1) < 5) {
        die("Sorry, you need PHP 5 or newer to run PHProjekt 6");
    }
    
    // check pdo_mysql library
    $tmp = phpversion('pdo_mysql');
    if (empty($tmp)) {
        die("Sorry, you need pdo_mysql extension to install PHProjekt 6");
    }

    // checking if configuration.ini exists
    $baseDir = substr($_SERVER['SCRIPT_FILENAME'], 0, -22);
    if (file_exists($baseDir."configuration.ini")) {
        die("Configuration file found. Please, delete it before run setup again.");
    }

}

function displaySetupForm() {

    $mysqlServer = (empty($_REQUEST['mysql_server'])?"localhost":$_REQUEST['mysql_server']);
    $mysqlUser = (empty($_REQUEST['mysql_user'])?"root":$_REQUEST['mysql_user']);
    $mysqlpass = (empty($_REQUEST['mysql_pass'])?"":$_REQUEST['mysql_pass']);
    $mysqlDatabase = (empty($_REQUEST['mysql_database'])?"phprojekt6":$_REQUEST['mysql_database']);
    $errorMessage = (empty($_SESSION['error_message'])?"":$_SESSION['error_message']);
    unset($_SESSION['error_message']);

    $formContent = file_get_contents("setupForm.php");
    $formContent = str_replace("<%MYSQL_SERVER%>", $mysqlServer, $formContent);
    $formContent = str_replace("<%MYSQL_USERNAME%>", $mysqlUser, $formContent);
    $formContent = str_replace("<%MYSQL_PASS%>", $mysqlPass, $formContent);
    $formContent = str_replace("<%MYSQL_DATABASE%>", $mysqlDatabase, $formContent);
    $formContent = str_replace("<%ERROR_MESSAGE%>", $errorMessage, $formContent);

    echo $formContent;
}

function preInstallChecks() {
    $returnValue = true;
    
    // connecting to mysql server
    $link = @mysql_connect($_REQUEST['mysql_server'], $_REQUEST['mysql_user'], $_REQUEST['mysql_pass']);
    if($link) {

    }
    else {
        $_SESSION['error_message'] = "Can't connect to MySQL server at '".$_REQUEST['mysql_server']."' using '".$_REQUEST['mysql_user']."' user";
        $returnValue = false;
    }

    // selecting database
    if ($returnValue && !mysql_select_db($_REQUEST['mysql_database'])) {
        mysql_query("CREATE DATABASE ".$_REQUEST['mysql_database']);

        if (!mysql_select_db($_REQUEST['mysql_database'])) {
            $_SESSION['error_message'] = "Error selecting database ".$_REQUEST['mysql_database'];
            $returnValue = false;
        }
    }
    
    // creating log folders
    $baseDir = substr($_SERVER['SCRIPT_FILENAME'], 0, -22);

    $configFlie = $baseDir."configuration.ini";
    if (!file_exists($configFlie)) {
        if (!file_put_contents($configFlie, "Test")) {
            $_SESSION['error_message'] = "Error creating the configuration file at ".$configFlie;
            $returnValue = false;
        }
    }
    

    return $returnValue;
}

function displayFinished() {

    if (strlen($_SERVER['REQUEST_URI']) > 16) {
        $serverUrl = "http://".$_SERVER['HTTP_HOST'].substr($_SERVER['REQUEST_URI'], 0, -15);
    }
    else {
        $serverUrl = "http://".$_SERVER['HTTP_HOST']."/";
    }
    $errorMessage = (empty($_SESSION['error_message'])?"":$_SESSION['error_message']);
    unset($_SESSION['error_message']);

    $pageContent = file_get_contents("setupFinished.php");
    $pageContent = str_replace("<%SERVER_URL%>", $serverUrl, $pageContent);
    $pageContent = str_replace("<%ERROR_MESSAGE%>", $errorMessage, $pageContent);

    echo $pageContent;
}

function installPhprojekt() {

    $link = @mysql_connect($_REQUEST['mysql_server'], $_REQUEST['mysql_user'], $_REQUEST['mysql_pass']) or die("Error connecing to MySQL server ".$_REQUEST['mysql_server']);
    if (!mysql_select_db($_REQUEST['mysql_database'])) {
        mysql_query("CREATE DATABASE ".$_REQUEST['mysql_database']) or die("Error creating database ".$_REQUEST['mysql_database']);
    }

    mysql_select_db($_REQUEST['mysql_database']) or die("Error selecting database ".$_REQUEST['mysql_database']);

    $tableList = array("Timecard",
    "Timeproj",
    "ItemRights",
    "Configuration",
    "Note",
    "TagsModules",
    "TagsUsers",
    "Tags",
    "TabModuleRelation",
    "ModuleTabRelation",
    "Tab",
    "SearchWords",
    "SearchWordModule",
    "SearchDisplay",
    "Todo",
    "RoleModulePermissions",
    "ProjectUserRoleRelation",
    "ProjectRoleUserPermissions",
    "ModuleProjectRelation",
    "ProjectModulePermissions",
    "Project",
    "History",
    "GroupsUserRelation",
    "Role",
    "Groups",
    "UserModuleSetting",
    "Module",
    "User",
    "DatabaseManager",
    "Calendar");

    foreach ($tableList as $oneTable) {
        mysql_query("DROP TABLE IF EXISTS ".$oneTable) or die("Error deleting ".$oneTable);
    }

    mysql_query('CREATE TABLE `DatabaseManager` (
      `id` int NOT NULL AUTO_INCREMENT,
      `tableName` varchar(50) default NULL,
      `tableField` varchar(60) default NULL,
      `formTab` int(11) default NULL,
      `formLabel` varchar(255) default NULL,
      `formTooltip` varchar(255) default NULL,
      `formType` varchar(50) default NULL,
      `formPosition` int(11) default NULL,
      `formColumns` int(11) default NULL,
      `formRegexp` varchar(255) default NULL,
      `formRange` text default NULL,
      `defaultValue` varchar(255) default NULL,
      `listPosition` int(11) default NULL,
      `listAlign` varchar(20) default NULL,
      `listUseFilter` int(4) default NULL,
      `altPosition` int(11) default NULL,
      `status` varchar(20) default NULL,
      `isInteger` int(4) default NULL,
      `isRequired` int(4) default NULL,
      `isUnique` int(11) default NULL,
      PRIMARY KEY  (`id`)
    )') or die("Error creating DatabaseManager table");

    mysql_query('CREATE TABLE `User` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `username` varchar(255) NOT NULL,
      `password` varchar(32) NOT NULL,
      `firstname` varchar(255) default NULL,
      `lastname` varchar(255) default NULL,
      `email` varchar(255) default NULL,
      `language` varchar(5) NOT NULL,
      `status` varchar(1) default \'A\',
      PRIMARY KEY(`id`),
      UNIQUE(`username`)
    )') or die("Error creating User table");

    mysql_query('CREATE TABLE `Module` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(255) NOT NULL,
      `active` int(1) NOT NULL default 1,
      PRIMARY KEY  (`id`)
    )') or die("Error creating Module table");

    mysql_query('CREATE TABLE `Groups` (
      `id` int NOT NULL AUTO_INCREMENT,
      `name` varchar(255),
      PRIMARY KEY  (`id`)
    )') or die("Error creating Groups table");


    mysql_query('CREATE TABLE `GroupsUserRelation` (
      `id` int NOT NULL AUTO_INCREMENT,
      `groupsId` int(11) NOT NULL,
      `userId` int(11) NOT NULL,
      PRIMARY KEY  (`id`),
      FOREIGN KEY (`userId`) REFERENCES User(`id`)
    )') or die("Error creating GroupsUserRelation table");


    mysql_query('CREATE TABLE `History` (
      `id` int(11) NOT NULL auto_increment,
      `moduleId` int(11) NOT NULL,
      `userId` int(11) NOT NULL,
      `itemId` int(11) NOT NULL,
      `field` varchar(255) NOT NULL,
      `oldValue` varchar(100) default NULL,
      `newValue` varchar(255) default NULL,
      `action` varchar(50) NOT NULL,
      `datetime` timestamp NOT NULL default CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      FOREIGN KEY (`userId`) REFERENCES User(`id`),
      FOREIGN KEY (`moduleId`) REFERENCES Module(`id`)
    )') or die("Error creating history table");
    mysql_query('CREATE INDEX `History_userId` ON `History`(`userId`)') or die("Error creating History_userId index");

    mysql_query("CREATE TABLE `Project` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `projectId` int(11) default NULL,
      `path` varchar(25) NOT NULL default '/',
      `title` varchar(255) NOT NULL,
      `notes` text default NULL,
      `ownerId` int(11) default NULL,
      `startDate` date default NULL,
      `endDate` date default NULL,
      `priority` int(11) default NULL,
      `currentStatus` varchar(50) NOT NULL default 'working',
      `completePercent` float default '0',
      `hourlyWageRate` float default NULL,
      `budget` float default NULL,
      PRIMARY KEY (`id`),
      FOREIGN KEY (`ownerId`) REFERENCES User(`id`)
    )") or die("Error creating Project table");
    mysql_query('CREATE INDEX `Project_ownerId` ON `Project`(`ownerId`)');

    mysql_query('CREATE TABLE `ProjectModulePermissions` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `moduleId` int(11) NOT NULL,
        `projectId` int(11) NOT NULL,
        PRIMARY KEY (`id`),
        FOREIGN KEY (`moduleId`) REFERENCES Module(`id`),
        FOREIGN KEY (`projectId`) REFERENCES Project(`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
    )') or die("Error creating ProjectModulePermissions table");

    mysql_query('CREATE TABLE `Role` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(255) NOT NULL,
      `parent` int(11) default NULL,
      PRIMARY KEY(`id`)
    )') or die("Error creating Role table");

    mysql_query('CREATE TABLE `ProjectRoleUserPermissions` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `projectId` int(11) NOT NULL,
      `userId` int(11) NOT NULL,
      `roleId` int(11) NOT NULL,
      PRIMARY KEY (`id`),
      FOREIGN KEY (`projectId`) REFERENCES Project(`id`),
      FOREIGN KEY (`userId`) REFERENCES User(`id`),
      FOREIGN KEY (`roleId`) REFERENCES Role(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
    )') or die("Error creating ProjectRoleUserPermissions table");

    mysql_query('CREATE TABLE `RoleModulePermissions` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `roleId` int(11) NOT NULL,
      `moduleId` int(11) NOT NULL,
      `access` int(3) NOT NULL,
      PRIMARY KEY  (`id`),
      FOREIGN KEY (`roleId`) REFERENCES Role(`id`),
      FOREIGN KEY (`moduleId`) REFERENCES Module(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
    )') or die("Error creating RoleModulePermissions table");


    mysql_query('CREATE TABLE `Todo` (
      `id` int(11) NOT NULL auto_increment,
      `title` varchar(255) NOT NULL,
      `notes` text default NULL,
      `ownerId` int(11) default NULL,
      `projectId` int(11) default NULL,
      `startDate` date default NULL,
      `endDate` date default NULL,
      `priority` int(11) default NULL,
      `currentStatus` varchar(50) NOT NULL default \'working\',
      PRIMARY KEY  (`id`)
    )') or die("Error creating Todo table");

    mysql_query('CREATE TABLE `UserModuleSetting` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `userId` int(11) NOT NULL,
      `moduleId` int(11) NOT NULL,
      `keyValue` varchar(255) NOT NULL,
      `value` varchar(255) NOT NULL,
      `identifier`  varchar(50) NOT NULL,
      PRIMARY KEY (`id`),
      FOREIGN KEY (`userId`) REFERENCES User(`id`),
      FOREIGN KEY (`moduleId`) REFERENCES Module(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
    )') or die("Error creating UserModuleSetting table");
    mysql_query('CREATE INDEX `UserModuleSetting_userId` ON `UserModuleSetting`(`userId`)') or die("Error creating UserModule_Settings_userId index");

    mysql_query('CREATE TABLE `SearchWords` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `word` varchar(255) NOT NULL,
      `count` int(11) NOT NULL,
      PRIMARY KEY  (`id`)
    )') or die("Error creating SearchWords table");

    mysql_query('CREATE TABLE `SearchWordModule` (
      `moduleId` int(11) NOT NULL,
      `itemId` int(11) NOT NULL,
      `wordId` int(11) NOT NULL,
      PRIMARY KEY  (`itemId`,`moduleId`,`wordId`)
    )') or die("Error creating SearchWordsModule table");

    mysql_query('CREATE TABLE `SearchDisplay` (
      `moduleId` int(11) NOT NULL,
      `itemId` int(11) NOT NULL,
      `firstDisplay` varchar(255),
      `secondDisplay` varchar(255),
      PRIMARY KEY  (`itemId`,`moduleId`)
    )') or die("Error creating SearchDisplay table");

    mysql_query('CREATE TABLE `Tags` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `word` varchar(255) NOT NULL,
      `crc32` bigint NOT NULL,
      PRIMARY KEY  (`id`)
    )') or die("Error creating Tags table");

    mysql_query('CREATE TABLE `TagsUsers` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `userId` int(11) NOT NULL,
      `tagId` int(11) NOT NULL,
      PRIMARY KEY  (`id`)
    )') or die("Error creating TagsUsers table");

    mysql_query('CREATE TABLE `TagsModules` (
      `moduleId` int(11) NOT NULL,
      `itemId` int(11) NOT NULL,
      `tagUserId` int(11) NOT NULL,
      PRIMARY KEY  (`moduleId`, `itemId`, `tagUserId`)
    )') or die("Error creating TagsModule table");

    mysql_query('CREATE TABLE `Tab` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `label` varchar(255) NOT NULL,
      PRIMARY KEY (`id`)
    )') or die("Error creating Tab table");

    mysql_query('CREATE TABLE `ModuleTabRelation` (
      `tabId` int(11) NOT NULL,
      `moduleId` int(11) NOT NULL,
      PRIMARY KEY (`tabId`, `moduleId`)
    )') or die("Error creating ModuleTableRelation table");

    mysql_query('CREATE TABLE `Note` (
      `id` int(11) NOT NULL auto_increment,
      `projectId` int(11) default NULL,
      `title` varchar(255) NOT NULL,
      `comments` text default NULL,
      `category` varchar(50) default NULL,
      `ownerId` int(11) default NULL,
      PRIMARY KEY  (`id`)
    )') or die("Error creating Note table");

    mysql_query('CREATE TABLE `Configuration` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `moduleId` int(11) NOT NULL,
      `key` varchar(255) NOT NULL,
      `value` text default NULL,
      PRIMARY KEY  (`id`),
      FOREIGN KEY (`moduleId`) REFERENCES Module(`id`)
    )') or die("Error creating Configuration table");

    mysql_query('CREATE TABLE `ItemRights` (
      `moduleId` int(11) NOT NULL,
      `itemId` int(11) NOT NULL,
      `userId` int(11) NOT NULL,
      `access` int(3) NOT NULL,
      PRIMARY KEY  (`moduleId`,`itemId`,`userId`),
      FOREIGN KEY (`moduleId`) REFERENCES Module(`id`),
      FOREIGN KEY (`userId`) REFERENCES User(`id`)
      ON DELETE CASCADE
      ON UPDATE CASCADE
    )') or die("Error creating ItemRights table");

    mysql_query('CREATE TABLE `Timecard` (
      `id` int(11) NOT NULL auto_increment,
      `notes` text default NULL,
      `ownerId` int(11) default NULL,
      `projectId` int(11) default NULL,
      `date` date default NULL,
      `startTime` time default NULL,
      `endTime` time default NULL,
      PRIMARY KEY  (`id`)
    )') or die("Error creating Timecard table");

    mysql_query('CREATE TABLE `Timeproj` (
      `id` int(11) NOT NULL auto_increment,
      `notes` text default NULL,
      `ownerId` int(11) default NULL,
      `projectId` int(11) default NULL,
      `date` date default NULL,
      `startTime` time default NULL,
      `endTime` time default NULL,
      PRIMARY KEY  (`id`)
    )') or die("Error creating Timeproj table");

    mysql_query('CREATE TABLE `Calendar` (
      `id` int(11) NOT NULL auto_increment,
      `title` varchar(255) default NULL,
      `notes` text default NULL,
      `ownerId` int(11) default NULL,
      `projectId` int(11) default NULL,
      `startDate` date default NULL,
      `participantId` int(11) default NULL,
      `startTime` time default NULL,
      `endTime` time default NULL,
      `parentId` int(11) default NULL,
      `serialType` int(11) default NULL,
      `serialDays` int(11) default NULL,
      `endDate` date default NULL,
      PRIMARY KEY  (`id`)
    )') or die("Error creating Calendar table");

    mysql_query("INSERT INTO `Module` (`id`, `name`, `active`) VALUES
        (1, 'Project', 1),
        (2, 'Todo', 1),
        (3, 'Note', 1),
        (4, 'Timecard', 1),
        (5, 'Timeproj', 1),
        (6, 'Calendar', 1)") or die("Error inserting Module information");

    mysql_query("INSERT INTO `DatabaseManager` (`id`, `tableName`, `tableField`, `formTab`, `formLabel`, `formTooltip`, `formType`, `formPosition`, `formColumns`, `formRegexp`, `formRange`, `defaultValue`, `listPosition`, `listAlign`, `listUseFilter`, `altPosition`, `status`, `isInteger`, `isRequired`, `isUnique`) VALUES
        (0, 'Project', 'projectId', 1, 'parent', 'parent', 'tree', 1, 1, NULL, 'Project', '1', 0, NULL, 1, 1, '1', 1, 0, 0),
        (0, 'Project', 'title', 1, 'title', 'title', 'text', 2, 1, NULL, NULL, '', 1, 'left', 1, 2, '1', 0, 1, 0),
        (0, 'Project', 'notes', 1, 'notes', 'notes', 'textarea', 3, 2, NULL, NULL, '', 0, NULL, 1, 0, '1', 0, 0, 0),
        (0, 'Project', 'startDate', 1, 'startDate', 'startDate', 'date', 4, 1, NULL, NULL, '', 3, 'center', 1, 3, '1', 0, 1, 0),
        (0, 'Project', 'endDate', 1, 'endDate', 'endDate', 'date', 5, 1, NULL, NULL, '', 4, 'center', 1, 4, '1', 0, 1, 0),
        (0, 'Project', 'priority', 1, 'priority', 'priority', 'selectValues', 6, 1, NULL, '1#1|2#2|3#3|4#4|5#5|6#6|7#7|8#8|9#9|10#10', '5', 5, 'center', 1, 5, '1', 1, 1, 0),
        (0, 'Project', 'currentStatus', 1, 'currentStatus', 'currentStatus', 'selectValues', 7, 1, NULL, '1#Offered|2#Ordered|3#Working|4#Ended|5#Stopped|6#Re-Opened|7#Waiting', '1', 6, 'center', 1, 6, '1', 0, 0, 0),
        (0, 'Project', 'completePercent', 1, 'completePercent', 'completePercent', 'text', 8, 1, NULL, NULL, '', 7, 'center', 1, 7, '1', 0, 0, 0),
        (0, 'Project', 'budget', 1, 'budget', 'budget', 'text', 9, 1, NULL, NULL, '', 0, NULL, 1, 8, '1', 0, 0, 0),
        (0, 'Todo', 'title', 1, 'title', 'title', 'text', 2, 1, NULL, NULL, '', 1, 'left', 1, 2, '1', 0, 1, 0),
        (0, 'Todo', 'notes', 1, 'notes', 'notes', 'textarea', 3, 2, NULL, NULL, '', 0, NULL, 1, 0, '1', 0, 0, 0),
        (0, 'Todo', 'startDate', 1, 'startDate', 'startDate', 'date', 4, 1, NULL, NULL, '', 3, 'center', 1, 3, '1', 0, 1, 0),
        (0, 'Todo', 'endDate', 1, 'endDate', 'endDate', 'date', 5, 1, NULL, NULL, '', 4, 'center', 1, 4, '1', 0, 1, 0),
        (0, 'Todo', 'priority', 1, 'priority', 'priority', 'selectValues', 6, 1, NULL, '1#1|2#2|3#3|4#4|5#5|6#6|7#7|8#8|9#9|10#10', '5', 5, 'center', 1, 5, '1', 1, 1, 0),
        (0, 'Todo', 'currentStatus', 1, 'currentStatus', 'currentStatus', 'selectValues', 7, 1, NULL, '1#Accepted|2#Working|4#Ended|5#Stopped|7#Waiting', '1', 6, 'center', 1, 6, '1', 0, 0, 0),
        (0, 'Todo', 'projectId', 1, 'project', 'project', 'tree', 1, 1, NULL, 'Project', '', 0, NULL, 1, 1, '1', 1, 0, 0),
        (0, 'History', 'userId', 1, 'UserId', 'UserId', 'userId', '1', '1', NULL, NULL, 0, 1, 'left', 1, 1, 1, 1, 1, 0),
        (0, 'History', 'itemId', 1, 'ItemId', 'ItemId', 'text', '2', '1', NULL, NULL, 0, 2, 'center', 1, 2, 1, 1, 1, 0),
        (0, 'History', 'moduleId', 1, 'Module', 'Module', 'text', '3', '1', NULL, NULL, '', 3, 'left', 1, 3, 1, 0, 1, 0),
        (0, 'History', 'field', 1, 'Field', 'Field', 'text', '4', '1', NULL, NULL, '', 4, 'left', 1, 4, 1, 0, 1, 0),
        (0, 'History', 'oldValue', 1, 'OldValue', 'OldValue', 'text', '5', '1', NULL, NULL, '', 0, '', 0, 0, 1, 0, 1, 0),
        (0, 'History', 'newValue', 1, 'NewValue', 'NewValue', 'text', '6', '1', NULL, NULL, '', 0, '', 0, 0, 1, 0, 1, 0),
        (0, 'History', 'action', 1, 'Action', 'Action', 'text', '7', '1', NULL, NULL, '', 7, 'left', 1, 7, 1, 0, 1, 0),
        (0, 'History', 'datetime', 1, 'Datetime', 'Datetime', 'datetime', '8', '1', NULL, NULL, '', 8, 'center', 1, 8, 1, 0, 1, 0),
        (0, 'Note', 'projectId', 1, 'project', 'project', 'tree', 1, 1, NULL, 'Project', '', 0, NULL, 1, 1, '1', 0, 1, 0),
        (0, 'Note', 'title', 1, 'title', 'title', 'text', 2, 1, NULL, NULL, '', 1, 'left', 1, 2, '1', 0, 1, 0),
        (0, 'Note', 'comments', 1, 'comments', 'comments', 'textarea', 3, 2, NULL, NULL, '', 0, NULL, 1, 0, '1', 0, 1, 0),
        (0, 'Note', 'category', 1, 'category', 'category', 'selectSqlAddOne', 4, 2, NULL, NULL, '', 3, 'center', 1, 3, '1', 0, 0, 0),
        (0, 'Timecard', 'notes'    ,  1, 'notes'    , 'notes'    , 'text'    , 1, 2, NULL, NULL     , '', 1, NULL    , 1, 0, '1', 0, 1, 0),
        (0, 'Timecard', 'date'     ,  1, 'date'     , 'date'     , 'date'    , 2, 1, NULL, NULL     , '', 2, 'center', 1, 1, '1', 0, 1, 0),
        (0, 'Timecard', 'startTime',  1, 'startTime', 'startTime', 'time'    , 3, 1, NULL, NULL     , '', 3, 'center', 1, 0, '1', 0, 1, 0),
        (0, 'Timecard', 'endTime'  ,  1, 'endTime'  , 'endTime'  , 'time'    , 4, 1, NULL, NULL     , '', 4, 'center', 1, 0, '1', 0, 0, 0),
        (0, 'Timecard', 'projectId',  1, 'project'  , 'project'  , 'tree'    , 0, 0, NULL, 'Project', '', 0, NULL, 1, 0, '1', 1, 0, 0),
        (0, 'Timeproj', 'notes'    ,  1, 'notes'    , 'notes'    , 'text'    , 1, 2, NULL, NULL     , '', 1, NULL    , 1, 0, '1', 0, 1, 0),
        (0, 'Timeproj', 'date'     ,  1, 'date'     , 'date'     , 'date'    , 2, 1, NULL, NULL     , '', 2, 'center', 1, 1, '1', 0, 1, 0),
        (0, 'Timeproj', 'startTime',  1, 'startTime', 'startTime', 'time'    , 3, 1, NULL, NULL     , '', 3, 'center', 1, 0, '1', 0, 1, 0),
        (0, 'Timeproj', 'endTime'  ,  1, 'endTime'  , 'endTime'  , 'time'    , 4, 1, NULL, NULL     , '', 4, 'center', 1, 0, '1', 0, 0, 0),
        (0, 'Timeproj', 'projectId',  1, 'project'  , 'project'  , 'tree'    , 5, 1, NULL, 'Project', '', 0, NULL, 1, 0, '1', 1, 1, 0),
        (0, 'Calendar', 'title',      1, 'title'    , 'title'    , 'text'    , 1, 1, NULL, NULL     , '', 1, 'left'  , 1, 2, '1', 0, 1, 0),
        (0, 'Calendar', 'notes',      1, 'notes'    , 'notes'    , 'textarea', 2, 2, NULL, NULL     , '', 0, NULL    , 1, 0, '1', 0, 0, 0),
        (0, 'Calendar', 'startDate',  1, 'startDate', 'startDate', 'date'    , 3, 1, NULL, NULL     , '', 3, 'center', 1, 3, '1', 0, 1, 0),
        (0, 'Calendar', 'participantId',1, 'participantId' , 'participantId'   , 'multipleSelectValues'  , 8, 1, NULL, 'User'     , '', 2, 'left'  , 1, 1, '1', 1, 1, 0),
        (0, 'Calendar', 'startTime',  1, 'startTime', 'startTime', 'time'    , 4, 1, NULL, NULL     , '', 4, 'center', 1, 0, '1', 0, 1, 0),
        (0, 'Calendar', 'endTime',    1, 'endTime'  , 'endTime'  , 'time'    , 5, 1, NULL, NULL     , '', 6, 'center', 1, 0, '1', 0, 0, 0),
        (0, 'Calendar', 'projectId',  1, 'project' , 'project'   , 'tree'    , 6, 1, NULL, 'Project', '', 0, NULL, 1, 0, '1', 1, 1, 0),
        (0, 'Calendar', 'serialType', 1, 'serialType', 'serialType', 'selectValues', 7, 1, NULL, '1#Once|2#Daily|3#Weekly|4#Montlhy|5#Anually', '1', 0, 'center', 1, 0, '1', 0, 0, 0),
        (0, 'Calendar', 'serialDays', 1, 'serialDays', 'serialDays', 'selectValues', 7, 1, NULL, '0#All|1#Monday|2#Tuesday|3#Wednesday|4#Thursday|5#Friday|6#Saturday|7#Sunday', '1', 0, 'center', 1, 0, '1', 0, 0, 0),
        (0, 'Calendar', 'endDate',    1, 'endDate'  , 'endDate'  , 'date'    , 8, 1, NULL, NULL     , '', 5, 'center', 1, 0, '1', 0, 1, 0)") or die("Error inserting DatabaseManager information");

    mysql_query("INSERT INTO `User` (`id`, `username`, `password`, `firstname`, `lastname`, `email`, `language`, `status`) VALUES
        (1,'admin',md5('phprojektmd5".$_REQUEST['admin_pass']."'),NULL,NULL,'admin@example.com','','A')") or die("Error inserting User information");

    mysql_query("INSERT INTO `Project` (`id`, `projectId`, `path`, `title`, `notes`, `ownerId`, `startDate`, `endDate`, `priority`, `currentStatus`, `completePercent`, `hourlyWageRate`, `budget`) VALUES
        (1, NULL, '/', 'PHProjekt', '', 1, '2008-08-01', '2008-12-31', 1, 'working', 0, NULL, NULL)") or die("Error inserting Project information");

    mysql_query("INSERT INTO `Groups` (`id`, `name`) VALUES (1, 'default')") or die("Error inserting GRoups information");

    mysql_query("INSERT INTO `Role` (`id`, `name`, `parent`) VALUES (1, 'Admin Role', null)") or die("Error inserting Role information");

    mysql_query("INSERT INTO `GroupsUserRelation` (`id`, `groupsId`, `userId`) VALUES (1, 1, 1)") or die("Error inserting GroupsUserRelation information");

    mysql_query("INSERT INTO `ProjectRoleUserPermissions` (`projectId`, `userId`, `roleId`) VALUES (1, 1, 1)") or die("Error inserting ProjectRoleUserPermissions information");

    mysql_query("INSERT INTO `RoleModulePermissions` (`roleId`, `moduleId`, `access`) VALUES (1, 1, 139)") or die("Error inserting RoleModulePermissions information");

    mysql_query("INSERT INTO `ItemRights` (`moduleId`, `itemId`, `userId`, `access`) VALUES (1, 1, 1, 255)") or die("Error inserting ItemRights information");

    mysql_query("INSERT INTO `ProjectModulePermissions` (`moduleId`, `projectId`) VALUES
        (1, 1),
        (2, 1),
        (3, 1),
        (4, 1),
        (5, 1),
        (6, 1)") or die("Error inserting Module information");
    
    // creating log folders
    $baseDir = substr($_SERVER['SCRIPT_FILENAME'], 0, -22);

    $logsDir = $baseDir."logs";

    if (!file_exists($logsDir)) {
        if (!mkdir($logsDir)) {
            $_SESSION['error_message'] = "Please, create the dir ".$logsDir." to save the logs. Otherwise, modify the log path on configuration.ini flie.";
        }
    }

    // getting the languaje

    $clientLanguaje = 'en'; // default value

    if (function_exists('getallheaders')) {
        $headers = getallheaders();
    } else {
        $headers['Accept-Language'] = $_ENV['HTTP_ACCEPT_LANGUAGE'];
    }
    if ((!empty($headers['Accept-Language'])) && strlen($headers['Accept-Language']) > 1) {
        $clientLanguaje = substr($headers['Accept-Language'],0,2);
    }
    
    if (strlen($_SERVER['REQUEST_URI']) > 16) {
        $webPath = "http://".$_SERVER['HTTP_HOST'].substr($_SERVER['REQUEST_URI'], 0, -15);
    }
    else {
        $webPath = "http://".$_SERVER['HTTP_HOST']."/";
    }

    // Creating the configuration file
    $configurationFileContent = '[production]

; Language configuration
language             = "'.$clientLanguaje.'"

; Path options
webpath              = '.$webPath.'
; Database options
database.type        = pdo_mysql
database.host        = '.$_REQUEST['mysql_server'].'
database.username    = '.$_REQUEST['mysql_user'].'
database.password    = '.$_REQUEST['mysql_pass'].'
database.name        = '.$_REQUEST['mysql_database'].'

; Log options
log.debug.filename   = '.$logsDir.'\debug.log
log.crit.filename    = '.$logsDir.'\crit.log
itemsPerPage         = 3;';

    file_put_contents($baseDir."configuration.ini",$configurationFileContent);

}