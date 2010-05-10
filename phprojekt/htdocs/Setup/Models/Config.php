<?php
/**
 * Configuration model class.
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
* Configuration model class.
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
class Setup_Models_Config
{
    /**
     * Windows string.
     */
    const OS_WINDOWS = 'Windows';

    /**
     * Unix string.
     */
    const OS_UNIX = 'Unix';

    /**
     * Curren OS.
     *
     * @var string
     */
    private $_os = null;

    /**
     * Current EOL depend on the OS.
     *
     * @var string
     */
    private $_eol = null;

    /**
     * Root path.
     *
     * @var string
     */
    private $_baseDir = null;

    /**
     * Private folder path.
     *
     * @var string
     */
    private $_privateDir = null;

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct()
    {
        $this->_setOs();
        $this->_setBaseDir();
        $this->_setPrivateDir();
    }

    /**
     * Return a default configuration.
     *
     * @param string $username Database username to set.
     * @param string $password Database password to set.
     * @param string $dbname   Database table name to set.
     * @param string $adapter  Database type to set.
     * @param string $host     Database host to set.
     *
     * @return string Output for save in the file.
     */
    public function getDefaultProduction($username, $password, $dbname, $adapter, $host)
    {
        $content = $this->_getIntroduction();
        $content .= $this->_eol . '[production]' . $this->_eol;
        $content .= $this->_getLanguage();
        $content .= $this->_getPaths();
        $content .= $this->_getDatabase($username, $password, $dbname, $adapter, $host);
        $content .= $this->_getLogs();
        $content .= $this->_getModules();
        $content .= $this->_getMail();
        $content .= $this->_getMisc();
        $content .= $this->_getFront();
        $content .= $this->_getFrontendMessage();

        return $content;
    }

    /**
     * Set the operating system.
     *
     * @return void
     */
    private function _setOs()
    {
        // Possible values for the PHP_OS constant include "AIX",
        // "Darwin" (MacOS), "Linux", "SunOS", "WIN32", and "WINNT".
        switch (PHP_OS) {
            case 'WIN32':
            case 'WINNT':
                $this->_os  = self::OS_WINDOWS;
                $this->_eol = "\r\n";
                break;
            default:
                $this->_os  = self::OS_UNIX;
                $this->_eol = "\n";
                break;
        }
    }

    /**
     * Set the current base dir.
     *
     * @return void
     */
    private function _setBaseDir()
    {
        $this->_baseDir = str_replace('htdocs/setup.php', '', $_SERVER['SCRIPT_FILENAME']);
    }

    /**
     * Set the private dir.
     *
     * @return void
     */
    private function _setPrivateDir()
    {
        $folderNamespace = new Zend_Session_Namespace('privateFolder');
        if (isset($folderNamespace->path)) {
            $this->_privateDir = $folderNamespace->path;
        } else {
            $this->_privateDir = $this->_baseDir;
        }
    }

    /**
     * Return the introduction text.
     *
     * @return string Output for save in the file.
     */
    private function _getIntroduction()
    {
        $content  = '; <?php die(); /* Do not remove this line */ ?>' . $this->_eol;
        $content .= $this->_eol;
        $content .= '; The semicolons \';\' are used preceding a comment line, or a line which has data' . $this->_eol;
        $content .= '; that is not being used.' . $this->_eol;
        $content .= $this->_eol;
        $content .= '; This file is divided into sections,' . $this->_eol;
        $content .= '; Each one of them corresponds to one' . $this->_eol;
        $content .= '; environment, it is used only one at a time, depending on what is speficied in' . $this->_eol;
        $content .= '; index.php, inside folder \'htdocs\' in the line that has:' . $this->_eol;
        $content .= '; define(\'PHPR_CONFIG_SECTION\', \'production\');' . $this->_eol;
        $content .= $this->_eol;
        $content .= '; You could leave that line as it is, and in configuration.php just modify the' . $this->_eol;
        $content .= '; parameters inside [production] section. You can also add your own sections.' . $this->_eol;

        return $content;
    }

    /**
     * Return the language text.
     *
     * @param string $language The language to set.
     *
     * @return string Output for save in the file.
     */
    private function _getLanguage($language = 'en')
    {
        $content  = $this->_eol;
        $content .= ';;;;;;;;;;;;' . $this->_eol;
        $content .= '; LANGUAGE ;' . $this->_eol;
        $content .= ';;;;;;;;;;;;' . $this->_eol;
        $content .= $this->_eol;

        $content .= '; Here it is specified the default language for the system, could be "de" for' . $this->_eol;
        $content .= '; German, "en" for English or "es" for Spanish. Actually, the language for each' . $this->_eol;
        $content .= '; user is specified individually from Administration -> User' . $this->_eol;
        $content .= 'language = "' . $language . '"' . $this->_eol;

        return $content;
    }

    /**
     * Return the paths text.
     *
     * @return string Output for save in the file.
     */
    private function _getPaths()
    {
        $content  = $this->_eol;
        $content .= ';;;;;;;;;' . $this->_eol;
        $content .= '; PATHS ;' . $this->_eol;
        $content .= ';;;;;;;;;' . $this->_eol;
        $content .= $this->_eol;

        $content .= '; Path where will be placed files uploaded by the user.' . $this->_eol;
        $content .= 'uploadPath = "' . $this->_privateDir . 'upload/"' . $this->_eol;

        $content .= $this->_eol;
        $content .= '; Path where will be placed temporaly files.' . $this->_eol;
        $content .= 'tmpPath = "' . $this->_privateDir . 'tmp/"' . $this->_eol;

        $content .= $this->_eol;
        $content .= '; Path where will be placed modules created by the admin.' . $this->_eol;
        $content .= 'applicationPath = "' . $this->_privateDir . 'application/"' . $this->_eol;

        return $content;
    }

    /**
     * Return the database text.
     *
     * @param string $username Database username to set.
     * @param string $password Database password to set.
     * @param string $dbname   Database table name to set.
     * @param string $adapter  Database type to set.
     * @param string $host     Database host to set.
     *
     * @return string Output for save in the file.
     */
    private function _getDatabase($username = '', $password = '', $dbname = '', $adapter = 'Pdo_Mysql',
        $host = 'localhost')
    {
        $content  = $this->_eol;
        $content .= ';;;;;;;;;;;;' . $this->_eol;
        $content .= '; DATABASE ;' . $this->_eol;
        $content .= ';;;;;;;;;;;;' . $this->_eol;
        $content .= $this->_eol;

        $content .= '; For this Developer Release, it just has been tested with pdo_mysql.' . $this->_eol;
        $content .= 'database.adapter = "' . $adapter . '"' . $this->_eol;
        $content .= $this->_eol;
        $content .= '; The assigned name or IP address for the database server.' . $this->_eol;
        $content .= 'database.params.host = "' . $host . '"' . $this->_eol;
        $content .= $this->_eol;
        $content .= '; Username and password with the appropriate rights for Phprojekt to access to' . $this->_eol;
        $content .= '; the database.' . $this->_eol;
        $content .= 'database.params.username = "' . $username . '"' . $this->_eol;
        $content .= 'database.params.password = "' . $password . '"' . $this->_eol;
        $content .= $this->_eol;
        $content .= '; Name of the database, inside the server' . $this->_eol;
        $content .= 'database.params.dbname = "' . $dbname . '"' . $this->_eol;

        return $content;
    }

    /**
     * Return the logs text.
     *
     * @return string Output for save in the file.
     */
    private function _getLogs()
    {
        $content  = $this->_eol;
        $content .= ';;;;;;;' . $this->_eol;
        $content .= '; LOG ;' . $this->_eol;
        $content .= ';;;;;;;' . $this->_eol;
        $content .= $this->_eol;

        $content .= '; Here will be logged things explicitly declared.' . $this->_eol;
        $content .= '; E.G.: (PHP) Phprojekt::getInstance()->getLog()->debug("String to be logged");' . $this->_eol;
        $content .= 'log.debug.filename = "' . $this->_privateDir . 'logs/debug.log"' . $this->_eol;
        $content .= $this->_eol;

        $content .= $this->_eol;
        $content .= '; This is another type of logging.' . $this->_eol;
        $content .= '; E.G.: (PHP) Phprojekt::getInstance()->getLog()->err("String to be logged");' . $this->_eol;
        $content .= '; Note for developers: there are many different type of logs defined that can be' . $this->_eol;
        $content .= '; added here, see the complete list in phprojekt/library/Phprojekt/Log.php' . $this->_eol;
        $content .= 'log.err.filename = "' . $this->_privateDir . 'logs/err.log"' . $this->_eol;

        return $content;
    }

    /**
     * Return the modules text.
     *
     * @return string Output for save in the file.
     */
    private function _getModules($userDisplayFormat = 0, $itemsPerPage = 3)
    {
        $content  = $this->_eol;
        $content .= ';;;;;;;;;;;' . $this->_eol;
        $content .= '; MODULES ;' . $this->_eol;
        $content .= ';;;;;;;;;;;' . $this->_eol;
        $content .= $this->_eol;

        $content .= '; Not used at the moment, leave it as it is.' . $this->_eol;
        $content .= 'itemsPerPage = ' . (int) $itemsPerPage . $this->_eol;
        $content .= $this->_eol;

        $content .= '; Users' . $this->_eol;
        $content .= '; How the users are displayed in the system' . $this->_eol;
        $content .= '; 0 = lastname, firstname' . $this->_eol;
        $content .= '; 1 = username, lastname, firstname' . $this->_eol;
        $content .= '; 2 = username' . $this->_eol;
        $content .= '; 3 = firstname, lastname' . $this->_eol;
        $content .= 'userDisplayFormat  = ' . (int) $userDisplayFormat . $this->_eol;

        $content .= $this->_eol;
        $content .= '; File containing words that should not be indexed in the search' . $this->_eol;
        $content .= 'searchStopwordList = ""' . $this->_eol;

        $content .= $this->_eol;
        $content .= '; Max size in bytes that is allowed to be uploaded per file.' . $this->_eol;
        $content .= '; 1 kb = 1024    bytes.' . $this->_eol;
        $content .= '; 1 Mb = 1048576 bytes.' . $this->_eol;
        $content .= 'maxUploadSize = ' . Phprojekt::DEFAULT_MAX_UPLOAD_SIZE . $this->_eol;

        return $content;
    }

    /**
     * Return the mail text.
     *
     * @return string Output for save in the file.
     */
    private function _getMail($endOfLine = 0, $server = 'localhost', $user = '', $password = '')
    {
        $content  = $this->_eol;
        $content .= ';;;;;;;;' . $this->_eol;
        $content .= '; MAIL ;' . $this->_eol;
        $content .= ';;;;;;;;' . $this->_eol;
        $content .= $this->_eol;
        $content .= '; Mail class is currently used by Notification class and Minutes module.' . $this->_eol;
        $content .= $this->_eol;
        $content .= '; 0 = Read SMTP parameters from here (smtpServer, smtpUser, smtpPassword, etc)' . $this->_eol;
        $content .= '; 1 = Read SMTP parameters from php.ini' . $this->_eol;
        $content .= 'mailTransport = 0;' . $this->_eol;
        $content .= $this->_eol;
        $content .= '; If mailTransport is set to 0, then fill all the needed \'smtp*\' values:' . $this->_eol;
        $content .= '; Name or IP address of the SMTP server to be used to send that notifications.' . $this->_eol;
        $content .= 'smtpServer = "' . $server . '"' . $this->_eol;
        $content .= '; If the SMTP server requires authentication, remove the semicolons \';\' in the' . $this->_eol;
        $content .= '; three following lines and write inside the inverted commas "" the appropriate' . $this->_eol;
        $content .= '; username and password. Auth mode: leave this as "login" if you don\'t know.' . $this->_eol;
        $content .= '; Other available options: plain, cram-md5' . $this->_eol;
        $content .= ';smtpAuth     = "login"' . $this->_eol;
        if (empty($user) && empty($password)) {
            $content .= ';smtpUser     = "' . $user . '"' . $this->_eol;
            $content .= ';smtpPassword = "' . $password . '"' . $this->_eol;
        } else {
            $content .= 'smtpUser     = "' . $user . '"' . $this->_eol;
            $content .= 'smtpPassword = "' . $password . '"' . $this->_eol;
        }
        $content .= '; You may specify SSL and Port, if the SMTP server of your choice requires them.' . $this->_eol;
        $content .= ';smtpSsl      = ""' . $this->_eol;
        $content .= ';smtpPort     = ""' . $this->_eol;
        $content .= $this->_eol;
        $content .= '; If the email is configured to be sent in Text mode, whether to use \r\n or \n' . $this->_eol;
        $content .= '; for the end of line.' . $this->_eol;
        $content .= '; (0 = \r\n  1 = \n)' . $this->_eol;
        $content .= 'mailEndOfLine = ' . (int) $endOfLine . $this->_eol;

        return $content;
    }

    /**
     * Return the misc text.
     *
     * @return string Output for save in the file.
     */
    private function _getMisc($compressedDojo = 'true', $useCacheForClasses = 'true')
    {
        $content  = $this->_eol;
        $content .= ';;;;;;;;' . $this->_eol;
        $content .= '; MISC ;' . $this->_eol;
        $content .= ';;;;;;;;' . $this->_eol;
        $content .= $this->_eol;

        $content .= '; Use compressed dojo to improve the speed of loading.' . $this->_eol;
        $content .= 'compressedDojo = ' . $compressedDojo . $this->_eol;
        $content .= $this->_eol;

        $content .= '; Use Zend_Registry for cache classes in the same request' . $this->_eol;
        $content .= 'useCacheForClasses = ' . $useCacheForClasses . $this->_eol;

        return $content;
    }

    /**
     * Return the front text.
     *
     * @return string Output for save in the file.
     */
    private function _getFront()
    {
        $content  = $this->_eol;
        $content .= ';;;;;;;;;' . $this->_eol;
        $content .= '; FRONT ;' . $this->_eol;
        $content .= ';;;;;;;;;' . $this->_eol;
        $content .= $this->_eol;
        $content .= '; Activate the mail notification by default' . $this->_eol;
        $content .= 'front.notificationEnabledByDefault = false' . $this->_eol;
        $content .= $this->_eol;
        $content .= '; Optional email support address to show inside error messages, general help and logo alt text'
            . $this->_eol;
        $content .= 'front.supportAddress = ""' . $this->_eol;
        $content .= $this->_eol;
        $content .= '; Show internal javascript errors for debug' . $this->_eol;
        $content .= 'front.showInternalJsErrors = false' . $this->_eol;

        return $content;
    }

    /**
     * Return the frontend message text.
     *
     * @return string Output for save in the file.
     */
    private function _getFrontendMessage()
    {
        $content  = $this->_eol;
        $content .= ';;;;;;;;;;;;;;;;;;;;;' . $this->_eol;
        $content .= '; FRONTEND MESSAGES ;' . $this->_eol;
        $content .= ';;;;;;;;;;;;;;;;;;;;;' . $this->_eol;
        $content .= $this->_eol;
        $content .= '; Show messages directely to the user if something will be changed on their data,' . $this->_eol;
        $content .= '; projects, modules, items, etc...' . $this->_eol;
        $content .= '; Options: true/ false' . $this->_eol;
        $content .= 'frontendMessages = true' . $this->_eol;
        $content .= $this->_eol;
        $content .= '; Set how long a frontend messgae is valid in minutes.' . $this->_eol;
        $content .= 'validPeriod = 2' . $this->_eol;
        $content .= $this->_eol;
        $content .= '; There is a notification to remind the user to a meeting she/he is invited in.' . $this->_eol;
        $content .= '; Define here the minutes where the notification should appear' . $this->_eol;
        $content .= '; before the meeting starts. This should be set in minutes!' . $this->_eol;
        $content .= 'remindBefore = 15' . $this->_eol;
        $content .= $this->_eol;
        $content .= '; Define the long polling time in seconds. Max. value is 20s!' . $this->_eol;
        $content .= 'pollingTime = 20' . $this->_eol;
        $content .= $this->_eol;
        $content .= '; Define the polling loops in seconds' . $this->_eol;
        $content .= '; This is the interval after a new poll will be done.' . $this->_eol;
        $content .= 'pollingLoop = 30' . $this->_eol;

        return $content;
    }
}
