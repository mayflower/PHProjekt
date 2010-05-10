<?php
/**
 * Default Controller for the Setup.
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
 * Default Controller for the Setup.
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
class IndexController extends Zend_Controller_Action
{
    /**
     * Internal var for keep the Setup model.
     *
     * @var Setup_Models_Setup
     */
    private $_setup = null;

    /**
     * Default values
     */
    const DEFAULT_DBHOST         = 'localhost';
    const DEFAULT_DBUSER         = 'phprojekt';
    const DEFAULT_DBNAME         = 'phprojekt';
    const DEFAULT_USE_EXTRA_DATA = 1;
    const DEFAULT_DIFF_TO_UTC    = 0;

    /**
     * Do some checks in the begin.
     *
     * @return void
     */
    public function init()
    {
        $this->_helper->viewRenderer->setNoRender();
        $this->view->clearVars();

        $front    = Zend_Controller_Front::getInstance();
        $response = $front->getRequest();
        $webPath  = $response->getScheme() . '://' . $response->getHttpHost() . $response->getBasePath() . '/';

        $this->view->webPath = $webPath;
        $this->view->message = array();
        $this->view->success = array();
        $this->view->error   = array();

        $this->view->exportModules = Setup_Models_Migration::getModulesToMigrate();

        $this->_helper->viewRenderer->setNoRender();

        try {
            $this->_setup = new Setup_Models_Setup();
            $message      = $this->_setup->getMessage();
            if (!empty($message)) {
                $this->view->message = $message;
            } else {
                $this->view->success = "Server OK";
            }
        } catch (Exception $error) {
            $this->view->error = explode("\n", $error->getMessage());
        }
    }

    /**
     * Default action.
     *
     * @return void
     */
    public function indexAction()
    {
        $this->view->template = $this->view->render('server.phtml');
        $this->render('index');
    }

    /**
     * Returns the database form.
     *
     * The return have:
     * <pre>
     * - type     => The type of the message (error or success).
     * - message  => The message.
     * - template => The template to show.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonDatabaseFormAction()
    {
        $this->view->message = array();
        $this->view->success = array();
        $this->view->dbHost  = self::DEFAULT_DBHOST;
        $this->view->dbUser  = self::DEFAULT_DBUSER;
        $this->view->dbPass  = '';
        $this->view->dbName  = self::DEFAULT_DBNAME;

        $message  = null;
        $type     = 'success';
        $template = $this->view->render('databaseForm.phtml');

        $this->returnContent($type, $message, $template);
    }

    /**
     * Validate the params and if is all ok, save them.
     *
     * REQUIRES request parameters:
     * <pre>
     *  - string <b>serverType</b> Type of database connector.
     *  - string <b>dbHost</b> Database host.
     *  - string <b>dbUser</b> Database username.
     *  - string <b>dbPass</b> Database password.
     *  - string <b>dbName</b> Database name.
     * </pre>
     *
     * The return have:
     * <pre>
     * - type     => The type of the message (error or success).
     * - message  => The message.
     * - template => The template to show.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonDatabaseSetupAction()
    {
        $this->view->message = array();
        $this->view->success = array();

        $params = array(
            'serverType' => Cleaner::sanitize('string', $this->getRequest()->getParam('serverType')),
            'dbHost'     => Cleaner::sanitize('string', $this->getRequest()->getParam('dbHost')),
            'dbUser'     => Cleaner::sanitize('string', $this->getRequest()->getParam('dbUser')),
            'dbPass'     => Cleaner::sanitize('string', $this->getRequest()->getParam('dbPass')),
            'dbName'     => Cleaner::sanitize('string', $this->getRequest()->getParam('dbName')));

        if (null !== $this->_setup) {
            if ($this->_setup->validateDatabase($params)) {
                $this->_setup->saveDatabase($params);
                $message = 'Database OK';
                $type    = 'success';
            } else {
                $error   = $this->_setup->getError();
                $message = array_shift($error);
                $type    = 'error';
            }
        } else {
            $this->getResponse()->setHttpResponseCode(403);
            $this->sendResponse();
        }

        $template = $this->view->render('databaseOk.phtml');

        $this->returnContent($type, $message, $template);
    }

    /**
     * Returns the users form.
     *
     * The return have:
     * <pre>
     * - type     => The type of the message (error or success).
     * - message  => The message.
     * - template => The template to show.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonUsersFormAction()
    {
        $this->view->message          = array();
        $this->view->success          = array();
        $this->view->adminPass        = '';
        $this->view->adminPassConfirm = '';
        $this->view->testPass         = '';
        $this->view->testPassConfirm  = '';

        $message  = null;
        $type     = 'success';
        $template = $this->view->render('usersForm.phtml');

        $this->returnContent($type, $message, $template);
    }

    /**
     * Validate the params and if is all ok, save them.
     *
     * REQUIRES request parameters:
     * <pre>
     *  - string <b>adminPass</b> Admin password.
     *  - string <b>adminPassConfirm</b> Admin password confirmation.
     *  - string <b>testPass</b> Test password.
     *  - string <b>testPassConfirm</b> Test password confirmation.
     * </pre>
     *
     * The return have:
     * <pre>
     * - type     => The type of the message (error or success).
     * - message  => The message.
     * - template => The template to show.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonUsersSetupAction()
    {
        $this->view->message = array();
        $this->view->success = array();

        $params = array(
            'adminPass'        => Cleaner::sanitize('string', $this->getRequest()->getParam('adminPass')),
            'adminPassConfirm' => Cleaner::sanitize('string', $this->getRequest()->getParam('adminPassConfirm')),
            'testPass'         => Cleaner::sanitize('string', $this->getRequest()->getParam('testPass')),
            'testPassConfirm'  => Cleaner::sanitize('string', $this->getRequest()->getParam('testPassConfirm')));

        if (null !== $this->_setup) {
            if ($this->_setup->validateUsers($params)) {
                $this->_setup->saveUsers($params);
                $message = 'Users OK';
                $type    = 'success';
            } else {
                $error   = $this->_setup->getError();
                $message = array_shift($error);
                $type    = 'error';
            }
        } else {
            $this->getResponse()->setHttpResponseCode(403);
            $this->sendResponse();
        }

        $template = $this->view->render('usersOk.phtml');

        $this->returnContent($type, $message, $template);
    }

    /**
     * Returns the folder form.
     *
     * The return have:
     * <pre>
     * - type     => The type of the message (error or success).
     * - message  => The message.
     * - template => The template to show.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonFoldersFormAction()
    {
        $this->view->message    = array();
        $this->view->success    = array();
        $this->view->privateDir = $this->_setup->getProposedPrivateFolderPath();

        $message  = null;
        $type     = 'success';
        $template = $this->view->render('foldersForm.phtml');

        $this->returnContent($type, $message, $template);
    }

    /**
     * Validate the params and if is all ok, save them.
     *
     * REQUIRES request parameters:
     * <pre>
     *  - string <b>privateDir</b> Path of the private folder.
     * </pre>
     *
     * The return have:
     * <pre>
     * - type     => The type of the message (error or success).
     * - message  => The message.
     * - template => The template to show.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonFoldersSetupAction()
    {
        $this->view->message = array();
        $this->view->success = array();

        $params = array(
            'privateDir'        => (string) $this->getRequest()->getParam('privateDir'),
            'confirmationCheck' => (int) $this->getRequest()->getParam('confirmationCheck')
        );

        if (null !== $this->_setup) {
            $return = $this->_setup->validatePrivateFolder($params);
            if ($return == 0) {
                // Error
                $error   = $this->_setup->getError();
                $message = array_shift($error);
                $type    = 'error';
            } else {
                if ($return == 2 && !$params['confirmationCheck']) {
                    // Confirmation needed
                    $error   = $this->_setup->getError();
                    $message = array_shift($error);
                    $type    = 'confirm';
                } else {
                    // OK
                    if ($this->_setup->writeFolders($params)) {
                        $error = $this->_setup->getError();
                        if (empty($error)) {
                            $message = 'Private folders OK';
                            $type    = 'success';
                        } else {
                            $message = $error;
  	                        $type    = 'notice';
                        }
                    } else {
                        $error   = $this->_setup->getError();
                        $message = array_shift($error);
                        $type    = 'error';
                    }
                }
            }
        } else {
            $this->getResponse()->setHttpResponseCode(403);
            $this->sendResponse();
        }

        $template = $this->view->render('foldersOk.phtml');
        $this->returnContent($type, $message, $template);
    }

    /**
     * Returns the tables form.
     *
     * The return have:
     * <pre>
     * - type     => The type of the message (error or success).
     * - message  => The message.
     * - template => The template to show.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonTablesFormAction()
    {
        $this->view->message      = array();
        $this->view->success      = array();
        $this->view->useExtraData = self::DEFAULT_USE_EXTRA_DATA;

        $message  = null;
        $type     = 'success';
        $template = $this->view->render('tablesForm.phtml');

        $this->returnContent($type, $message, $template);
    }

    /**
     * Validate the params and if is all ok, install the tables.
     *
     * REQUIRES request parameters:
     * <pre>
     *  - integer <b>useExtraData</b> Install extra data or not.
     * </pre>
     *
     * The return have:
     * <pre>
     * - type     => The type of the message (error or success).
     * - message  => The message.
     * - template => The template to show.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonTablesSetupAction()
    {
        $this->view->message = array();
        $this->view->success = array();

        $params = array('useExtraData' => (int) $this->getRequest()->getParam('useExtraData'));

        if (null !== $this->_setup) {
            ob_start();
            $message = $this->_setup->install($params);
            $type    = 'success';
            ob_end_clean();
        } else {
            $this->getResponse()->setHttpResponseCode(403);
            $this->sendResponse();
        }

        $template = $this->view->render('tablesOk.phtml');

        $this->returnContent($type, $message, $template);
    }

    /**
     * Returns the migration form.
     *
     * The return have:
     * <pre>
     * - type     => The type of the message (error or success).
     * - message  => The message.
     * - template => The template to show.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonMigrationFormAction()
    {
        $this->view->message             = array();
        $this->view->success             = array();
        $this->view->migrationConfigFile = '';
        $this->view->diffToUtc           = self::DEFAULT_DIFF_TO_UTC;

        $message  = null;
        $type     = 'success';
        $template = $this->view->render('migrationForm.phtml');

        $this->returnContent($type, $message, $template);
    }

    /**
     * Validate the params and if is all ok, migrate the data.
     *
     * REQUIRES request parameters:
     * <pre>
     *  - string <b>migrationConfigFile</b> File for get the config of P5.
     *  - integer <b>diffToUtc</b> Difference between the server and UTC.
     *  - string <b>module</b> Module to migrate.
     * </pre>
     *
     * The return have:
     * <pre>
     * - type     => The type of the message (error or success).
     * - message  => The message.
     * - template => The template to show.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonMigrateSetupAction()
    {
        $this->view->message = array();
        $this->view->success = array();

        $params = array(
            'migrationConfigFile' => Cleaner::sanitize('string', $this->getRequest()->getParam('migrationConfigFile')),
            'diffToUtc'           => Cleaner::sanitize('integer', $this->getRequest()->getParam('diffToUtc')),
            'module'              => Cleaner::sanitize('string', $this->getRequest()->getParam('module')));

        if (null !== $this->_setup) {
            if ($this->_setup->validateMigration($params)) {
                if (in_array($params['module'], $this->view->exportModules)) {
                    ob_start();
                    $this->_setup->migrate($params);
                    $errors = ob_get_contents();
                    if (!empty($errors)) {
                        $message = $errors;
                        $type    = 'error';
                    } else {
                        $message = "Migration OK";
                        $type    = 'success';
                    }
                    ob_end_clean();
                } else {
                    $message = 'Wrong module';
                    $type    = 'error';
                }
            } else {
                $error   = $this->_setup->getError();
                $message = array_shift($error);
                $type    = 'error';
            }
        } else {
            $this->getResponse()->setHttpResponseCode(403);
            $this->sendResponse();
        }

        $template = $this->view->render('migrationOk.phtml');

        $this->returnContent($type, $message, $template, $params['module']);
    }

    /**
     * Returns the finish template.
     *
     * The return have:
     * <pre>
     * - type     => The type of the message (error or success).
     * - message  => The message.
     * - template => The template to show.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @return void
     */
    public function jsonFinishAction()
    {
        $this->view->message = array();
        $this->view->success = array();

        if (null !== $this->_setup) {
            ob_start();
            $this->_setup->finish();
            $error = $this->_setup->getError();
            if (!empty($error)) {
                $message = array_shift($error);
                $type    = 'notice';
            } else {
                $message = ob_get_contents();
                $type    = 'success';
            }
            ob_end_clean();
        } else {
            $this->getResponse()->setHttpResponseCode(403);
            $this->sendResponse();
        }

        $template = $this->view->render('finish.phtml');

        $this->returnContent($type, $message, $template);
    }

    /**
     * Returns server feedback.
     *
     * The return have:
     * <pre>
     * - type     => The type of the message (error or success).
     * - message  => The message.
     * - template => The template to show.
     * - module   => OPTIONAL, the module installed.
     * </pre>
     *
     * The return is in JSON format.
     *
     * @param string $type     Type of message (error, success, warning).
     * @param string $message  Message to show.
     * @param string $template HTML of the templatate to show.
     * @param string $module   Module installed.
     *
     * @return void
     */
    public function returnContent($type, $message, $template, $module = null)
    {
        $return = array('type'     => $type,
                        'message'  => $message,
                        'template' => $template);

        if (null !== $module) {
            $return['module'] = $module;
        }

        echo '{}&&(' . Zend_Json_Encoder::encode($return) . ')';
    }
}
