<?php
/**
 * Core Controller for PHProjekt 6.0
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
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 */

/**
 * Core Controller for PHProjekt 6.0
 *
 * @copyright  Copyright (c) 2008 Mayflower GmbH (http://www.mayflower.de)
 * @version    Release: @package_version@
 * @license    LGPL 2.1 (See LICENSE file)
 * @package    PHProjekt
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Core_IndexController extends IndexController
{
    /**
     * Add Check for see if the current user is an admin
     * If not, go to the login page
     *
     * @return void
     */
    public function init()
    {
        parent::init();

        if (!Phprojekt_Auth::isAdminUser()) {
            $valid = false;
            // Add exceptions for public calls into the Core
            if ($this->getRequest()->getControllerName() == 'history' &&
                $this->getRequest()->getActionName() == 'jsonList') {
                $valid = true;
            } else if ($this->getRequest()->getControllerName() == 'module' &&
                $this->getRequest()->getActionName() == 'jsonGetGlobalModules') {
                $valid = true;
            } else if ($this->getRequest()->getControllerName() == 'role' &&
                $this->getRequest()->getActionName() == 'jsonGetModulesAccess') {
                $valid = true;
            } else if ($this->getRequest()->getControllerName() == 'user' &&
                $this->getRequest()->getActionName() == 'jsonGetUsers') {
                $valid = true;
            } else if ($this->getRequest()->getControllerName() == 'tab' &&
                $this->getRequest()->getActionName() == 'jsonList') {
                $valid = true;
            }

            if (!$valid) {
                // If is a GET, show the login page
                // If is a POST, send message in json format
                if (!$this->getFrontController()->getRequest()->isGet()) {
                    throw new Phprojekt_PublishedException('Admin section is only for admin users', 500);
                } else {
                    $this->_redirect(Phprojekt::getInstance()->getConfig()->webpath . 'index.php/Login/logout');
                }
                exit;
            }
        }
    }

    /**
     * Get the model object
     * This function must be redefined in each module
     *
     * @return Phprojekt_Model_Interface
     */
    public function getModelObject()
    {
        static $object = null;
        if (null === $object) {
            $moduleName = ucfirst($this->getRequest()->getControllerName());
            $moduleName = "Phprojekt_" . $moduleName . "_" . $moduleName;
            if (Phprojekt_Loader::tryToLoadLibClass($moduleName)) {
                $db     = Phprojekt::getInstance()->getDb();
                $object = new $moduleName($db);
            } else {
                $object = null;
            }
            if (null === $object) {
                $object = Phprojekt_Loader::getModel('Default', 'Default');
            }
        }
        return $object;
    }
}
