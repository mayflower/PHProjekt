<?php
/**
 * Setup model class.
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
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
 * Setup model class.
 *
 * @category   PHProjekt
 * @package    Htdocs
 * @subpackage Setup
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Setup_Models_Setup
{
    /**
     * Default value for the last part of the private folder path.
     *
     * @see getProposedPrivateFolderPath
     */
    const PFOLDER_NAME = 'phprojekt_private';

    /**
     * Array with erros.
     *
     * @var array
     */
    private $_error = array();

    /**
     * Array with messages.
     *
     * @var array
     */
    private $_message = array();

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->_checkServer();
    }

    /**
     * Do some checks before install.
     *
     * @throws Expeption If the server don't have the requirements.
     *
     * @return void
     */
    private function _checkServer()
    {
        // Check the server
        $checkServer = Phprojekt::checkExtensionsAndSettings();

        // Check the PHP version
        if (!$checkServer['requirements']['php']['checked']) {
            $missingRequirements[] = "You need the PHP Version " . $checkServer['requirements']['php']['required']
                . " or newer. Follow this link for help: <a href=\"". $checkServer['requirements']['php']['help'] ."\""
                . " target=\"_new\">HELP</a>";
        }

        // Check required extension
        foreach ($checkServer['requirements']['extension'] as $name => $values) {
            if (!$values['checked']) {
                $missingRequirements[] = "The '" . $name . "' extension must be enabled. Follow this link for help: "
                    . "<a href=\"". $values['help'] ."\" target=\"_new\">HELP</a>";
            }
        }

        // Check required settings
        foreach ($checkServer['requirements']['settings'] as $name => $values) {
            if (!$values['checked']) {
                $missingRequirements[] = "The php.ini setting of '" . $name . "' has to be '"
                    . $values['required'] . "'. Follow this link for help: <a href=\"". $values['help'] . "\""
                    . " target=\"_new\">HELP</a>";
            }
        }

        // Checking if configuration.php exists
        $baseDir = str_replace('htdocs/setup.php', '', $_SERVER['SCRIPT_FILENAME']);
        if (file_exists($baseDir . "configuration.php")) {
            throw new Exception("Configuration file found. Please, delete it before run setup again.");
        }

        if (!empty($missingRequirements)) {
            $message = implode("\n", $missingRequirements);
            throw new Exception($message);
        }

        if (strncmp($_SERVER['SCRIPT_NAME'], '/setup.php', 10) != 0) {
            $this->_message[] = "It is recommend install PHProjekt 6 using a virtual host.<br />"
                . "You should try to generate an extra virtual host (or a sub-domain) to phprojekt/htdocs.";

            // Works the .htaccess?
            $response = new Zend_Controller_Request_Http();
            $webpath  = $response->getHttpHost();
            $str      = '';
            $sock     = fsockopen($webpath, $response->getServer('SERVER_PORT'));
            $request  = "GET " . str_replace('htdocs/setup.php', '', $response->getRequestUri()) . '/application/'
                . " HTTP/1.1\r\n" .  "Host: " . $webpath . "\r\nConnection: close\r\n\r\n";
            fwrite($sock, $request);
            while ($buff = fread($sock, 1024)) {
                $str .= $buff;
            }
            $response = Zend_Http_Response::fromString($str);
            if ($response->getStatus() != '403') {
                $this->_message[] = "Please note that your webserver needs to support .htaccess files "
                    . "to deny access to the configuration files.<br />"
                    . "Running PHProjekt 6 without using the provided .htaccess files to deny access to "
                    . "certain files and folders, might not be secure and is not recommended.";
            }
            fclose($sock);
        }

        foreach ($checkServer['recommendations']['settings'] as $name => $values) {
            if (!$values['checked']) {
                $this->_message[] = "It is recommend to have '" . $name . "' set to '" . $values['required']
                    . "', but it is not required to run PHProjekt. Follow this link for help: <a href=\""
                    . $values['help'] . "\" target=\"_new\">HELP</a>";
            }
        }
    }

    /**
     * Validate the params for the database.
     *
     * @param array $params Array with the POST values.
     *
     * @return boolean True for valid.
     */
    public function validateDatabase($params)
    {
        $valid = false;

        if (!isset($params['dbHost']) || empty($params['dbHost'])) {
            $this->_error[] = 'The database server address can not be empty';
        } else if (!isset($params['dbUser']) || empty($params['dbUser'])) {
            $this->_error[] = 'The database user can not be empty';
        } else if (!isset($params['dbName']) || empty($params['dbName'])) {
            $this->_error[] = 'The database name can not be empty';
        } else {
            ob_start();
            try {
                $dbParams = array(
                    'host'     => $params['dbHost'],
                    'username' => $params['dbUser'],
                    'password' => $params['dbPass'],
                    'dbname'   => $params['dbName']
                );
                $db = Zend_Db::factory($params['serverType'], $dbParams);
                $db->getConnection();
                $valid = true;
            } catch (Exception $error) {
                $this->_error[] = 'Cannot connect to server at ' . $params['dbHost']
                    . ' using ' . $params['dbUser'] . ' user ' . '(' . $error->getMessage() . ')';
            }
            ob_end_clean();
        }

        return $valid;
    }

    /**
     * Save the database params into the SESSION.
     *
     * @param array $params Array with the POST values.
     *
     * @return void
     */
    public function saveDatabase($params)
    {
        $this->_saveSession('databaseData', $params);
    }

    /**
     * Validate the params for the users.
     *
     * @param array $params Array with the POST values.
     *
     * @return boolean True for valid.
     */
    public function validateUsers($params)
    {
        $valid = true;

        // Admin pass
        if (!isset($params['adminPass']) || empty($params['adminPass'])) {
            $this->_error[] = 'The admin password cannot be empty';
            $valid = false;
        } else if ($params['adminPassConfirm'] != $params['adminPass']) {
            $this->_error[] = 'The admin password and confirmation are different';
            $valid = false;
        }

        // Test pass
        if (!isset($params['testPass']) || empty($params['testPass'])) {
            $this->_error[] = 'The test password cannot be empty';
            $valid = false;
        } else if ($params['testPassConfirm'] != $params['testPass']) {
            $this->_error[] = 'The test password and confirmation are different';
            $valid = false;
        }

        // Test pass
        if ($params['adminPass'] == $params['testPass']) {
            $this->_error[] = 'The password for the users "admin" and "test" should be different';
            $valid = false;
        }

        return $valid;
    }

    /**
     * Save the users params into the SESSION.
     *
     * @param array $params Array with the POST values.
     *
     * @return void
     */
    public function saveUsers($params)
    {
        $this->_saveSession('usersData', $params);
    }

    /**
     * Validate the private folder.
     *
     * @param array $params Array with the POST values.
     *
     * @return boolean True for valid.
     */
    public function validatePrivateFolder($params)
    {
        $valid = 1;
        $privateDir = self::slashify($params['privateDir']);

        // Check private folder path
        if (!isset($privateDir) || empty($privateDir)) {
            $this->_error[] = 'The path cannot be empty';
            $valid = 0;
        }

        // Check private directory
        $privateBaseDir = dirname($privateDir) . DIRECTORY_SEPARATOR;
        if ($valid && !$this->_checkWriteAccess($privateBaseDir, basename($privateDir))) {
            $valid = 0;
        }

        if (!$params['confirmationCheck']) {
            $privateDirCheck = str_replace("\\", "/", realpath($privateDir));
            $documentRoot    = str_replace("\\", "/", $_SERVER['DOCUMENT_ROOT']);
            if (substr($privateDirCheck, 0, strlen($documentRoot)) == $documentRoot) {
                $this->_error[] = 'Use a folder inside your document root can be dangerous, do you really want to use '
                    . 'this folder?';
                $valid = 2;
            }
        }

        if ($valid == 1) {
            $folderNamespace       = new Zend_Session_Namespace('privateFolder');
            $folderNamespace->path = $privateDir;
        }

        return $valid;
    }

    /**
     * Try to create the private folder and sub-folders.
     *
     * @param array $params Array with the POST values.
     *
     * @return boolean True for valid.
     */
    public function writeFolders($params)
    {
        $valid      = true;
        $privateDir = self::slashify($params['privateDir']);

        // Check write access
        $baseDir    = str_replace('htdocs/setup.php', '', $_SERVER['SCRIPT_FILENAME']);
        $configFile = $baseDir . "configuration.php";

        if (!file_exists($configFile)) {
            if (!is_writable($baseDir)) {
                $this->_error[] = 'Error creating the configuration file at '. $configFile
                    . ': Do not have write access.';
                $valid = false;
            } else {
                if (!file_put_contents($configFile, "Test")) {
                    $this->_error[] = 'Error creating the configuration file at '. $configFile;
                    $valid = false;
                } else {
                    unlink($configFile);
                }
            }
        }

        // Write the .htaccess file
        $htaccess = @fopen($privateDir . '.htaccess', 'w+', false);
        if (false === $htaccess) {
            $this->_error[] = 'The .htaccess file cannot be created';
            $valid = false;
        } else {
            fwrite($htaccess, "# deny all access\ndeny from all\n");
            fclose($htaccess);
        }

        // Check log files
        if (!$this->_checkWriteAccess($privateDir, 'logs')) {
            $valid = false;
        } else {
            if (!@fopen($privateDir . 'logs' . DIRECTORY_SEPARATOR . 'debug.log', 'a', false)) {
                $this->_error[] = 'The debug log cannot be created';
                $valid = false;
            }
            if (!@fopen($privateDir . 'logs' . DIRECTORY_SEPARATOR . 'err.log', 'a', false)) {
                $this->_error[] = 'The err log can not be created';
                $valid = false;
            }
        }

        // Check application dir
        if (!$this->_checkWriteAccess($privateDir, 'application')) {
            $valid = false;
        }

        // Check upload dir
        if (!$this->_checkWriteAccess($privateDir, 'upload')) {
            $valid = false;
        }

        // Check tmp dir
        if (!$this->_checkWriteAccess($privateDir, 'tmp')) {
            $valid = false;
        }

        // Check zendCache dir
        if (!$this->_checkWriteAccess($privateDir . 'tmp' . DIRECTORY_SEPARATOR, 'zendCache')) {
            $valid = false;
        } else {
            // Remove old data if exists
            $cacheDir = $privateDir . 'tmp' . DIRECTORY_SEPARATOR . 'zendCache';
            if ($directory = opendir($cacheDir)) {
                while (($file = readdir($directory)) !== false) {
                    if ($file == '.' || $file == '..') {
                        continue;
                    }
                    unlink($cacheDir . DIRECTORY_SEPARATOR . $file);
                }
            }
        }

        // Check old installations

        // Upload dir
        $this->_moveAndRemoveDirectory($privateDir, $baseDir, "upload");

        // Logs dir
        $this->_moveAndRemoveDirectory($privateDir, $baseDir, "logs");

        // Temporals dir
        $this->_moveAndRemoveDirectory($privateDir, $baseDir, "tmp", false);

        // Set access
        if (PHP_OS == 'WIN32' || PHP_OS == 'WINNT') {
            $this->_error[] = '"' . $privateDir . '" should have the next rights: 0700 for folders, 0600 for files';
        } else {
            // Private folder
            if (!$this->chmodRecursive($privateDir, 0700, 0600)) {
                $this->_error[] = '"' . $privateDir . '" should have the next rights: 0700 for folders, 0600 for files';
            }
        }

        // Configuration.ini and ini-dist files
        if (file_exists($baseDir . "configuration.ini")) {
            unlink($baseDir . "configuration.ini");
        }
        if (file_exists($baseDir . "configuration.ini-dist")) {
            unlink($baseDir . "configuration.ini-dist");
        }

        return $valid;
    }

    /**
     * Check if the folder exists and have write access.
     *
     * If not, try to create it.
     *
     * @param string $baseDir Path to the folder.
     * @param string $folder  Folder name.
     *
     * @return boolean True if the folder is writable.
     */
    private function _checkWriteAccess($baseDir, $folder)
    {
        $valid = false;
        $path  = $baseDir . $folder;

        if (!is_dir($path)) {
            if (!is_writable($baseDir)) {
                $this->_error[] = 'Error creating the "' . $folder . '" folder: Do not have write access.';
            } else if (!mkdir($path)) {
                $this->_error[] = 'Please create the dir ' . $path . '.';
            } else {
                $valid = true;
            }
        } else if (!is_writable($path)) {
            $this->_error[] = 'Please set write permission to ' . $path . '.';
        } else {
            $valid = true;
        }

        return $valid;
    }

    /**
     * Delete or move files from old installations.
     *
     * @param string  $privateDir Path to the private folder (New path).
     * @param string  $baseDir    Path to the document root (Old path).
     * @param string  $name       Name of the folder.
     * @param boolean $move       Move the file or not.
     *
     * @return void
     */
    private function _moveAndRemoveDirectory($privateDir, $baseDir, $name, $move = true)
    {
        $path = $baseDir . $name;
        if (is_dir($path)) {
            // Remove all
            if ($directory = opendir($path)) {
                while (($file = readdir($directory)) !== false) {
                    if ($file == '.' || $file == '..') {
                        continue;
                    }
                    $subPath = $path . DIRECTORY_SEPARATOR . $file;
                    if (is_dir($subPath)) {
                        $this->_moveAndRemoveDirectory($privateDir, $path . DIRECTORY_SEPARATOR, $file, $move);
                    } else {
                        if ($move) {
                            copy($path . DIRECTORY_SEPARATOR . $file,
                                $privateDir . DIRECTORY_SEPARATOR . $name . DIRECTORY_SEPARATOR . $file);
                        }
                        unlink($path . DIRECTORY_SEPARATOR . $file);
                    }
                }
                closedir($directory);
            }
            rmdir($path);
         }
    }

    /**
     * Install all the tables and return the messages generated.
     *
     * @param array $params Array with options for the install.
     *
     * @return array Array with messages of what was installed.
     */
    public function install($params)
    {
        $options                 = array();
        $options['useExtraData'] = (boolean) $params['useExtraData'];
        $db                      = $this->_getDb();

        // Install tables
        $dbParser           = new Phprojekt_DbParser($options, $db);
        $dbParser->parseData(PHPR_ROOT_PATH . DIRECTORY_SEPARATOR . 'application');

        // Update users passwords
        $usersNamespace = new Zend_Session_Namespace('usersData');

        // Update admin Pass
        $db->update('setting', array('value' => md5('phprojektmd5' . $usersNamespace->data['adminPass'])),
            'id = 1');

        // Update test Pass
        $db->update('setting', array('value' => md5('phprojektmd5' . $usersNamespace->data['testPass'])),
            'user_id = 2 AND key_value = \'password\'');

        return $dbParser->getMessages();
    }

    /**
     * Validate the params for the migration.
     *
     * @param array $params Array with the POST values.
     *
     * @return boolean True for valid.
     */
    public function validateMigration($params)
    {
        $valid = true;

        if (!empty($params['migrationConfigFile'])) {
            if (!file_exists($params['migrationConfigFile'])) {
                $this->_error[] = 'The file "' . $params['migrationConfigFile'] . '" do not exists.';
                $valid = false;
            } else if (!strstr($params['migrationConfigFile'], 'config.inc.php')) {
                $this->_error[] = 'The file "' . $params['migrationConfigFile'] . '" do not exists.';
                $valid = false;
            }
        } else {
            $this->_error[] = 'You must provide the path to the config.inc.php file of your old PHProjekt 5.x.';
            $valid = false;
        }

        return $valid;
    }

    /**
     * Migrate old versions to the new one.
     *
     * @param array $params Array with options for the migration.
     *
     * @return array Array with messages of what was migrated.
     */
    public function migrate($params)
    {
        if (file_exists($params['migrationConfigFile'])) {
            try {
                $migration = new Setup_Models_Migration($params['migrationConfigFile'], $params['diffToUtc'],
                    $this->_getDb());
                $migration->{'migrate' . $params['module']}();
            } catch (Exception $error) {
                echo $error->getMessage();
            }
        }
    }

    /**
     * Complete the installation writing the config file.
     *
     * @return void
     */
    public function finish()
    {
        // Create config file
        $databaseNamespace = new Zend_Session_Namespace('databaseData');
        $config            = new Setup_Models_Config();
        $content           = $config->getDefaultProduction($databaseNamespace->data['dbUser'],
            $databaseNamespace->data['dbPass'], $databaseNamespace->data['dbName'], 'Pdo_Mysql',
            $databaseNamespace->data['dbHost']);

        $baseDir    = str_replace('htdocs/setup.php', '', $_SERVER['SCRIPT_FILENAME']);
        $configFile = $baseDir . "configuration.php";
        file_put_contents($configFile, $content);

        // Set access
        $baseDir = str_replace('htdocs/setup.php', '', $_SERVER['SCRIPT_FILENAME']);
        if (PHP_OS == 'WIN32' || PHP_OS == 'WINNT') {
            $this->_error[] = '"' . $baseDir . '" should have the next rights: 0750 for folders, 0640 for files';
        } else {
            // Root
            if (!$this->chmodRecursive($baseDir, 0750, 0640)) {
                $this->_error[] = '"' . $baseDir . '" should have the next rights: 0750 for folders, 0640 for files';
            }
        }

        // Delete a session if exists
        $_SESSION = array();
        foreach ($_COOKIE as $key => $value) {
            setcookie($key, "", 1);
        }
        Zend_Session::writeClose();
    }

    /**
     * Return the errors created by validate().
     *
     * @return array Array with errors.
     */
    public function getError()
    {
        $error        = $this->_error;
        $this->_error = array();

        return $error;
    }

    /**
     * Return the messages created by _checkServer().
     *
     * @return array Array with messages.
     */
    public function getMessage()
    {
        $message        = $this->_message;
        $this->_message = array();

        return $message;
    }

    /**
     * Return the database connection.
     *
     * @return Zend_Db The database conection.
     */
    private function _getDb()
    {
        $databaseNamespace = new Zend_Session_Namespace('databaseData');

        $dbParams = array(
                    'host'     => $databaseNamespace->data['dbHost'],
                    'username' => $databaseNamespace->data['dbUser'],
                    'password' => $databaseNamespace->data['dbPass'],
                    'dbname'   => $databaseNamespace->data['dbName']);

        return Zend_Db::factory($databaseNamespace->data['serverType'], $dbParams);
    }

    /**
     * Save a value into the session
     *
     * @param string $name  Namespace for the session.
     * @param mix    $value Mix value to save.
     *
     * @return void
     */
    private function _saveSession($name, $value)
    {
        $namespace       = new Zend_Session_Namespace($name);
        $namespace->data = $value;
    }

    /**
     * Proposes a path for the private folder.
     *
     * We propose that the path for the private folder where we store
     * private data is outside of the document root that we use.
     * In most cases this is a secure location.
     * Nevertheless we cannot force the user to use it.
     *
     * @param string $folderName Name of the last path of the folder path.
     *
     * @return string The proposed folder path.
     */
    public static function getProposedPrivateFolderPath($folderName = self::PFOLDER_NAME)
    {
        return dirname($_SERVER['DOCUMENT_ROOT']) . DIRECTORY_SEPARATOR . $folderName . DIRECTORY_SEPARATOR;
    }

    /**
     * Make sure path ends in a slash.
     *
     * @param string $path Directory path.
     *
     * @return string Directory path wiht trailing slash.
     */
    public static function slashify($path)
    {
        if ($path[strlen($path) - 1] != '/' && $path[strlen($path) - 1] != "\\") {
            $path = $path . '/';
        }

        return $path;
    }

    /**
     * Make a recursive chmod.
     *
     * @param string $path     Path for the file/directory.
     * @param string $dirMode  Directory mode for apply.
     * @param string $fileMode File mode for apply.
     *
     * @return boolean True on success.
     */
    private function chmodRecursive($path, $dirMode, $fileMode)
    {
        if (!is_dir($path)) {
            return @chmod($path, (int) $fileMode);
        }

        $dir = opendir($path);
        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                $fullPath = $path . '/' . $file;
                $this->chmodRecursive($fullPath, $dirMode, $fileMode);

            }
        }
        closedir($dir);

        return @chmod($path, (int) $dirMode);
    }
}
