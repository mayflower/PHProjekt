<?php
/**
 * Calendar2 Module CalDAV Controller.
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
 * @subpackage Calendar2
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: @package_version@
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 */
require_once 'Sabre.autoload.php';

/**
 * Calendar2 Module CalDAV Controller.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage Calendar2
 * @copyright  Copyright (c) 2010 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: @package_version@
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 */
class Calendar2_CaldavController extends IndexController
{
    public function preDispatch()
    {
        $this->_helper->viewRenderer->setNoRender(true);
    }

    public function checkAuthentication()
    {
    }

    public function indexAction() {
        // This is mostly copied from the sabredav wiki
        // Backends
        $authBackend = new Calendar2_CalDAV_Auth();
        $principalBackend = new Calendar2_CalDAV_PrincipalBackend();
        $calendarBackend = new Calendar2_CalDAV_CalendarBackend();

        // Directory tree
        $tree = array(
                new Sabre_DAVACL_PrincipalCollection($principalBackend),
                new Sabre_CalDAV_CalendarRootNode($principalBackend, $calendarBackend)
        );
        $server = new Sabre_DAV_Server($tree);

        $server->setBaseUri('/index.php/Calendar2/caldav/index');

        // Authentication plugin
        $authPlugin = new Sabre_DAV_Auth_Plugin($authBackend,'Calendar2/CalDAV');
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
