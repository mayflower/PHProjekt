#!/usr/bin/env php
<?php

/**
 * This script calls the setup screens from the command-line.
 *
 * It should only be used in development.
 */
$url        = 'http://phprojekt.local';
$adminpw    = 'a';
$testpw     = 'q';
$privateDir =  '/phprojekt_private/';

/////////////////////////////////////////////////////////
// No modification beyond this point should be neccessary

require_once 'goutte.phar';
use Goutte\Client;

$client  = new Client();
$client->request('GET', $url . '/setup.php');
$client->request('POST', $url . '/setup.php/index/jsonDatabaseForm');
$client->request(
    'POST',
    $url . '/setup.php/index/jsonDatabaseSetup',
    array(
        'dbHost'     => 'localhost',
        'dbName'     => 'phprojekt',
        'dbPass'     => '',
        'dbPort'     => 3306,
        'dbUser'     => 'phprojekt',
        'serverType' => 'pdo_mysql'
    )
);
$client->request('POST', $url . '/setup.php/index/jsonUsersForm');
$client->request(
    'POST',
    $url . '/setup.php/index/jsonUsersSetup',
    array(
        'adminPass' => $adminpw,
        'adminPassConfirm' => $adminpw,
        'testPass' => $testpw,
        'testPassConfirm' => $testpw
    )
);
$client->request('POST', $url . '/setup.php/index/jsonFoldersForm');
$client->request(
    'POST',
    $url . '/setup.php/index/jsonFoldersSetup',
    array(
        'confirmationCheck' => '0',
        'privateDir' => realpath(dirname(__FILE__) . '/../phprojekt') . $privateDir
    )
);
$client->request('POST', $url . '/setup.php/index/jsonTablesForm');
$client->request(
    'POST',
    $url . '/setup.php/index/jsonTablesSetup',
    array(
        'useExtraData' => '1'
    )
);
$client->request('POST', $url . '/setup.php/index/jsonMigrateForm');
$client->request('POST', $url . '/setup.php/index/jsonFinish');
