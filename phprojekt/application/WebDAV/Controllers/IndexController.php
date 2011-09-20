<?php
/**
 * WebDAV Module Controller.
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
 * @subpackage WebDAV
 * @copyright  Copyright (c) 2011 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: @package_version@
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 */

require_once 'Sabre.autoload.php';

/**
 * WebDAV Module Controller.
 *
 * @category   PHProjekt
 * @package    Application
 * @subpackage WebDAV
 * @copyright  Copyright (c) 2011 Mayflower GmbH (http://www.mayflower.de)
 * @license    LGPL v3 (See LICENSE file)
 * @link       http://www.phprojekt.com
 * @since      File available since Release 6.1
 * @version    Release: @package_version@
 * @author     Simon Kohlmeyer <simon.kohlmeyer@mayflower.de>
 */
class WebDAV_IndexController extends IndexController
{
    public function preDispatch()
    {
        parent::preDispatch();
        $this->_helper->viewRenderer->setNoRender(true);
    }

    public function checkAuthentication()
    {
        // Log the user in so that phprojekt recognizes us.
        // If the client doesn't send http headers, he probably has a cookie and assumes to be already logged in
        if (array_key_exists('PHP_AUTH_USER', $_SERVER)) {
            Phprojekt_Auth::login($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
        }
    }

    public function indexAction()
    {
        // Set the root directory
        $webdavPath    = Phprojekt::getInstance()->getConfig()->webdavPath;
        if (Phprojekt_Auth::isLoggedIn()) {
            $project       = new Project_Models_Project();
            $project       = $project->find(1);
            $rootDirectory = new WebDAV_Models_ProjectDirectory($project);
        } else {
            // Some clients seem to send some queries without http auth. We need the dummy to serve those.
            $rootDirectory = new WebDAV_Models_EmptyDir();
        }

        // The server object is responsible for making sense out of the WebDAV protocol
        $server = new Sabre_DAV_Server($rootDirectory);
        $server->setBaseUri(PHPR_ROOT_WEB_BASE_PATH . 'WebDAV/index/index/');

        // The lock manager is reponsible for making sure users don't overwrite each others changes.
        // Change 'data' to a different directory, if you're storing your data somewhere else.
        $lockBackend = new Sabre_DAV_Locks_Backend_File($webdavPath . 'data/locks');
        $lockPlugin  = new Sabre_DAV_Locks_Plugin($lockBackend);
        $server->addPlugin($lockPlugin);

        // Authentication
        $authBackend = new WebDAV_Helper_Auth();
        $authPlugin  = new Sabre_DAV_Auth_Plugin($authBackend, 'WebDAV');
        $server->addPlugin($authPlugin);

        // All we need to do now, is to fire up the server
        $server->exec();
    }
}
