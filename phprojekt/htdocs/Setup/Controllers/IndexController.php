<?php
/**
 * Default Controller for the Setup
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
 * @license    LGPL 2.1 (See LICENSE file)
 * @version    $Id$
 * @author     Gustavo Solt <solt@mayflower.de>
 * @package    Setup
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Default Controller for the Setup
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    Setup
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class IndexController extends Zend_Controller_Action
{
    private $_setup = null;

    /**
     * Do some checks in the begin
     *
     * @return void
     */
    public function init()
    {
        if (file_exists(PHPR_CONFIG_FILE)) {
            throw new Exception('Config file exists');
        }

        $this->_helper->viewRenderer->setNoRender();
        $this->view->clearVars();

        $webPath = "http://" . $_SERVER['HTTP_HOST'] . str_replace('setup.php', '', $_SERVER['SCRIPT_NAME']);

        $this->view->webPath = $webPath;

        try {
            $this->_setup = new Setup_Models_Setup();
        } catch (Exception $error) {
            $this->view->error = $error->getMessage();
        }
    }

    /**
     * Default action
     *
     * @return void
     */
    public function indexAction()
    {
        $this->view->dbHost           = 'localhost';
        $this->view->dbUser           = 'phprojekt';
        $this->view->dbPass           = '';
        $this->view->dbName           = 'phprojekt';
        $this->view->adminPass        = '';
        $this->view->adminPassConfirm = '';
        $this->view->useExtraData     = 1;
    }

    /**
     * Try to install all
     * Show finish (success) or message (error)
     *
     * @return void
     */
    public function installAction()
    {
        $params = $this->_setParams();

        if ($this->_setup->validate($params)) {
            ob_start();
            $this->_setup->install($params);
            $this->view->success = ob_get_flush();
            $this->view->finish  = true;
        } else {
            $this->view->message = array_shift($this->_setup->getError());
        }
        $this->view->dbHost              = $params['dbHost'];
        $this->view->dbUser              = $params['dbUser'];
        $this->view->dbPass              = $params['dbPass'];
        $this->view->dbName              = $params['dbName'];
        $this->view->adminPass           = $params['adminPass'];
        $this->view->adminPassConfirm    = $params['adminPassConfirm'];
        $this->view->useExtraData        = $params['useExtraData'];
        $this->view->migrationConfigFile = $params['migrationConfigFile'];
    }

    /**
     * Sanitize all the parsams
     *
     * @return array
     */
    private function _setParams() {
        return array(
            'serverType'          => Cleaner::sanitize('string', $this->getRequest()->getParam('serverType')),
            'dbHost'              => Cleaner::sanitize('alnum', $this->getRequest()->getParam('dbHost')),
            'dbHost'              => Cleaner::sanitize('alnum', $this->getRequest()->getParam('dbHost')),
            'dbUser'              => Cleaner::sanitize('alnum', $this->getRequest()->getParam('dbUser')),
            'dbPass'              => Cleaner::sanitize('alnum', $this->getRequest()->getParam('dbPass')),
            'dbName'              => Cleaner::sanitize('alnum', $this->getRequest()->getParam('dbName')),
            'adminPass'           => Cleaner::sanitize('alnum', $this->getRequest()->getParam('adminPass')),
            'adminPassConfirm'    => Cleaner::sanitize('alnum', $this->getRequest()->getParam('adminPassConfirm')),
            'migrationConfigFile' => Cleaner::sanitize('string', $this->getRequest()->getParam('migrationConfigFile')),
            'useExtraData'        => (int) $this->getRequest()->getParam('useExtraData'),
        );
    }

    /**
     * Render always the index template
     *
     * @return void
     */
    public function postDispatch()
    {
        $this->render('index');
    }
}
