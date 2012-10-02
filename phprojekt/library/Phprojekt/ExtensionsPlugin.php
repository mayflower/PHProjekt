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
 * @copyright  Copyright (c) 2011 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 */

/**
 * This zend controller plugin is used to implement the phprojekt extensions.
 *
 * It is also used to check whether we need to redirect the user to the
 * migration screen. This is because the check uses extensions, but must be done
 * before init is called, as the modules will assume that they have a current
 * database.
 */
class Phprojekt_ExtensionsPlugin extends Zend_Controller_Plugin_Abstract
{
    private $_extensions;

    public function __construct()
    {
        /* initialize PHPRojekt Extensions */
        $this->_extensions = new Phprojekt_Extensions(PHPR_CORE_PATH);
    }

    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        /* Redirect to the upgrade controller if an upgrade is neccessary */
        if (Phprojekt_Auth::isLoggedIn()
                && ($request->getModuleName() != 'Core'
                    || $request->getControllerName() != 'Upgrade')
                && ($request->getControllerName() != 'Login'
                    || $request->getActionName() != 'logout')) {
            $migration = new Phprojekt_Migration($this->_extensions);
            if ($migration->needsUpgrade()) {
                $this->_request->setModuleName('Core');
                $this->_request->setControllerName('Upgrade');
                $this->_request->setActionName('index');
            }
        }
    }

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        // Call the init method on every extension
        $this->_extensions->init();
    }
}
