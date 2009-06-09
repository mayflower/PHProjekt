<?php
/**
 * Setup
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
 * Setup
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @package    Setup
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Setup_Models_Setup
{
    private $_error = array();
    private $_db    = null;

    public function __construct()
    {
        $this->_checkServer();
    }

    /**
     * Do some checks before install
     *
     * @throws Expeption
     *
     * @return void
     */
    private function _checkServer()
    {
        // Check the PHP version
        if (substr(phpversion(), 0, 1) < 5) {
            throw new Exception("Sorry, you need PHP 5 or newer to run PHProjekt 6");
        }

        // Check pdo library
        $mysql  = phpversion('pdo_mysql');
        $sqlite = phpversion('pdo_sqlite2');
        $pgsql  = phpversion('pdo_pgsql');

        if (empty($mysql) && empty($sqlite) && empty($pgsql)) {
            throw new Exception("Sorry, you need pdo_mysql, pdo_pgsql or pdo_sqlite "
                . "extension to install PHProjekt 6");
        }

        // Checking if configuration.ini exists
        $baseDir = ereg_replace('htdocs/setup.php', '', $_SERVER['SCRIPT_FILENAME']);
        if (file_exists($baseDir . "configuration.ini")) {
            throw new Exception("Configuration file found. Please, delete it before run setup again.");
        }
    }

    /**
     * Validate the params
     *
     * @param array $params Array with the POST values
     *
     * @return boolean
     */
    public function validate($params)
    {
        $valid = true;

        if (!isset($params['dbHost']) || empty($params['dbHost'])) {
            $this->_error[] = 'The database server address can not be empty';
            $valid = false;
        } else if (!isset($params['dbUser']) || empty($params['dbUser'])) {
            $this->_error[] = 'The database user can not be empty';
            $valid = false;
        } else if (!isset($params['dbPass']) || empty($params['dbPass'])) {
            $this->_error[] = 'The database password can not be empty';
            $valid = false;
        } else if (!isset($params['dbName']) || empty($params['dbName'])) {
            $this->_error[] = 'The database name can not be empty';
            $valid = false;
        } else {
            // Mysql
            @mysql_connect($params['dbHost'], $params['dbUser'], $params['dbPass']);
            @mysql_query("DROP DATABASE " . $params['dbName']);
            @mysql_query("CREATE DATABASE " . $params['dbName'] . " DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;");
            if (!mysql_select_db($params['dbName'])) {
                $this->_error[] = 'Error selecting database ' . $params['dnName'];
                $valid = false;
            }

            try {
                $dbParams = array(
                    'host'     => $params['dbHost'],
                    'username' => $params['dbUser'],
                    'password' => $params['dbPass'],
                    'dbname'   => $params['dbName']
                );
                $this->_db = Zend_Db::factory($params['serverType'], $dbParams);
            } catch (Exception $error) {
                $this->_error[] = 'Can not connect to server at ' . $params['dbHost']
                    . ' using ' . $params['dbUser'] . ' user ' . '(' . $error->getMessage() . ')';
                $valid = false;
            }
        }

        if (!isset($params['adminPass']) || empty($params['adminPass'])) {
            $this->_error[] = 'The admin password can not be empty';
            $valid = false;
        } else if ($params['adminPassConfirm'] != $params['adminPass']) {
            $this->_error[] = 'The admin password and confirmation are different';
            $valid = false;
        }

        // Check write access
        $baseDir    = ereg_replace('htdocs/setup.php', '', $_SERVER['SCRIPT_FILENAME']);
        $configFile = $baseDir . "configuration.ini";

        if (!file_exists($configFile)) {
            if (!file_put_contents($configFile, "Test")) {
                $this->_error[] = 'Error creating the configuration file at '. $configFile;
                $valid = false;
            } else {
                unlink($configFile);
            }
        }

        // Check log files
        $logsDir = $baseDir . "logs";

        if (!file_exists($logsDir)) {
            if (!mkdir($logsDir)) {
                $this->_error[] = 'Please create the dir ' . $logsDir . ' to save the logs';
                $valid = false;
            }
            if (!@fopen($logsDir . DIRECTORY_SEPARATOR . 'debug.log', 'a', false)) {
                $this->_error[] = 'The debug log can not be created';
                $valid = false;
            }
            if (!@fopen($logsDir . DIRECTORY_SEPARATOR . 'crit.log', 'a', false)) {
                $this->_error[] = 'The crit log can not be created';
                $valid = false;
            }
        } else if (!is_writable($logsDir)) {
            $this->_error[] = 'Please, set apache permission to writo on ' . $logsDir . ' to allow write logs';
            $valid = false;
        }

        // Check upload dir
        $uploadDir = $baseDir . "upload";

        if (!file_exists($uploadDir)) {
            if (!mkdir($uploadDir)) {
                $this->_error[] = 'Please create the dir ' . $uploadDir . ' to upload files';
                $valid = false;
            }
        } else if (!is_writable($uploadDir)) {
            $this->_error[] = 'Please, set apache permission to writo on ' . $uploadDir . ' to allow upload '
                . 'files on modules.';
            $valid = false;
        }

        return $valid;
    }

    /**
     * Return the errors created by validate()
     *
     * @return array
     */
    public function getError()
    {
        $error        = $this->_error;
        $this->_error = array();

        return $error;
    }

    /**
     * Install itself
     *
     * @param array $params Array with the POST values
     *
     * @return void
     */
    public function install($params)
    {
        $options = array();
        $options['useExtraData'] = (boolean) $params['useExtraData'];

        $dbParser = new Phprojekt_DbParser($options, $this->_db);
        $dbParser->parseData(PHPR_ROOT_PATH . DIRECTORY_SEPARATOR . 'application');

        // Update admin Pass
        $this->_db->update('setting', array('value' => md5('phprojektmd5' . $params['adminPass'])), 'id = 1');

        // Migration
        if (file_exists($params['migrationConfigFile'])) {
            try {
                $migration = new Setup_Models_Migration($params['migrationConfigFile'], $this->_db);
                $migration->migrateTables();
            } catch (Exception $error) {
                echo $error->getMessage();
            }
        }

        // Create config file
        $config  = new Setup_Models_Config();
        $content = $config->getDefaultProduction($params['dbUser'], $params['dbPass'], $params['dbName'],
            'Pdo_Mysql', $params['dbHost']);

        $baseDir    = ereg_replace('htdocs/setup.php', '', $_SERVER['SCRIPT_FILENAME']);
        $configFile = $baseDir . "configuration.ini";
        file_put_contents($configFile, $content);
    }
}
