<?php
/**
 * Configuration
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
 * Configuration
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @package    Setup
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    Release: @package_version@
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Setup_Models_Config
{
    const OS_WINDOWS = 'Windows';
    const OS_UNIX    = 'Unix';

    private $_os      = null;
    private $_EOL     = null;
    private $_baseDir = null;

    public function __construct()
    {
        $this->_setOs();
        $this->_setBaseDir();
    }

    /**
     * Return a default configuration
     *
     * @param string $username Database username to set
     * @param string $password Database password to set
     * @param string $dbname   Database table name to set
     * @param string $adapter  Database type to set
     * @param string $host     Database host to set
     *
     * @return string
     */
    public function getDefaultProduction($username, $password, $dbname, $adapter, $host)
    {
        $content = $this->_getIntroduction();
        $content .= $this->_EOL . '[production]' . $this->_EOL;
        $content .= $this->_getLanguage();
        $content .= $this->_getPaths();
        $content .= $this->_getDatabase($username, $password, $dbname, $adapter, $host);
        $content .= $this->_getLogs();
        $content .= $this->_getModules();
        $content .= $this->_getMail();
        $content .= $this->_getMisc();

        return $content;
    }

    /**
     * Set the operating system
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
                $this->_EOL = "\r\n";
                break;
            default:
                $this->_os = self::OS_UNIX;
                $this->_EOL = $this->_EOL;
                break;
        }
    }

    /**
     * Set the current base dir
     *
     * @return void
     */
    private function _setBaseDir()
    {
        $this->_baseDir = ereg_replace('htdocs/setup.php', '', $_SERVER['SCRIPT_FILENAME']);
        if ($this->_isWindows()) {
            $this->_baseDir = ereg_replace('/', '\\', $this->_baseDir);
        }
    }

    /**
     * Return if the system is Windows
     *
     * @return boolean
     */
    private function _isWindows()
    {
        return ($this->_os == self::OS_WINDOWS);
    }

    /**
     * Return the introduction text
     *
     * @return string
     */
    private function _getIntroduction()
    {
        $content  = '; The semicolons \';\' are used preceding a comment line, or a line which has data' . $this->_EOL;
        $content .= '; that is not being used.' . $this->_EOL;
        $content .= $this->_EOL;
        $content .= '; This file is divided into sections,' . $this->_EOL;
        $content .= '; Each one of them corresponds to one' . $this->_EOL;
        $content .= '; environment, it is used only one at a time, depending on what is speficied in' . $this->_EOL;
        $content .= '; index.php, inside folder \'htdocs\' in the line that has:' . $this->_EOL;
        $content .= '; define(\'PHPR_CONFIG_SECTION\', \'production\');' . $this->_EOL;
        $content .= $this->_EOL;
        $content .= '; You could leave that line as it is, and in configuration.ini just modify the' . $this->_EOL;
        $content .= '; parameters inside [production] section. You can also add your own sections.' . $this->_EOL;

        return $content;
    }

    /**
     * Return the language text
     *
     * @param string $language The language to set
     *
     * @return string
     */
    private function _getLanguage($language = 'en')
    {
        $content  = $this->_EOL;
        $content .= ';;;;;;;;;;;;' . $this->_EOL;
        $content .= '; LANGUAGE ;' . $this->_EOL;
        $content .= ';;;;;;;;;;;;' . $this->_EOL;
        $content .= $this->_EOL;

        $content .= '; Here it is specified the default language for the system, could be "de" for' . $this->_EOL;
        $content .= '; German, "en" for English or "es" for Spanish. Actually, the language for each' . $this->_EOL;
        $content .= '; user is specified individually from Administration -> User' . $this->_EOL;
        $content .= 'language = "' . $language . '"' . $this->_EOL;

        return $content;
    }

    /**
     * Return the paths text
     *
     * @return string
     */
    private function _getPaths()
    {
        $content  = $this->_EOL;
        $content .= ';;;;;;;;;' . $this->_EOL;
        $content .= '; PATHS ;' . $this->_EOL;
        $content .= ';;;;;;;;;' . $this->_EOL;
        $content .= $this->_EOL;

        $content .= '; Where the site and the main file (index.php) are located (htdocs folder).' . $this->_EOL;
        $webPath = "http://" . $_SERVER['HTTP_HOST'] . ereg_replace('setup.php', '', $_SERVER['SCRIPT_NAME']);
        $content .= 'webpath = "' . $webPath . '"' . $this->_EOL;
        $content .= $this->_EOL;

        $content .= '; Path where will be placed files uploaded by the user.' . $this->_EOL;
        $content .= 'uploadpath = "' . $this->_baseDir . 'upload\"' . $this->_EOL;

        return $content;
    }

    /**
     * Return the database text
     *
     * @param string $username Database username to set
     * @param string $password Database password to set
     * @param string $dbname   Database table name to set
     * @param string $adapter  Database type to set
     * @param string $host     Database host to set
     *
     * @return string
     */
    private function _getDatabase($username = '', $password = '', $dbname = '', $adapter = 'Pdo_Mysql',
        $host = 'localhost')
    {
        $content  = $this->_EOL;
        $content .= ';;;;;;;;;;;;' . $this->_EOL;
        $content .= '; DATABASE ;' . $this->_EOL;
        $content .= ';;;;;;;;;;;;' . $this->_EOL;
        $content .= $this->_EOL;

        $content .= '; For this Developer Release, it just has been tested with pdo_mysql.' . $this->_EOL;
        $content .= 'database.adapter = "' . $adapter . '"' . $this->_EOL;
        $content .= $this->_EOL;
        $content .= '; The assigned name or IP address for the database server.' . $this->_EOL;
        $content .= 'database.params.host = "' . $host . '"' . $this->_EOL;
        $content .= $this->_EOL;
        $content .= '; Username and password with the appropriate rights for Phprojekt to access to' . $this->_EOL;
        $content .= '; the database.' . $this->_EOL;
        $content .= 'database.params.username = "' . $username . '"' . $this->_EOL;
        $content .= 'database.params.password = "' . $password . '"' . $this->_EOL;
        $content .= $this->_EOL;
        $content .= '; Name of the database, inside the server' . $this->_EOL;
        $content .= 'database.params.dbname = "' . $dbname . '"' . $this->_EOL;

        return $content;
    }

    /**
     * Return the logs text
     *
     * @return string
     */
    private function _getLogs()
    {
        $content  = $this->_EOL;
        $content .= ';;;;;;;' . $this->_EOL;
        $content .= '; LOG ;' . $this->_EOL;
        $content .= ';;;;;;;' . $this->_EOL;
        $content .= $this->_EOL;

        $content .= '; Here will be logged things explicitly declared.' . $this->_EOL;
        $content .= '; E.G.: (PHP) Phprojekt::getInstance()->getLog()->debug("String to be logged");' . $this->_EOL;
        $content .= 'log.debug.filename = "' . $this->_baseDir . 'logs\debug.log"' . $this->_EOL;
        $content .= $this->_EOL;

        $content .= $this->_EOL;
        $content .= '; This is another type of logging.' . $this->_EOL;
        $content .= '; E.G.: (PHP) Phprojekt::getInstance()->getLog()->crit("String to be logged");' . $this->_EOL;
        $content .= '; Note for developers: there are many different type of logs defined that can be' . $this->_EOL;
        $content .= '; added here, see the complete list in phprojekt\library\Phprojekt\Log.php' . $this->_EOL;
        $content .= 'log.crit.filename = "' . $this->_baseDir . 'logs\crit.log"' . $this->_EOL;

        return $content;
    }

    /**
     * Return the modules text
     *
     * @return string
     */
    private function _getModules($userDisplayFormat = 0, $itemsPerPage = 3)
    {
        $content  = $this->_EOL;
        $content .= ';;;;;;;;;;;' . $this->_EOL;
        $content .= '; MODULES ;' . $this->_EOL;
        $content .= ';;;;;;;;;;;' . $this->_EOL;
        $content .= $this->_EOL;

        $content .= '; Not used at the moment, leave it as it is.' . $this->_EOL;
        $content .= 'itemsPerPage = ' . (int) $itemsPerPage . $this->_EOL;
        $content .= $this->_EOL;

        $content .= '; Users' . $this->_EOL;
        $content .= '; How the users are displayed in the system' . $this->_EOL;
        $content .= '; (0 = lastname, firstname  1 = username, lastname, firstname, 2 = username)' . $this->_EOL;
        $content .= 'userDisplayFormat  = ' . (int) $userDisplayFormat . $this->_EOL;

        return $content;
    }

    /**
     * Return the mail text
     *
     * @return string
     */
    private function _getMail($endOfLine = 0, $server = 'localhost', $user = '', $password = '')
    {
        $content  = $this->_EOL;
        $content .= ';;;;;;;;;;;;;;;;;;;;;' . $this->_EOL;
        $content .= '; MAIL NOTIFICATION ;' . $this->_EOL;
        $content .= ';;;;;;;;;;;;;;;;;;;;;' . $this->_EOL;
        $content .= $this->_EOL;

        $content .= '; Inside many modules, when adding or editing an item, there is a tab' . $this->_EOL;
        $content .= '; "Notification" that allows the user to send an email notification to the' . $this->_EOL;
        $content .= '; people involved in that item, telling them about the creation or modification' . $this->_EOL;
        $content .= '; of it.' . $this->_EOL;
        $content .= $this->_EOL;
        $content .= '; If the email is configured to be sent in Text mode, whether to use \r\n or \n' . $this->_EOL;
        $content .= '; for the end of line.' . $this->_EOL;
        $content .= '; (0 = \r\n  1 = \n)' . $this->_EOL;
        $content .= 'mailEndOfLine = ' . (int) $endOfLine . $this->_EOL;
        $content .= $this->_EOL;

        $content .= '; Name or IP address of the SMTP server to be used to send that notifications.' . $this->_EOL;
        $content .= 'smtpServer = "' . $server . '"' . $this->_EOL;
        $content .= $this->_EOL;
        $content .= '; If the SMTP server requires authentication, remove the semicolons \';\' and' . $this->_EOL;
        $content .= '; write inside the inverted commas "" the appropriate username and password.' . $this->_EOL;

        if (empty($user) && empty($password)) {
            $content .= ';smtpUser     = "' . $user . '"' . $this->_EOL;
            $content .= ';smtpPassword = "' . $password . '"' . $this->_EOL;
        } else {
            $content .= 'smtpUser     = "' . $user . '"' . $this->_EOL;
            $content .= 'smtpPassword = "' . $password . '"' . $this->_EOL;
        }

        return $content;
    }

    /**
     * Return the misc text
     *
     * @return string
     */
    private function _getMisc($compressedDojo = 'true', $useCacheForClasses = 'true')
    {
        $content  = $this->_EOL;
        $content .= ';;;;;;;;' . $this->_EOL;
        $content .= '; MISC ;' . $this->_EOL;
        $content .= ';;;;;;;;' . $this->_EOL;
        $content .= $this->_EOL;

        $content .= '; Use compressed dojo to improve the speed of loading.' . $this->_EOL;
        $content .= 'compressedDojo = ' . $compressedDojo . $this->_EOL;
        $content .= $this->_EOL;

        $content .= '; Use Zend_Registry for cache classes in the same request' . $this->_EOL;
        $content .= 'useCacheForClasses = ' . $useCacheForClasses . $this->_EOL;

        return $content;
    }
}
