<?php
/**
 * Setup routine
 *
 * LICENSE: Licensed under the terms of the GNU Publice License
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *             GNU Public License 2.0
 * @version    $Id:$
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
    "pdo_pgsql" => "PostgreSQL",
    "pdo_sqlite2" => "SQLite");

    $serverType = (empty($_REQUEST['server_type'])?"pdo_mysql":$_REQUEST['server_type']);
    $serverHost = (empty($_REQUEST['server_host'])?"localhost":$_REQUEST['server_host']);
    $serverUser = (empty($_REQUEST['server_user'])?"root":$_REQUEST['server_user']);
    $serverPass = (empty($_REQUEST['server_pass'])?"":$_REQUEST['server_pass']);
    $serverDatabase = (empty($_REQUEST['server_database'])?"phprojekt6":$_REQUEST['server_database']);
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
    $formContent = str_replace("<%ERROR_MESSAGE%>", $errorMessage, $formContent);

    echo $formContent;
}

function preInstallChecks()
{
    $returnValue = true;

    if ($_REQUEST['server_type'] == 'pdo_mysql') {

        @mysql_connect($_REQUEST['server_host'], $_REQUEST['server_user'], $_REQUEST['server_pass']);

        mysql_query("CREATE DATABASE ".$_REQUEST['server_database']);

        if (!mysql_select_db($_REQUEST['server_database'])) {
            $_SESSION['error_message'] = "Error selecting database ".$_REQUEST['server_database'];
            $returnValue = false;
        }
    }

    try {
        Zend_Db::factory($_REQUEST['server_type'], array(
        'host'     => $_REQUEST['server_host'],
        'username' => $_REQUEST['server_user'],
        'password' => $_REQUEST['server_pass'],
        'dbname'   => $_REQUEST['server_database']
        ));
    } catch (Exception $error) {
        $_SESSION['error_message'] = "Can't connect to server at '".$_REQUEST['server_host']
            . "' using '".$_REQUEST['server_user']."' user"
            . "(". $error->getMessage() .")";
        $returnValue = false;
    }

    // creating log folders
    $baseDir = substr($_SERVER['SCRIPT_FILENAME'], 0, -22);

    $configFlie = $baseDir."configuration.ini";
    if (!file_exists($configFlie)) {
        if (!file_put_contents($configFlie, "Test")) {
            $_SESSION['error_message'] = "Error creating the configuration file at ".$configFlie;
            $returnValue = false;
        } else {
            unlink($configFlie);
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
        $db = Zend_Db::factory($_REQUEST['server_type'], array(
        'host'     => $_REQUEST['server_host'],
        'username' => $_REQUEST['server_user'],
        'password' => $_REQUEST['server_pass'],
        'dbname'   => $_REQUEST['server_database']
        ));
    } catch (Exception $error) {
        die("Error connecting to server " . "(" . $error->getMessage() . ")");
    }

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
    "UserSetting",
    "Setting",
    "Module",
    "User",
    "DatabaseManager",
    "Calendar");

    foreach ($tableList as $oneTable) {
        $tableManager->dropTable($oneTable);
    }

    $tableManager = new Phprojekt_Table($db);

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
    'formTooltip' => array(
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
        'type' => 'varchar', 'length' => 100, 'null' => true),
    'newValue' => array(
        'type' => 'varchar', 'length' => 255, 'null' => true),
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
        'type' => 'varchar', 'length' => 50, 'null' => true),
    'hourlyWageRate' => array(
        'type' => 'varchar', 'length' => 10, 'null' => true),
    'budget' => array(
        'type' => 'varchar', 'length' => 10, 'null' => true),

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
        'type' => 'varchar', 'length' => 255, 'null' => true),
    'secondDisplay' => array(
        'type' => 'varchar', 'length' => 255, 'null' => true),
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
    'startTime' => array(
        'type' => 'time', 'null' => true),
    'endTime' => array(
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
    'recurrence_id' => array(
        'type' => 'varchar', 'length' => 100, 'null' => true),
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
        die("Error creating the table ");
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

    $db->insert('DatabaseManager', array(
    'id' => 1,
    'tableName' => 'Project',
    'tableField' => 'title',
    'formTab' => 1,
    'formLabel' => 'title',
    'formTooltip' => 'title',
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
    'formTooltip' => 'notes',
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
    'formTooltip' => 'parent',
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
    'formTooltip' => 'startDate',
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
    'formTooltip' => 'endDate',
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
    'formTooltip' => 'priority',
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
    'formTooltip' => 'currentStatus',
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
    'formTooltip' => 'completePercent',
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
    'formTooltip' => 'budget',
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
    'id' => 10,
    'tableName' => 'Todo',
    'tableField' => 'title',
    'formTab' => 1,
    'formLabel' => 'title',
    'formTooltip' => 'title',
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
    'formTooltip' => 'notes',
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
    'formTooltip' => 'startDate',
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
    'formTooltip' => 'endDate',
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
    'formTooltip' => 'priority',
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
    'formTooltip' => 'currentStatus',
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
    'formTooltip' => 'project',
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
    'id' => 17,
    'tableName' => 'Note',
    'tableField' => 'projectId',
    'formTab' => 1,
    'formLabel' => 'project',
    'formTooltip' => 'project',
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
    'formTooltip' => 'title',
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
    'formTooltip' => 'comments',
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
    'formTooltip' => 'category',
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
    'formTooltip' => 'title',
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
    'formTooltip' => 'notes',
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
    'formTooltip' => 'startDate',
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
    'formTooltip' => 'startTime',
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
    'formTooltip' => 'endDate',
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
    'formTooltip' => 'endTime',
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
    'formTooltip' => 'participantId',
    'formType' => 'multipleSelectValues',
    'formPosition' => 8,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => 'User#id#username',
    'defaultValue' => '',
    'listPosition' => 2,
    'listAlign' => 'left',
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
    'formTooltip' => 'rrule',
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


    $db->insert('User', array('id' => 1,
    'username' => 'admin',
    'firstname' => 'Adminsitrator',
    'lastname' => 'Administrator',
    'status' => 'A'));

    $db->insert('Setting', array('id' => 1,
    'userId' => 1,
    'moduleId' => 0,
    'keyvalue' => 'password',
    'value' => md5('phprojektmd5'.$_REQUEST['admin_pass']),
    'identifier' => 'Core'));

    $db->insert('Setting', array('id' => 2,
    'userId' => 1,
    'moduleId' => 0,
    'keyvalue' => 'email',
    'value' => 'test@example.com',
    'identifier' => 'Core'));

    $db->insert('Setting', array('id' => 3,
    'userId' => 1,
    'moduleId' => 0,
    'keyvalue' => 'language',
    'value' => 'en',
    'identifier' => 'Core'));

    $db->insert('Setting', array('id' => 4,
    'userId' => 1,
    'moduleId' => 0,
    'keyvalue' => 'timeZone',
    'value' => '2',
    'identifier' => 'Core'));

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

    $db->insert('groupsUserRelation', array(
    'id' => 1,
    'groupsId' => 1,
    'userId' => 1
    ));

    $db->insert('ProjectRoleUserPermissions', array(
    'projectId' => 1,
    'userId' => 1,
    'roleId' => 1
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
    'moduleId' => 4,
    'access' => 139
    ));

    $db->insert('RoleModulePermissions', array(
    'roleId' => 1,
    'moduleId' => 5,
    'access' => 139
    ));

    $db->insert('RoleModulePermissions', array(
    'roleId' => 1,
    'moduleId' => 6,
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

    $clientLanguaje = 'en'; // default value

    if (function_exists('getallheaders')) {
        $headers = getallheaders();
    } else {
        $headers['Accept-Language'] = $_ENV['HTTP_ACCEPT_LANGUAGE'];
    }
    if ((!empty($headers['Accept-Language'])) && strlen($headers['Accept-Language']) > 1) {
        $clientLanguaje = substr($headers['Accept-Language'], 0, 2);
    }

    if (strlen($_SERVER['REQUEST_URI']) > 16) {
        $webPath = "http://".$_SERVER['HTTP_HOST'].substr($_SERVER['REQUEST_URI'], 0, -15);
    } else {
        $webPath = "http://".$_SERVER['HTTP_HOST']."/";
    }

    // Creating the configuration file
    $configurationFileContent = '[production]

; Language configuration
language             = "'.$clientLanguaje.'"

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
log.debug.filename   = '.$logsDir.'\debug.log
log.crit.filename    = '.$logsDir.'\crit.log
itemsPerPage         = 3;';

    file_put_contents($baseDir."configuration.ini", $configurationFileContent);

}