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

    $availableEngines = array();

    // check the PHP version
    if (substr(phpversion(), 0, 1) < 5) {
        die("Sorry, you need PHP 5 or newer to run PHProjekt 6");
    }

    // check pdo library
    $tmp = phpversion('pdo_mysql');
    $tmp2 = phpversion('pdo_sqlite2');
    $tmp3 = phpversion('pdo_pgsql');
    
    if (empty($tmp) && empty($tmp) && empty($tmp)) {
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

function preInstallChecks() {
    $returnValue = true;

    if($_REQUEST['server_type'] == 'pdo_mysql') {

        $link = @mysql_connect($_REQUEST['server_host'], $_REQUEST['server_user'], $_REQUEST['server_pass']);

        mysql_query("CREATE DATABASE ".$_REQUEST['server_database']);

        if (!mysql_select_db($_REQUEST['server_database'])) {
            $_SESSION['error_message'] = "Error selecting database ".$_REQUEST['server_database'];
            $returnValue = false;
        }
    }
    
    try {
        $db = Zend_Db::factory($_REQUEST['server_type'], array(
        'host'     => $_REQUEST['server_host'],
        'username' => $_REQUEST['server_user'],
        'password' => $_REQUEST['server_pass'],
        'dbname'   => $_REQUEST['server_database']
        ));
    } catch (Exception $e) {
        $_SESSION['error_message'] = "Can't connect to server at '".$_REQUEST['server_host'].
                                     "' using '".$_REQUEST['server_user']."' user";
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

    try {
        $db = Zend_Db::factory($_REQUEST['server_type'], array(
        'host'     => $_REQUEST['server_host'],
        'username' => $_REQUEST['server_user'],
        'password' => $_REQUEST['server_pass'],
        'dbname'   => $_REQUEST['server_database']
        ));
    } catch (Exception $e) {
        die("Error connecting to server");
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
        $db->getConnection()->exec("DROP TABLE IF EXISTS ".$oneTable);
    }

    $result = $db->getConnection()->exec('CREATE TABLE DatabaseManager (
      id int NOT NULL AUTO_INCREMENT,
      tableName varchar(50) default NULL,
      tableField varchar(60) default NULL,
      formTab int(11) default NULL,
      formLabel varchar(255) default NULL,
      formTooltip varchar(255) default NULL,
      formType varchar(50) default NULL,
      formPosition int(11) default NULL,
      formColumns int(11) default NULL,
      formRegexp varchar(255) default NULL,
      formRange text default NULL,
      defaultValue varchar(255) default NULL,
      listPosition int(11) default NULL,
      listAlign varchar(20) default NULL,
      listUseFilter int(4) default NULL,
      altPosition int(11) default NULL,
      status varchar(20) default NULL,
      isInteger int(4) default NULL,
      isRequired int(4) default NULL,
      isUnique int(11) default NULL,
      PRIMARY KEY  (id)
    )');

    if ($result === false) die("Error creating DatabaseManager table");

    $result = $db->getConnection()->exec('CREATE TABLE User (
      id int(11) NOT NULL AUTO_INCREMENT,
      username varchar(255) NOT NULL,
      firstname varchar(255) default NULL,
      lastname varchar(255) default NULL,
      status varchar(1) default \'A\',
      PRIMARY KEY(id),
      UNIQUE(username)
    )');

    if ($result === false) die("Error creating User table");

    $result = $db->getConnection()->exec('CREATE TABLE Module (
      id int(11) NOT NULL AUTO_INCREMENT,
      name varchar(255) NOT NULL,
      saveType int(1) NOT NULL default 0,
      active int(1) NOT NULL default 1,
      PRIMARY KEY  (id)
    )');

    if ($result === false) die("Error creating Module table");

    $result = $db->getConnection()->exec('CREATE TABLE Groups (
      id int NOT NULL AUTO_INCREMENT,
      name varchar(255),
      PRIMARY KEY  (id)
    )');

    if ($result === false) die("Error creating Groups table");


    $result = $db->getConnection()->exec('CREATE TABLE GroupsUserRelation (
      id int NOT NULL AUTO_INCREMENT,
      groupsId int(11) NOT NULL,
      userId int(11) NOT NULL,
      PRIMARY KEY  (id)
    )');

    if ($result === false) die("Error creating GroupsUserRelation table");


    $result = $db->getConnection()->exec('CREATE TABLE History (
      id int(11) NOT NULL auto_increment,
      moduleId int(11) NOT NULL,
      userId int(11) NOT NULL,
      itemId int(11) NOT NULL,
      field varchar(255) NOT NULL,
      oldValue varchar(100) default NULL,
      newValue varchar(255) default NULL,
      action varchar(50) NOT NULL,
      datetime timestamp NOT NULL default CURRENT_TIMESTAMP,
      PRIMARY KEY (id)
    )');

    if ($result === false) die("Error creating history table");

    $result = $db->getConnection()->exec("CREATE TABLE Project (
      id int(11) NOT NULL AUTO_INCREMENT,
      projectId int(11) default NULL,
      path varchar(25) NOT NULL default '/',
      title varchar(255) NOT NULL,
      notes text default NULL,
      ownerId int(11) default NULL,
      startDate date default NULL,
      endDate date default NULL,
      priority int(11) default NULL,
      currentStatus varchar(50) NOT NULL default 'working',
      completePercent float default '0',
      hourlyWageRate float default NULL,
      budget float default NULL,
      PRIMARY KEY (id)
    )");

    if ($result === false) die("Error creating Project table");

    $result = $db->getConnection()->exec('CREATE TABLE ProjectModulePermissions (
        id int(11) NOT NULL AUTO_INCREMENT,
        moduleId int(11) NOT NULL,
        projectId int(11) NOT NULL,
        PRIMARY KEY (id)
    )');

    if ($result === false) die("Error creating ProjectModulePermissions table");

    $result = $db->getConnection()->exec('CREATE TABLE Role (
      id int(11) NOT NULL AUTO_INCREMENT,
      name varchar(255) NOT NULL,
      parent int(11) default NULL,
      PRIMARY KEY(id)
    )');

    if ($result === false) die("Error creating Role table");

    $result = $db->getConnection()->exec('CREATE TABLE ProjectRoleUserPermissions (
      id int(11) NOT NULL AUTO_INCREMENT,
      projectId int(11) NOT NULL,
      userId int(11) NOT NULL,
      roleId int(11) NOT NULL,
      PRIMARY KEY (id)
    )');

    if ($result === false) die("Error creating ProjectRoleUserPermissions table");

    $result = $db->getConnection()->exec('CREATE TABLE RoleModulePermissions (
      id int(11) NOT NULL AUTO_INCREMENT,
      roleId int(11) NOT NULL,
      moduleId int(11) NOT NULL,
      access int(3) NOT NULL,
      PRIMARY KEY  (id)
    )');

    if ($result === false) die("Error creating RoleModulePermissions table");


    $result = $db->getConnection()->exec('CREATE TABLE Todo (
      id int(11) NOT NULL auto_increment,
      title varchar(255) NOT NULL,
      notes text default NULL,
      ownerId int(11) default NULL,
      projectId int(11) default NULL,
      startDate date default NULL,
      endDate date default NULL,
      priority int(11) default NULL,
      currentStatus varchar(50) NOT NULL default \'working\',
      PRIMARY KEY  (id)
    )');

    if ($result === false) die("Error creating Todo table");

    $result = $db->getConnection()->exec('CREATE TABLE Setting (
      id int(11) NOT NULL AUTO_INCREMENT,
      userId int(11) NOT NULL,
      moduleId int(11) NOT NULL,
      keyValue varchar(255) NOT NULL,
      value varchar(255) NOT NULL,
      identifier  varchar(50) NOT NULL,
      PRIMARY KEY (id)
    )');

    if ($result === false) die("Error creating Setting table");

    $result = $db->getConnection()->exec('CREATE TABLE SearchWords (
      id int(11) NOT NULL AUTO_INCREMENT,
      word varchar(255) NOT NULL,
      count int(11) NOT NULL,
      PRIMARY KEY  (id)
    )');

    if ($result === false) die("Error creating SearchWords table");

    $result = $db->getConnection()->exec('CREATE TABLE SearchWordModule (
      moduleId int(11) NOT NULL,
      itemId int(11) NOT NULL,
      wordId int(11) NOT NULL,
      PRIMARY KEY  (itemId,moduleId,wordId)
    )');

    if ($result === false) die("Error creating SearchWordsModule table");

    $result = $db->getConnection()->exec('CREATE TABLE SearchDisplay (
      moduleId int(11) NOT NULL,
      itemId int(11) NOT NULL,
      firstDisplay varchar(255),
      secondDisplay varchar(255),
      projectId int(11) NOT NULL,
      PRIMARY KEY  (itemId,moduleId)
    )');

    if ($result === false) die("Error creating SearchDisplay table");

    $result = $db->getConnection()->exec('CREATE TABLE Tags (
      id int(11) NOT NULL AUTO_INCREMENT,
      word varchar(255) NOT NULL,
      crc32 bigint NOT NULL,
      PRIMARY KEY  (id)
    )');

    if ($result === false) die("Error creating Tags table");

    $result = $db->getConnection()->exec('CREATE TABLE TagsUsers (
      id int(11) NOT NULL AUTO_INCREMENT,
      userId int(11) NOT NULL,
      tagId int(11) NOT NULL,
      PRIMARY KEY  (id)
    )');

    if ($result === false) die("Error creating TagsUsers table");

    $result = $db->getConnection()->exec('CREATE TABLE TagsModules (
      moduleId int(11) NOT NULL,
      itemId int(11) NOT NULL,
      tagUserId int(11) NOT NULL,
      PRIMARY KEY  (moduleId, itemId, tagUserId)
    )');

    if ($result === false) die("Error creating TagsModule table");

    $result = $db->getConnection()->exec('CREATE TABLE Tab (
      id int(11) NOT NULL AUTO_INCREMENT,
      label varchar(255) NOT NULL,
      PRIMARY KEY (id)
    )');

    if ($result === false) die("Error creating Tab table");

    $result = $db->getConnection()->exec('CREATE TABLE ModuleTabRelation (
      tabId int(11) NOT NULL,
      moduleId int(11) NOT NULL,
      PRIMARY KEY (tabId, moduleId)
    )');

    if ($result === false) die("Error creating ModuleTableRelation table");

    $result = $db->getConnection()->exec('CREATE TABLE Note (
      id int(11) NOT NULL auto_increment,
      projectId int(11) default NULL,
      title varchar(255) NOT NULL,
      comments text default NULL,
      category varchar(50) default NULL,
      ownerId int(11) default NULL,
      PRIMARY KEY  (id)
    )');

    if ($result === false) die("Error creating Note table");

    $result = $db->getConnection()->exec('CREATE TABLE Configuration (
      id int(11) NOT NULL AUTO_INCREMENT,
      moduleId int(11) NOT NULL,
      `key` varchar(255) NOT NULL,
      `value` text default NULL,
      PRIMARY KEY (id)
    )');

    if ($result === false) die("Error creating Configuration table");

    $result = $db->getConnection()->exec('CREATE TABLE ItemRights (
      moduleId int(11) NOT NULL,
      itemId int(11) NOT NULL,
      userId int(11) NOT NULL,
      access int(3) NOT NULL,
      PRIMARY KEY (moduleId,itemId,userId)
    )');

    if ($result === false) die("Error creating ItemRights table");

    $result = $db->getConnection()->exec('CREATE TABLE Timecard (
      id int(11) NOT NULL auto_increment,
      ownerId int(11) default NULL,
      date date default NULL,
      startTime time default NULL,
      endTime time default NULL,
      PRIMARY KEY (id)
    )');

    if ($result === false) die("Error creating Timecard table");

    $result = $db->getConnection()->exec('CREATE TABLE Timeproj (
      id int(11) NOT NULL auto_increment,
      notes text default NULL,
      ownerId int(11) default NULL,
      projectId int(11) default NULL,
      date date default NULL,
      amount  time default NULL,
      PRIMARY KEY (id)
    )');

    if ($result === false) die("Error creating Timeproj table");

    $result = $db->getConnection()->exec('CREATE TABLE Calendar (
      id int(11) NOT NULL auto_increment,
      title varchar(255) default NULL,
      notes text default NULL,
      ownerId int(11) default NULL,
      projectId int(11) default NULL,
      startDate date default NULL,
      participantId int(11) default NULL,
      startTime time default NULL,
      endTime time default NULL,
      parentId int(11) default NULL,
      serialType int(11) default NULL,
      serialDays int(11) default NULL,
      endDate date default NULL,
      PRIMARY KEY (id)
    )');

    if ($result === false) die("Error creating Calendar table");

    $db->insert('Module', array(
    'id' => 1,
    'name' => 'Project',
    'saveType' => 0,
    'active' => 1
    ));

    $db->insert('Module', array(
    'id' => 2,
    'name' => 'Todo',
    'saveType' => 0,
    'active' => 1
    ));

    $db->insert('Module', array(
    'id' => 3,
    'name' => 'Note',
    'saveType' => 0,
    'active' => 1
    ));

    $db->insert('Module', array(
    'id' => 4,
    'name' => 'Timecard',
    'saveType' => 1,
    'active' => 1
    ));

    $db->insert('Module', array(
    'id' => 5,
    'name' => 'Calendar',
    'saveType' => 1,
    'active' => 1
    ));

    $db->insert('Module', array(
    'id' => 6,
    'name' => 'Gantt',
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
    'formType' => 'tree',
    'formPosition' => 3,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => 'Project',
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
    'formType' => 'tree',
    'formPosition' => 3,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => 'Project',
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
    'tableName' => 'History',
    'tableField' => 'userId',
    'formTab' => 1,
    'formLabel' => 'UserId',
    'formTooltip' => 'UserId',
    'formType' => 'userId',
    'formPosition' => 1,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '',
    'defaultValue' => '0',
    'listPosition' => 1,
    'listAlign' => 'left',
    'listUseFilter' => 1,
    'altPosition' => 1,
    'status' => '1',
    'isInteger' => 1,
    'isRequired' => 1,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 18,
    'tableName' => 'History',
    'tableField' => 'itemId',
    'formTab' => 1,
    'formLabel' => 'ItemId',
    'formTooltip' => 'ItemId',
    'formType' => 'text',
    'formPosition' => 2,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '',
    'defaultValue' => '0',
    'listPosition' => 2,
    'listAlign' => 'center',
    'listUseFilter' => 1,
    'altPosition' => 2,
    'status' => '1',
    'isInteger' => 1,
    'isRequired' => 1,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 19,
    'tableName' => 'History',
    'tableField' => 'moduleId',
    'formTab' => 1,
    'formLabel' => 'Module',
    'formTooltip' => 'Module',
    'formType' => 'text',
    'formPosition' => 3,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '',
    'defaultValue' => '',
    'listPosition' => 3,
    'listAlign' => 'left',
    'listUseFilter' => 1,
    'altPosition' => 3,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 1,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 20,
    'tableName' => 'History',
    'tableField' => 'field',
    'formTab' => 1,
    'formLabel' => 'Field',
    'formTooltip' => 'Field',
    'formType' => 'text',
    'formPosition' => 4,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '',
    'defaultValue' => '',
    'listPosition' => 4,
    'listAlign' => 'left',
    'listUseFilter' => 1,
    'altPosition' => 4,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 1,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 21,
    'tableName' => 'History',
    'tableField' => 'oldValue',
    'formTab' => 1,
    'formLabel' => 'OldValue',
    'formTooltip' => 'OldValue',
    'formType' => 'text',
    'formPosition' => 5,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '',
    'defaultValue' => '',
    'listPosition' => 0,
    'listAlign' => '',
    'listUseFilter' => 0,
    'altPosition' => 0,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 1,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 22,
    'tableName' => 'History',
    'tableField' => 'newValue',
    'formTab' => 1,
    'formLabel' => 'NewValue',
    'formTooltip' => 'NewValue',
    'formType' => 'text',
    'formPosition' => 6,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '',
    'defaultValue' => '',
    'listPosition' => 0,
    'listAlign' => '',
    'listUseFilter' => 0,
    'altPosition' => 0,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 1,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 23,
    'tableName' => 'History',
    'tableField' => 'action',
    'formTab' => 1,
    'formLabel' => 'Action',
    'formTooltip' => 'Action',
    'formType' => 'text',
    'formPosition' => 7,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '',
    'defaultValue' => '',
    'listPosition' => 7,
    'listAlign' => 'left',
    'listUseFilter' => 1,
    'altPosition' => 7,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 1,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 24,
    'tableName' => 'History',
    'tableField' => 'datetime',
    'formTab' => 1,
    'formLabel' => 'Datetime',
    'formTooltip' => 'Datetime',
    'formType' => 'datetime',
    'formPosition' => 8,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '',
    'defaultValue' => '',
    'listPosition' => 8,
    'listAlign' => 'center',
    'listUseFilter' => 1,
    'altPosition' => 8,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 1,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 25,
    'tableName' => 'Note',
    'tableField' => 'projectId',
    'formTab' => 1,
    'formLabel' => 'project',
    'formTooltip' => 'project',
    'formType' => 'tree',
    'formPosition' => 3,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => 'Project',
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
    'id' => 26,
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
    'id' => 27,
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
    'id' => 28,
    'tableName' => 'Note',
    'tableField' => 'category',
    'formTab' => 1,
    'formLabel' => 'category',
    'formTooltip' => 'category',
    'formType' => 'selectSqlAddOne',
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
    'id' => 29,
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
    'id' => 30,
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
    'id' => 31,
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
    'id' => 32,
    'tableName' => 'Calendar',
    'tableField' => 'participantId',
    'formTab' => 1,
    'formLabel' => 'participantId',
    'formTooltip' => 'participantId',
    'formType' => 'multipleSelectValues',
    'formPosition' => 8,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => 'User',
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
    'id' => 33,
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
    'id' => 34,
    'tableName' => 'Calendar',
    'tableField' => 'endTime',
    'formTab' => 1,
    'formLabel' => 'endTime',
    'formTooltip' => 'endTime',
    'formType' => 'time',
    'formPosition' => 5,
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
    'isRequired' => 0,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 35,
    'tableName' => 'Calendar',
    'tableField' => 'serialType',
    'formTab' => 1,
    'formLabel' => 'serialType',
    'formTooltip' => 'serialType',
    'formType' => 'selectValues',
    'formPosition' => 7,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '1#Once|2#Daily|3#Weekly|4#Montlhy|5#Anually',
    'defaultValue' => '1',
    'listPosition' => 0,
    'listAlign' => 'center',
    'listUseFilter' => 1,
    'altPosition' => 0,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 0,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 36,
    'tableName' => 'Calendar',
    'tableField' => 'serialDays',
    'formTab' => 1,
    'formLabel' => 'serialDays',
    'formTooltip' => 'serialDays',
    'formType' => 'selectValues',
    'formPosition' => 7,
    'formColumns' => 1,
    'formRegexp' => '',
    'formRange' => '0#All|1#Monday|2#Tuesday|3#Wednesday|4#Thursday|5#Friday|6#Saturday|7#Sunday',
    'defaultValue' => '1',
    'listPosition' => 0,
    'listAlign' => 'center',
    'listUseFilter' => 1,
    'altPosition' => 0,
    'status' => '1',
    'isInteger' => 0,
    'isRequired' => 0,
    'isUnique' => 0
    ));

    $db->insert('DatabaseManager', array(
    'id' => 37,
    'tableName' => 'Calendar',
    'tableField' => 'endDate',
    'formTab' => 1,
    'formLabel' => 'endDate',
    'formTooltip' => 'endDate',
    'formType' => 'date',
    'formPosition' => 8,
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

    $db->insert('ProjectRoleUserPermissions', array(
    'projectId' => 1,
    'userId' => 1,
    'roleId' => 1
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