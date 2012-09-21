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
require_once 'Sabre.autoload.php';

/**
 * Calendar2 Module CalDAV Controller.
 */
class Calendar2_CaldavController extends IndexController
{
    /**
     * Overwrite preDispatch from the indexController.
     * We need to stop the view from rendering.
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    /**
     * Overwrite checkAuthentication.
     * We don't use the normal authentication. Instead, we have to authenticate the user based on httpauth data.
     */
    public function checkAuthentication()
    {
        try {
            if (array_key_exists('PHP_AUTH_USER', $_SERVER)) {
                Phprojekt_Auth::login($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
                $_SERVER['PHP_AUTH_USER'] = strtolower(Phprojekt_Auth::getRealUser()->username);
            }
        } catch (Phprojekt_Auth_Exception $e) {
            // We have to delete the stack trace here because we need to avoid logging the user's password.
            // This would be done because of Phprojekt_Auth::login($user, $password)
            throw new Phprojekt_Auth_Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Fire up the SabreDAV server with our custom backends.
     *
     * This is mostly copied from the sabredav wiki
     */
    public function indexAction()
    {
        // Backends
        $authBackend      = new WebDAV_Helper_Auth();
        $principalBackend = new Phprojekt_CalDAV_PrincipalBackend();
        $calendarBackend  = new Calendar2_CalDAV_CalendarBackend();

        // Directory tree
        $tree = array(
                new Sabre_DAVACL_PrincipalCollection($principalBackend),
                new Sabre_CalDAV_CalendarRootNode($principalBackend, $calendarBackend)
        );
        $server = new Sabre_DAV_Server($tree);

        $server->setBaseUri('/index.php/Calendar2/caldav/index');

        // Authentication plugin
        $authPlugin = new Sabre_DAV_Auth_Plugin($authBackend, 'CalDAV');
        $server->addPlugin($authPlugin);

        // CalDAV plugin
        $caldavPlugin = new Sabre_CalDAV_Plugin();
        $server->addPlugin($caldavPlugin);

        // ACL plugin
        $aclPlugin = new Sabre_DAVACL_Plugin();
        $server->addPlugin($aclPlugin);

        // Support for html frontend
        $browser = new Sabre_DAV_Browser_Plugin();
        $server->addPlugin($browser);

        // And off we go!
        $server->exec();
    }
}
