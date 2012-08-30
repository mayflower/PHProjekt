<?php
/**
 * This software is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License version 3 as published by the Free Software Foundation
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 *
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * Core Controller.
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
     * @throws Zend_Controller_Action_Exception If the user is not an admin.
     *
     * @return void
     */
    public function preDispatch()
    {
        parent::preDispatch();

        if (!Phprojekt_Auth::isAdminUser()) {
            $valid = false;

            // Add exceptions for public calls into the Core
            $controller = strtolower($this->getRequest()->getControllerName());
            $action     = $this->getRequest()->getActionName();

            if ($controller == 'history' && $action == 'jsonList') {
                $valid = true;
            } else if ($controller == 'module' && $action == 'jsonGetGlobalModules') {
                $valid = true;
            } else if ($controller == 'role' && $action == 'jsonGetModulesAccess') {
                $valid = true;
            } else if ($controller == 'user' && $action == 'jsonGetUsers') {
                $valid = true;
            } else if ($controller == 'user' && $action == 'jsonGetProxyableUsers') {
                $valid = true;
            } else if ($controller == 'tab' && $action == 'jsonList') {
                $valid = true;
            } else if ($controller == 'setting') {
                $valid = true;
            } else if ($controller == 'upgrade') {
                $valid = true;
            }

            if (!$valid) {
                $this->getResponse()->setRawHeader('HTTP/1.1 401 Authorization Require');
                $this->getResponse()->sendHeaders();
                exit;
            }
        }
    }

    /**
     * Gets the core class model of the module.
     *
     * @return Phprojekt_Model_Interface An instance of Phprojekt_Model_Interface.
     */
    public function getModelObject()
    {
        static $moduleName = null;

        if (is_null($moduleName)) {
            $moduleName = ucfirst($this->getRequest()->getControllerName());
            $moduleName = "Phprojekt_" . $moduleName . "_" . $moduleName;
        }
        if (Phprojekt_Loader::tryToLoadLibClass($moduleName)) {
            $db = Phprojekt::getInstance()->getDb();
            return new $moduleName($db);
        } else {
            throw new Exception('No model object could be found');
        }
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
