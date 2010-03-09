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
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
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
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
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
        $this->_helper->viewRenderer->setNoRender();
        $this->view->clearVars();

        $webPath = "http://" . $_SERVER['HTTP_HOST'] . str_replace('setup.php', '', $_SERVER['SCRIPT_NAME']);

        $this->view->webPath = $webPath;

        try {
            $this->_setup        = new Setup_Models_Setup();
            $this->view->message = nl2br($this->_setup->getMessage());
        } catch (Exception $error) {
            $this->view->error = nl2br($error->getMessage());
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
        $this->view->testPass         = '';
        $this->view->testPassConfirm  = '';
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

        if (null !== $this->_setup) {
            if ($this->_setup->validate($params)) {
                ob_start();
                $this->_setup->install($params);
                $this->view->success = ob_get_contents();
                ob_end_clean();
                $this->view->finish  = true;
                $this->view->message = null;
            } else {
                $error               = $this->_setup->getError();
                $this->view->message = array_shift($error);
            }
        }
        $this->view->dbHost              = $params['dbHost'];
        $this->view->dbUser              = $params['dbUser'];
        $this->view->dbPass              = $params['dbPass'];
        $this->view->dbName              = $params['dbName'];
        $this->view->adminPass           = $params['adminPass'];
        $this->view->adminPassConfirm    = $params['adminPassConfirm'];
        $this->view->testPass            = $params['testPass'];
        $this->view->testPassConfirm     = $params['testPassConfirm'];
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
            'dbHost'              => Cleaner::sanitize('string', $this->getRequest()->getParam('dbHost')),
            'dbUser'              => Cleaner::sanitize('string', $this->getRequest()->getParam('dbUser')),
            'dbPass'              => Cleaner::sanitize('string', $this->getRequest()->getParam('dbPass')),
            'dbName'              => Cleaner::sanitize('string', $this->getRequest()->getParam('dbName')),
            'adminPass'           => Cleaner::sanitize('string', $this->getRequest()->getParam('adminPass')),
            'adminPassConfirm'    => Cleaner::sanitize('string', $this->getRequest()->getParam('adminPassConfirm')),
            'testPass'            => Cleaner::sanitize('string', $this->getRequest()->getParam('testPass')),
            'testPassConfirm'     => Cleaner::sanitize('string', $this->getRequest()->getParam('testPassConfirm')),
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
