<?php
/**
 * Core Controller.
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
 * @package    Application
 * @subpackage Core
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */

/**
 * Core Controller.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Core
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.0
 * @version    Release: @package_version@
 * @author     Gustavo Solt <solt@mayflower.de>
 */
class Core_IndexController extends IndexController
{
    /**
     * Init function.
     *
     * There are only a few actions that a normal user can do requesting the Core controller.
     * The function check them, and allow the acction or not,
     * if not, the user is redirected to the login form or throws an exception.
     *
     * @throws Phprojekt_PublishedException If the user is not an admin.
     *
     * @return void
     */
    public function preDispatch()
    {
        parent::preDispatch();

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
            } else if ($this->getRequest()->getControllerName() == 'setting') {
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
     * Gets the core class model of the module or the default one.
     *
     * @return Phprojekt_Model_Interface An instance of Phprojekt_Model_Interface.
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

    /**
     * Keep in the session the current project ID.
     *
     * @return void
     */
    public function setCurrentProjectId()
    {
        Phprojekt::setCurrentProjectId(self::INVISIBLE_ROOT);
    }
}
