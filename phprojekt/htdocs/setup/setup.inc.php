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

function displaySetupForm()
{
    $availableEngines = array("pdo_mysql" => "MySQL",
                            // "pdo_pgsql" => "PostgreSQL",
                            // "pdo_sqlite" => "SQLite"
    );

    $serverType      = (empty($_REQUEST['server_type'])      ? "pdo_mysql" :$_REQUEST['server_type']);
    $serverHost      = (empty($_REQUEST['server_host'])      ? "localhost" :$_REQUEST['server_host']);
    $serverUser      = (empty($_REQUEST['server_user'])      ? "root"      :$_REQUEST['server_user']);
    $serverPass      = (empty($_REQUEST['server_pass'])      ? ""          :$_REQUEST['server_pass']);
    $serverDatabase  = (empty($_REQUEST['server_database'])  ? "phprojekt" :$_REQUEST['server_database']);
    $migrationConfig = (empty($_REQUEST['migration_config']) ? ""          :$_REQUEST['migration_config']);
    $errorMessage    = (empty($_SESSION['error_message'])    ? ""          :$_SESSION['error_message']);
    unset($_SESSION['error_message']);

    $formContent = file_get_contents("setupForm.php");

    $tmp = '';
    foreach ($availableEngines as $key => $value) {
        $tmp .= '<option value="'.$key.'"';
        if ($key == $serverType) {
            $tmp .= "selected='selected'";
        }
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

        @mysql_query("CREATE DATABASE " . $_REQUEST['server_database']
            . " DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;");

        if (!mysql_select_db($_REQUEST['server_database'])) {
            $_SESSION['error_message'] = "Error selecting database ".$_REQUEST['server_database'];
            $returnValue               = false;
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
            . "' using '".$_REQUEST['server_user']."' user" . "(". $error->getMessage() .")";
        $returnValue = false;
    }

    // creating log folders
    $baseDir    = substr($_SERVER['SCRIPT_FILENAME'], 0, -22);
    $configFile = $baseDir."configuration.ini";

    if (!file_exists($configFile)) {
        if (!file_put_contents($configFile, "Test")) {
            $_SESSION['error_message'] = "Error creating the configuration file at ".$configFile;
            $returnValue               = false;
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

function displayFinished()
{
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

function installPhprojekt()
{
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

    $tableList = array(
        "contact",
        "timecard",
        "timeproj",
        "item_rights",
        "configuration",
        "note",
        "tags_modules",
        "tags_users",
        "tags",
        "tab_module_relation",
        "module_tab_relation",
        "tab",
        "search_words",
        "search_word_module",
        "search_display",
        "todo",
        "role_module_permissions",
        "project_user_role_relation",
        "project_role_user_permissions",
        "module_project_relation",
        "project_module_permissions",
        "project",
        "history",
        "groups_user_relation",
        "role",
        "groups",
        "user_setting",
        "setting",
        "module",
        "user",
        "database_manager",
        "calendar",
        "filemanager",
        "helpdesk",
        "minutes",
        "minutes_item");

    $tableManager = new Phprojekt_Table($db);

    foreach ($tableList as $oneTable) {
        if ($tableManager->tableExists($oneTable)) {
            // Fix for Zend Framework 1.7.2
            $db->closeConnection();
            $tableManager->dropTable($oneTable);
        }
    }

    $result = $tableManager->createTable('database_manager', array(
            'id'              => array('type' => 'auto_increment', 'null' => false),
            'table_name'      => array('type' => 'varchar', 'length' => 50, 'null' => true),
            'table_field'     => array('type' => 'varchar', 'length' => 60, 'null' => true),
            'form_tab'        => array('type' => 'int', 'length' => 11, 'null' => true),
            'form_label'      => array('type' => 'varchar', 'length' => 255, 'null' => true),
            'form_type'       => array('type' => 'varchar', 'length' => 50, 'null' => true),
            'form_position'   => array('type' => 'int', 'length' => 11, 'null' => true),
            'form_columns'    => array('type' => 'int', 'length' => 11, 'null' => true),
            'form_regexp'     => array('type' => 'varchar', 'length' => 255, 'null' => true),
            'form_range'      => array('type' => 'text', 'null' => true),
            'default_value'   => array('type' => 'varchar', 'length' => 255, 'null' => true),
            'list_position'   => array('type' => 'int', 'length' => 11, 'null' => true),
            'list_align'      => array('type' => 'varchar', 'length' => 20, 'null' => true),
            'list_use_filter' => array('type' => 'int', 'length' => 4, 'null' => true),
            'alt_position'    => array('type' => 'int', 'length' => 11, 'null' => true),
            'status'          => array('type' => 'varchar', 'length' => 20, 'null' => true),
            'is_integer'      => array('type' => 'int', 'length' => 4, 'null' => true),
            'is_required'     => array('type' => 'int', 'length' => 4, 'null' => true),
            'is_unique'       => array('type' => 'int', 'length' => 11, 'null' => true),
        ), array('primary key' => array('id')));
    if (!$result) {
        die("Error creating the table database_manager");
    }

    $result = $tableManager->createTable('user', array(
            'id'        => array('type' => 'auto_increment', 'null' => false),
            'username'  => array('type' => 'varchar', 'length' => 255, 'null' => true),
            'firstname' => array('type' => 'varchar', 'length' => 255, 'null' => true),
            'lastname'  => array('type' => 'varchar', 'length' => 255, 'null' => true),
            'status'    => array('type' => 'varchar', 'length' => 1, 'null' => true, 'default' => 'A'),
            'admin'     => array('type' => 'int', 'length' => 1, 'null' => false , 'default' => 0),
        ), array('primary key' => array('id'), 'unique' => array('username')));
    if (!$result) {
        die("Error creating the table user");
    }

    $result = $tableManager->createTable('module', array(
            'id'        => array('type' => 'auto_increment', 'null' => false),
            'name'      => array('type' => 'varchar', 'length' => 255, 'null' => true),
            'label'     => array('type' => 'varchar', 'length' => 255, 'null' => true),
            'save_type' => array('type' => 'int', 'length' => 1, 'null' => false, 'default' => 0),
            'active'    => array('type' => 'int', 'length' => 1, 'null' => false, 'default' => 1),
        ), array('primary key' => array('id')));
    if (!$result) {
        die("Error creating the table module");
    }

    $result = $tableManager->createTable('groups', array(
            'id'   => array('type' => 'auto_increment', 'null' => false),
            'name' => array('type' => 'varchar', 'length' => 255, 'null' => true),
        ), array('primary key' => array('id')));
    if (!$result) {
        die("Error creating the table groups");
    }

    $result = $tableManager->createTable('groups_user_relation', array(
        'id'        => array('type' => 'auto_increment', 'null' => false),
        'groups_id' => array('type' => 'int', 'length' => 11, 'null' => true),
        'user_id'   => array('type' => 'int', 'length' => 11, 'null' => true),
        ),array('primary key' => array('id')));
    if (!$result) {
        die("Error creating the table groups_user_relation");
    }

    $result = $tableManager->createTable('history', array(
            'id'        => array('type' => 'auto_increment', 'null' => false),
            'module_id' => array('type' => 'int', 'length' => 11, 'null' => true),
            'user_id'   => array('type' => 'int', 'length' => 11, 'null' => true),
            'item_id'   => array('type' => 'int', 'length' => 11, 'null' => true),
            'field'     => array('type' => 'varchar', 'length' => 255, 'null' => true),
            'old_value' => array('type' => 'text', 'null' => true),
            'new_value' => array('type' => 'text', 'null' => true),
            'action'    => array('type' => 'varchar', 'length' => 50, 'null' => true),
            'datetime'  => array('type' => 'timestamp', 'length' => 255, 'null' => false,
            'default' => 'CURRENT_TIMESTAMP', 'default_no_quote' => true),
        ), array('primary key' => array('id')));
    if (!$result) {
        die("Error creating the table history");
    }

    $result = $tableManager->createTable('project', array(
            'id'               => array('type' => 'auto_increment', 'null' => false),
            'project_id'       => array('type' => 'int', 'length' => 11, 'null' => true),
            'path'             => array('type' => 'varchar', 'length' => 25, 'null' => true, 'default' => '/'),
            'title'            => array('type' => 'varchar', 'length' => 255, 'null' => true),
            'notes'            => array('type' => 'text', 'null' => true),
            'owner_id'         => array('type' => 'int', 'length' => 11, 'null' => true),
            'start_date'       => array('type' => 'date', 'null' => true),
            'end_date'         => array('type' => 'date', 'null' => true),
            'priority'         => array('type' => 'int', 'length' => 11, 'null' => true),
            'current_status'   => array('type' => 'varchar', 'length' => 50, 'null' => true, 'default' => 'working'),
            'complete_percent' => array('type' => 'varchar', 'length' => 4, 'null' => true),
            'hourly_wage_rate' => array('type' => 'varchar', 'length' => 10, 'null' => true),
            'budget'           => array('type' => 'varchar', 'length' => 10, 'null' => true),
            'contact_id'       => array('type' => 'int', 'length' => 11, 'null' => true),
        ), array('primary key' => array('id')));
    if (!$result) {
        die("Error creating the table project");
    }

    $result = $tableManager->createTable('project_module_permissions', array(
            'id'         => array('type' => 'auto_increment', 'null' => false),
            'module_id'  => array('type' => 'int', 'length' => 11, 'null' => true),
            'project_id' => array('type' => 'int', 'length' => 11, 'null' => true),
        ), array('primary key' => array('id')));
    if (!$result) {
        die("Error creating the table project_module_permissions");
    }

    $result = $tableManager->createTable('role', array(
            'id'     => array('type' => 'auto_increment', 'null' => false),
            'name'   => array('type' => 'varchar', 'length' => 255, 'null' => false),
            'parent' => array('type' => 'int', 'length' => 11, 'null' => true),
        ), array('primary key' => array('id')));
    if (!$result) {
        die("Error creating the table role");
    }

    $result = $tableManager->createTable('project_role_user_permissions', array(
            'id'         => array('type' => 'auto_increment', 'null' => false),
            'project_id' => array('type' => 'int', 'length' => 11, 'null' => false),
            'user_id'    => array('type' => 'int', 'length' => 11, 'null' => false),
            'role_id'    => array('type' => 'int', 'length' => 11, 'null' => false),
        ), array('primary key' => array('id')));
    if (!$result) {
        die("Error creating the table project_role_user_permissions");
    }

    $result = $tableManager->createTable('role_module_permissions', array(
            'id'        => array('type' => 'auto_increment', 'null' => false),
            'role_id'   => array('type' => 'int', 'length' => 11, 'null' => false),
            'module_id' => array('type' => 'int', 'length' => 11, 'null' => false),
            'access'    => array('type' => 'int', 'length' => 3, 'null' => false),
        ), array('primary key' => array('id')));
    if (!$result) {
        die("Error creating the table role_module_permissions");
    }

    $result = $tableManager->createTable('todo', array(
            'id'             => array('type' => 'auto_increment', 'null' => false),
            'title'          => array('type' => 'varchar', 'length' => 255, 'null' => true),
            'notes'          => array('type' => 'text', 'null' => true),
            'owner_id'       => array('type' => 'int', 'length' => 11, 'null' => true),
            'project_id'     => array('type' => 'int', 'length' => 11, 'null' => true),
            'start_date'     => array('type' => 'date', 'null' => true),
            'end_date'       => array('type' => 'date', 'null' => true),
            'priority'       => array('type' => 'int', 'length' => 11, 'null' => true),
            'current_status' => array('type' => 'varchar', 'length' => 50, 'null' => true, 'default' => 'working'),
            'user_id'        => array('type' => 'int', 'length' => 11, 'null' => true),
        ), array('primary key' => array('id')));
    if (!$result) {
        die("Error creating the table todo");
    }

    $result = $tableManager->createTable('setting', array(
            'id'         => array('type' => 'auto_increment', 'null' => false),
            'user_id'    => array('type' => 'int', 'length' => 11, 'null' => true),
            'module_id'  => array('type' => 'int', 'length' => 11, 'null' => true),
            'key_value'  => array('type' => 'varchar', 'length' => 255, 'null' => true),
            'value'      => array('type' => 'text', 'null' => true),
            'identifier' => array('type' => 'varchar', 'length' => 50, 'null' => true),
        ), array('primary key' => array('id')));
    if (!$result) {
        die("Error creating the table setting");
    }

    $result = $tableManager->createTable('search_words', array(
            'id'    => array('type' => 'auto_increment', 'null' => false),
            'word'  => array('type' => 'varchar', 'length' => 255, 'null' => true),
            'count' => array('type' => 'int', 'length' => 11, 'null' => true),
        ), array('primary key' => array('id')));
    if (!$result) {
        die("Error creating the table search_words");
    }

    $result = $tableManager->createTable('search_word_module', array(
            'module_id' => array('type' => 'int', 'length' => 11, 'null' => false),
            'item_id'   => array('type' => 'int', 'length' => 11, 'null' => false),
            'word_id'   => array('type' => 'int', 'length' => 11, 'null' => false),
        ), array('primary key' => array('item_id', 'module_id', 'word_id')));
    if (!$result) {
        die("Error creating the table search_word_module");
    }

    $result = $tableManager->createTable('search_display', array(
            'module_id'      => array('type' => 'int', 'length' => 11, 'null' => false),
            'item_id'        => array('type' => 'int', 'length' => 11, 'null' => false),
            'first_display'  => array('type' => 'text', 'null' => true),
            'second_display' => array('type' => 'text', 'null' => true),
            'project_id'     => array('type' => 'int', 'length' => 11, 'null' => false),
        ), array('primary key' => array('item_id', 'module_id')));
    if (!$result) {
        die("Error creating the table search_display");
    }

    $result = $tableManager->createTable('tags', array(
            'id'    => array('type' => 'auto_increment', 'null' => false),
            'word'  => array('type' => 'varchar', 'length' => 255, 'null' => false),
            'crc32' => array('type' => 'bigint', 'null' => false),
        ), array('primary key' => array('id')));
    if (!$result) {
        die("Error creating the table tags");
    }

    $result = $tableManager->createTable('tags_users', array(
            'id'      => array('type' => 'auto_increment', 'null' => false),
            'user_id' => array('type' => 'int', 'length' => 11, 'null' => false),
            'tag_id'  => array('type' => 'int', 'length' => 11, 'null' => false),
        ), array('primary key' => array('id')));
    if (!$result) {
        die("Error creating the table tags_users");
    }

    $result = $tableManager->createTable('tags_modules', array(
            'module_id'   => array('type' => 'int', 'length' => 11, 'null' => false),
            'item_id'     => array('type' => 'int', 'length' => 11, 'null' => false),
            'tag_user_id' => array('type' => 'int', 'length' => 11, 'null' => false),
        ), array('primary key' => array('module_id', 'item_id', 'tag_user_id')));
    if (!$result) {
        die("Error creating the table tags_modules");
    }

    $result = $tableManager->createTable('tab', array(
            'id'    => array('type' => 'auto_increment', 'null' => false),
            'label' => array('type' => 'varchar', 'length' => 255, 'null' => false),
        ), array('primary key' => array('id')));
    if (!$result) {
        die("Error creating the table tab");
    }

    $result = $tableManager->createTable('module_tab_relation', array(
            'tab_id'    => array('type' => 'int', 'length' => 11, 'null' => false),
            'module_id' => array('type' => 'int', 'length' => 11, 'null' => false),
        ), array('primary key' => array('tab_id', 'module_id')));
    if (!$result) {
        die("Error creating the table module_tab_relation");
    }

    $result = $tableManager->createTable('note', array(
            'id'         => array('type' => 'auto_increment', 'null' => false),
            'project_id' => array('type' => 'int', 'length' => 11, 'null' => true),
            'title'      => array('type' => 'varchar', 'length' => 255, 'null' => true),
            'comments'   => array('type' => 'text', 'null' => true),
            'category'   => array('type' => 'varchar', 'length' => 50, 'null' => true),
            'owner_id'   => array('type' => 'int', 'length' => 11, 'null' => true),
        ), array('primary key' => array('id')));
    if (!$result) {
        die("Error creating the table note");
    }

    $result = $tableManager->createTable('configuration', array(
            'id'        => array('type' => 'auto_increment', 'null' => false),
            'module_id' => array('type' => 'int', 'length' => 11, 'null' => false),
            'key_value' => array('type' => 'varchar', 'length' => 255, 'null' => false),
            'value'     => array('type' => 'text', 'null' => true),
        ), array('primary key' => array('id')));
    if (!$result) {
        die("Error creating the table configuration");
    }

    $result = $tableManager->createTable('item_rights', array(
            'module_id' => array('type' => 'int', 'length' => 11, 'null' => false),
            'item_id'   => array('type' => 'int', 'length' => 11, 'null' => false),
            'user_id'   => array('type' => 'int', 'length' => 11, 'null' => false),
            'access'    => array('type' => 'int', 'length' => 3, 'null' => false),
        ), array('primary key' => array('module_id', 'item_id', 'user_id')));
    if (!$result) {
        die("Error creating the table item_rights");
    }

    $result = $tableManager->createTable('timecard', array(
            'id'         => array('type' => 'auto_increment', 'null' => false),
            'owner_id'   => array('type' => 'int', 'length' => 11, 'null' => true),
            'date'       => array('type' => 'date', 'null' => true),
            'start_time' => array('type' => 'time', 'null' => true),
            'end_time'   => array('type' => 'time', 'null' => true),
        ), array('primary key' => array('id')));
    if (!$result) {
        die("Error creating the table timecard");
    }

    $result = $tableManager->createTable('timeproj', array(
            'id'         => array('type' => 'auto_increment', 'null' => false),
            'notes'      => array('type' => 'text', 'null' => true),
            'owner_id'   => array('type' => 'int', 'length' => 11, 'null' => true),
            'project_id' => array('type' => 'int', 'length' => 11, 'null' => true),
            'date'       => array('type' => 'date', 'null' => true),
            'amount'     => array('type' => 'time', 'null' => true),
        ), array('primary key' => array('id')));
    if (!$result) {
        die("Error creating the table timeproj");
    }

    $result = $tableManager->createTable('calendar', array(
            'id'             => array('type' => 'auto_increment', 'null' => false),
            'parent_id'      => array('type' => 'int', 'length' => 11, 'null' => true),
            'owner_id'       => array('type' => 'int', 'length' => 11, 'null' => true),
            'project_id'     => array('type' => 'int', 'length' => 11, 'null' => false),
            'title'          => array('type' => 'varchar', 'length' => 255, 'null' => true),
            'place'          => array('type' => 'varchar', 'length' => 255, 'null' => true),
            'notes'          => array('type' => 'text', 'null' => true),
            'uid'            => array('type' => 'varchar', 'length' => 255, 'null' => false),
            'start_date'     => array('type' => 'date', 'null' => true),
            'start_time'     => array('type' => 'time', 'null' => true),
            'end_date'       => array('type' => 'date', 'null' => true),
            'end_time'       => array('type' => 'time', 'null' => true),
            'created'        => array('type' => 'int', 'length' => 11, 'null' => true),
            'modified'       => array('type' => 'int', 'length' => 10, 'null' => true),
            'timezone'       => array('type' => 'varchar', 'length' => 50, 'null' => true),
            'location'       => array('type' => 'varchar', 'length' => 255, 'null' => true),
            'categories'     => array('type' => 'text', 'null' => true),
            'attendee'       => array('type' => 'text', 'null' => true),
            'status'         => array('type' => 'int', 'length' => 1, 'null' => true),
            'priority'       => array('type' => 'int', 'length' => 1, 'null' => true),
            'class'          => array('type' => 'int', 'length' => 1, 'null' => true),
            'transparent'    => array('type' => 'int', 'length' => 1, 'null' => true),
            'rrule'          => array('type' => 'text', 'null' => true),
            'properties'     => array('type' => 'text', 'null' => true),
            'deleted'        => array('type' => 'int', 'length' => 1, 'null' => true),
            'participant_id' => array('type' => 'int', 'length' => 11, 'null' => false),
        ), array('primary key' => array('id')));
    if (!$result) {
        die("Error creating the table calendar");
    }

    $result = $tableManager->createTable('contact', array(
            'id'          => array('type' => 'auto_increment', 'null' => false),
            'owner_id'    => array('type' => 'int', 'length' => 11, 'null' => true),
            'project_id'  => array('type' => 'int', 'length' => 11, 'null' => true),
            'name'        => array('type' => 'varchar', 'length' => 255, 'null' => true),
            'email'       => array('type' => 'varchar', 'length' => 255, 'null' => true),
            'company'     => array('type' => 'varchar', 'length' => 255, 'null' => true),
            'firstphone'  => array('type' => 'varchar', 'length' => 255, 'null' => true),
            'secondphone' => array('type' => 'varchar', 'length' => 255, 'null' => true),
            'mobilephone' => array('type' => 'varchar', 'length' => 255, 'null' => true),
            'street'      => array('type' => 'varchar', 'length' => 255, 'null' => true),
            'city'        => array('type' => 'varchar', 'length' => 255, 'null' => true),
            'zipcode'     => array('type' => 'varchar', 'length' => 50, 'null' => true),
            'country'     => array('type' => 'varchar', 'length' => 255, 'null' => true),
            'comment'     => array('type' => 'text', 'null' => true),
            'private'     => array('type' => 'int', 'length' => 1, 'null' => true),
        ), array('primary key' => array('id')));
    if (!$result) {
        die("Error creating the table contact");
    }

    $result = $tableManager->createTable('filemanager', array(
            'id'         => array('type' => 'auto_increment', 'null' => false),
            'owner_id'   => array('type' => 'int', 'length' => 11, 'null' => true),
            'title'      => array('type' => 'varchar', 'length' => 50, 'null' => true),
            'comments'   => array('type' => 'text', 'null' => true),
            'project_id' => array('type' => 'int', 'length' => 11, 'null' => true),
            'category'   => array('type' => 'varchar', 'length' => 50, 'null' => true),
            'files'      => array('type' => 'text', 'null' => true),
        ), array('primary key' => array('id')));
    if (!$result) {
        die("Error creating the table filemanager");
    }

    $result = $tableManager->createTable('helpdesk', array(
            'id'          => array('type' => 'auto_increment', 'null' => false),
            'owner_id'    => array('type' => 'int', 'length' => 11, 'null' => true),
            'title'       => array('type' => 'varchar', 'length' => 255, 'null' => true),
            'assigned'    => array('type' => 'int', 'length' => 11, 'null' => true),
            'date'        => array('type' => 'date', 'null' => true),
            'project_id'  => array('type' => 'int', 'length' => 11, 'null' => true),
            'priority'    => array('type' => 'int', 'length' => 11, 'null' => true),
            'attachments' => array('type' => 'text', 'null' => true),
            'description' => array('type' => 'text', 'null' => true),
            'status'      => array('type' => 'varchar', 'length' => 50, 'null' => true),
            'due_date'    => array('type' => 'date', 'null' => true),
            'author'      => array('type' => 'varchar', 'length' => 255, 'null' => true),
            'solved_by'   => array('type' => 'varchar', 'length' => 255, 'null' => true),
            'solved_date' => array('type' => 'date', 'null' => true),
            'contact_id'  => array('type' => 'int', 'length' => 11, 'null' => true),
        ), array('primary key' => array('id')));
    if (!$result) {
        die("Error creating the table helpdesk");
    }

    $result = $tableManager->createTable('minutes', array(
            'id'                     => array('type' => 'auto_increment', 'null' => false),
            'owner_id'               => array('type' => 'int', 'length' => 11, 'null' => true),
            'project_id'             => array('type' => 'int', 'length' => 11, 'null' => true),
            'title'                  => array('type' => 'varchar', 'length' => 255, 'null' => true),
            'description'            => array('type' => 'text', 'null' => true),
            'meeting_date'           => array('type' => 'date', 'null' => true),
            'start_time'             => array('type' => 'time', 'null' => true),
            'end_time'               => array('type' => 'time', 'null' => true),
            'place'                  => array('type' => 'varchar', 'length' => 255, 'null' => true),
            'moderator'              => array('type' => 'varchar', 'length' => 255, 'null' => true),
            'participants_Invited'   => array('type' => 'text', 'null' => true),
            'participants_attending' => array('type' => 'text', 'null' => true),
            'participants_excused'   => array('type' => 'text', 'null' => true),
            'recipients'             => array('type' => 'text', 'null' => true),
            'item_status'            => array('type' => 'int', 'length' => 11, 'null' => true),
        ), array('primary key' => array('id')));
    if (!$result) {
        die("Error creating the table minutes");
    }

    $result = $tableManager->createTable('minutes_item', array(
            'id'         => array('type' => 'auto_increment', 'null' => false),
            'owner_id'   => array('type' => 'int', 'length' => 11, 'null' => true),
            'project_id' => array('type' => 'int', 'length' => 11, 'null' => true),
            'minutes_id' => array('type' => 'int', 'length' => 11, 'null' => true),
            'topic_id'   => array('type' => 'int', 'length' => 11, 'null' => true),
            'topic_type' => array('type' => 'int', 'length' => 11, 'null' => true),
            'sort_order' => array('type' => 'int', 'length' => 11, 'null' => true),
            'title'      => array('type' => 'varchar', 'length' => 255, 'null' => true),
            'comment'    => array('type' => 'text', 'null' => true),
            'topic_date' => array('type' => 'date', 'null' => true),
            'user_id'    => array('type' => 'int', 'length' => 11, 'null' => true),
        ), array('primary key' => array('id')));
    if (!$result) {
        die("Error creating the table minutes_item");
    }

    // Default information

    $db->insert('module', array(
        'id'        => 1,
        'name'      => 'Project',
        'label'     => 'Project',
        'save_type' => 0,
        'active'    => 1
    ));

    $db->insert('module', array(
        'id'        => 2,
        'name'      => 'Todo',
        'label'     => 'Todo',
        'save_type' => 0,
        'active'    => 1
    ));

    $db->insert('module', array(
        'id'        => 3,
        'name'      => 'Note',
        'label'     => 'Note',
        'save_type' => 0,
        'active'    => 1
    ));

    $db->insert('module', array(
        'id'        => 4,
        'name'      => 'Timecard',
        'label'     => 'Timecard',
        'save_type' => 1,
        'active'    => 1
    ));

    $db->insert('module', array(
        'id'        => 5,
        'name'      => 'Calendar',
        'label'     => 'Calendar',
        'save_type' => 1,
        'active'    => 1
    ));

    $db->insert('module', array(
        'id'        => 6,
        'name'      => 'Gantt',
        'label'     => 'Gantt',
        'save_type' => 0,
        'active'    => 1
    ));

    $db->insert('module', array(
        'id'        => 7,
        'name'      => 'Filemanager',
        'label'     => 'Filemanager',
        'save_type' => 0,
        'active'    => 1
    ));

    $db->insert('module', array(
        'id'        => 8,
        'name'      => 'Statistic',
        'label'     => 'Statistics',
        'save_type' => 0,
        'active'    => 1
    ));

    $db->insert('module', array(
        'id'        => 9,
        'name'      => 'Contact',
        'label'     => 'Contact',
        'save_type' => 1,
        'active'    => 1
    ));

    $db->insert('module', array(
        'id'        => 10,
        'name'      => 'Helpdesk',
        'label'     => 'Helpdesk',
        'save_type' => 0,
        'active'    => 1
    ));

    $db->insert('module', array(
        'id'        => 11,
        'name'      => 'Minutes',
        'label'     => 'Minutes',
        'save_type' => 0,
        'active'    => 1
    ));

    $db->insert('database_manager', array(
        'id'              => 1,
        'table_name'      => 'Project',
        'table_field'     => 'title',
        'form_tab'        => 1,
        'form_label'      => 'Title',
        'form_type'       => 'text',
        'form_position'   => 1,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 1,
        'list_align'      => 'left',
        'list_use_filter' => 1,
        'alt_position'    => 2,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 1,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 2,
        'table_name'      => 'Project',
        'table_field'     => 'notes',
        'form_tab'        => 1,
        'form_label'      => 'Notes',
        'form_type'       => 'textarea',
        'form_position'   => 2,
        'form_columns'    => 2,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 0,
        'list_align'      => '',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 3,
        'table_name'      => 'Project',
        'table_field'     => 'project_id',
        'form_tab'        => 1,
        'form_label'      => 'Parent',
        'form_type'       => 'selectValues',
        'form_position'   => 3,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => 'Project#id#title',
        'default_value'   => '1',
        'list_position'   => 0,
        'list_align'      => '',
        'list_use_filter' => 1,
        'alt_position'    => 1,
        'status'          => '1',
        'is_integer'      => 1,
        'is_required'     => 1,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 4,
        'table_name'      => 'Project',
        'table_field'     => 'start_date',
        'form_tab'        => 1,
        'form_label'      => 'Start date',
        'form_type'       => 'date',
        'form_position'   => 4,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 3,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 3,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 5,
        'table_name'      => 'Project',
        'table_field'     => 'end_date',
        'form_tab'        => 1,
        'form_label'      => 'End date',
        'form_type'       => 'date',
        'form_position'   => 5,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 4,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 4,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 6,
        'table_name'      => 'Project',
        'table_field'     => 'priority',
        'form_tab'        => 1,
        'form_label'      => 'Priority',
        'form_type'       => 'selectValues',
        'form_position'   => 6,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '1#1|2#2|3#3|4#4|5#5|6#6|7#7|8#8|9#9|10#10',
        'default_value'   => '5',
        'list_position'   => 5,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 5,
        'status'          => '1',
        'is_integer'      => 1,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 7,
        'table_name'      => 'Project',
        'table_field'     => 'current_status',
        'form_tab'        => 1,
        'form_label'      => 'Current status',
        'form_type'       => 'selectValues',
        'form_position'   => 7,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '1#Offered|2#Ordered|3#Working|4#Ended|5#Stopped|6#Re-Opened|7#Waiting',
        'default_value'   => '1',
        'list_position'   => 6,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 6,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 8,
        'table_name'      => 'Project',
        'table_field'     => 'complete_percent',
        'form_tab'        => 1,
        'form_label'      => 'Complete percent',
        'form_type'       => 'percentage',
        'form_position'   => 8,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 7,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 7,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 9,
        'table_name'      => 'Project',
        'table_field'     => 'budget',
        'form_tab'        => 1,
        'form_label'      => 'Budget',
        'form_type'       => 'text',
        'form_position'   => 9,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 0,
        'list_align'      => '',
        'list_use_filter' => 1,
        'alt_position'    => 8,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 10,
        'table_name'      => 'Project',
        'table_field'     => 'contact_id',
        'form_tab'        => 1,
        'form_label'      => 'Contact',
        'form_type'       => 'selectValues',
        'form_position'   => 10,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => 'Contact#id#name',
        'default_value'   => '',
        'list_position'   => 0,
        'list_align'      => '',
        'list_use_filter' => 1,
        'alt_position'    => 1,
        'status'          => '1',
        'is_integer'      => 1,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 11,
        'table_name'      => 'Todo',
        'table_field'     => 'title',
        'form_tab'        => 1,
        'form_label'      => 'Title',
        'form_type'       => 'text',
        'form_position'   => 1,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 1,
        'list_align'      => 'left',
        'list_use_filter' => 1,
        'alt_position'    => 2,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 1,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 12,
        'table_name'      => 'Todo',
        'table_field'     => 'notes',
        'form_tab'        => 1,
        'form_label'      => 'Notes',
        'form_type'       => 'textarea',
        'form_position'   => 2,
        'form_columns'    => 2,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 0,
        'list_align'      => '',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 13,
        'table_name'      => 'Todo',
        'table_field'     => 'start_date',
        'form_tab'        => 1,
        'form_label'      => 'Start date',
        'form_type'       => 'date',
        'form_position'   => 4,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 3,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 3,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 14,
        'table_name'      => 'Todo',
        'table_field'     => 'end_date',
        'form_tab'        => 1,
        'form_label'      => 'End date',
        'form_type'       => 'date',
        'form_position'   => 5,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 4,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 4,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 15,
        'table_name'      => 'Todo',
        'table_field'     => 'priority',
        'form_tab'        => 1,
        'form_label'      => 'Priority',
        'form_type'       => 'selectValues',
        'form_position'   => 6,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '1#1|2#2|3#3|4#4|5#5|6#6|7#7|8#8|9#9|10#10',
        'default_value'   => '5',
        'list_position'   => 5,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 5,
        'status'          => '1',
        'is_integer'      => 1,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 16,
        'table_name'      => 'Todo',
        'table_field'     => 'current_status',
        'form_tab'        => 1,
        'form_label'      => 'Current status',
        'form_type'       => 'selectValues',
        'form_position'   => 7,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '1#Accepted|2#Working|4#Ended|5#Stopped|7#Waiting',
        'default_value'   => '1',
        'list_position'   => 7,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 6,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 17,
        'table_name'      => 'Todo',
        'table_field'     => 'project_id',
        'form_tab'        => 1,
        'form_label'      => 'Project',
        'form_type'       => 'selectValues',
        'form_position'   => 3,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => 'Project#id#title',
        'default_value'   => '',
        'list_position'   => 0,
        'list_align'      => '',
        'list_use_filter' => 1,
        'alt_position'    => 1,
        'status'          => '1',
        'is_integer'      => 1,
        'is_required'     => 1,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 18,
        'table_name'      => 'Todo',
        'table_field'     => 'user_id',
        'form_tab'        => 1,
        'form_label'      => 'User',
        'form_type'       => 'selectValues',
        'form_position'   => 8,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => 'User#id#lastname',
        'default_value'   => '',
        'list_position'   => 6,
        'list_align'      => 'left',
        'list_use_filter' => 1,
        'alt_position'    => 1,
        'status'          => '1',
        'is_integer'      => 1,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 19,
        'table_name'      => 'Note',
        'table_field'     => 'project_id',
        'form_tab'        => 1,
        'form_label'      => 'Project',
        'form_type'       => 'selectValues',
        'form_position'   => 3,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => 'Project#id#title',
        'default_value'   => '',
        'list_position'   => 0,
        'list_align'      => '',
        'list_use_filter' => 1,
        'alt_position'    => 1,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 1,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 20,
        'table_name'      => 'Note',
        'table_field'     => 'title',
        'form_tab'        => 1,
        'form_label'      => 'Title',
        'form_type'       => 'text',
        'form_position'   => 1,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 1,
        'list_align'      => 'left',
        'list_use_filter' => 1,
        'alt_position'    => 2,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 1,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 21,
        'table_name'      => 'Note',
        'table_field'     => 'comments',
        'form_tab'        => 1,
        'form_label'      => 'Comments',
        'form_type'       => 'textarea',
        'form_position'   => 2,
        'form_columns'    => 2,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 0,
        'list_align'      => '',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 22,
        'table_name'      => 'Note',
        'table_field'     => 'category',
        'form_tab'        => 1,
        'form_label'      => 'Category',
        'form_type'       => 'text',
        'form_position'   => 4,
        'form_columns'    => 2,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 3,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 3,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 23,
        'table_name'      => 'Calendar',
        'table_field'     => 'title',
        'form_tab'        => 1,
        'form_label'      => 'Title',
        'form_type'       => 'text',
        'form_position'   => 1,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 1,
        'list_align'      => 'left',
        'list_use_filter' => 1,
        'alt_position'    => 2,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 1,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 24,
        'table_name'      => 'Calendar',
        'table_field'     => 'place',
        'form_tab'        => 1,
        'form_label'      => 'Place',
        'form_type'       => 'text',
        'form_position'   => 2,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 0,
        'list_align'      => '',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 25,
        'table_name'      => 'Calendar',
        'table_field'     => 'notes',
        'form_tab'        => 1,
        'form_label'      => 'Notes',
        'form_type'       => 'textarea',
        'form_position'   => 3,
        'form_columns'    => 2,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 0,
        'list_align'      => '',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 26,
        'table_name'      => 'Calendar',
        'table_field'     => 'start_date',
        'form_tab'        => 1,
        'form_label'      => 'Start date',
        'form_type'       => 'date',
        'form_position'   => 4,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 2,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 3,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 1,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 27,
        'table_name'      => 'Calendar',
        'table_field'     => 'start_time',
        'form_tab'        => 1,
        'form_label'      => 'Start time',
        'form_type'       => 'time',
        'form_position'   => 5,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 3,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 1,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 28,
        'table_name'      => 'Calendar',
        'table_field'     => 'end_date',
        'form_tab'        => 1,
        'form_label'      => 'End date',
        'form_type'       => 'date',
        'form_position'   => 6,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 4,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 1,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 29,
        'table_name'      => 'Calendar',
        'table_field'     => 'end_time',
        'form_tab'        => 1,
        'form_label'      => 'End time',
        'form_type'       => 'time',
        'form_position'   => 7,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 5,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 1,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 30,
        'table_name'      => 'Calendar',
        'table_field'     => 'participant_id',
        'form_tab'        => 1,
        'form_label'      => 'Participant',
        'form_type'       => 'hidden',
        'form_position'   => 8,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 0,
        'list_align'      => '',
        'list_use_filter' => 1,
        'alt_position'    => 1,
        'status'          => '1',
        'is_integer'      => 1,
        'is_required'     => 1,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 31,
        'table_name'      => 'Calendar',
        'table_field'     => 'rrule',
        'form_tab'        => 1,
        'form_label'      => 'rrule',
        'form_type'       => 'hidden',
        'form_position'   => 9,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 0,
        'list_align'      => '',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 32,
        'table_name'      => 'Filemanager',
        'table_field'     => 'title',
        'form_tab'        => 1,
        'form_label'      => 'Title',
        'form_type'       => 'text',
        'form_position'   => 1,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 1,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 1,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 33,
        'table_name'      => 'Filemanager',
        'table_field'     => 'comments',
        'form_tab'        => 1,
        'form_label'      => 'Comments',
        'form_type'       => 'textarea',
        'form_position'   => 2,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 0,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 34,
        'table_name'      => 'Filemanager',
        'table_field'     => 'project_id',
        'form_tab'        => 1,
        'form_label'      => 'Project',
        'form_type'       => 'selectValues',
        'form_position'   => 3,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => 'Project # id # title',
        'default_value'   => '1',
        'list_position'   => 0,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 1,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 35,
        'table_name'      => 'Filemanager',
        'table_field'     => 'category',
        'form_tab'        => 1,
        'form_label'      => 'Category',
        'form_type'       => 'text',
        'form_position'   => 4,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 2,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 36,
        'table_name'      => 'Filemanager',
        'table_field'     => 'files',
        'form_tab'        => 1,
        'form_label'      => 'Upload',
        'form_type'       => 'upload',
        'form_position'   => 5,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 3,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 1,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 37,
        'table_name'      => 'Contact',
        'table_field'     => 'name',
        'form_tab'        => 1,
        'form_label'      => 'Name',
        'form_type'       => 'text',
        'form_position'   => 1,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 1,
        'list_align'      => 'left',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 1,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 38,
        'table_name'      => 'Contact',
        'table_field'     => 'email',
        'form_tab'        => 1,
        'form_label'      => 'E-Mail',
        'form_type'       => 'text',
        'form_position'   => 2,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 2,
        'list_align'      => 'left',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 39,
        'table_name'      => 'Contact',
        'table_field'     => 'company',
        'form_tab'        => 1,
        'form_label'      => 'Company',
        'form_type'       => 'text',
        'form_position'   => 3,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 0,
        'list_align'      => '',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 40,
        'table_name'      => 'Contact',
        'table_field'     => 'firstphone',
        'form_tab'        => 1,
        'form_label'      => 'First phone',
        'form_type'       => 'text',
        'form_position'   => 4,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 3,
        'list_align'      => 'left',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 41,
        'table_name'      => 'Contact',
        'table_field'     => 'secondphone',
        'form_tab'        => 1,
        'form_label'      => 'Second phone',
        'form_type'       => 'text',
        'form_position'   => 5,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 0,
        'list_align'      => '',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 42,
        'table_name'      => 'Contact',
        'table_field'     => 'mobilephone',
        'form_tab'        => 1,
        'form_label'      => 'Mobile phone',
        'form_type'       => 'text',
        'form_position'   => 6,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 0,
        'list_align'      => '',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 43,
        'table_name'      => 'Contact',
        'table_field'     => 'street',
        'form_tab'        => 1,
        'form_label'      => 'Street',
        'form_type'       => 'text',
        'form_position'   => 7,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 4,
        'list_align'      => 'left',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 44,
        'table_name'      => 'Contact',
        'table_field'     => 'city',
        'form_tab'        => 1,
        'form_label'      => 'City',
        'form_type'       => 'text',
        'form_position'   => 8,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 0,
        'list_align'      => '',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 45,
        'table_name'      => 'Contact',
        'table_field'     => 'zipcode',
        'form_tab'        => 1,
        'form_label'      => 'Zip Code',
        'form_type'       => 'text',
        'form_position'   => 9,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 0,
        'list_align'      => '',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 46,
        'table_name'      => 'Contact',
        'table_field'     => 'country',
        'form_tab'        => 1,
        'form_label'      => 'Country',
        'form_type'       => 'text',
        'form_position'   => 10,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 0,
        'list_align'      => '',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 47,
        'table_name'      => 'Contact',
        'table_field'     => 'comment',
        'form_tab'        => 1,
        'form_label'      => 'Comment',
        'form_type'       => 'textarea',
        'form_position'   => 11,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 0,
        'list_align'      => '',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 48,
        'table_name'      => 'Contact',
        'table_field'     => 'private',
        'form_tab'        => 1,
        'form_label'      => 'Private',
        'form_type'       => 'selectValues',
        'form_position'   => 12,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '0#No|1#Yes',
        'default_value'   => '0',
        'list_position'   => 5,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 49,
        'table_name'      => 'Helpdesk',
        'table_field'     => 'title',
        'form_tab'        => 1,
        'form_label'      => 'Title',
        'form_type'       => 'text',
        'form_position'   => 1,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 1,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 1,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 50,
        'table_name'      => 'Helpdesk',
        'table_field'     => 'assigned',
        'form_tab'        => 1,
        'form_label'      => 'Assigned',
        'form_type'       => 'selectValues',
        'form_position'   => 3,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => 'User#id#lastname',
        'default_value'   => '',
        'list_position'   => 4,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 51,
        'table_name'      => 'Helpdesk',
        'table_field'     => 'date',
        'form_tab'        => 1,
        'form_label'      => 'Date',
        'form_type'       => 'display',
        'form_position'   => 4,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 2,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 1,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 52,
        'table_name'      => 'Helpdesk',
        'table_field'     => 'project_id',
        'form_tab'        => 1,
        'form_label'      => 'Project',
        'form_type'       => 'selectValues',
        'form_position'   => 6,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => 'Project # id # title',
        'default_value'   => '1',
        'list_position'   => 0,
        'list_align'      => '',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 1,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 53,
        'table_name'      => 'Helpdesk',
        'table_field'     => 'priority',
        'form_tab'        => 1,
        'form_label'      => 'Priority',
        'form_type'       => 'selectValues',
        'form_position'   => 7,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '1#1|2#2|3#3|4#4|5#5|6#6|7#7|8#8|9#9|10#10',
        'default_value'   => '',
        'list_position'   => 5,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 54,
        'table_name'      => 'Helpdesk',
        'table_field'     => 'attachments',
        'form_tab'        => 1,
        'form_label'      => 'Attachments',
        'form_type'       => 'upload',
        'form_position'   => 8,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 0,
        'list_align'      => '',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 55,
        'table_name'      => 'Helpdesk',
        'table_field'     => 'description',
        'form_tab'        => 1,
        'form_label'      => 'Description',
        'form_type'       => 'textarea',
        'form_position'   => 11,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 0,
        'list_align'      => '',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 56,
        'table_name'      => 'Helpdesk',
        'table_field'     => 'status',
        'form_tab'        => 1,
        'form_label'      => 'Status',
        'form_type'       => 'selectValues',
        'form_position'   => 12,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '1#Open|2#Assigned|3#Solved|4#Verified|5#Closed',
        'default_value'   => '1',
        'list_position'   => 6,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 1,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 57,
        'table_name'      => 'Helpdesk',
        'table_field'     => 'due_date',
        'form_tab'        => 1,
        'form_label'      => 'Due date',
        'form_type'       => 'date',
        'form_position'   => 5,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 0,
        'list_align'      => '',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 58,
        'table_name'      => 'Helpdesk',
        'table_field'     => 'author',
        'form_tab'        => 1,
        'form_label'      => 'author',
        'form_type'       => 'display',
        'form_position'   => 2,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => 'User#id#lastname',
        'default_value'   => '',
        'list_position'   => 3,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 1,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 59,
        'table_name'      => 'Helpdesk',
        'table_field'     => 'solved_by',
        'form_tab'        => 1,
        'form_label'      => 'Solved by',
        'form_type'       => 'display',
        'form_position'   => 9,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => 'User#id#lastname',
        'default_value'   => '',
        'list_position'   => 0,
        'list_align'      => '',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 60,
        'table_name'      => 'Helpdesk',
        'table_field'     => 'solved_date',
        'form_tab'        => 1,
        'form_label'      => 'Solved date',
        'form_type'       => 'display',
        'form_position'   => 10,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 0,
        'list_align'      => '',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 61,
        'table_name'      => 'Helpdesk',
        'table_field'     => 'contact_id',
        'form_tab'        => 1,
        'form_label'      => 'Contact',
        'form_type'       => 'selectValues',
        'form_position'   => 13,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => 'Contact#id#name',
        'default_value'   => '',
        'list_position'   => 0,
        'list_align'      => '',
        'list_use_filter' => 1,
        'alt_position'    => 1,
        'status'          => '1',
        'is_integer'      => 1,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 62,
        'table_name'      => 'Minutes',
        'table_field'     => 'project_id',
        'form_tab'        => 1,
        'form_label'      => 'Select',
        'form_type'       => 'hidden',
        'form_position'   => 1,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 0,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 63,
        'table_name'      => 'Minutes',
        'table_field'     => 'title',
        'form_tab'        => 1,
        'form_label'      => 'Title',
        'form_type'       => 'text',
        'form_position'   => 2,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 3,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 1,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 64,
        'table_name'      => 'Minutes',
        'table_field'     => 'description',
        'form_tab'        => 1,
        'form_label'      => 'Description',
        'form_type'       => 'textarea',
        'form_position'   => 3,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 4,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 65,
        'table_name'      => 'Minutes',
        'table_field'     => 'meeting_date',
        'form_tab'        => 1,
        'form_label'      => 'Date of Meeting',
        'form_type'       => 'date',
        'form_position'   => 4,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 1,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 1,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 66,
        'table_name'      => 'Minutes',
        'table_field'     => 'start_time',
        'form_tab'        => 1,
        'form_label'      => 'Start Time',
        'form_type'       => 'time',
        'form_position'   => 5,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 2,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 67,
        'table_name'      => 'Minutes',
        'table_field'     => 'end_time',
        'form_tab'        => 1,
        'form_label'      => 'End Time',
        'form_type'       => 'time',
        'form_position'   => 6,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 0,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 68,
        'table_name'      => 'Minutes',
        'table_field'     => 'place',
        'form_tab'        => 1,
        'form_label'      => 'Place',
        'form_type'       => 'text',
        'form_position'   => 7,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 5,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 69,
        'table_name'      => 'Minutes',
        'table_field'     => 'moderator',
        'form_tab'        => 1,
        'form_label'      => 'Moderator',
        'form_type'       => 'text',
        'form_position'   => 8,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '',
        'default_value'   => '',
        'list_position'   => 0,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 70,
        'table_name'      => 'Minutes',
        'table_field'     => 'participants_invited',
        'form_tab'        => 2,
        'form_label'      => 'Invited',
        'form_type'       => 'multipleSelectValues',
        'form_position'   => 9,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => 'User#id#username',
        'default_value'   => '',
        'list_position'   => 0,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 71,
        'table_name'      => 'Minutes',
        'table_field'     => 'participants_attending',
        'form_tab'        => 2,
        'form_label'      => 'Attending',
        'form_type'       => 'multipleSelectValues',
        'form_position'   => 10,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => 'User#id#username',
        'default_value'   => '',
        'list_position'   => 0,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 72,
        'table_name'      => 'Minutes',
        'table_field'     => 'participants_excused',
        'form_tab'        => 2,
        'form_label'      => 'Excused',
        'form_type'       => 'multipleSelectValues',
        'form_position'   => 11,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => 'User#id#username',
        'default_value'   => '',
        'list_position'   => 0,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 73,
        'table_name'      => 'Minutes',
        'table_field'     => 'recipients',
        'form_tab'        => 2,
        'form_label'      => 'recipients',
        'form_type'       => 'multipleSelectValues',
        'form_position'   => 12,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => 'User#id#username',
        'default_value'   => '',
        'list_position'   => 0,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('database_manager', array(
        'id'              => 74,
        'table_name'      => 'Minutes',
        'table_field'     => 'item_status',
        'form_tab'        => 1,
        'form_label'      => 'Status',
        'form_type'       => 'selectValues',
        'form_position'   => 13,
        'form_columns'    => 1,
        'form_regexp'     => null,
        'form_range'      => '1# PLANNED | 2# CREATED | 3# PREVIEW | 4 #FINAL',
        'default_value'   => '0',
        'list_position'   => 6,
        'list_align'      => 'center',
        'list_use_filter' => 1,
        'alt_position'    => 0,
        'status'          => '1',
        'is_integer'      => 0,
        'is_required'     => 0,
        'is_unique'       => 0
    ));

    $db->insert('user', array(
        'id'        => 1,
        'username'  => 'admin',
        'firstname' => 'Adminsitrator',
        'lastname'  => 'Administrator',
        'status'    => 'A',
        'admin'     => 1
    ));

    $db->insert('setting', array(
        'id'         => 1,
        'user_id'    => 1,
        'module_id'  => 0,
        'key_value'  => 'password',
        'value'      => md5('phprojektmd5'.$_REQUEST['admin_pass']),
        'identifier' => 'Core'
    ));

    $db->insert('setting', array(
        'id'         => 2,
        'user_id'    => 1,
        'module_id'  => 0,
        'key_value'  => 'email',
        'value'      => 'test@example.com',
        'identifier' => 'Core'
    ));

    $db->insert('setting', array(
        'id'         => 3,
        'user_id'    => 1,
        'module_id'  => 0,
        'key_value'  => 'language',
        'value'      => 'en',
        'identifier' => 'Core'
    ));

    $db->insert('setting', array(
        'id'         => 4,
        'user_id'    => 1,
        'module_id'  => 0,
        'key_value'  => 'timeZone',
        'value'      => '2',
        'identifier' => 'Core'
    ));

    // If it is not a migration, we will create the test user
    if (empty($_REQUEST["migration_config"])) {
        $db->insert('user', array(
            'id'        => 2,
            'username'  => 'test',
            'firstname' => 'Test',
            'lastname'  => 'Test',
            'status'    => 'A',
            'admin'     => 0
        ));

        $db->insert('setting', array(
            'id'         => 5,
            'user_id'    => 2,
            'module_id'  => 0,
            'key_value'  => 'password',
            'value'      => md5('phprojektmd5'.$_REQUEST['admin_pass']),
            'identifier' => 'Core'
        ));

        $db->insert('setting', array(
            'id'         => 6,
            'user_id'    => 2,
            'module_id'  => 0,
            'key_value'  => 'email',
            'value'      => 'test@example.com',
            'identifier' => 'Core'
        ));

        $db->insert('setting', array(
            'id'         => 7,
            'user_id'    => 2,
            'module_id'  => 0,
            'key_value'  => 'language',
            'value'      => 'en',
            'identifier' => 'Core'
        ));

        $db->insert('setting', array(
            'id'         => 8,
            'user_id'    => 2,
            'module_id'  => 0,
            'key_value'  => 'timeZone',
            'value'      => '2',
            'identifier' => 'Core'
        ));
    }

    $db->insert('project', array(
        'id'               => 1,
        'project_id'       => null,
        'path'             => '/',
        'title'            => 'PHProjekt',
        'notes'            => '',
        'owner_id'         => 1,
        'start_date'       => '2009-05-12',
        'end_date'         => '2010-07-28',
        'priority'         => 1,
        'current_status'   => 'working',
        'complete_percent' => 0,
        'hourly_wage_rate' => null,
        'budget'           => null
    ));

    $db->insert('groups', array(
        'id'   => 1,
        'name' => 'default'
    ));

    $db->insert('role', array(
        'id'     => 1,
        'name'   => 'Admin Role',
        'parent' => null
    ));

    $db->insert('groups_user_relation', array(
        'id'        => 1,
        'groups_id' => 1,
        'user_id'   => 1
    ));

    $db->insert('groups_user_relation', array(
        'id'        => 2,
        'groups_id' => 2,
        'user_id'   => 2
    ));

    $db->insert('project_role_user_permissions', array(
        'project_id' => 1,
        'user_id'    => 1,
        'role_id'    => 1
    ));

    $db->insert('project_role_user_permissions', array(
        'project_id' => 1,
        'user_id'    => 2,
        'role_id'    => 2
    ));

    $db->insert('role_module_permissions', array(
        'role_id'   => 1,
        'module_id' => 1,
        'access'    => 139
    ));

    $db->insert('role_module_permissions', array(
        'role_id'   => 1,
        'module_id' => 2,
        'access'    => 139
    ));

    $db->insert('role_module_permissions', array(
        'role_id'   => 1,
        'module_id' => 3,
        'access'    => 139
    ));

    $db->insert('role_module_permissions', array(
        'role_id'   => 1,
        'module_id' => 6,
        'access'    => 139
    ));

    $db->insert('role_module_permissions', array(
        'role_id'   => 1,
        'module_id' => 7,
        'access'    => 139
    ));

    $db->insert('role_module_permissions', array(
        'role_id'   => 1,
        'module_id' => 8,
        'access'    => 139
    ));

    $db->insert('role_module_permissions', array(
        'role_id'   => 1,
        'module_id' => 10,
        'access'    => 139
    ));

    $db->insert('role_module_permissions', array(
        'role_id'   => 1,
        'module_id' => 11,
        'access'    => 139
    ));

    $db->insert('item_rights', array(
        'module_id' => 1,
        'item_id'   => 1,
        'user_id'   => 1,
        'access'    => 255
    ));

    $db->insert('project_module_permissions', array(
        'module_id'  => 1,
        'project_id' => 1
    ));

    $db->insert('project_module_permissions', array(
        'module_id'  => 2,
        'project_id' => 1
    ));

    $db->insert('project_module_permissions', array(
        'module_id'  => 3,
        'project_id' => 1
    ));

    $db->insert('project_module_permissions', array(
        'module_id'  => 4,
        'project_id' => 1
    ));

    $db->insert('project_module_permissions', array(
        'module_id'  => 5,
        'project_id' => 1
    ));

    $db->insert('project_module_permissions', array(
        'module_id'  => 6,
        'project_id' => 1
    ));

    $db->insert('project_module_permissions', array(
        'module_id'  => 7,
        'project_id' => 1
    ));

    $db->insert('project_module_permissions', array(
        'module_id'  => 8,
        'project_id' => 1
    ));

    $db->insert('project_module_permissions', array(
        'module_id'  => 9,
        'project_id' => 1
    ));

    $db->insert('project_module_permissions', array(
        'module_id'  => 10,
        'project_id' => 1
    ));

    $db->insert('project_module_permissions', array(
        'module_id'  => 11,
        'project_id' => 1
    ));

    $db->insert('tab', array(
        'id'    => 1,
        'label' => 'Basic Data'
    ));

    $db->insert('tab', array(
        'id'    => 2,
        'label' => 'People'
    ));

    // creating log folders
    $baseDir = substr($_SERVER['SCRIPT_FILENAME'], 0, -22);
    $logsDir = $baseDir."logs";

    if (!file_exists($logsDir)) {
        if (!mkdir($logsDir)) {
            $_SESSION['error_message'] = "Please create the dir ".$logsDir." to save the logs or modify the log path "
                . "on configuration.ini file.";
        }
    }

    $uploadDir = $baseDir."upload";

    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir)) {
            $_SESSION['error_message'] = "Please create the dir ".$uploadDir." to upload files or modify the upload "
                . "path on configuration.ini file.";
        }
    } elseif (!is_writable($uploadDir)) {
        $_SESSION['error_message'] = "Please, set apache permission to writo on ".$uploadDir." to allow file upload "
            . "fields on modules.";
    }

    // Getting the language
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
        $webPath = "http://" . $_SERVER['HTTP_HOST'] . substr($_SERVER['REQUEST_URI'], 0, -15);
    } else {
        $webPath = "http://" . $_SERVER['HTTP_HOST'] . "/";
    }

    // Creating the configuration file
    $configurationFileContent = '[production]

; Language configuration
language             = "' . $clientLanguage . '"

; Path options
webpath              = ' . $webPath . '
uploadpath           = ' . $uploadDir . '

; Database options
database.adapter         = ' . $_REQUEST['server_type'] . '
database.params.host     = ' . $_REQUEST['server_host'] . '
database.params.username = ' . $_REQUEST['server_user'] . '
database.params.password = ' . $_REQUEST['server_pass'] . '
database.params.dbame    = ' . $_REQUEST['server_database'] . '

; Log options
log.debug.filename   = ' . $logsDir . DIRECTORY_SEPARATOR . 'debug.log
log.crit.filename    = ' . $logsDir . DIRECTORY_SEPARATOR . 'crit.log
itemsPerPage         = 3;

compressedDojo       = true
useCacheForClasses   = true

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
language             = "' . $clientLanguage . '"


; PATHS
; Where the site and the main file (index.php) are located (htdocs folder).
webpath              = "' . $webPath . '"

; Path where will be placed files uploaded by the user.
uploadpath           = "' . $uploadDir . '"


; DATABASE
; For this Developer Release, it just has been tested with pdo_mysql.
database.adapter            = "' . $_REQUEST['server_type'] . '"

; The assigned name or IP address for the database server.
database.params.host        = "' . $_REQUEST['server_host'] . '"

; Username and password with the appropriate rights for Phprojekt to access to
; the database.
database.params.username    = "' . $_REQUEST['server_user'] . '"
database.params.password    = "' . $_REQUEST['server_pass'] . '"

; Name of the database, inside the server
database.params.dbname      = "' . $_REQUEST['server_database'] . '"


; LOG
; Here will be logged things explicitly declared.
; E.G.: (PHP) Phprojekt::getInstance()->getLog()->debug("String to be logged");
log.debug.filename   = "' . $logsDir . DIRECTORY_SEPARATOR . 'debug.log"

; This is another type of logging.
; E.G.: (PHP) Phprojekt::getInstance()->getLog()->crit("String to be logged");
; Note for developers: there are many different type of logs defined that can be
; added here, see the complete list in phprojekt\library\Phprojekt\Log.php
log.crit.filename    = "' . $logsDir.DIRECTORY_SEPARATOR . 'crit.log"

; MODULES
; Not used at the moment, leave it as it is.
itemsPerPage         = 3

; Users
; How the users are displayed in the system
; (0 = lastname, firstname  1 = username, lastname, firstname, 2 = username)
userDisplayFormat = 0

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

    file_put_contents($baseDir . "configuration.ini", $configurationFileContent);

    // Migration
    if (isset($_REQUEST['migration_config']) && file_exists($_REQUEST['migration_config'])) {
        $statusConversion = array(
            1 => "offered",
            2 => "ordered",
            3 => "Working",
            4 => "ended",
            5 => "stopped",
            6 => "Re-Opened",
            7 => "waiting"
        );

        include_once($_REQUEST['migration_config']);

        // Check version
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

        // Group migration
        $groupUsers = array();
        $groups     = $dbOrig->fetchAll("SELECT * FROM " . PHPR_DB_PREFIX . "gruppen");

        while (list($dummy, $group) = each($groups)) {
            $tmp              = $group["ID"];
            $groupUsers[$tmp] = array();
        }

        // User migration
        $userKurz = array();
        $users    = $dbOrig->fetchAll("SELECT * FROM " . PHPR_DB_PREFIX . "users");

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

                $db->insert('user', array(
                    'id'        => $user["ID"],
                    'username'  => $username,
                    'firstname' => $user["vorname"],
                    'lastname'  => $user["nachname"],
                    'status'    => $status,
                    'admin'     => 0
                ));

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

                $db->insert('setting', array(
                    'id'         => null,
                    'user_id'    => $user["ID"],
                    'module_id'  => 0,
                    'key_value'  => 'password',
                    'value'      => $password,
                    'identifier' => 'Core'
                ));

                $db->insert('setting', array(
                    'id'         => null,
                    'user_id'    => $user["ID"],
                    'module_id'  => 0,
                    'key_value'  => 'email',
                    'value'      => $user["email"],
                    'identifier' => 'Core'
                ));

                $db->insert('setting', array(
                    'id'         => null,
                    'user_id'    => $user["ID"],
                    'module_id'  => 0,
                    'key_value'  => 'language',
                    'value'      => $language,
                    'identifier' => 'Core'
                ));

                $db->insert('setting', array(
                    'id'         => null,
                    'user_id'    => $user["ID"],
                    'module_id'  => 0,
                    'key_value'  => 'timeZone',
                    'value'      => $timeZone,
                    'identifier' => 'Core'
                ));

                $db->insert('item_rights', array(
                    'module_id' => 1,
                    'item_id'   => 1,
                    'user_id'   => $user["ID"],
                    'access'    => 255
                ));
            }

            $kurz            = $user['kurz'];
            $userKurz[$kurz] = $user['ID'];
        }

        // User group
        $userGroups = $dbOrig->fetchAll("SELECT * FROM " . PHPR_DB_PREFIX . "grup_user");

        while (list($dummy, $userGroup) = each($userGroups)) {
            $db->insert('groups_user_relation', array(
                'groups_id' => $userGroup["grup_ID"],
                'user_id'   => $userGroup["user_ID"]
            ));
            $tmp                = $userGroup["grup_ID"];
            $groupUsers[$tmp][] = $userGroup["user_ID"];
        }

        // Project migration
        $projects = $dbOrig->fetchAll("SELECT * FROM " . PHPR_DB_PREFIX . "projekte ORDER BY ID");

        $paths    = array();
        $paths[1] = "/1/";

        while (list($dummy, $project) = each($projects)) {
            $project["ID"] = convertProjectId($project["ID"]);
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

            $db->insert('project', array(
                'id'               => $project["ID"],
                'project_id'       => $project["parent"],
                "path"             => $project["path"],
                "title"            => $project["name"],
                "notes"            => $project["note"],
                "owner_id"         => $project["von"],
                "start_date"       => $project["anfang"],
                "end_date"         => $project["ende"],
                "priority"         => $project["wichtung"],
                "current_status"   => $tmpStatus,
                "complete_percent" => $project["status"],
                "hourly_wage_rate" => $project["stundensatz"],
                "budget"           => $project["budget"]));

            $db->insert('item_rights', array(
                'module_id' => 1,
                'item_id'   => $project["ID"],
                'user_id'   => 1,
                'access'    => 255
            ));

            migratePermissions($db, 1, $project["ID"], $project["acc"], 255, $project["gruppe"], $groupUsers,
                $userKurz);

            $db->insert('project_module_permissions', array(
                'module_id'  => 1,
                'project_id' => $project["ID"]
            ));

            $db->insert('project_module_permissions', array(
                'module_id'  => 2,
                'project_id' => $project["ID"]
            ));

            $db->insert('project_module_permissions', array(
                'module_id'  => 3,
                'project_id' => $project["ID"]
            ));

            $db->insert('project_module_permissions', array(
                'module_id'  => 4,
                'project_id' => $project["ID"]
            ));

            $db->insert('project_module_permissions', array(
                'module_id'  => 5,
                'project_id' => $project["ID"]
            ));

            $db->insert('project_module_permissions', array(
                'module_id'  => 6,
                'project_id' => $project["ID"]
            ));

            $db->insert('project_module_permissions', array(
                'module_id'  => 7,
                'project_id' => $project["ID"]
            ));

            $db->insert('project_module_permissions', array(
                'module_id'  => 8,
                'project_id' => $project["ID"]
            ));

            $db->insert('project_module_permissions', array(
                'module_id'  => 9,
                'project_id' => $project["ID"]
            ));

            $db->insert('project_module_permissions', array(
                'module_id'  => 10,
                'project_id' => $project["ID"]
            ));

            $db->insert('project_module_permissions', array(
                'module_id'  => 11,
                'project_id' => $project["ID"]
            ));
        }

        // Todo
        $todos = $dbOrig->fetchAll("SELECT * FROM " . PHPR_DB_PREFIX . "todo ORDER BY ID");

        while (list($dummy, $todo) = each($todos)) {
            $todo["project"] = convertProjectId($todo["project"]);
            $db->insert('todo', array(
                'id'             => $todo["ID"],
                'project_id'     => $todo["project"],
                "title"          => $todo["remark"],
                "notes"          => $todo["note"],
                "owner_id"       => $todo["von"],
                "start_date"     => $todo["anfang"],
                "end_date"       => $todo["deadline"],
                "priority"       => $todo["priority"],
                "current_status" => $todo["status"]
            ));

            $db->insert('item_rights', array(
                'module_id' => 2,
                'item_id'   => $todo["ID"],
                'user_id'   => 1,
                'access'    => 255
            ));

            migratePermissions($db, 2, $todo["ID"], $todo["acc"], 255, $todo["gruppe"], $groupUsers, $userKurz);
        }

        // Notes
        $notes = $dbOrig->fetchAll("SELECT * FROM " . PHPR_DB_PREFIX . "notes ORDER BY ID");

        while (list($dummy, $note) = each($notes)) {
            $note["projekt"] = convertProjectId($note["projekt"]);
            $db->insert('note', array(
                'id'         => $note["ID"],
                'project_id' => $note["projekt"],
                "title"      => $note["name"],
                "comments"   => $note["remark"],
                "owner_id"   => $note["von"],
                "category"   => $note["kategorie"]
            ));

            $db->insert('item_rights', array(
                'module_id' => 3,
                'item_id'   => $note["ID"],
                'user_id'   => 1,
                'access'    => 255
            ));

            migratePermissions($db, 3, $note["ID"], $note["acc"], 255, $note["gruppe"], $groupUsers, $userKurz);
        }

        // Timeproj
        $timeprojRecords = $dbOrig->fetchAll("SELECT * FROM " . PHPR_DB_PREFIX . "timeproj ORDER BY ID");

        while (list($dummy, $timeProj) = each($timeprojRecords)) {
            $timeProj["projekt"] = convertProjectId($timeProj["projekt"]);
            $db->insert('timeproj', array(
                'id'         => $timeProj["ID"],
                'notes'      => $timeProj["note"],
                "owner_id"   => $timeProj["users"],
                "project_id" => $timeProj["projekt"],
                "date"       => $timeProj["datum"],
                "amount"     => $timeProj["h"].":".$timeProj["m"].":00"
            ));
        }

        // Timecard
        $timecardRecords = $dbOrig->fetchAll("SELECT * FROM " . PHPR_DB_PREFIX . "timecard ORDER BY ID");

        while (list($dummy, $timeCard) = each($timecardRecords)) {
            $timeCard["anfang"]  = stringToTime($timeCard["anfang"]);
            $timeCard["ende"]    = stringToTime($timeCard["ende"]);
            $timeCard["projekt"] = convertProjectId($timeCard["projekt"]);
            $db->insert('timecard', array(
                'id'         => $timeCard["ID"],
                "owner_id"   => $timeCard["users"],
                "date"       => $timeCard["datum"],
                "start_time" => $timeCard["anfang"],
                "end_time"   => $timeCard["ende"]
            ));
        }

        // Calendar
        $events = $dbOrig->fetchAll("SELECT * FROM " . PHPR_DB_PREFIX . "termine ORDER BY ID");

        while (list($dummy, $calendar) = each($events)) {
            $calendar["projekt"] = convertProjectId($calendar["projekt"]);
            $calendar["anfang"]  = stringToTime($calendar["anfang"]);
            $calendar["ende"]    = stringToTime($calendar["ende"]);
            $db->insert('calendar', array(
                'id'             => $calendar["ID"],
                "parent_id"      => $calendar["parent"],
                "owner_id"       => $calendar["von"],
                "project_id"     => $calendar["projekt"],
                "title"          => $calendar["event"],
                "place"          => $calendar["ort"],
                "notes"          => $calendar["remark"],
                "uid"            => $calendar["serie_id"],
                "start_date"     => $calendar["datum"],
                "start_time"     => $calendar["anfang"],
                "end_date"       => $calendar["datum"],
                "end_time"       => $calendar["ende"],
                "timezone"       => $timeZone,
                "location"       => $calendar["ort"],
                "categories"     => "",
                "attendee"       => $calendar["an"],
                "priority"       => $calendar["priority"],
                "rrule"          => "",
                "properties"     => "",
                "participant_id" => $calendar["an"]
            ));

            $db->insert('item_rights', array(
                'module_id' => 5,
                'item_id'   => $calendar["ID"],
                'user_id'   => 1,
                'access'    => 255
            ));

            $db->insert('item_rights', array(
                'module_id' => 5,
                'item_id'   => $calendar["ID"],
                'user_id'   => $calendar["an"],
                'access'    => 255
            ));
        }

        // Filemanager
        $files = $dbOrig->fetchAll("SELECT * FROM " . PHPR_DB_PREFIX . "dateien ORDER BY ID");

        while (list($dummy, $file) = each($files)) {
            $file["div2"] = convertProjectId($file["div2"]);
            $newFilename  = md5($file["tempname"]);

            copy(PHPR_FILE_PATH . "\\" . $file["tempname"], $uploadDir . "\\" . $newFilename);

            $db->insert('filemanager', array(
                'id'         => $file["ID"],
                'owner_id'   => $file["von"],
                "title"      => $file["filename"],
                "comments"   => $file["remark"],
                "project_id" => $file["div2"],
                "category"   => $file["kat"],
                "files"      => $newFilename."|".$file["filename"]
            ));

            $db->insert('item_rights', array(
                'module_id' => 7,
                'item_id'   => $file["ID"],
                'user_id'   => 1,
                'access'    => 255
            ));

            migratePermissions($db, 7, $file["ID"], $file["acc"], 255, $file["gruppe"], $groupUsers, $userKurz);
        }
    } // end of migration
}

/**
 * Migrates the permission from PHProjekt 5.x version to PHProjekt 6.0
 *
 * @param database_manager $db
 * @param int              $moduleId   Module to grant permissions
 * @param int              $itemId     Item to set the permission
 * @param mixed            $users      Serialized array of users or group
 * @param int              $access     Access value
 * @param int              $group      Group of the project
 * @param array            $userGroups Array with users by group
 * @param array            $userKurz   Array with ID => kurz conversion
 *
 * @return void
 */
function migratePermissions($db, $moduleId, $itemId, $users, $access, $group, $userGroups, $userKurz)
{
    $userList     = array();
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
        $db->insert('item_rights', array(
            'module_id' => (int)$moduleId,
            'item_id'   => (int)$itemId,
            'user_id'   => (int)$oneUser,
            'access'    => (int)$access
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
function stringToTime($stringTime)
{
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
 * @param integer $oldprojectId
 *
 * @return integer
 */
function convertProjectId($oldprojectId)
{
    if ($oldprojectId == 1) {
        return 10001;
    } elseif (empty($oldprojectId)) {
        return 1;
    }

    return $oldprojectId;
}
