<?php
/**
 * Setup routine
 *
 * LICENSE: Licensed under the terms of the GNU Publice License
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *             GNU Public License 2.0
 * @version    $Id$
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
*/

function checkServer()
{
    // check the PHP version
    if (substr(phpversion(), 0, 1) < 5) {
        die("Sorry, you need PHP 5 or newer to run PHProjekt 6");
    }

    // check pdo library
    $tmp  = phpversion('pdo_mysql');
    $tmp2 = phpversion('pdo_sqlite2');
    $tmp3 = phpversion('pdo_pgsql');

    if (empty($tmp) && empty($tmp2) && empty($tmp3)) {
        die("Sorry, you need pdo_mysql, pdo_pgsql or pdo_sqlite extension to install PHProjekt 6");
    }

    // checking if configuration.ini exists
    $baseDir = substr($_SERVER['SCRIPT_FILENAME'], 0, -22);
    if (file_exists($baseDir."configuration.ini")) {
        die("Configuration file found. Please, delete it before run setup again.");
    }
}

function displaySetupForm() {

    $availableEngines = array ("pdo_mysql" => "MySQL",
    // "pdo_pgsql" => "PostgreSQL",
    // "pdo_sqlite" => "SQLite"
    );

    $serverType = (empty($_REQUEST['server_type'])?"pdo_mysql":$_REQUEST['server_type']);
    $serverHost = (empty($_REQUEST['server_host'])?"localhost":$_REQUEST['server_host']);
    $serverUser = (empty($_REQUEST['server_user'])?"root":$_REQUEST['server_user']);
    $serverPass = (empty($_REQUEST['server_pass'])?"":$_REQUEST['server_pass']);
    $serverDatabase = (empty($_REQUEST['server_database'])?"phprojekt6":$_REQUEST['server_database']);
    $migrationConfig = (empty($_REQUEST['migration_config'])?"":$_REQUEST['migration_config']);
    $errorMessage = (empty($_SESSION['error_message'])?"":$_SESSION['error_message']);
    unset($_SESSION['error_message']);



    $formContent = file_get_contents("setupForm.php");

    $tmp = '';
    foreach ($availableEngines as $key => $value) {
        $tmp .= '<option value="'.$key.'"';
        if ($key == $serverType) $tmp .= "selected='selected'";
        $tmp .= ">".$value."</option>\n";
    }
    $formContent = str_replace("<%SERVER_TYPE%>", $tmp, $formContent);
    $formContent = str_replace("<%SERVER_HOST%>", $serverHost, $formContent);
    $formContent = str_replace("<%SERVER_USERNAME%>", $serverUser, $formContent);
    $formContent = str_replace("<%SERVER_PASS%>", $serverPass, $formContent);
    $formContent = str_replace("<%SERVER_DATABASE%>", $serverDatabase, $formContent);
    $formContent = str_replace("<%MIGRATION_CONFIG%>", $migrationConfig, $formContent);
    $formContent = str_replace("<%ERROR_MESSAGE%>", $errorMessage, $formContent);

    echo $formContent;
}

function preInstallChecks()
{
    $returnValue = true;

    if ($_REQUEST['server_type'] == 'pdo_mysql') {

        @mysql_connect($_REQUEST['server_host'], $_REQUEST['server_user'], $_REQUEST['server_pass']);

        @mysql_query("CREATE DATABASE ".$_REQUEST['server_database']." DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;");

        if (!mysql_select_db($_REQUEST['server_database'])) {
            $_SESSION['error_message'] = "Error selecting database ".$_REQUEST['server_database'];
            $returnValue = false;
        }
    }

    try {
        if ($_REQUEST['server_type'] == 'pdo_sqlite2') {
            $params = array('dbname' => $_REQUEST['server_host']);
        } else {
            $params = array(
            'host'     => $_REQUEST['server_host'],
            'username' => $_REQUEST['server_user'],
            'password' => $_REQUEST['server_pass'],
            'dbname'   => $_REQUEST['server_database']
            );

        }

        Zend_Db::factory($_REQUEST['server_type'], $params);

    } catch (Exception $error) {
        $_SESSION['error_message'] = "Can't connect to server at '".$_REQUEST['server_host']
        . "' using '".$_REQUEST['server_user']."' user"
        . "(". $error->getMessage() .")";
        $returnValue = false;
    }

    // creating log folders
    $baseDir = substr($_SERVER['SCRIPT_FILENAME'], 0, -22);

    $configFile = $baseDir."configuration.ini";
    
    if (!file_exists($configFile)) {
        if (!file_put_contents($configFile, "Test")) {
            $_SESSION['error_message'] = "Error creating the configuration file at ".$configFile;
            $returnValue = false;
        } else {
            unlink($configFile);
        }
    }

    if (isset($_REQUEST['migration_config']) && file_exists($_REQUEST['migration_config'])) {

        include_once($_REQUEST['migration_config']);

        // check version
        if (substr(PHPR_VERSION, 0, 1) != '5') {
            $_SESSION['error_message'] ="Sorry, but it is not possible to migrate PHProjekt minor than 5.0";
        }
    }

    return $returnValue;
}

function displayFinished() {

    if (strlen($_SERVER['REQUEST_URI']) > 16) {
        $serverUrl = "http://".$_SERVER['HTTP_HOST'].substr($_SERVER['REQUEST_URI'], 0, -15);
    } else {
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
    
    try {
        if ($_REQUEST['server_type'] == 'pdo_sqlite2') {
            $params = array('dbname' => $_REQUEST['server_host']);
        } else {
            

            $params = array(
            'host'     => $_REQUEST['server_host'],
            'username' => $_REQUEST['server_user'],
            'password' => $_REQUEST['server_pass'],
            'dbname'   => $_REQUEST['server_database']
            );

        }

        $db = Zend_Db::factory($_REQUEST['server_type'], $params);
        

    } catch (Exception $error) {
        die("Error connecting to server " . "(" . $error->getMessage() . ")");
    }

    $tableList = array("Contact",
    "Timecard",
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
    "UserSetting",
    "Setting",
    "Module",
    "User",
    "DatabaseManager",
    "Calendar",
    "Filemanager");

    $tableManager = new Phprojekt_Table($db);

    foreach ($tableList as $oneTable) {
        if ($tableManager->tableExists($oneTable)) {
            
            // fix for Zend Framework 1.7.2 and Windows operating system
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $db->closeConnection();
            }

            $tableManager->dropTable($oneTable);
        }
    }

    $result = $tableManager->createTable('databaseManager',
    array('id' => array (
    'type' => 'auto_increment', 'null' => false),
    'tableName' => array(
    'type' => 'varchar', 'length' => 50, 'null' => true),
    'tableField' => array(
    'type' => 'varchar', 'length' => 60, 'null' => true),
    'formTab' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    'formLabel' => array(
    'type' => 'varchar', 'length' => 255, 'null' => true),
    'formType' => array(
    'type' => 'varchar', 'length' => 50, 'null' => true),
    'formPosition' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    'formColumns' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    'formRegexp' => array(
    'type' => 'varchar', 'length' => 255, 'null' => true),
    'formRange' => array(
    'type' => 'text', 'null' => true),
    'defaultValue' => array(
    'type' => 'varchar', 'length' => 255, 'null' => true),
    'listPosition' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    'listAlign' => array(
    'type' => 'varchar', 'length' => 20, 'null' => true),
    'listUseFilter' => array(
    'type' => 'int', 'length' => 4, 'null' => true),
    'altPosition' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    'status' => array(
    'type' => 'varchar', 'length' => 20, 'null' => true),
    'isInteger' => array(
    'type' => 'int', 'length' => 4, 'null' => true),
    'isRequired' => array(
    'type' => 'int', 'length' => 4, 'null' => true),
    'isUnique' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    ),
    // primary keys
    array('id'));
    if (!$result) {
        die("Error creating the table ");
    }

    $result = $tableManager->createTable('User',
    array('id' => array (
    'type' => 'auto_increment', 'null' => false),
    'username' => array(
    'type' => 'varchar', 'length' => 255, 'null' => true),
    'firstname' => array(
    'type' => 'varchar', 'length' => 255, 'null' => true),
    'lastname' => array(
    'type' => 'varchar', 'length' => 255, 'null' => true),
    'status' => array(
    'type' => 'varchar', 'length' => 1, 'null' => true, 'default' => 'A'),
    'admin' => array(
    'type' => 'int', 'length' => 1, 'null' => false , 'default' => 0),
    ),
    array('id'));
    if (!$result) {
        die("Error creating the table ");
    }

    $result = $tableManager->createTable('Module',
    array('id' => array (
    'type' => 'auto_increment', 'null' => false),
    'name' => array(
    'type' => 'varchar', 'length' => 255, 'null' => true),
    'label' => array(
    'type' => 'varchar', 'length' => 255, 'null' => true),
    'saveType' => array(
    'type' => 'int', 'length' => 1, 'null' => false, 'default' => '0'),
    'active' => array(
    'type' => 'int', 'length' => 1, 'null' => false, 'default' => '1'),
    ),
    array('id'));
    if (!$result) {
        die("Error creating the table ");
    }

    $result = $tableManager->createTable('Groups',
    array('id' => array (
    'type' => 'auto_increment', 'null' => false),
    'name' => array(
    'type' => 'varchar', 'length' => 255, 'null' => true),
    ),
    array('id'));
    if (!$result) {
        die("Error creating the table ");
    }

    $result = $tableManager->createTable('GroupsUserRelation',
    array('id' => array (
    'type' => 'auto_increment', 'null' => false),
    'groupsId' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    'userId' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    ),
    array('id'));
    if (!$result) {
        die("Error creating the table ");
    }

    $result = $tableManager->createTable('History',
    array('id' => array (
    'type' => 'auto_increment', 'null' => false),
    'moduleId' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    'userId' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    'itemId' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    'field' => array(
    'type' => 'varchar', 'length' => 255, 'null' => true),
    'oldValue' => array(
    'type' => 'text', 'null' => true),
    'newValue' => array(
    'type' => 'text', 'null' => true),
    'action' => array(
    'type' => 'varchar', 'length' => 50, 'null' => true),
    'datetime' => array(
    'type' => 'timestamp', 'length' => 255, 'null' => false, 'default' => 'CURRENT_TIMESTAMP',
    'default_no_quote' => true),
    ),
    array('id'));
    if (!$result) {
        die("Error creating the table ");
    }

    $result = $tableManager->createTable('Project',
    array('id' => array (
    'type' => 'auto_increment', 'null' => false),
    'projectId' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    'path' => array(
    'type' => 'varchar', 'length' => 25, 'null' => true, 'default' => '/'),
    'title' => array(
    'type' => 'varchar', 'length' => 255, 'null' => true),
    'notes' => array(
    'type' => 'text', 'null' => true),
    'ownerId' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    'startDate' => array(
    'type' => 'date', 'null' => true),
    'endDate' => array(
    'type' => 'date', 'null' => true),
    'priority' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    'currentStatus' => array(
    'type' => 'varchar', 'length' => 50, 'null' => true, 'default' => 'working'),
    'completePercent' => array(
    'type' => 'varchar', 'length' => 4, 'null' => true),
    'hourlyWageRate' => array(
    'type' => 'varchar', 'length' => 10, 'null' => true),
    'budget' => array(
    'type' => 'varchar', 'length' => 10, 'null' => true),
    'contactId' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    ),
    array('id'));
    if (!$result) {
        die("Error creating the table ");
    }

    $result = $tableManager->createTable('ProjectModulePermissions',
    array('id' => array (
    'type' => 'auto_increment', 'null' => false),
    'moduleId' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    'projectId' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    ),
    array('id'));
    if (!$result) {
        die("Error creating the table ");
    }

    $result = $tableManager->createTable('Role',
    array('id' => array (
    'type' => 'auto_increment', 'null' => false),
    'name' => array(
    'type' => 'varchar', 'length' => 255, 'null' => false),
    'parent' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    ),
    array('id'));
    if (!$result) {
        die("Error creating the table ");
    }

    $result = $tableManager->createTable('ProjectRoleUserPermissions',
    array('id' => array (
    'type' => 'auto_increment', 'null' => false),
    'projectId' => array(
    'type' => 'int', 'length' => 11, 'null' => false),
    'userId' => array(
    'type' => 'int', 'length' => 11, 'null' => false),
    'roleId' => array(
    'type' => 'int', 'length' => 11, 'null' => false),
    ),
    array('id'));
    if (!$result) {
        die("Error creating the table ");
    }

    $result = $tableManager->createTable('RoleModulePermissions',
    array('id' => array (
    'type' => 'auto_increment', 'null' => false),
    'roleId' => array(
    'type' => 'int', 'length' => 11, 'null' => false),
    'moduleId' => array(
    'type' => 'int', 'length' => 11, 'null' => false),
    'access' => array(
    'type' => 'int', 'length' => 3, 'null' => false),
    ),
    array('id'));
    if (!$result) {
        die("Error creating the table ");
    }

    $result = $tableManager->createTable('Todo',
    array('id' => array (
    'type' => 'auto_increment', 'null' => false),
    'title' => array(
    'type' => 'varchar', 'length' => 255, 'null' => true),
    'notes' => array(
    'type' => 'text', 'null' => true),
    'ownerId' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    'projectId' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    'startDate' => array(
    'type' => 'date', 'null' => true),
    'endDate' => array(
    'type' => 'date', 'null' => true),
    'priority' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    'currentStatus' => array(
    'type' => 'varchar', 'length' => 50, 'null' => true, 'default' => 'working'),
    'userId' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    
    ),
    array('id'));
    if (!$result) {
        die("Error creating the table ");
    }

    $result = $tableManager->createTable('Setting',
    array('id' => array (
    'type' => 'auto_increment', 'null' => false),
    'userId' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    'moduleId' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    'keyValue' => array(
    'type' => 'varchar', 'length' => 255, 'null' => true),
    'value' => array(
    'type' => 'text', 'null' => true),
    'identifier' => array(
    'type' => 'varchar', 'length' => 50, 'null' => true),
    ),
    array('id'));
    if (!$result) {
        die("Error creating the table ");
    }

    $result = $tableManager->createTable('SearchWords',
    array('id' => array (
    'type' => 'auto_increment', 'null' => false),
    'word' => array(
    'type' => 'varchar', 'length' => 255, 'null' => true),
    'count' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    ),
    array('id'));
    if (!$result) {
        die("Error creating the table ");
    }

    $result = $tableManager->createTable('SearchWordModule',
    array('moduleId' => array(
    'type' => 'int', 'length' => 11, 'null' => false),
    'itemId' => array(
    'type' => 'int', 'length' => 11, 'null' => false),
    'wordId' => array(
    'type' => 'int', 'length' => 11, 'null' => false),
    ),
    array('itemId', 'moduleId', 'wordId'));
    if (!$result) {
        die("Error creating the table ");
    }

    $result = $tableManager->createTable('SearchDisplay',
    array('moduleId' => array(
    'type' => 'int', 'length' => 11, 'null' => false),
    'itemId' => array(
    'type' => 'int', 'length' => 11, 'null' => false),
    'firstDisplay' => array(
    'type' => 'text', 'null' => true),
    'secondDisplay' => array(
    'type' => 'text', 'null' => true),
    'projectId' => array(
    'type' => 'int', 'length' => 11, 'null' => false),
    ),
    array('itemId', 'moduleId'));
    if (!$result) {
        die("Error creating the table ");
    }

    $result = $tableManager->createTable('Tags',
    array('id' => array (
    'type' => 'auto_increment', 'null' => false),
    'word' => array(
    'type' => 'varchar', 'length' => 255, 'null' => false),
    'crc32' => array(
    'type' => 'bigint', 'null' => false),
    ),
    array('id'));
    if (!$result) {
        die("Error creating the table ");
    }

    $result = $tableManager->createTable('TagsUsers',
    array('id' => array (
    'type' => 'auto_increment', 'null' => false),
    'userId' => array(
    'type' => 'int', 'length' => 11, 'null' => false),
    'tagId' => array(
    'type' => 'int', 'length' => 11, 'null' => false),
    ),
    array('id'));
    if (!$result) {
        die("Error creating the table ");
    }

    $result = $tableManager->createTable('TagsModules',
    array('moduleId' => array(
    'type' => 'int', 'length' => 11, 'null' => false),
    'itemId' => array(
    'type' => 'int', 'length' => 11, 'null' => false),
    'tagUserId' => array(
    'type' => 'int', 'length' => 11, 'null' => false),
    ),
    array('moduleId', 'itemId', 'tagUserId'));
    if (!$result) {
        die("Error creating the table ");
    }

    $result = $tableManager->createTable('Tab',
    array('id' => array (
    'type' => 'auto_increment', 'null' => false),
    'label' => array(
    'type' => 'varchar', 'length' => 255, 'null' => false),
    ),
    array('id'));
    if (!$result) {
        die("Error creating the table ");
    }

    $result = $tableManager->createTable('ModuleTabRelation',
    array('tabId' => array(
    'type' => 'int', 'length' => 11, 'null' => false),
    'moduleId' => array(
    'type' => 'int', 'length' => 11, 'null' => false),
    ),
    array('tabId', 'moduleId'));
    if (!$result) {
        die("Error creating the table ");
    }

    $result = $tableManager->createTable('Note',
    array('id' => array (
    'type' => 'auto_increment', 'null' => false),
    'projectId' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    'title' => array(
    'type' => 'varchar', 'length' => 255, 'null' => true),
    'comments' => array(
    'type' => 'text', 'null' => true),
    'category' => array(
    'type' => 'varchar', 'length' => 50, 'null' => true),
    'ownerId' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    ),
    array('id'));
    if (!$result) {
        die("Error creating the table ");
    }

    $result = $tableManager->createTable('Configuration',
    array('id' => array (
    'type' => 'auto_increment', 'null' => false),
    'moduleId' => array(
    'type' => 'int', 'length' => 11, 'null' => false),
    'keyValue' => array(
    'type' => 'varchar', 'length' => 255, 'null' => false),
    'value' => array(
    'type' => 'text', 'null' => true),
    ),
    array('id'));
    if (!$result) {
        die("Error creating the table ");
    }

    $result = $tableManager->createTable('ItemRights',
    array('moduleId' => array(
    'type' => 'int', 'length' => 11, 'null' => false),
    'itemId' => array(
    'type' => 'int', 'length' => 11, 'null' => false),
    'userId' => array(
    'type' => 'int', 'length' => 11, 'null' => false),
    'access' => array(
    'type' => 'int', 'length' => 3, 'null' => false),
    ),
    array('moduleId', 'itemId', 'userId'));
    if (!$result) {
        die("Error creating the table ");
    }

    $result = $tableManager->createTable('Timecard',
    array('id' => array (
    'type' => 'auto_increment', 'null' => false),
    'ownerId' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    'date' => array(
    'type' => 'date', 'null' => true),
    'startTime' => array(
    'type' => 'time', 'null' => true),
    'endTime' => array(
    'type' => 'time', 'null' => true),
    ),
    array('id'));
    if (!$result) {
        die("Error creating the table ");
    }

    $result = $tableManager->createTable('Timeproj',
    array('id' => array (
    'type' => 'auto_increment', 'null' => false),
    'notes' => array(
    'type' => 'text', 'null' => true),
    'ownerId' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    'projectId' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    'date' => array(
    'type' => 'date', 'null' => true),
    'amount' => array(
    'type' => 'time', 'null' => true),
    ),
    array('id'));
    if (!$result) {
        die("Error creating the table ");
    }

    $result = $tableManager->createTable('Calendar',
    array('id' => array (
    'type' => 'auto_increment', 'null' => false),
    'parentId' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    'ownerId' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    'projectId' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    'title' => array(
    'type' => 'varchar', 'length' => 255, 'null' => true),
    'notes' => array(
    'type' => 'text', 'null' => true),
    'uid' => array(
    'type' => 'varchar', 'length' => 255, 'null' => true),
    'startDate' => array(
    'type' => 'date', 'null' => true),
    'startTime' => array(
    'type' => 'time', 'null' => true),
    'endDate' => array(
    'type' => 'date', 'null' => true),
    'endTime' => array(
    'type' => 'time', 'null' => true),
    'created' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    'modified' => array(
    'type' => 'int', 'length' => 10, 'null' => true),
    'timezone' => array(
    'type' => 'varchar', 'length' => 50, 'null' => true),
    'location' => array(
    'type' => 'varchar', 'length' => 255, 'null' => true),
    'categories' => array(
    'type' => 'text', 'null' => true),
    'attendee' => array(
    'type' => 'text', 'null' => true),
    'status' => array(
    'type' => 'int', 'length' => 1, 'null' => true),
    'priority' => array(
    'type' => 'int', 'length' => 1, 'null' => true),
    'class' => array(
    'type' => 'int', 'length' => 1, 'null' => true),
    'transparent' => array(
    'type' => 'int', 'length' => 1, 'null' => true),
    'rrule' => array(
    'type' => 'text', 'null' => true),
    'properties' => array(
    'type' => 'text', 'null' => true),
    'deleted' => array(
    'type' => 'int', 'length' => 1, 'null' => true),
    'participantId' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    ),
    array('id'));
    if (!$result) {
        die("Error creating the table Calendar");
    }
    
    
    $result = $tableManager->createTable('Contact',
    array('id' => array (
    'type' => 'auto_increment', 'null' => false),
    'ownerId' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    'projectId' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    'name' => array(
    'type' => 'varchar', 'length' => 255, 'null' => true),
    'email' => array(
    'type' => 'varchar', 'length' => 255, 'null' => true),
    'company' => array(
    'type' => 'varchar', 'length' => 255, 'null' => true),
    'firstphone' => array(
    'type' => 'varchar', 'length' => 255, 'null' => true),
    'secondphone' => array(
    'type' => 'varchar', 'length' => 255, 'null' => true),
    'mobilephone' => array(
    'type' => 'varchar', 'length' => 255, 'null' => true),
    'street' => array(
    'type' => 'varchar', 'length' => 255, 'null' => true),
    'city' => array(
    'type' => 'varchar', 'length' => 255, 'null' => true),
    'zipcode' => array(
    'type' => 'varchar', 'length' => 50, 'null' => true),
    'country' => array(
    'type' => 'varchar', 'length' => 255, 'null' => true),
    'comment' => array(
    'type' => 'text', 'null' => true),
    'private' => array(
    'type' => 'int', 'length' => 1, 'null' => true),
    ),
    array('id'));
    if (!$result) {
        die("Error creating the table Contact");
    }

    $result = $tableManager->createTable('Filemanager',
    array('id' => array (
    'type' => 'auto_increment', 'null' => false),
    'ownerId' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    'title' => array(
    'type' => 'varchar', 'length' => 50, 'null' => true),
    'comments' => array(
    'type' => 'text', 'null' => true),
    'projectId' => array(
    'type' => 'int', 'length' => 11, 'null' => true),
    'category' => array(
    'type' => 'varchar', 'length' => 50, 'null' => true),
    'files' => array(
    'type' => 'text', 'null' => true),
    ),
    array('id'));
    if (!$result) {
        die("Error creating the table Filemanager");
    }

    $db->insert('Module', array(
    'id' => 1,
    'name' => 'Project',
    'label' => 'Project',
    'saveType' => 0,
    'active' => 1
    ));

    $db->insert('Module', array(
    'id' => 2,
    'name' => 'Todo',
    'label' => 'Todo',
    'saveType' => 0,
    'active' => 1
    ));

    $db->insert('Module', array(
    'id' => 3,
    'name' => 'Note',
    'label' => 'Note',
    'saveType' => 0,
    'active' => 1
    ));

    $db->insert('Module', array(
    'id' => 4,
    'name' => 'Timecard',
    'label' => 'Timecard',
    'saveType' => 1,
    'active' => 1
    ));

    $db->insert('Module', array(
    'id' => 5,
    'name' => 'Calendar',
    'label' => 'Calendar',
    'saveType' => 1,
    'active' => 1
    ));

    $db->insert('Module', array(
    'id' => 6,
    'name' => 'Gantt',
    'label' => 'Gantt',
    'saveType' => 0,
    'active' => 1
    ));

    $db->insert('Module', array(
    'id' => 7,
    'name' => 'Filemanager',
    'label' => 'Filemanager',
    'saveType' => 1,
    'active' => 1
    ));

    $db->insert('Module', array(
    'id' => 8,
    'name' => 'Statistics',
    'label' => 'Statistics',
    'saveType' => 0,
    'active' => 1
    ));

    $db->insert('DatabaseManager', array(
    'id' => 1,
    'tableName' => 'Project',
    'tableField' => 'title',
    'formTab' => 1,
    'formLabel' => 'title',
    'formType' => 'text',
    'formPosition' => 1,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '',
    'defaultValue' => '',
    'listPosition' => 1,
    'listAlign' => 'left',
    'listUseFilter' => 1,
    'altPosition' => 2,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 1,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 2,
    'tableName' => 'Project',
    'tableField' => 'notes',
    'formTab' => 1,
    'formLabel' => 'notes',
    'formType' => 'textarea',
    'formPosition' => 2,
    'formColumns' => 2,
    'formRegexp' => '',
    'formRange' => '',
    'defaultValue' => '',
    'listPosition' => 0,
    'listAlign' => '',
    'listUseFilter' => 1,
    'altPosition' => 0,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 0,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 3,
    'tableName' => 'Project',
    'tableField' => 'projectId',
    'formTab' => 1,
    'formLabel' => 'parent',
    'formType' => 'selectValues',
    'formPosition' => 3,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => 'Project#id#title',
    'defaultValue' => '1',
    'listPosition' => 0,
    'listAlign' => '',
    'listUseFilter' => 1,
    'altPosition' => 1,
    'status' => '1',
    'isInteger' => 1,
    'isRequired' => 0,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 4,
    'tableName' => 'Project',
    'tableField' => 'startDate',
    'formTab' => 1,
    'formLabel' => 'startDate',
    'formType' => 'date',
    'formPosition' => 4,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '',
    'defaultValue' => '',
    'listPosition' => 3,
    'listAlign' => 'center',
    'listUseFilter' => 1,
    'altPosition' => 3,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 0,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 5,
    'tableName' => 'Project',
    'tableField' => 'endDate',
    'formTab' => 1,
    'formLabel' => 'endDate',
    'formType' => 'date',
    'formPosition' => 5,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '',
    'defaultValue' => '',
    'listPosition' => 4,
    'listAlign' => 'center',
    'listUseFilter' => 1,
    'altPosition' => 4,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 0,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 6,
    'tableName' => 'Project',
    'tableField' => 'priority',
    'formTab' => 1,
    'formLabel' => 'priority',
    'formType' => 'selectValues',
    'formPosition' => 6,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '1#1|2#2|3#3|4#4|5#5|6#6|7#7|8#8|9#9|10#10',
    'defaultValue' => '5',
    'listPosition' => 5,
    'listAlign' => 'center',
    'listUseFilter' => 1,
    'altPosition' => 5,
    'status' => '1',
    'isInteger' => 1,
    'isRequired' => 0,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 7,
    'tableName' => 'Project',
    'tableField' => 'currentStatus',
    'formTab' => 1,
    'formLabel' => 'currentStatus',
    'formType' => 'selectValues',
    'formPosition' => 7,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '1#Offered|2#Ordered|3#Working|4#Ended|5#Stopped|6#Re-Opened|7#Waiting',
    'defaultValue' => '1',
    'listPosition' => 6,
    'listAlign' => 'center',
    'listUseFilter' => 1,
    'altPosition' => 6,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 0,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 8,
    'tableName' => 'Project',
    'tableField' => 'completePercent',
    'formTab' => 1,
    'formLabel' => 'completePercent',
    'formType' => 'percentage',
    'formPosition' => 8,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '',
    'defaultValue' => '',
    'listPosition' => 7,
    'listAlign' => 'center',
    'listUseFilter' => 1,
    'altPosition' => 7,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 0,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 9,
    'tableName' => 'Project',
    'tableField' => 'budget',
    'formTab' => 1,
    'formLabel' => 'budget',
    'formType' => 'text',
    'formPosition' => 9,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '',
    'defaultValue' => '',
    'listPosition' => 0,
    'listAlign' => '',
    'listUseFilter' => 1,
    'altPosition' => 8,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 0,
    'isUnique' => 0
    ));
    
    $db->insert('DatabaseManager', array(
    'id' => 35,
    'tableName' => 'Todo',
    'tableField' => 'contactId',
    'formTab' => 1,
    'formLabel' => 'Contact',
    'formType' => 'selectValues',
    'formPosition' => 10,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => 'Contact#id#name',
    'defaultValue' => '',
    'listPosition' => 0,
    'listAlign' => '',
    'listUseFilter' => 1,
    'altPosition' => 1,
    'status' => '1',
    'isInteger' => 1,
    'isRequired' => 0,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 10,
    'tableName' => 'Todo',
    'tableField' => 'title',
    'formTab' => 1,
    'formLabel' => 'title',
    'formType' => 'text',
    'formPosition' => 1,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '',
    'defaultValue' => '',
    'listPosition' => 1,
    'listAlign' => 'left',
    'listUseFilter' => 1,
    'altPosition' => 2,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 1,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 11,
    'tableName' => 'Todo',
    'tableField' => 'notes',
    'formTab' => 1,
    'formLabel' => 'notes',
    'formType' => 'textarea',
    'formPosition' => 2,
    'formColumns' => 2,
    'formRegexp' => '',
    'formRange' => '',
    'defaultValue' => '',
    'listPosition' => 0,
    'listAlign' => '',
    'listUseFilter' => 1,
    'altPosition' => 0,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 0,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 12,
    'tableName' => 'Todo',
    'tableField' => 'startDate',
    'formTab' => 1,
    'formLabel' => 'startDate',
    'formType' => 'date',
    'formPosition' => 4,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '',
    'defaultValue' => '',
    'listPosition' => 3,
    'listAlign' => 'center',
    'listUseFilter' => 1,
    'altPosition' => 3,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 0,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 13,
    'tableName' => 'Todo',
    'tableField' => 'endDate',
    'formTab' => 1,
    'formLabel' => 'endDate',
    'formType' => 'date',
    'formPosition' => 5,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '',
    'defaultValue' => '',
    'listPosition' => 4,
    'listAlign' => 'center',
    'listUseFilter' => 1,
    'altPosition' => 4,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 0,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 14,
    'tableName' => 'Todo',
    'tableField' => 'priority',
    'formTab' => 1,
    'formLabel' => 'priority',
    'formType' => 'selectValues',
    'formPosition' => 6,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '1#1|2#2|3#3|4#4|5#5|6#6|7#7|8#8|9#9|10#10',
    'defaultValue' => '5',
    'listPosition' => 5,
    'listAlign' => 'center',
    'listUseFilter' => 1,
    'altPosition' => 5,
    'status' => '1',
    'isInteger' => 1,
    'isRequired' => 0,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 15,
    'tableName' => 'Todo',
    'tableField' => 'currentStatus',
    'formTab' => 1,
    'formLabel' => 'currentStatus',
    'formType' => 'selectValues',
    'formPosition' => 7,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '1#Accepted|2#Working|4#Ended|5#Stopped|7#Waiting',
    'defaultValue' => '1',
    'listPosition' => 6,
    'listAlign' => 'center',
    'listUseFilter' => 1,
    'altPosition' => 6,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 0,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 16,
    'tableName' => 'Todo',
    'tableField' => 'projectId',
    'formTab' => 1,
    'formLabel' => 'project',
    'formType' => 'selectValues',
    'formPosition' => 3,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => 'Project#id#title',
    'defaultValue' => '',
    'listPosition' => 0,
    'listAlign' => '',
    'listUseFilter' => 1,
    'altPosition' => 1,
    'status' => '1',
    'isInteger' => 1,
    'isRequired' => 0,
    'isUnique' => 0
    ));
    
     $db->insert('DatabaseManager', array(
    'id' => 34,
    'tableName' => 'Todo',
    'tableField' => 'userId',
    'formTab' => 1,
    'formLabel' => 'User',
    'formType' => 'selectValues',
    'formPosition' => 8,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => 'User#id#lastname',
    'defaultValue' => '',
    'listPosition' => 0,
    'listAlign' => '',
    'listUseFilter' => 1,
    'altPosition' => 1,
    'status' => '1',
    'isInteger' => 1,
    'isRequired' => 0,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 17,
    'tableName' => 'Note',
    'tableField' => 'projectId',
    'formTab' => 1,
    'formLabel' => 'project',
    'formType' => 'selectValues',
    'formPosition' => 3,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => 'Project#id#title',
    'defaultValue' => '',
    'listPosition' => 0,
    'listAlign' => '',
    'listUseFilter' => 1,
    'altPosition' => 1,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 1,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 18,
    'tableName' => 'Note',
    'tableField' => 'title',
    'formTab' => 1,
    'formLabel' => 'title',
    'formType' => 'text',
    'formPosition' => 1,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '',
    'defaultValue' => '',
    'listPosition' => 1,
    'listAlign' => 'left',
    'listUseFilter' => 1,
    'altPosition' => 2,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 0,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 19,
    'tableName' => 'Note',
    'tableField' => 'comments',
    'formTab' => 1,
    'formLabel' => 'comments',
    'formType' => 'textarea',
    'formPosition' => 2,
    'formColumns' => 2,
    'formRegexp' => '',
    'formRange' => '',
    'defaultValue' => '',
    'listPosition' => 0,
    'listAlign' => '',
    'listUseFilter' => 1,
    'altPosition' => 0,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 0,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 20,
    'tableName' => 'Note',
    'tableField' => 'category',
    'formTab' => 1,
    'formLabel' => 'category',
    'formType' => 'text',
    'formPosition' => 4,
    'formColumns' => 2,
    'formRegexp' => '',
    'formRange' => '',
    'defaultValue' => '',
    'listPosition' => 3,
    'listAlign' => 'center',
    'listUseFilter' => 1,
    'altPosition' => 3,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 0,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 21,
    'tableName' => 'Calendar',
    'tableField' => 'title',
    'formTab' => 1,
    'formLabel' => 'title',
    'formType' => 'text',
    'formPosition' => 1,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '',
    'defaultValue' => '',
    'listPosition' => 1,
    'listAlign' => 'left',
    'listUseFilter' => 1,
    'altPosition' => 2,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 1,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 22,
    'tableName' => 'Calendar',
    'tableField' => 'notes',
    'formTab' => 1,
    'formLabel' => 'notes',
    'formType' => 'textarea',
    'formPosition' => 2,
    'formColumns' => 2,
    'formRegexp' => '',
    'formRange' => '',
    'defaultValue' => '',
    'listPosition' => 0,
    'listAlign' => '',
    'listUseFilter' => 1,
    'altPosition' => 0,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 0,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 23,
    'tableName' => 'Calendar',
    'tableField' => 'startDate',
    'formTab' => 1,
    'formLabel' => 'startDate',
    'formType' => 'date',
    'formPosition' => 3,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '',
    'defaultValue' => '',
    'listPosition' => 3,
    'listAlign' => 'center',
    'listUseFilter' => 1,
    'altPosition' => 3,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 1,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 24,
    'tableName' => 'Calendar',
    'tableField' => 'startTime',
    'formTab' => 1,
    'formLabel' => 'startTime',
    'formType' => 'time',
    'formPosition' => 4,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '',
    'defaultValue' => '',
    'listPosition' => 4,
    'listAlign' => 'center',
    'listUseFilter' => 1,
    'altPosition' => 0,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 1,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 25,
    'tableName' => 'Calendar',
    'tableField' => 'endDate',
    'formTab' => 1,
    'formLabel' => 'endDate',
    'formType' => 'date',
    'formPosition' => 5,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '',
    'defaultValue' => '',
    'listPosition' => 5,
    'listAlign' => 'center',
    'listUseFilter' => 1,
    'altPosition' => 0,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 1,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 26,
    'tableName' => 'Calendar',
    'tableField' => 'endTime',
    'formTab' => 1,
    'formLabel' => 'endTime',
    'formType' => 'time',
    'formPosition' => 6,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '',
    'defaultValue' => '',
    'listPosition' => 6,
    'listAlign' => 'center',
    'listUseFilter' => 1,
    'altPosition' => 0,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 1,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 27,
    'tableName' => 'Calendar',
    'tableField' => 'participantId',
    'formTab' => 1,
    'formLabel' => 'participantId',
    'formType' => 'hidden',
    'formPosition' => 7,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '',
    'defaultValue' => '',
    'listPosition' => 0,
    'listAlign' => '',
    'listUseFilter' => 1,
    'altPosition' => 1,
    'status' => '1',
    'isInteger' => 1,
    'isRequired' => 1,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 28,
    'tableName' => 'Calendar',
    'tableField' => 'rrule',
    'formTab' => 1,
    'formLabel' => 'rrule',
    'formType' => 'hidden',
    'formPosition' => 9,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '',
    'defaultValue' => '',
    'listPosition' => 0,
    'listAlign' => '',
    'listUseFilter' => 1,
    'altPosition' => 0,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 0,
    'isUnique' => 0
    ));


    $db->insert('DatabaseManager', array(
    'id' => 29,
    'tableName' => 'Filemanager',
    'tableField' => 'Title',
    'formTab' => 1,
    'formLabel' => 'Title',
    'formType' => 'text',
    'formPosition' => 1,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '',
    'defaultValue' => '',
    'listPosition' => 1,
    'listAlign' => 'center',
    'listUseFilter' => 1,
    'altPosition' => 0,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 1,
    'isUnique' => 0
    ));


    $db->insert('DatabaseManager', array(
    'id' => 30,
    'tableName' => 'Filemanager',
    'tableField' => 'Comments',
    'formTab' => 1,
    'formLabel' => 'Comments',
    'formType' => 'textarea',
    'formPosition' => 2,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '',
    'defaultValue' => '',
    'listPosition' => 0,
    'listAlign' => 'centar',
    'listUseFilter' => 1,
    'altPosition' => 0,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 0,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 31,
    'tableName' => 'Filemanager',
    'tableField' => 'ProjectId',
    'formTab' => 1,
    'formLabel' => 'Project',
    'formType' => 'selectValues',
    'formPosition' => 3,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => 'Project#id#title',
    'defaultValue' => '1',
    'listPosition' => 0,
    'listAlign' => 'center',
    'listUseFilter' => 1,
    'altPosition' => 0,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 1,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 32,
    'tableName' => 'Filemanager',
    'tableField' => 'category',
    'formTab' => 1,
    'formLabel' => 'category',
    'formType' => 'text',
    'formPosition' => 4,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '',
    'defaultValue' => '',
    'listPosition' => 2,
    'listAlign' => 'center',
    'listUseFilter' => 1,
    'altPosition' => 0,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 0,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 33,
    'tableName' => 'Filemanager',
    'tableField' => 'files',
    'formTab' => 1,
    'formLabel' => 'Upload',
    'formType' => 'upload',
    'formPosition' => 5,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '',
    'defaultValue' => '',
    'listPosition' => 3,
    'listAlign' => 'center',
    'listUseFilter' => 1,
    'altPosition' => 0,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 1,
    'isUnique' => 0
    ));


    $db->insert('User', array('id' => 1,
    'username' => 'admin',
    'firstname' => 'Adminsitrator',
    'lastname' => 'Administrator',
    'status' => 'A',
    'admin' => 1));

    $db->insert('Setting', array('id' => 1,
    'userId' => 1,
    'moduleId' => 0,
    'keyValue' => 'password',
    'value' => md5('phprojektmd5'.$_REQUEST['admin_pass']),
    'identifier' => 'Core'));

    $db->insert('Setting', array('id' => 2,
    'userId' => 1,
    'moduleId' => 0,
    'keyValue' => 'email',
    'value' => 'test@example.com',
    'identifier' => 'Core'));

    $db->insert('Setting', array('id' => 3,
    'userId' => 1,
    'moduleId' => 0,
    'keyValue' => 'language',
    'value' => 'en',
    'identifier' => 'Core'));

    $db->insert('Setting', array('id' => 4,
    'userId' => 1,
    'moduleId' => 0,
    'keyValue' => 'timeZone',
    'value' => '2',
    'identifier' => 'Core'));
    
    // If it is not a migration, we will create the test user
    if (empty($_REQUEST["migration_config"])) {
        $db->insert('User', array('id' => 2,
        'username' => 'test',
        'firstname' => 'Test',
        'lastname' => 'Test',
        'status' => 'A',
        'admin' => 0));
    
        $db->insert('Setting', array('id' => 5,
        'userId' => 2,
        'moduleId' => 0,
        'keyValue' => 'password',
        'value' => md5('phprojektmd5'.$_REQUEST['admin_pass']),
        'identifier' => 'Core'));
    
        $db->insert('Setting', array('id' => 6,
        'userId' => 2,
        'moduleId' => 0,
        'keyValue' => 'email',
        'value' => 'test@example.com',
        'identifier' => 'Core'));
    
        $db->insert('Setting', array('id' => 7,
        'userId' => 2,
        'moduleId' => 0,
        'keyValue' => 'language',
        'value' => 'en',
        'identifier' => 'Core'));
    
        $db->insert('Setting', array('id' => 8,
        'userId' => 2,
        'moduleId' => 0,
        'keyValue' => 'timeZone',
        'value' => '2',
        'identifier' => 'Core'));
    }
    
    $db->insert('Project', array('id' => 1,
    'projectId' => null,
    'path' => '/',
    'title' => 'PHProjekt',
    'notes' => '',
    'ownerId' => 1,
    'startDate' => '2008-08-01',
    'endDate' => '2010-12-31',
    'priority' => 1,
    'currentStatus' => 'working',
    'completePercent' => 0,
    'hourlyWageRate' => null,
    'budget' => null));


    $db->insert('Groups', array('id' => 1,
    'name' => 'default'));

    $db->insert('Role', array('id' => 1,
    'name' => 'Admin Role',
    'parent' => null));

    $db->insert('GroupsUserRelation', array(
    'id' => 1,
    'groupsId' => 1,
    'userId' => 1
    ));
    
    $db->insert('GroupsUserRelation', array(
    'id' => 2,
    'groupsId' => 2,
    'userId' => 2
    ));

    $db->insert('ProjectRoleUserPermissions', array(
    'projectId' => 1,
    'userId' => 1,
    'roleId' => 1
    ));
    
    $db->insert('ProjectRoleUserPermissions', array(
    'projectId' => 1,
    'userId' => 2,
    'roleId' => 2
    ));

    $db->insert('RoleModulePermissions', array(
    'roleId' => 1,
    'moduleId' => 1,
    'access' => 139
    ));

    $db->insert('RoleModulePermissions', array(
    'roleId' => 1,
    'moduleId' => 2,
    'access' => 139
    ));

    $db->insert('RoleModulePermissions', array(
    'roleId' => 1,
    'moduleId' => 3,
    'access' => 139
    ));

    $db->insert('RoleModulePermissions', array(
    'roleId' => 1,
    'moduleId' => 6,
    'access' => 139
    ));

    $db->insert('RoleModulePermissions', array(
    'roleId' => 1,
    'moduleId' => 7,
    'access' => 139
    ));

    $db->insert('RoleModulePermissions', array(
    'roleId' => 1,
    'moduleId' => 8,
    'access' => 139
    ));


    $db->insert('ItemRights', array(
    'moduleId' => 1,
    'itemId' => 1,
    'userId' => 1,
    'access' => 255
    ));

    $db->insert('ProjectModulePermissions', array(
    'moduleId' => 1,
    'projectId' => 1
    ));

    $db->insert('ProjectModulePermissions', array(
    'moduleId' => 2,
    'projectId' => 1
    ));

    $db->insert('ProjectModulePermissions', array(
    'moduleId' => 3,
    'projectId' => 1
    ));

    $db->insert('ProjectModulePermissions', array(
    'moduleId' => 4,
    'projectId' => 1
    ));

    $db->insert('ProjectModulePermissions', array(
    'moduleId' => 5,
    'projectId' => 1
    ));

    $db->insert('ProjectModulePermissions', array(
    'moduleId' => 6,
    'projectId' => 1
    ));

    $db->insert('ProjectModulePermissions', array(
    'moduleId' => 7,
    'projectId' => 1
    ));

    $db->insert('ProjectModulePermissions', array(
    'moduleId' => 8,
    'projectId' => 1
    ));

    $db->insert('Tab', array(
    'id' => 1,
    'label' => 'Basic Data'
    ));

    // creating log folders
    $baseDir = substr($_SERVER['SCRIPT_FILENAME'], 0, -22);

    $logsDir = $baseDir."logs";

    if (!file_exists($logsDir)) {
        if (!mkdir($logsDir)) {
            $_SESSION['error_message'] =
            "Please create the dir ".$logsDir." to save the logs or modify the log path on configuration.ini file.";
        }
    }

    $uploadDir = $baseDir."upload";

    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir)) {
            $_SESSION['error_message'] =
            "Please create the dir ".$uploadDir." to upload files or modify the upload path on configuration.ini file.";
        }
    } elseif (!is_writable($uploadDir)) {
        $_SESSION['error_message'] =
        "Please, set apache permission to writo on ".$uploadDir." to allow file upload fields on modules.";
    }

    // getting the language

    $clientLanguage = 'en'; // default value

    if (function_exists('getallheaders')) {
        $headers = getallheaders();
    } else {
        $headers['Accept-Language'] = $_ENV['HTTP_ACCEPT_LANGUAGE'];
    }
    if ((!empty($headers['Accept-Language'])) && strlen($headers['Accept-Language']) > 1) {
        $clientLanguage = substr($headers['Accept-Language'], 0, 2);
    }

    if (strlen($_SERVER['REQUEST_URI']) > 16) {
        $webPath = "http://".$_SERVER['HTTP_HOST'].substr($_SERVER['REQUEST_URI'], 0, -15);
    } else {
        $webPath = "http://".$_SERVER['HTTP_HOST']."/";
    }

    // Creating the configuration file
    $configurationFileContent = '[production]

; Language configuration
language             = "'.$clientLanguage.'"

; Path options
webpath              = '.$webPath.'
uploadpath           = '.$uploadDir.'

; Database options
database.type        = '.$_REQUEST['server_type'].'
database.host        = '.$_REQUEST['server_host'].'
database.username    = '.$_REQUEST['server_user'].'
database.password    = '.$_REQUEST['server_pass'].'
database.name        = '.$_REQUEST['server_database'].'

; Log options
log.debug.filename   = '.$logsDir.DIRECTORY_SEPARATOR.'debug.log
log.crit.filename    = '.$logsDir.DIRECTORY_SEPARATOR.'crit.log
itemsPerPage         = 3;


; In the process of the initial configuration of PHProjekt 6, this file must be
; renamed to configuration.ini (without the sufix "-dist") and the parameters
; have to be set as explained.

; The semicolons ";" are used preceding a comment line, or a line which has data
; that is not being used.

; This file is divided into 3 sections: [production], [testing-mysql] y
; [testing], every one has the same options. Each one of them corresponds to one
; environment, it is used only one at a time, depending on what is speficied in
; index.php, inside folder "htdocs" in the line that has:
; define("PHPR_CONFIG_SECTION", "production");

; You could leave that line as it is, and in configuration.ini just modify the
; parameters inside [production] section. You can also add your own sections.

[production]

; LANGUAGE
; Here it is specified the default language for the system, could be "de" for
; German, "en" for English or "es" for Spanish. Actually, the language for each
; user is specified individually from Administration -> User
language             = "'.$clientLanguage.'"


; PATHS
; Where the site and the main file (index.php) are located (htdocs folder).
webpath              = "'.$webPath.'"

; Path where will be placed files uploaded by the user.
uploadpath           = "'.$uploadDir.'"


; DATABASE
; For this Developer Release, it just has been tested with pdo_mysql.
database.adapter            = "'.$_REQUEST['server_type'].'"

; The assigned name or IP address for the database server.
database.params.host        = "'.$_REQUEST['server_host'].'"

; Username and password with the appropriate rights for Phprojekt to access to
; the database.
database.params.username    = "'.$_REQUEST['server_user'].'"
database.params.password    = "'.$_REQUEST['server_pass'].'"

; Name of the database, inside the server
database.params.dbname      = "'.$_REQUEST['server_database'].'"


; LOG
; Here will be logged things explicitly declared.
; E.G.: (PHP) Phprojekt::getInstance()->getLog()->debug("String to be logged");
log.debug.filename   = "'.$logsDir.DIRECTORY_SEPARATOR.'debug.log"

; This is another type of logging.
; E.G.: (PHP) Phprojekt::getInstance()->getLog()->crit("String to be logged");
; Note for developers: there are many different type of logs defined that can be
; added here, see the complete list in phprojekt\library\Phprojekt\Log.php
log.crit.filename    = "'.$logsDir.DIRECTORY_SEPARATOR.'crit.log"

; MODULES
; Not used at the moment, leave it as it is.
itemsPerPage         = 3


; MAIL NOTIFICATION
; Inside many modules, when adding or editing an item, there is a tab
; "Notification" that allows the user to send an email notification to the
; people involved in that item, telling them about the creation or modification
; of it.

mailEndOfLine        = 0    ; (0 = \r\n  1 = \n)
; If the email is configured to be sent in Text mode, whether to use \r\n or \n
; for the end of line.

; Name or IP address of the SMTP server to be used to send that notifications.
smtpServer           = "localhost"

; If the SMTP server requires authentication, remove the semicolons ";" and
; write inside the inverted commas "" the appropriate username and password.
;smtpUser            = ""
;smtpPassword        = ""

; MISC
; Use compressed dojo to improve the speed of loading.
compressedDojo       = true;
; Use Zend_Registry for cache classes in the same request
useCacheForClasses   = true;
';

    file_put_contents($baseDir."configuration.ini", $configurationFileContent);


    // migration

    if (isset($_REQUEST['migration_config']) && file_exists($_REQUEST['migration_config'])) {

        $statusConversion = array(1 => "offered",
        2 => "ordered",
        3 => "Working",
        4 => "ended",
        5 => "stopped",
        6 => "Re-Opened",
        7 => "waiting");

        include_once($_REQUEST['migration_config']);

        // check version
        if (substr(PHPR_VERSION, 0, 1) != '5') {
            die("Sorry, but it is not possible to migrate PHProjekt minor than 5.0");
        }

        try {
            if (PHPR_DB_TYPE == 'sqlite') {
                $params = array('dbname' => PHPR_DB_HOST);
            } else {
                $params = array(
                'host'     => PHPR_DB_HOST,
                'username' => PHPR_DB_USER,
                'password' => PHPR_DB_PASS,
                'dbname'   => PHPR_DB_NAME
                );

            }

            $dbOrig = Zend_Db::factory('pdo_'.PHPR_DB_TYPE, $params);

        } catch (Exception $error) {
            die("Error connecting to server " . "(" . $error->getMessage() . ")");
        }

        // group migration

        $groupUsers = array();

        $groups = $dbOrig->fetchAll("SELECT * FROM ".PHPR_DB_PREFIX."gruppen");

        while (list($dummy, $group) = each($groups)) {
            $tmp = $group["ID"];
            $groupUsers[$tmp] = array();

        }


        // user migration
        $userKurz = array();

        $users = $dbOrig->fetchAll("SELECT * FROM ".PHPR_DB_PREFIX."users");

        while (list($dummy, $user) = each($users)) {
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

                $db->insert('User', array('id' => $user["ID"],
                'username' => $username,
                'firstname' => $user["vorname"],
                'lastname' => $user["nachname"],
                'status' => $status,
                'admin' => 0));

                $timeZone = 1;
                $language = $clientLanguage;


                if ($settings = unserialize($user["settings"])) {
                    if (isset($settings["timezone"])) {
                        $timeZone = $settings["timezone"];
                    }
                    if (isset($settings["language"])) {
                        $language = $settings["language"];
                    }
                }

                if (defined("PHPR_VERSION") && PHPR_VERSION >= '5.2.1') {
                    $password = $user['pw'];
                } else {
                    $password = md5('phprojektmd5'.$username);
                }

                $db->insert('Setting', array('id' => null,
                'userId' => $user["ID"],
                'moduleId' => 0,
                'keyValue' => 'password',
                'value' => $password,
                'identifier' => 'Core'));

                $db->insert('Setting', array('id' => null,
                'userId' => $user["ID"],
                'moduleId' => 0,
                'keyValue' => 'email',
                'value' => $user["email"],
                'identifier' => 'Core'));

                $db->insert('Setting', array('id' => null,
                'userId' => $user["ID"],
                'moduleId' => 0,
                'keyValue' => 'language',
                'value' => $language,
                'identifier' => 'Core'));

                $db->insert('Setting', array('id' => null,
                'userId' => $user["ID"],
                'moduleId' => 0,
                'keyValue' => 'timeZone',
                'value' => $timeZone,
                'identifier' => 'Core'));

                $db->insert('ItemRights', array(
                'moduleId' => 1,
                'itemId' => 1,
                'userId' => $user["ID"],
                'access' => 255
                ));

            }
            $kurz = $user['kurz'];

            $userKurz[$kurz] = $user['ID'];

        }

        // user group

        $UserGroups = $dbOrig->fetchAll("SELECT * FROM ".PHPR_DB_PREFIX."grup_user");

        while (list($dummy, $UserGroup) = each($UserGroups)) {

            $db->insert('groupsuserrelation', array(
            'groupsId' => $UserGroup["grup_ID"],
            'userId' => $UserGroup["user_ID"]
            ));
            $tmp = $UserGroup["grup_ID"];
            $groupUsers[$tmp][] = $UserGroup["user_ID"];

        }

        // project migration

        $projects = $dbOrig->fetchAll("SELECT * FROM ".PHPR_DB_PREFIX."projekte ORDER BY ID");

        $paths = array();
        $paths[1] = "/1/";

        while (list($dummy, $project) = each($projects)) {


            $project["ID"] = convertProjectId($project["ID"]);

            switch ($project["parent"]) {
                case 0:
                    $project["parent"] = 1;
                    $project["path"] = $paths[1] . $project["ID"] . "/";
                    break;
                case 1:
                    $project["parent"] = 10001;
                    $project["path"] = $paths[10001] . $project["ID"] . "/";
                    break;
                default:
                    $parent = $project["parent"];
                    $project["path"] = $paths[$parent] . $project["ID"] . "/";
                    break;
            }
            $ID = $project["ID"];
            $paths[$ID] = $project["path"];

            $tmpStatus = $project["kategorie"];
            if (!empty($statusConversion[$tmpStatus])) {
                $tmpStatus = $statusConversion[$tmpStatus];
            } else {
                $tmpStatus = $statusConversion[3];
            }

            $db->insert('Project', array('id' => $project["ID"],
            'projectId' => $project["parent"],
            "path" => $project["path"],
            "title" => $project["name"],
            "notes" => $project["note"],
            "ownerId" => $project["von"],
            "startDate" => $project["anfang"],
            "endDate" => $project["ende"],
            "priority" => $project["wichtung"],
            "currentStatus" => $tmpStatus,
            "completePercent" => $project["status"],
            "hourlyWageRate" => $project["stundensatz"],
            "budget" => $project["budget"]));

            $db->insert('ItemRights', array(
            'moduleId' => 1,
            'itemId' => $project["ID"],
            'userId' => 1,
            'access' => 255
            ));

            migratePermissions($db, 1, $project["ID"], $project["acc"], 255, $project["gruppe"], $groupUsers, $userKurz);

            $db->insert('ProjectModulePermissions', array(
            'moduleId' => 1,
            'projectId' => $project["ID"]));

            $db->insert('ProjectModulePermissions', array(
            'moduleId' => 2,
            'projectId' => $project["ID"]));

            $db->insert('ProjectModulePermissions', array(
            'moduleId' => 3,
            'projectId' => $project["ID"]));

            $db->insert('ProjectModulePermissions', array(
            'moduleId' => 4,
            'projectId' => $project["ID"]));

            $db->insert('ProjectModulePermissions', array(
            'moduleId' => 5,
            'projectId' => $project["ID"]));

            $db->insert('ProjectModulePermissions', array(
            'moduleId' => 6,
            'projectId' => $project["ID"]));

            $db->insert('ProjectModulePermissions', array(
            'moduleId' => 7,
            'projectId' => $project["ID"]));

            $db->insert('ProjectModulePermissions', array(
            'moduleId' => 8,
            'projectId' => $project["ID"]));

        }

        // todo

        $todos = $dbOrig->fetchAll("SELECT * FROM ".PHPR_DB_PREFIX."todo ORDER BY ID");


        while (list($dummy, $todo) = each($todos)) {

            $todo["project"] = convertProjectId($todo["project"]);


            $db->insert('Todo', array('id' => $todo["ID"],
            'projectId' => $todo["project"],
            "title" => $todo["remark"],
            "notes" => $todo["note"],
            "ownerId" => $todo["von"],
            "startDate" => $todo["anfang"],
            "endDate" => $todo["deadline"],
            "priority" => $todo["priority"],
            "currentStatus" => $todo["status"]));

            $db->insert('ItemRights', array(
            'moduleId' => 2,
            'itemId' => $todo["ID"],
            'userId' => 1,
            'access' => 255
            ));

            migratePermissions($db, 2, $todo["ID"], $todo["acc"], 255, $todo["gruppe"], $groupUsers, $userKurz);

        }

        // notes

        $notes = $dbOrig->fetchAll("SELECT * FROM ".PHPR_DB_PREFIX."notes ORDER BY ID");


        while (list($dummy, $note) = each($notes)) {

            $note["projekt"] = convertProjectId($note["projekt"]);

            $db->insert('Note', array('id' => $note["ID"],
            'projectId' => $note["projekt"],
            "title" => $note["name"],
            "comments" => $note["remark"],
            "ownerId" => $note["von"],
            "category" => $note["kategorie"]));

            $db->insert('ItemRights', array(
            'moduleId' => 3,
            'itemId' => $note["ID"],
            'userId' => 1,
            'access' => 255
            ));

            migratePermissions($db, 3, $note["ID"], $note["acc"], 255, $note["gruppe"], $groupUsers, $userKurz);

        }

        // timeproj

        $timeprojRecords = $dbOrig->fetchAll("SELECT * FROM ".PHPR_DB_PREFIX."timeproj ORDER BY ID");


        while (list($dummy, $timeProj) = each($timeprojRecords)) {

            $timeProj["projekt"] = convertProjectId($timeProj["projekt"]);

            $db->insert('Timeproj', array('id' => $timeProj["ID"],
            'notes' => $timeProj["note"],
            "ownerId" => $timeProj["users"],
            "projectId" => $timeProj["projekt"],
            "date" => $timeProj["datum"],
            "amount" => $timeProj["h"].":".$timeProj["m"].":00"));

        }

        // timecard

        $timecardRecords = $dbOrig->fetchAll("SELECT * FROM ".PHPR_DB_PREFIX."timecard ORDER BY ID");


        while (list($dummy, $timeCard) = each($timecardRecords)) {

            $timeCard["anfang"] = stringToTime($timeCard["anfang"]);

            $timeCard["ende"] = stringToTime($timeCard["ende"]);


            $timeCard["projekt"] = convertProjectId($timeCard["projekt"]);

            $db->insert('Timecard', array('id' => $timeCard["ID"],
            "ownerId" => $timeCard["users"],
            "date" => $timeCard["datum"],
            "startTime" => $timeCard["anfang"],
            "endTime" => $timeCard["ende"]));

        }

        // calendar

        $events = $dbOrig->fetchAll("SELECT * FROM ".PHPR_DB_PREFIX."termine ORDER BY ID");


        while (list($dummy, $calendar) = each($events)) {

            $calendar["projekt"] = convertProjectId($calendar["projekt"]);

            $calendar["anfang"] = stringToTime($calendar["anfang"]);

            $calendar["ende"] = stringToTime($calendar["ende"]);

            $db->insert('Calendar', array('id' => $calendar["ID"],
            "parentId" => $calendar["parent"],
            "ownerId" => $calendar["von"],
            "projectId" => $calendar["projekt"],
            "title" => $calendar["event"],
            "notes" => $calendar["remark"],
            "uid" => $calendar["serie_id"],
            "startDate" => $calendar["datum"],
            "startTime" => $calendar["anfang"],
            "endDate" => $calendar["datum"],
            "endTime" => $calendar["ende"],
            "timezone" => $timeZone,
            "location" => $calendar["ort"],
            "categories" => "",
            "attendee" => $calendar["an"],
            "priority" => $calendar["priority"],
            "rrule" => "",
            "properties" => "",
            "participantId" => $calendar["an"]));

            $db->insert('ItemRights', array(
            'moduleId' => 5,
            'itemId' => $calendar["ID"],
            'userId' => 1,
            'access' => 255
            ));

            $db->insert('ItemRights', array(
            'moduleId' => 5,
            'itemId' => $calendar["ID"],
            'userId' => $calendar["an"],
            'access' => 255
            ));

        }

        // timecard

        $files = $dbOrig->fetchAll("SELECT * FROM ".PHPR_DB_PREFIX."dateien ORDER BY ID");


        while (list($dummy, $file) = each($files)) {

            $file["div2"] = convertProjectId($file["div2"]);


            $newFilename = md5($file["tempname"]);

            copy(PHPR_FILE_PATH."\\".$file["tempname"], $uploadDir."\\".$newFilename);

            $db->insert('Filemanager', array('id' => $file["ID"],
            'ownerId' => $file["von"],
            "title" => $file["filename"],
            "comments" => $file["remark"],
            "projectId" => $file["div2"],
            "category" => $file["kat"],
            "files" => $newFilename."|".$file["filename"]));

            $db->insert('ItemRights', array(
            'moduleId' => 7,
            'itemId' => $file["ID"],
            'userId' => 1,
            'access' => 255
            ));

            migratePermissions($db, 7, $file["ID"], $file["acc"], 255, $file["gruppe"], $groupUsers, $userKurz);

        }

    }  // end of migration

}

/**
 * Migrates the permission from PHProjekt 5.x version to PHProjekt 6.0
 *
 * @param DatabaseManager $db
 * @param int $moduleId module to grant permissions
 * @param int $itemId item to set the permission
 * @param mixed $users serialized array of users or group
 * @param int $access access value
 * @param int $group group of the project
 * @param array $userGroups array with users by group
 * @param array $userKurz array with ID => kurz conversion
 */
function migratePermissions($db, $moduleId, $itemId, $users, $access, $group, $userGroups, $userKurz) {

    $userList = array();

    @$tmpUserList = unserialize($users);

    if (is_array($tmpUserList)) {

        foreach ($tmpUserList as $dummy => $kurz) {
            $userList[] = $userKurz[$kurz];
        }

    } elseif ($users == 'group') {
        if (isset($userGroups[$group])) {
            $userList = $userGroups[$group];
        }
    } elseif (is_int($users)) {
        $userList[] = $users;
    }


    foreach ($userList as $oneUser) {
        $db->insert('ItemRights', array(
        'moduleId' => (int)$moduleId,
        'itemId' => (int)$itemId,
        'userId' => (int)$oneUser,
        'access' => (int)$access
        ));

    }
}

/**
 * Converts the old time format (hhmm) to a time format (hh:mm:ss)
 *
 * @param string $stringTime
 * @return time
 */
function stringToTime($stringTime) {
    $time = null;

    if (strlen($stringTime) == 3) {
        $time = substr($stringTime, 0, 1).":".substr($stringTime, 1, 2).":00";
    } elseif ((strlen($stringTime)  == 4) && (is_numeric($stringTime))) {
        $time = substr($stringTime, 0, 2).":".substr($stringTime, 2, 2).":00";
    }

    return $time;
}

/**
 * Converts the old project Id to a Phprojekt 6 ID
 *
 * @param integer $oldProjectId
 * @return integer
 */
function convertProjectId($oldProjectId) {

    if ($oldProjectId == 1) {
        return 10001;
    } elseif (empty($oldProjectId)) {
        return 1;
    }

    return $oldProjectId;


}