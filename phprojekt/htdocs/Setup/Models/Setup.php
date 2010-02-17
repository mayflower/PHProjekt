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
    private $_error   = array();
    private $_message = array();
    private $_db      = null;

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
        $missingRequirements = array();

        // The following extensions are either needed by components of the Zend Framework that are used
        // or by P6 components itself.
        $extensionsNeeded = array('mbstring', 'iconv', 'ctype', 'gd', 'pcre', 'pdo', 'Reflection', 'session', 'SPL',
            'zlib');

        // These settings need to be properly configured by the admin
        $settingsNeeded = array('magic_quotes_gpc' => 0, 'magic_quotes_runtime' => 0, 'magic_quotes_sybase' => 0);

        // These settings should be properly configured by the admin
        $settingsRecommended = array('register_globals' => 0, 'safe_mode' => 0);

        // Check the PHP version
        $requiredPhpVersion = "5.2.4";
        if (version_compare(phpversion(), $requiredPhpVersion, '<')) {
            // This is a requirement of the Zend Framework
            $missingRequirements[] = "PHP Version $requiredPhpVersion or newer";
        }

        foreach ($extensionsNeeded as $extension) {
            if (!extension_loaded($extension)) {
                $missingRequirements[] = "The $extension extension must be enabled.";
            }
        }

        // Check pdo library
        $mysql  = extension_loaded('pdo_mysql');
        $sqlite = extension_loaded('pdo_sqlite2');
        $pgsql  = extension_loaded('pdo_pgsql');

        if (!$mysql && !$sqlite && !$pgsql) {
            $missingRequirements[] = "You need one of these PDO extensions: pdo_mysql, pdo_pgsql or pdo_sqlite";
        }

        foreach ($settingsNeeded as $conf => $value) {
            if (ini_get($conf) != $value) {
                $missingRequirements[] = "The php.ini setting of \"$conf\" has to be \"$value\".";
            }
        }

        // Checking if configuration.ini exists
        $baseDir = str_replace('htdocs/setup.php', '', $_SERVER['SCRIPT_FILENAME']);
        if (file_exists($baseDir . "configuration.ini")) {
            throw new Exception("Configuration file found. Please, delete it before run setup again.");
        }

        if (!empty($missingRequirements)) {
            $message = "Your PHP does not meet the requirements needed for PHProjekt 6.\n"
                . implode("\n", $missingRequirements);
            throw new Exception($message);
        }

        if (strncmp($_SERVER['PHP_SELF'], '/setup.php', 10) < 0) {
            $message = "PHProjekt 6 must be installed in a DocumentRoot directory.";
            throw new Exception($message);
        }

        foreach ($settingsRecommended as $conf => $value) {
            if (ini_get($conf) != $value) {
                $this->_message[] = "It is recommend to have \"$conf\" set to \"$value\", but it is not required "
                    . "to run PHProjekt.";
            }
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
        } else if (!isset($params['dbName']) || empty($params['dbName'])) {
            $this->_error[] = 'The database name can not be empty';
            $valid = false;
        } else {
            try {
                $dbParams = array(
                    'host'     => $params['dbHost'],
                    'username' => $params['dbUser'],
                    'password' => $params['dbPass'],
                    'dbname'   => $params['dbName']
                );
                $this->_db = Zend_Db::factory($params['serverType'], $dbParams);
                $this->_db->query("DROP DATABASE `" . $params['dbName'] . "`");
                $this->_db->query("CREATE DATABASE `" . $params['dbName'] . "`"
                    ." DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;");
                $this->_db = Zend_Db::factory($params['serverType'], $dbParams);
            } catch (Exception $error) {
                $this->_error[] = 'Cannot connect to server at ' . $params['dbHost']
                    . ' using ' . $params['dbUser'] . ' user ' . '(' . $error->getMessage() . ')';
                $valid = false;
            }
        }

        if (!isset($params['adminPass']) || empty($params['adminPass'])) {
            $this->_error[] = 'The admin password cannot be empty';
            $valid = false;
        } else if ($params['adminPassConfirm'] != $params['adminPass']) {
            $this->_error[] = 'The admin password and confirmation are different';
            $valid = false;
        }

        // Check write access
        $baseDir    = str_replace('htdocs/setup.php', '', $_SERVER['SCRIPT_FILENAME']);
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
                $this->_error[] = 'The debug log cannot be created';
                $valid = false;
            }
            if (!@fopen($logsDir . DIRECTORY_SEPARATOR . 'err.log', 'a', false)) {
                $this->_error[] = 'The err log can not be created';
                $valid = false;
            }
        } else if (!is_writable($logsDir)) {
            $this->_error[] = 'Please set permission to allow writing logs in ' . $logsDir;
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
            $this->_error[] = 'Please set permission to allow writing uploaded files in ' . $uploadDir;
            $valid = false;
        }

        // Check tmp dir
        $cacheDir = $baseDir . "tmp";

        if (!file_exists($cacheDir)) {
            if (!mkdir($cacheDir)) {
                $this->_error[] = 'Please create the dir ' . $cacheDir . ' to use the cache';
                $valid = false;
            }
        } else if (!is_writable($cacheDir)) {
            $this->_error[] = 'Please set permission to allow use the cache in ' . $cacheDir;
            $valid = false;
        }

        // Check zendCache dir
        $cacheDir = $baseDir . "tmp" . DIRECTORY_SEPARATOR . "zendCache";

        if (!file_exists($cacheDir)) {
            if (!mkdir($cacheDir)) {
                $this->_error[] = 'Please create the dir ' . $cacheDir . ' to use the cache';
                $valid = false;
            }
        } else if (!is_writable($cacheDir)) {
            $this->_error[] = 'Please set permission to allow use the cache in ' . $cacheDir;
            $valid = false;
        } else if (is_dir($cacheDir)) {
            // Remove old data if exists
            if ($directory = opendir($cacheDir)) {
                while (($file = readdir($directory)) !== false) {
                    if ($file == '.' || $file == '..') {
                        continue;
                    }
                    unlink($cacheDir . DIRECTORY_SEPARATOR . $file);
                }
            }
        }

        // Migration
        if (!empty($params['migrationConfigFile'])) {
            if (!file_exists($params['migrationConfigFile'])) {
                $this->_error[] = 'The file "' . $params['migrationConfigFile'] . '" do not exists.';
                $valid = false;
            }
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
     * Return the messages created by _checkServer()
     *
     * @return array
     */
    public function getMessage()
    {
        $message        = $this->_message;
        $this->_message = array();

        return implode("\n", $message);
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

        $baseDir    = str_replace('htdocs/setup.php', '', $_SERVER['SCRIPT_FILENAME']);
        $configFile = $baseDir . "configuration.ini";
        file_put_contents($configFile, $content);

        // Delete a session if exists
        $_SESSION = array();
        foreach ($_COOKIE as $key => $value) {
            setcookie($key, "", 1);
        }
        Zend_Session::writeClose();
    }
}
